<?php
class coursedeletion_coursesettings_form extends moodleform{

    function definition() {
        $mform =& $this->_form;
        $coursedeletion = $this->_customdata['coursedeletion'];

        $mform->addElement('hidden', 'id');

        $strenddate = get_string('enddate', 'local_coursedeletion');

        $startyear = min(date('Y') - 1, date('Y', $coursedeletion->enddate));

        $mform->addElement('date_selector', 'deletionstagedate',
            $strenddate,
            array('startyear' => $startyear)
        );
        $mform->setDefault('deletionstagedate', $this->_customdata['enddate']);
        $mform->addRule('deletionstagedate', null, 'required');
        $mform->addHelpButton('deletionstagedate', 'enddate', 'local_coursedeletion');

        $strscheduledeletion = get_string('scheduledeletion', 'local_coursedeletion');
        $mform->addElement('selectyesno', 'scheduledeletion', $strscheduledeletion);
        $mform->setDefault('scheduledeletion', $coursedeletion->status == CourseDeletion::STATUS_NOT_SCHEDULED ? 0 : 1);
        $mform->addRule('scheduledeletion', null, 'required');
        $mform->addHelpButton('scheduledeletion', 'scheduledeletion', 'local_coursedeletion');

        $this->add_action_buttons();
    }

    function force_end_date($timestamp) {
        $this->_form->setConstant('deletionstagedate', $timestamp);
    }
}