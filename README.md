# Moodle course deletion scheduling

This plugin allows teachers / managers to schedule deletion of their courses.

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

== To test locally for development ==
* Read the comment at the top of locallib.php to understand how the workflow works.

* If necessary, create a deletion staging course category and update the config value
  to point to this course category id:

     update mdl_config_plugins set value = '12' where plugin = 'local_coursedeletion' and name = 'deletion_staging_category_id';

* To easily change the status and/or end date of the course to a date in the past, access
  /local/coursedeletion/force_settings.php .  However, you will first need to allow
  access for your user id with a line like this in config.php:

      // Provide a comma-separated list of user ids:
      $CFG->local_coursedeletion_force_users = '20760';

* The course deletion workflow task called by the cron job will only run once a day.  You can however
  use the command line script local/coursedeletion/cli/run_cron.php, which will, by default, force
  the task to run regardless of when the task was last run.  The cli script should be run as the
  www-data user.

      # To see options:
      php cli/run_cron.php --help

      # To do it:
      php cli/run_cron.php --help
