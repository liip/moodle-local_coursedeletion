<?php
defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_coursedeletion', new lang_string('settings', 'local_coursedeletion'));
    // From address to use when sending emails
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
