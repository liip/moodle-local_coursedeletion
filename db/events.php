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

$observers = array (
    array(
        'eventname' => '\core\event\course_created',
        'callback'  => 'local_coursedeletion\eventhandler::course_created',
    ),
    array(
        'eventname' => '\core\event\course_restored',
        'callback'  => 'local_coursedeletion\eventhandler::course_restored',
    ),
    array(
        'eventname' => '\core\event\course_reset_ended',
        'callback'  => 'local_coursedeletion\eventhandler::course_reset_ended',
    ),
    array(
        'eventname' => '\core\event\course_restored',
        'callback'  => 'local_coursedeletion\eventhandler::course_restored',
    ),
);
