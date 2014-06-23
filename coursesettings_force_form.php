<?php
class coursedeletion_coursesettings_force_form extends moodleform{

    function definition() {
        $mform =& $this->_form;
        $coursedeletion = $this->_customdata['coursedeletion'];

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $strenddate = get_string('enddate', 'local_coursedeletion');
        $startyear = min(date('Y') - 3, date('Y', $coursedeletion->enddate));
        $mform->addElement('date_selector', 'enddate',
            $strenddate,
            array('startyear' => $startyear)
        );
        $mform->addRule('enddate', null, 'required');
        $mform->addHelpButton('enddate', 'enddate', 'local_coursedeletion');

        $statii = array(
            CourseDeletion::STATUS_NOT_SCHEDULED => 'not scheduled for deletion',
            CourseDeletion::STATUS_SCHEDULED => 'scheduled for deletion',
            CourseDeletion::STATUS_SCHEDULED_NOTIFIED => 'scheduled, first notification sent',
            CourseDeletion::STATUS_STAGED_FOR_DELETION => 'scheduled, already moved to trash',
        );
        $mform->addElement('select', 'status', 'Status', $statii);
        $mform->addRule('status', null, 'required');

        $this->add_action_buttons();
    }
}