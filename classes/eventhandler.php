<?php

namespace local_coursedeletion;

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