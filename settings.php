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

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_coursedeletion', new lang_string('settings', 'local_coursedeletion'));
    // From address to use when sending emails.
    $settings->add(new admin_setting_configtext('local_coursedeletion/mailfrom_address',
        get_string('mailfrom_address', 'local_coursedeletion'),
        get_string('mailfrom_address_desc', 'local_coursedeletion'),
        '',
        PARAM_EMAIL));

    $settings->add(new admin_setting_configtext('local_coursedeletion/mailfrom_text',
        get_string('mailfrom_text', 'local_coursedeletion'),
        get_string('mailfrom_text_desc', 'local_coursedeletion'),
        '',
        PARAM_TEXT));

    $ADMIN->add('localplugins', $settings);
}
