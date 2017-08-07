<?php

/**
 * This hook is called to add a node to the course administration sideblock menu.
 *
 * @param $settingsnav
 * @param $context
 */
function local_coursedeletion_extend_navigation_course  (navigation_node $parentnode, stdClass $course, context_course $context) {
    global $PAGE;

    // Only add navigation item on non-site course pages.
    if (!$PAGE->course or $PAGE->course->id == 1) {
        return;
    }

    if (!has_capability('local/coursedeletion:course_autodelete_settings', context_course::instance($PAGE->course->id))) {
        return;
    }


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
    $parentnode->add_node($newnode);
    
}