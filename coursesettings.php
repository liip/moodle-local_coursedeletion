<?php
require_once(__DIR__ . '/../../config.php');
require_once("$CFG->libdir/formslib.php");
require_once(__DIR__ . '/coursesettings_form.php');
require_once(__DIR__ . '/locallib.php');

$id = required_param('id', PARAM_INT);

$PAGE->set_url('/local/coursedeletion/coursesettings.php', array('id'=>$id));

if (! $course = $DB->get_record("course", array('id' => $id))) {
    print_error('invalidcourseid');
}

require_course_login($course);
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

// If a course doesn't have coursedeletion enabled, and it does get enabled,
// the end date by default should be set to 13 months later (depending on config).
$form_enddate = $coursedeletion->enddate;
if ($coursedeletion->status == CourseDeletion::STATUS_NOT_SCHEDULED) {
    $form_enddate = CourseDeletion::default_course_end_date();
}
$formparams = array(
    'coursedeletion' => $coursedeletion,
    'enddate' => $form_enddate,
);
$mform = new coursedeletion_coursesettings_form(null, $formparams);

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/course/view.php', array('id' => $course->id)));
}

/* @var core_renderer $OUTPUT */
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('coursedeletionsettingsheader', 'local_coursedeletion'));

$flash = array();
$changed = array();

if ($form = $mform->get_data()){
    $cd = new CourseDeletion();
    $info = CourseDeletion::update_from_form($coursedeletion, $form, $cd);
    if (!is_null($info['minimum_date_forced'])) {
        $flash[]= get_string('minimum_date_was_forced', 'local_coursedeletion');
        $mform->force_end_date($info['minimum_date_forced']);
    }
    if (!is_null($info['trigger_mail'])) {
        $cd->send_mail_to_teachers(array($coursedeletion), $info['trigger_mail']);
        $flash[]= get_string('mail_sent', 'local_coursedeletion');
    }

    if (count($info['changes'])) {
        echo $OUTPUT->container(
            get_string('settingssaved', 'message') . $OUTPUT->continue_button(new moodle_url('/course/view.php',
            array('id' => $course->id))),
            'alert alert-success'
        );
    } else {
        echo $OUTPUT->container(
            get_string('nochange') . $OUTPUT->continue_button(new moodle_url('/course/view.php',
            array('id' => $course->id))),
            'alert alert-success'
        );
    }
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
}

foreach ($flash as $message) {
    echo $OUTPUT->container($message, 'alert alert-info');
}

$mform->set_data(array('id' => $course->id));
$mform->display();

echo $OUTPUT->footer();
