<?php

/**
 * This hook is called to add a node to the course administration sideblock menu.
 *
 * @param $settingsnav
 * @param $context
 */
function local_coursedeletion_extends_settings_navigation($settingsnav, $context) {
    global $PAGE;

    // Only add navigation item on non-site course pages.
    if (!$PAGE->course or $PAGE->course->id == 1) {
        return;
    }

    if (!has_capability('local/coursedeletion:course_autodelete_settings', context_course::instance($PAGE->course->id))) {
        return;
    }

    if ($settingnode = $settingsnav->find('courseadmin', navigation_node::TYPE_COURSE)) {
        $label = get_string('coursedeletionsettings', 'local_coursedeletion');
        $url = new moodle_url('/local/coursedeletion/coursesettings.php', array('id' => $PAGE->course->id));
        $newnode = navigation_node::create(
            $label,
            $url,
            navigation_node::NODETYPE_LEAF,
            'duplicatecourse',
            'duplicatecourse',
            new pix_icon('i/calendar', $label)
        );
        if ($PAGE->url->compare($url, URL_MATCH_BASE)) {
            $newnode->make_active();
        }
        $settingnode->add_node($newnode);
    }
}

class local_coursedeletion_event_handler {

    /**
     * Handler for 'course_created' event.
     *
     * Creates a record in the local_coursedeletion table for this course
     *
     * @param stdClass $eventdata
     * @return bool
     */
    public static function course_created($eventdata) {
        require_once(__DIR__ . '/locallib.php');

        if (!empty($eventdata->id)) {
            CourseDeletion::create_record($eventdata->id);
            return true;
        }
        debug("eventdata does not have expected field 'id'");
        return false;
    }

    /**
     * Handler for 'course_restored' event.
     *
     * Creates a record in the local_coursedeletion table for this course
     *
     * @param stdClass $eventdata
     * @return bool
     */
    public static function course_restored($eventdata) {
        require_once(__DIR__ . '/locallib.php');

        if (!empty($eventdata->courseid)) {
            CourseDeletion::create_record($eventdata->courseid);
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
     * @param stdClass $course
     * @return bool
     */
    public static function course_deleted($course) {
        require_once(__DIR__ . '/locallib.php');

        if (!empty($course->id)) {
            CourseDeletion::delete_record($course->id);
            return true;
        }
        debug("eventdata does not have expected field 'id'");
        return false;
    }

    /**
     * Handler for 'local_course_reset' event.
     *
     * Updates record in the local_coursedeletion table for this course with values as if it was a new course.
     *
     * @param stdClass $eventdata
     * @return bool
     */
    public static function local_course_reset($eventdata) {
        require_once(__DIR__ . '/locallib.php');

        if (!empty($eventdata->courseid)) {
            CourseDeletion::reset_course($eventdata->courseid);
            return true;
        }

        debug("eventdata does not have expected field 'courseid'");
        return false;
    }
}
