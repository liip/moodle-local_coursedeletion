# To test locally for development

* Read the comment at the top of locallib.php to understand how the workflow works.

* If necessary, create a deletion staging course category and update the config value
  to point to this course category id:

```sql
update mdl_config_plugins set value = '12' where plugin = 'local_coursedeletion' and name = 'deletion_staging_category_id';
```

* To easily change the status and/or end date of the course to a date in the past, access
  /local/coursedeletion/force_settings.php .  However, you will first need to allow
  access for your user id with a line like this in config.php:

```php
      // Provide a comma-separated list of user ids:
      $CFG->local_coursedeletion_force_users = '20760';
```

* The course deletion workflow task called by the cron job will only run once a day.  You can however
  use the command line script local/coursedeletion/cli/run_cron.php, which will, by default, force
  the task to run regardless of when the task was last run.  The cli script should be run as the
  www-data user.

```bash
      # To see options:
      php local/coursedeletion/cli/run_cron.php --help

      # To do it:
      php local/coursedeletion/cli/run_cron.php
```
