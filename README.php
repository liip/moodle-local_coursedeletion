<?php
/*
This plugin allows teachers to schedule deletion of their courses.

It was written for FHNW and has FHNW-specific content (e.g. the mails).

It requires a deletion staging category (a.k.a. recycling bin category).

These settings are configured in db/install.php:

    set_config('deletion_staging_category_id', 605, 'local_coursedeletion');
    set_config('interval_enddate_default', 'P13M', 'local_coursedeletion'); // 13 months
    set_config('interval_notification_before_enddate', 'P3W', 'local_coursedeletion'); // 3 weeks
    set_config('interval_staged_to_deletion', 'P3M', 'local_coursedeletion');  // 3 months

To generalize this plugin for other customers, an interface should be added to allow editing the settings
and perhaps the notification emails.

For Moodle < 2.7, a core patch is needed to add a course reset trigger. (patches/moodle_before_27/lib_moodlelib.php.patch)
It will need to be adapted to work on Moodle >= 2.7.
*/
