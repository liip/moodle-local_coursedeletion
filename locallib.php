<?php
// This file is part of local/coursedeletion
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package local/coursedeletion
 * @copyright 2014-2018 Liip AG <https://www.liip.ch/>
 * @author Brian King <brian.king@liip.ch>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Class CourseDeletion
 *
 *                phase 1                   phase 2                phase 3
 *      {      13 months (or setting)                    }
 *                                      {    3 weeks     }{         3 months            }
 *      * . . . . . . . . . . . . . . . *  . . . . . . . * . . . . . . . . . . . . . . .*
 *      |                               |                |                              |
 * [course created / reset]             |              [course end date]              [course deleted]
 *                                      |              moved to deletion-staging      (mail_was_deleted)
 *                            (mail_will_be_staged)    (mail_will_be_deleted_soon)
 *
 * In case the cron job does not run for a few days or weeks for some reason, the course end date will
 * be reset so that teachers / managers will have time to react.
 * Example: cron didn't run for some weeks:
 *   * end_date was 2014-07-01
 *   * cron starts running again on 2014-07-12
 *   * end_date is reset to 2014-07-12 + 3 weeks, and teachers / managers  are sent mail about upcoming move to deletion-staging.
 * Example 2: cron didn't run for two weeks:
 *   * end_date was 2014-07-24
 *   * cron starts running again on 2014-07-30
 *   * end_date is reset to 2014-07-30, course is moved to deletion-staging, teachers / managers notified.
 *
 */
class CourseDeletion {

    const STATUS_NOT_SCHEDULED = 0;
    const STATUS_SCHEDULED = 1;
    const STATUS_SCHEDULED_NOTIFIED = 2;
    const STATUS_STAGED_FOR_DELETION = 3;

    const MAIL_WILL_BE_STAGED_FOR_DELETION = 'mail_will_be_staged';
    const MAIL_WILL_SOON_BE_DELETED = 'mail_will_be_deleted_soon';
    const MAIL_WAS_DELETED = 'mail_was_deleted';

    /**
     * @var int $deletion_staging_category_id
     */
    protected $deletion_staging_category_id;

    /**
     * @var coursecat $deletion_staging_category
     */
    protected $deletion_staging_category;

    /**
     * @var array of integers: ids of role records (editingteacher, ...)
     */
    protected $notification_user_role_ids;

    /**
     * @var array of strings: short names of roles that should receive notifications.
     */
    protected $notification_user_role_names = array(
        'teacher',
        'keyholder',
        'manager',
    );

    /**
     * @var stdClass db user record that will be used as the sender of the notification mails.
     */
    protected $email_from_user;

    /**
     * @var string $contacturl URL used in mails to help people find who they should contact if they have questions.
     */
    protected $contacturl;

    /**
     * @var int verbosity: 0 be quiet, 1 echo output about some actions
     */
    protected $verbosity;

    function __construct($deletion_staging_category_id = null, $verbosity = 1) {
        if (is_null($deletion_staging_category_id)) {
            $deletion_staging_category_id = get_config('local_coursedeletion', 'deletion_staging_category_id');
        }
        $this->deletion_staging_category_id = $deletion_staging_category_id;
        $this->verbosity = $verbosity;
    }

    /**
     * Lazy instantiation of course category
     */
    protected function staging_category() {
        if (is_null($this->deletion_staging_category)) {
            $this->deletion_staging_category = coursecat::get($this->deletion_staging_category_id);
        }
    }

    public function deletion_staging_category_id() {
        return $this->deletion_staging_category_id;
    }

    public function course_is_in_deletion_staging_category($courseid) {
        global $DB;

        $delstagecontext = context_coursecat::instance($this->deletion_staging_category_id);

        $sql = "
            SELECT 1
              FROM {course} c
              JOIN {context} ctx on ctx.instanceid = c.id
             WHERE c.id = :courseid
               AND ctx.contextlevel = :contextlevel
               AND ctx.path LIKE :stage_cat_ctx_path
        ";
        $params = array(
            'courseid' => $courseid,
            'contextlevel' => CONTEXT_COURSE,
            'stage_cat_ctx_path' => "$delstagecontext->path/%"
        );
        return $DB->get_record_sql($sql, $params) != false;

    }

    /**
     * Remove any records from the coursedeletion table for courses which no longer exist.
     */
    public function remove_records_for_missing_courses() {
        global $DB;

        $this->out('remove_records_for_missing_courses start');

        $orphans = $DB->get_records_sql("
              SELECT lcd.*
                FROM {local_coursedeletion} lcd
           LEFT JOIN {course} c ON lcd.courseid = c.id
               WHERE c.id IS NULL
        ");

        if (count($orphans)) {
            list($in, $params) = $DB->get_in_or_equal(array_keys($orphans));
            foreach ($orphans as $orphan) {
                local_coursedeletion\event\course_delete::create(array(
                    // record the course id instead of the id of this record
                    'objectid' => $orphan->courseid,
                    'other' => array(
                        'courseid' => $orphan->courseid,
                        'detail' => 'removed record for no longer existant course'
                    )
                ))->trigger();
            }
            $DB->execute("DELETE FROM {local_coursedeletion} WHERE id $in", $params);
            $this->out(count($orphans) . " records removed for no longer existant courses");
        }
        $this->out('remove_records_for_missing_courses end');
    }

    /**
     * Change the status to STATUS_SCHEDULED for any courses that are not in the staging category
     * but currently have the status STATUS_STAGED_FOR_DELETION.  (e.g. courses that were moved
     * out of the deletion staging area).  Also reset their end date.
     */
    public function reset_status_for_unstaged_courses() {
        global $DB;

        $this->out('reset_status_for_unstaged_courses start');

        $delstagecontext = context_coursecat::instance($this->deletion_staging_category_id);
        $sql = "
          SELECT lcd.*
            FROM {local_coursedeletion} lcd
            JOIN {course} c ON c.id = lcd.courseid
            JOIN {context} ctx ON ctx.contextlevel = :contextlevel AND ctx.instanceid = c.id
           WHERE lcd.status = :status_staged
             AND ctx.path NOT LIKE :stage_cat_ctx_path
        ";
        $params = array(
            'status_staged' => self::STATUS_STAGED_FOR_DELETION,
            'contextlevel' => CONTEXT_COURSE,
            'stage_cat_ctx_path' => "$delstagecontext->path/%"
        );
        $unstaged = $DB->get_records_sql($sql, $params);

        if (count($unstaged)) {
            list($in, $params) = $DB->get_in_or_equal(array_keys($unstaged), SQL_PARAMS_NAMED);
            $params['status_scheduled'] = self::STATUS_SCHEDULED;
            $params['enddate'] = self::default_course_end_date();
            $sql = "
                UPDATE {local_coursedeletion} lcd
                   SET status = :status_scheduled,
                       enddate = :enddate
                 WHERE id $in
            ";
            $DB->execute($sql, $params);
            foreach ($unstaged as $coursedeletion) {
                self::log($coursedeletion, 'settings_update', 'status reset from STAGED to SCHEDULED');
            }
            $this->out("Un-staged courses: status reset from STAGED to SCHEDULED: "
                . implode(',', array_keys($unstaged)));
        }
        $this->out('reset_status_for_unstaged_courses end');
    }

    /**
     * Three weeks before Verfallsdatum, send mail to all notification users in the course, telling them that
     * the course will be moved to the trash soon.
     */
    public function prenotify_users_of_staging() {
        global $DB;

        $this->out('prenotify_users_of_staging start');

        $staging_time = self::midnight_timestamp(self::interval_until_staging());

        $sql = "SELECT * from {local_coursedeletion} lcd
             WHERE lcd.status = ?
               AND lcd.enddate <= ?";

        if ($records = $DB->get_records_sql($sql, array(self::STATUS_SCHEDULED, $staging_time))) {
            $records = $this->reset_enddates(
                $records,
                $staging_time,
                $staging_time
            );
            $this->add_course_info($records);
            $this->send_mail_to_notification_users($records, self::MAIL_WILL_BE_STAGED_FOR_DELETION);

            list($in, $params) = $DB->get_in_or_equal(array_keys($records), SQL_PARAMS_NAMED);
            $params['status'] = self::STATUS_SCHEDULED_NOTIFIED;

            $sql = "
                UPDATE {local_coursedeletion}
                   SET status = :status
                 WHERE id $in
            ";
            $DB->execute($sql, $params);

            foreach ($records as $rec) {
                self::log($rec, 'workflow_notify', 'users_prenotified');
                $this->out("Course $rec->courseid notification users informed of course end");
            }
        }
        $this->out('prenotify_users_of_staging end');
    }

    /**
     * On the Verfallsdatum, move course to course deletion staging category (i.e. trashcan).
     * Also, inform the notification users that this has been done.
     */
    public function stage_courses_for_deletion($courseid = null) {
        global $CFG, $DB;
        require_once($CFG->libdir . '/coursecatlib.php');

        $this->out('stage_courses_for_deletion start');

        $expected_end_date = self::midnight_timestamp();
        $sql = "
            SELECT * from {local_coursedeletion} lcd
             WHERE lcd.status = ?
               AND lcd.enddate <= ?
        ";
        $params = array(self::STATUS_SCHEDULED_NOTIFIED, $expected_end_date);
        if ($courseid) {
            $sql .= " AND lcd.courseid = ?";
            $params[] = $courseid;
        }
        if ($records = $DB->get_records_sql($sql, $params)) {
            $records = $this->reset_enddates($records, $expected_end_date, $expected_end_date);
            $this->add_course_info($records);
            $deletionstage = coursecat::get(get_config('local_coursedeletion', 'deletion_staging_category_id'));
            $this->send_mail_to_notification_users($records, self::MAIL_WILL_SOON_BE_DELETED);
            $this->make_courses_invisible($records);
            $this->move_to_staging_area($records, $deletionstage->id);
            foreach ($records as $rec) {
                self::log($rec, 'settings_update', 'moved to deletion staging category');
                self::log($rec, 'workflow_notify', 'users_notified_of_move_to_trash');
                $this->out("Course $rec->courseid moved to deletion staging category");
            }

            // update the status of the records
            list($in, $params) = $DB->get_in_or_equal(array_keys($records), SQL_PARAMS_NAMED);
            $params['status'] = self::STATUS_STAGED_FOR_DELETION;
            $DB->execute("UPDATE {local_coursedeletion} set status = :status WHERE id $in", $params);
        }
        $this->out('stage_courses_for_deletion end');
    }

    /**
     * Three months after Verfallsdatum, permanently delete course.
     */
    public function delete_courses() {
        global $DB;

        $this->out('delete_courses start');

        $delstagecontext = context_coursecat::instance($this->deletion_staging_category_id);

        $time = self::timestamp_for_courses_needing_deletion();

        // This selects records that are marked as due for deletion (and that
        // are actually in the staged-for-deletion category).
        $sql = "
            SELECT lcd.*
              FROM {local_coursedeletion} lcd
              JOIN {course} c ON lcd.courseid = c.id
              JOIN {context} ctx on ctx.instanceid = c.id
             WHERE lcd.status = :status
               AND lcd.enddate <= :timestamp
               AND ctx.contextlevel = :contextlevel
               AND ctx.path LIKE :stage_cat_ctx_path
        ";
        $params = array(
            'status' => self::STATUS_STAGED_FOR_DELETION,
            'timestamp' => $time,
            'contextlevel' => CONTEXT_COURSE,
            'stage_cat_ctx_path' => "$delstagecontext->path/%"
        );

        if ($records = $DB->get_records_sql($sql, $params)) {
            // The user and course information will be needed to send them a mail
            $this->add_course_info($records);
            $deleted = $this->run_course_deletion($records);
            if (count($deleted)) {
                $this->send_mail_to_notification_users($deleted, self::MAIL_WAS_DELETED);
                $courseids = array();
                foreach ($deleted as $rec) {
                    $courseids[] = $rec->courseid;
                    // Improved logging interface in 2.7?  Not so sure ...
                    local_coursedeletion\event\course_delete::create(array(
                        // record the course id instead of the id of this record
                        'objectid' => $rec->courseid,
                        'other' => array(
                            'courseid' => $rec->courseid,
                            'detail' => 'Course deleted'
                        )
                    ))->trigger();
                    $this->out("Course $rec->courseid deleted");
                }
                $DB->delete_records_list('local_coursedeletion', 'courseid', $courseids);
            }
        }
        $this->out('delete_courses end');
    }

    public function send_mail_to_notification_users($delrecords, $mailtype) {

        $from = $this->email_from_user();
        foreach ($delrecords as $rec) {
            if (empty($rec->notification_users)) {
                local_coursedeletion\event\workflow_notify_error::create(array(
                    // record the course id instead of the id of this record
                    'objectid' => $rec->courseid,
                    'other' => array(
                        'courseid' => $rec->courseid,
                        'detail' => "No notification users found for course $rec->courseid",
                    )
                ))->trigger();
                continue;
            }

            $course = $rec->course_record;
            $a = new stdClass;
            $a->coursefullname = $course->fullname;
            $url1 = new moodle_url('/course/view.php', array('id' => $course->id));
            $a->courseurl = $url1->out();
            $a->contacturl = $this->contact_url();
            $url2 = new moodle_url('/local/coursedeletion/coursesettings.php', array('id' => $course->id));
            $a->settingsurl = $url2->out();
            if ($mailtype != self::MAIL_WAS_DELETED) {
                $a->stagedate = self::date_course_will_be_staged_for_deletion($rec->enddate)->format('d.m.Y');
                $a->deletiondate = self::date_course_will_be_deleted($rec->enddate)->format('d.m.Y');
            }

            foreach ($rec->notification_users as $user) {
                $a->userfullname = fullname($user);
                $subject = get_string($mailtype . '_subject', 'local_coursedeletion', $a);
                $body = get_string($mailtype . '_body', 'local_coursedeletion', $a);
                $body_html = nl2br(get_string($mailtype . '_body_html', 'local_coursedeletion', $a));
                if (!email_to_user($user, $from, $subject, $body, $body_html)) {
                    local_coursedeletion\event\workflow_notify_error::create(array(
                        // record the course id instead of the id of this record
                        'objectid' => $rec->courseid,
                        'relateduserid' => $user->id,
                        'other' => array(
                            'courseid' => $rec->courseid,
                            'detail' => "Unable to send mail to user id $user->id",
                        )
                    ))->trigger();
                }
                else {
                    if ($mailtype === self::MAIL_WILL_BE_STAGED_FOR_DELETION || $mailtype === self::MAIL_WILL_SOON_BE_DELETED) {
                        // SUP-7120 write one log entry for each mail warning sent
                        self::log($rec, 'workflow_notify', 'user ' . $user->email . ' notified with ' . $mailtype);
                    }
                }
            }
        }
    }

    protected function email_from_user() {
        if (is_null($this->email_from_user)) {
            $conf = get_config('local_coursedeletion');
            if ($conf) {
                if (!$conf->mailfrom_address) {
                    $this->email_from_user = get_admin();
                } else {
                    $mailfrom = new stdClass;

                    // Avoid debugging message:
                    $all_user_name_fields = get_all_user_name_fields();
                    foreach ($all_user_name_fields as $fieldname) {
                        $mailfrom->$fieldname = '';
                    }

                    $mailfrom->maildisplay = 1;
                    $mailfrom->lastname = '';
                    $mailfrom->email = $conf->mailfrom_address;
                    if ($conf->mailfrom_text) {
                        $mailfrom->firstname = $conf->mailfrom_text;
                    } else {
                        $mailfrom->firstname = "No-Reply";
                    }
                    $this->email_from_user = $mailfrom;
                }
            } else {
                $this->email_from_user = get_admin();
            }
        }
        return $this->email_from_user;
    }

    protected function contact_url() {
        if (is_null($this->contacturl)) {
            $this->contacturl = get_config('local_coursedeletion', 'school_contact_url');
        }
        return $this->contacturl;
    }


    protected function get_notification_users($courseid) {
        $context = context_course::instance($courseid);
        $users = array();
        $include_higher_contexts_for_teachers = true;
        $include_higher_contexts_for_other_users = false;
        foreach ($this->course_teacher_role_ids() as $teacher_role_id) {
            $users = array_merge($users, get_role_users($teacher_role_id, $context, $include_higher_contexts_for_teachers));
        }
        foreach ($this->notification_user_role_ids() as $user_role_id) {
            $users = array_merge($users, get_role_users($user_role_id, $context, $include_higher_contexts_for_other_users));
        }
        return $users;
    }

    protected function course_teacher_role_ids() {
        global $DB;

        if (is_null($this->course_teacher_role_ids)) {
            if ($role = $DB->get_record('role', array('shortname' => 'editingteacher'))) {
                $this->course_teacher_role_ids = array($role->id);
            }
        }

        return $this->course_teacher_role_ids;
    }

    protected function notification_user_role_ids() {
        global $DB;

        if (is_null($this->notification_user_role_ids)) {
            $this->notification_user_role_ids = array();
            foreach ($this->notification_user_role_names as $role_name) {
                if ($role = $DB->get_record('role', array('shortname' => $role_name))) {
                    $this->notification_user_role_ids [] = array($role->id);
                }
            }
        }

        return $this->notification_user_role_ids;
    }

    /**
     * Modifies passed $delrecords, adding course_record and notification_users.
     *
     * @param array $delrecords course deletion records
     */
    protected function add_course_info($delrecords) {
        foreach ($delrecords as $record) {
            $record->course_record = get_course($record->courseid);
            $record->notification_users = $this->get_notification_users($record->courseid);
        }
    }

    protected function move_to_staging_area($delrecords, $targetcategoryid) {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/course/lib.php');

        $courseids = array();
        foreach ($delrecords as $rec) {
            $courseids[] = $rec->courseid;
        }

        $transaction = $DB->start_delegated_transaction();
        if (move_courses($courseids, $targetcategoryid) !== true) {
            throw new RuntimeException("Failed to move courses");
        }
        $transaction->allow_commit();
    }

    protected function run_course_deletion($delrecords) {
        global $DB;
        // For now, not wrapping this is a transaction, because the chance of failure is too high,
        // and the result of a course not being deleted is not tragic.
        // $transaction = $DB->start_delegated_transaction();
        $deleted = array();
        foreach ($delrecords as $rec) {
            if (delete_course($rec->courseid, false)) {
                $deleted[] = $rec;
            } else {
                self::log($rec, 'course_delete_error', "Failed to delete course $rec->courseid");
            }
        }
        // $transaction->allow_commit();
        return $deleted;
    }

    protected function make_courses_invisible($delrecords) {
        global $DB;

        $courseids = array();
        foreach ($delrecords as $rec) {
            $courseids[] = $rec->courseid;
        }
        list($in, $params) = $DB->get_in_or_equal($courseids);

        // If visibleold was 1, and the course was moved to a non-hidden category, the course
        // would become visible.
        $sql = "
            UPDATE {course}
               SET visible = 0, visibleold = 0
             WHERE id $in
        ";
        $DB->execute($sql, $params);
    }

    /**
     * Updates records to have enddate to be $set_to_timestamp if endate is less than $expected_min_timestamp.
     * Any updated records are saved to the database.
     *
     * This is done to avoid the course being staged for deletion right after the mail_will_be_staged notification
     * was sent, in case the cron job had not been running for a few days or weeks.
     *
     * @param array $records db records of local_coursedeletion
     * @param $expected_min_timestamp
     * @return array
     */
    protected function reset_enddates($records, $expected_min_timestamp, $set_to_timestamp) {
        global $DB;
        foreach ($records as $record) {
            if ($record->enddate < $expected_min_timestamp) {
                $message = "enddate_too_old, reset (c: $record->courseid): $record->enddate -> $set_to_timestamp";
                $record->enddate = $set_to_timestamp;
                $DB->update_record('local_coursedeletion', $record);
                $this->out($message);
                self::log($record, 'settings_update', $message);
            }
        }
        return $records;
    }

    protected function out($message) {
        if ($this->verbosity) {
            mtrace($message);
        }
    }

    public static function default_course_end_date() {
        return self::midnight_timestamp(self::interval_before_end_date_default());
    }

    /**
     * When a course is created or reset, it's enddate will be set to today + this value.
     *
     * @param bool $negative should this be a negative interval (e.g. -3 weeks)?
     * @return DateInterval like 13 months
     */
    public static function interval_before_end_date_default($negative = false) {
        static $intervalstring;
        if (is_null($intervalstring)) {
            $intervalstring = get_config('local_coursedeletion', 'interval_enddate_default');
        }
        return self::interval($intervalstring, $negative);
    }

    /**
     * The length of time between when the first notification mail is sent, and the course end date.
     *
     * @param bool $negative should this be a negative interval (e.g. -3 weeks)?
     * @return DateInterval like 3 weeks
     */
    public static function interval_until_staging($negative = false) {
        static $intervalstring;
        if (is_null($intervalstring)) {
            $intervalstring = get_config('local_coursedeletion', 'interval_notification_before_enddate');
        }
        return self::interval($intervalstring, $negative);
    }

    /**
     * The time interval from when the course was staged for deletion until the course is deleted.
     *
     * @param bool $negative should this be a negative interval (e.g. -3 weeks)?
     * @return DateInterval like 3 months
     */
    public static function interval_before_deletion($negative = false) {
        static $intervalstring;
        if (is_null($intervalstring)) {
            $intervalstring = get_config('local_coursedeletion', 'interval_staged_to_deletion');
        }
        return self::interval($intervalstring, $negative);
    }

    /**
     * @param $intervalstring
     * @param bool $negative should this be a negative period (e.g. -3 weeks)?
     * @return DateInterval
     */
    public static function interval($intervalstring, $negative = false) {
        $interval = new DateInterval($intervalstring);
        if ($negative) {
            $interval->invert = 1;
        }
        return $interval;
    }

    /**
     * Calculates date on which the date will be moved the deletion staging category.
     *
     * @param int $enddate_timestamp unix timestamp
     * @return DateTime
     */
    public static function date_course_will_be_staged_for_deletion($enddate_timestamp) {
        return self::midnight(null, $enddate_timestamp);
    }

    /**
     * Calculates date on which the course will be deleted.
     *
     * @param int $enddate_timestamp unix timestamp
     * @return DateTime
     */
    public static function date_course_will_be_deleted($enddate_timestamp) {
        $staged = self::date_course_will_be_staged_for_deletion($enddate_timestamp);
        return $staged->add(self::interval_before_deletion());
    }

    public static function first_notification_date($enddate_timestamp) {
        $staged = self::date_course_will_be_staged_for_deletion($enddate_timestamp);
        return $staged->sub(self::interval_until_staging());
    }

    public static function today_is_in_notification_before_staging_period($enddate_timestamp) {
        $notification_timestamp = self::first_notification_date($enddate_timestamp)->getTimestamp();
        $staging_timestamp = self::date_course_will_be_staged_for_deletion($enddate_timestamp)->getTimestamp();
        $today = self::midnight_timestamp();
        return $today > $notification_timestamp && $today < $staging_timestamp;
    }




    /**
     * Calculates unix timestamp representing midnight on a day some time ago.
     * Courses that are staged for deletion, and that have an enddate <=  the returned date, can be deleted.
     *
     * @return int
     */
    public static function timestamp_for_courses_needing_deletion() {
        return self::midnight(self::interval_before_deletion(true))->getTimestamp();
    }

    /**
     * Calculates unix timestamp corresponding to midnight on the day
     * determined by $interval and optionally $time
     *
     * @param null|DateInterval $interval now, if null
     * @param null|int $time unix timestamp
     * @return int
     */
    public static function midnight_timestamp($interval = null, $time = null) {
        return self::midnight($interval, $time)->getTimestamp();
    }

    /**
     * Gets a DateTime object corresponding to midnight on the day
     * determined by $interval and optionally $time
     *
     * @param null|DateInterval $interval now, if null
     * @param null|int $time unix timestamp
     * @return DateTime
     */
    public static function midnight($interval = null, $time = null) {
        $date = new DateTime();
        if (!is_null($time)) {
            $date->setTimestamp($time);
        }

        $date->setTime(0,0,0);

        if (is_null($interval)) {
            return $date;
        }
        return $date->add($interval);
    }

    /**
     * @param int|null $unix_timestamp
     * @return DateTime
     */
    public static function midnight_before_timestamp($unix_timestamp = null) {
        return self::midnight(null, $unix_timestamp);
    }

    /**
     * Create a new record in the course deletion table.
     *
     * @param $courseid
     * @param mixed $status if null, self::STATUS_SCHEDULED.  Otherwise, pass one of self::STATUS_*.
     * @return stdClass
     */
    public static function create_record($courseid, $status = null) {
        global $DB;

        if (is_null($status)) {
            $status = self::STATUS_SCHEDULED;
        }

        $rec = new stdClass;
        $rec->courseid = $courseid;
        $rec->status = $status;
        $rec->enddate = self::default_course_end_date();
        $rec->id = $DB->insert_record('local_coursedeletion', $rec);
        return $rec;
    }


    public static function delete_record($courseid) {
        global $DB;
        return $DB->delete_records('local_coursedeletion', array('courseid' => $courseid));
    }

    /**
     * Handle course reset.
     *
     * Currently deletes any old record, and inserts a new one.
     *
     * @param $courseid
     * @return stdClass
     */
    public static function reset_course($courseid) {
        self::delete_record($courseid);
        return self::create_record($courseid);
    }

    /**
     * @param $new_timestamp
     * @param $status
     * @return array
     * @throws UnexpectedValueException
     */
    public static function minimum_settable_enddate($new_timestamp, $old_timestamp, $status) {

        $timestamp = $new_timestamp;
        switch ($status) {
            case self::STATUS_NOT_SCHEDULED:
                // If not enabled, store anything - the date provided in the form to re-enable will always
                // show e.g. 13 months from the current date.
                break;
            case self::STATUS_SCHEDULED:
                // Don't set the date so far back that the full normal notification period is cut short.
                $minimum = self::midnight(self::interval_until_staging())->add(self::interval('P1D'))->getTimestamp();
                $timestamp = max($new_timestamp, $minimum);
                break;
            case self::STATUS_SCHEDULED_NOTIFIED:
                // A first notification mail was already sent.
                // Don't set the date further back than the currently set enddate.  This enforces
                // that the full notification period elapses before phase 3 is entered.
                $timestamp = max($new_timestamp, $old_timestamp);
                break;
            case self::STATUS_STAGED_FOR_DELETION:
                // Don't set the date so far back that the full normal notification period is cut short.
                // This enforces that the course is not deleted earlier than someone expects it to be (unless
                // someone *manually* deletes the course).
                $timestamp = max($new_timestamp, $old_timestamp);
                break;
            default:
                throw new UnexpectedValueException("Unrecognized status ($status)");
                break;
        }
        return array(
            $timestamp,
            $timestamp != $new_timestamp  // bool: was it adjusted to a later date?
        );
    }

    public static function log($coursedeletion, $action, $info = null) {
        // add_to_log($coursedeletion->courseid, 'coursedeletion', $action, '', $info, 0, $relateduserid);
        $data = array(
            'objectid' => $coursedeletion->id,
            'courseid' => $coursedeletion->courseid,
            'context'  => context_course::instance($coursedeletion->courseid),
        );
        if (!is_null($info)) {
            $data['other'] = array('detail' => $info);
        }

        /* @var \core\event\base @classname */
        $classname = 'local_coursedeletion\event\\' . $action;

        $classname::create($data)->trigger();
    }

    /**
     * @param stdClass &$coursedeletion
     * @param stdClass $formvalues
     * @return array
     */
    public static function update_from_form(&$coursedeletion, $formvalues, CourseDeletion $cd) {
        global $DB;

        $info = array(
            'changes' => array(),
            'minimum_date_forced' => null,
            'trigger_mail' => null
        );

        $is_staged_for_deletion = $cd->course_is_in_deletion_staging_category($coursedeletion->courseid);
        if ($formvalues->scheduledeletion) {
            $scheduled_changed_to_yes = false;
            if ($coursedeletion->status == self::STATUS_NOT_SCHEDULED) {
                $info['changes']['do_delete'] = 'do_delete: no to yes';
                $scheduled_changed_to_yes = true;
            }

            // If the course is already staged for deletion, send another mail with the recalculated date
            // and leave the status as-is.
            // If not yet staged, set the status to scheduled for staging deletion (if anything changed).
            if ($is_staged_for_deletion) {
                $coursedeletion->status = self::STATUS_STAGED_FOR_DELETION;
                $info['changes']['status'] = 'status: staged_for_deletion, mail resent';
            } else if ($scheduled_changed_to_yes) {
                $coursedeletion->status = self::STATUS_SCHEDULED;
                $info['changes']['status'] = 'status: scheduled';
            }

            list ($enddate, $minimum_date_was_forced) = self::minimum_settable_enddate($formvalues->deletionstagedate, $coursedeletion->enddate, $coursedeletion->status);
            if ($minimum_date_was_forced) {
                $info['minimum_date_forced'] = $enddate;
            }
            $enddate_changed = $enddate != $coursedeletion->enddate;

            if ($is_staged_for_deletion) {
                if ($enddate_changed) {
                    $info['trigger_mail'] = self::MAIL_WILL_SOON_BE_DELETED;
                }

            } else {
                if ($coursedeletion->status == self::STATUS_SCHEDULED_NOTIFIED) {
                    if (self::today_is_in_notification_before_staging_period($enddate)) {
                        if ($enddate_changed) {
                            $info['trigger_mail'] = self::MAIL_WILL_BE_STAGED_FOR_DELETION;
                        }
                    } else {
                        // Reset the status to scheduled if:
                        // not yet staged for deletion, but already notified, and the date has been pushed out beyond
                        // the new notification period.
                        $info['changes']['status'] = "status: $coursedeletion->status -> scheduled";
                        $coursedeletion->status = self::STATUS_SCHEDULED;
                    }

                }

            }
        } else {
            if ($coursedeletion->status != self::STATUS_NOT_SCHEDULED) {
                $info['changes']['do_delete'] = 'do_delete: yes to no';
                $coursedeletion->status = self::STATUS_NOT_SCHEDULED;
            }
            $enddate = $formvalues->deletionstagedate;
        }

        if ($coursedeletion->enddate != $enddate) {
            $info['changes']['enddate'] = 'enddate: ' .
                self::midnight_before_timestamp($coursedeletion->enddate)->format('d.m.Y') .
                ' to ' .
                self::midnight_before_timestamp($enddate)->format('d.m.Y');
            $coursedeletion->enddate = $enddate;
        }

        if (count($info['changes'])) {
            $DB->update_record('local_coursedeletion', $coursedeletion);
            self::log($coursedeletion, 'settings_update', implode(' / ', $info['changes']));
        }

        return $info;
    }

}
