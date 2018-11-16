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
 * Create initial settings
 */
function xmldb_local_coursedeletion_install() {
    global $DB;
    set_config('deletion_staging_category_id', 605, 'local_coursedeletion');
    set_config('interval_enddate_default', 'P13M', 'local_coursedeletion');
    set_config('interval_notification_before_enddate', 'P3W', 'local_coursedeletion');
    set_config('interval_staged_to_deletion', 'P3M', 'local_coursedeletion');
    set_config('school_contact_url', 'http://web.fhnw.ch/e-learning', 'local_coursedeletion');
    local_coursedeletion_add_course_deletion_records();
}

function local_coursedeletion_add_course_deletion_records() {
    global $DB;
    require_once(__DIR__ . '/../locallib.php');

    $deletion_staging_category_id = get_config('local_coursedeletion', 'deletion_staging_category_id');

    if($DB->record_exists('course_categories', array('id' => $deletion_staging_category_id))) {

        $staging_cat_context = context_coursecat::instance($deletion_staging_category_id);

        // Get all the courses that are in some category (e.g. not the site course) but not in the toDelete category,
        // and that don't already have a coursedeletion record.
        $courseids = $DB->get_fieldset_sql("
          SELECT c.id
            FROM {course} c
            JOIN {context} ctx on ctx.instanceid = c.id
       LEFT JOIN {local_coursedeletion} lcd on ctx.instanceid = lcd.courseid
           WHERE ctx.contextlevel = :contextlevel
             AND c.category > 0
             and ctx.path NOT LIKE :stage_cat_ctx_path
             AND lcd.id IS NULL", array('contextlevel' => CONTEXT_COURSE, 'stage_cat_ctx_path' => "$staging_cat_context->path/%")
        );

        foreach ($courseids as $id) {
            CourseDeletion::create_record($id, CourseDeletion::STATUS_NOT_SCHEDULED);
        }
    }
}
