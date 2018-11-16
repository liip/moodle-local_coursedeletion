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

namespace local_coursedeletion;

defined('MOODLE_INTERNAL') || die();

use \debug;
use \CourseDeletion;


class eventhandler {

    /**
     * Handler for 'course_created' event.
     *
     * Creates a record in the local_coursedeletion table for this course
     *
     * @param local_coursedeletion\event_base $eventdata
     * @return bool
     */
    public static function course_created($eventdata) {
        require_once(__DIR__ . '/../locallib.php');

        if (!empty($eventdata->courseid)) {
            CourseDeletion::create_record($eventdata->courseid);
            return true;
        }
        debug("eventdata does not have expected field 'courseid'");
        return false;
    }

    /**
     * Handler for 'course_restored' event.
     *
     * Creates a record in the local_coursedeletion table for this course
     *
     * @param local_coursedeletion\event_base $eventdata
     * @return bool
     */
    public static function course_restored($eventdata) {
        require_once(__DIR__ . '/../locallib.php');

        if (!empty($eventdata->courseid)) {
            // The course_created signal may have been triggered already, so
            // remove any existing record, and insert a new record for this course.
            CourseDeletion::reset_course($eventdata->courseid);
            return true;
        }
        debug("eventdata does not have expected field 'courseid'");
        return false;
    }

    /**
     * Handler for 'course_deleted' event.
     *
     * Deletes record from the local_coursedeletion table for this course
     *
     * @param local_coursedeletion\event_base $eventdata
     * @return bool
     */
    public static function course_deleted($eventdata) {
        require_once(__DIR__ . '/../locallib.php');

        if (!empty($eventdata->id)) {
            CourseDeletion::delete_record($eventdata->courseid);
            return true;
        }
        debug("eventdata does not have expected field 'id'");
        return false;
    }

    /**
     * Handler for 'course_reset_ended' event.
     *
     * Updates record in the local_coursedeletion table for this course with values as if it was a new course.
     *
     * @param local_coursedeletion\event_base $eventdata
     * @return bool
     */
    public static function course_reset_ended($eventdata) {
        require_once(__DIR__ . '/../locallib.php');

        if (!empty($eventdata->courseid)) {
            CourseDeletion::reset_course($eventdata->courseid);
            return true;
        }

        debug("eventdata does not have expected field 'courseid'");
        return false;
    }
}
