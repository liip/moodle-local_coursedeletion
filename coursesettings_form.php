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

class coursedeletion_coursesettings_form extends moodleform{

    function definition() {
        $mform =& $this->_form;
        $coursedeletion = $this->_customdata['coursedeletion'];

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

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
