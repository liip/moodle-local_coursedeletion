<?php
require_once(__DIR__ . '/../../config.php');
require_once("$CFG->libdir/formslib.php");
require_once(__DIR__ . '/coursesettings_force_form.php');
require_once(__DIR__ . '/locallib.php');

$id = required_param('id', PARAM_INT);

$PAGE->set_url('/local/coursedeletion/coursesettings.php', array('id'=>$id));

if (! $course = $DB->get_record("course", array('id' => $id))) {
    print_error('invalidcourseid');
}
require_course_login($course);

// This script is only meant to be used for testing / debugging purposes.
// If someone should be able to access it, define in config.php a value like:
//
//    $CFG->local_coursedeletion_force_users = 12;
// or
//    $CFG->local_coursedeletion_force_users = '12,99,102';
//
// where the numbers are user ids of the users who shall be permitted access.

$can_access = false;
if (isset($CFG->local_coursedeletion_force_users)) {
    $userids = explode(',', clean_param($CFG->local_coursedeletion_force_users, PARAM_SEQUENCE));
    $can_access = in_array($USER->id, $userids);
}
if (!$can_access) {
    send_file_not_found();
    exit;
}

$coursecontext = context_course::instance($course->id);
if (!has_capability('local/coursedeletion:course_autodelete_settings', $coursecontext)) {
    print_error(
        'nopermissions',
        'error',
        new moodle_url('/course/view.php', array('id' => $course->id))
    );
}

if (!$coursedeletion = $DB->get_record('local_coursedeletion', array('courseid' => $id))) {
    print_error(
        'invalidrecordunknown',
        'error',
        new moodle_url('/course/view.php', array('id' => $course->id))
    );
}

$strcoursedeletion = get_string('coursedeletionsettings', 'local_coursedeletion');
$PAGE->set_pagelayout('incourse');
$PAGE->set_title($strcoursedeletion);
$PAGE->set_heading($course->fullname);

$formparams = array(
    'coursedeletion' => $coursedeletion,
);
$mform = new coursedeletion_coursesettings_force_form(null, $formparams);

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/course/view.php', array('id' => $course->id)));
}

/* @var core_renderer $OUTPUT */
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('coursedeletionsettingsheader', 'local_coursedeletion'));

echo $OUTPUT->container('Be careful!  Here you can directly manipulate the values; no validity checking will be done.', 'alert');

$flash = array();

if ($form = $mform->get_data()){
    $coursedeletion->enddate = $form->enddate;
    $coursedeletion->status = $form->status;
    $DB->update_record('local_coursedeletion', $coursedeletion);
}

// SUP-6847: always show the scheduled_upcoming_events:
if (in_array($coursedeletion->status, array(CourseDeletion::STATUS_SCHEDULED, CourseDeletion::STATUS_SCHEDULED_NOTIFIED))) {
    $a = new stdClass;
    if ($coursedeletion->status == CourseDeletion::STATUS_SCHEDULED) {
        $a->maildate = CourseDeletion::first_notification_date($coursedeletion->enddate)->format('d.m.Y');
    } else {
        $a->maildate = get_string('already_sent', 'local_coursedeletion');
    }
    $a->stagedate = CourseDeletion::date_course_will_be_staged_for_deletion($coursedeletion->enddate)->format('d.m.Y');
    $a->deletiondate = CourseDeletion::date_course_will_be_deleted($coursedeletion->enddate)->format('d.m.Y');
    $flash[] = get_string('scheduled_upcoming_events', 'local_coursedeletion', $a);
} else if ($coursedeletion->status == CourseDeletion::STATUS_NOT_SCHEDULED) {
    $flash[] = get_string('deletion_not_scheduled', 'local_coursedeletion');
}

foreach ($flash as $message) {
    echo $OUTPUT->container($message, 'alert alert-info');
}

$mform->set_data(array(
    'id' => $course->id,
    'status' => $coursedeletion->status,
    'enddate' => $coursedeletion->enddate,
));
$mform->display();

echo $OUTPUT->footer();
