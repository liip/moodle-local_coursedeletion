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
 * @author Didier Raboud <didier.raboud@liip.ch>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_coursedeletion', new lang_string('settings', 'local_coursedeletion'));

    $settings->add(new admin_setting_configselect('local_coursedeletion/deletion_staging_category_id',
        get_string('deletion_staging_category', 'local_coursedeletion'),
        get_string('deletion_staging_category_desc', 'local_coursedeletion'),
        636,
        make_categories_options()
    ));
    $settings->add(new admin_setting_configtext('local_coursedeletion/interval_enddate_default',
        get_string('interval_enddate_default', 'local_coursedeletion'),
        get_string('interval_enddate_default_desc', 'local_coursedeletion'),
        'P13M',
        PARAM_TEXT
    ));
    $settings->add(new admin_setting_configtext('local_coursedeletion/interval_notification_before_enddate',
        get_string('interval_notification_before_enddate', 'local_coursedeletion'),
        get_string('interval_notification_before_enddate_desc', 'local_coursedeletion'),
        'P3W',
        PARAM_TEXT
    ));
    $settings->add(new admin_setting_configtext('local_coursedeletion/interval_staged_to_deletion',
        get_string('interval_staged_to_deletion', 'local_coursedeletion'),
        get_string('interval_staged_to_deletion_desc', 'local_coursedeletion'),
        'P3M',
        PARAM_TEXT
    ));

    $settings->add(new admin_setting_configtext('local_coursedeletion/school_contact_url',
        get_string('school_contact_url', 'local_coursedeletion'),
        get_string('school_contact_url_desc', 'local_coursedeletion'),
        'http://web.fhnw.ch/e-learning',
        PARAM_URL
    ));

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
