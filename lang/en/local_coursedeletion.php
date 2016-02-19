<?php
$string['pluginname'] = 'Auto Course Deletion';
$string['coursedeletion'] = 'Auto Course Deletion';
$string['settings'] = $string['pluginname'];


$string['coursedeletion:course_autodelete_settings'] = 'Configure auto-deletion settings for a course';
$string['workflow_task_name'] = 'Daily workflow';

// course settings:
$string['coursedeletionsettings'] = 'Course Deletion';
$string['coursedeletionsettingsheader'] = 'Course Deletion settings';
$string['enddate'] = 'End date';
$string['enddate_help'] = 'Three weeks before this date, a reminder will be sent to the teachers. On the end date, the course will be sent to the trash and made invisible for students. Three months after this date, the course will be permanently deleted.';
$string['scheduledeletion'] = 'Schedule deletion';
$string['scheduledeletion_help'] = 'This allows starting or stopping the course deletion process.';

// events:
$string['course_delete'] = 'Delete course';
$string['course_delete_error'] = 'Error deleting course';
$string['workflow_notify'] = 'Send notification';
$string['workflow_notify_error'] = 'Error sending notification';
$string['settings_update'] = 'Change settings';

// errors:
$string['enddatemustbeinfuture'] = 'The end date must be today or later';

// messages:
$string['deletion_not_scheduled'] = 'The course is currently not scheduled for deletion';
$string['minimum_date_was_forced'] = 'The end date was adjusted to the minimum possible date.';
$string['scheduled_upcoming_events'] = 'Expiry reminder mail: {$a->maildate}<br/>Course will be moved to trash: {$a->stagedate}<br/>Course will be deleted: {$a->deletiondate}';
$string['already_sent'] = 'Already sent';
$string['mail_sent'] = 'A mail has been sent to teachers with the new dates';


// notification mails
$string['mailfrom_address'] = 'Mail from address';
$string['mailfrom_address_desc'] = 'Email address to use for the from: field.  If blank, the primary admin\'s address will be used';
$string['mailfrom_text'] = 'Mail from name';
$string['mailfrom_text_desc'] = 'Name to show for the from: field for notification emails.  If blank, "No-reply" will be used';

$string['mail_will_be_staged_subject'] = 'Ihr Moodle-Kursraum {$a->coursefullname} wird demnächst gelöscht';
$string['mail_will_be_staged_body'] = 'Liebe Moodle-Kursraum-Verantwortliche /
Lieber Moodle-Kursraum-Verantwortlicher {$a->userfullname}

Der Moodle-Kursraum {$a->coursefullname} ( {$a->courseurl} ) hat das Verfalldatum bald erreicht. Falls nichts unternommen wird, wird er am {$a->stagedate} in den Papierkorb verschoben und am {$a->deletiondate} endgültig gelöscht.

Falls dies nicht gewünscht ist, kann das Verfalldatum hier verlängert werden: {$a->settingsurl} . Bei Fragen wenden Sie sich bitte an eine zuständige Ansprechperson Ihrer Hochschule ( {$a->contacturl} )

Freundliche Grüsse
Moodle Systemadministration';
$string['mail_will_be_staged_body_html'] = 'Liebe Moodle-Kursraum-Verantwortliche /
Lieber Moodle-Kursraum-Verantwortlicher {$a->userfullname}

Der Moodle-Kursraum <a href="{$a->courseurl}">{$a->coursefullname}</a> hat das Verfalldatum bald erreicht. Falls nichts unternommen wird, wird er am {$a->stagedate} in den Papierkorb verschoben und am {$a->deletiondate} endgültig gelöscht.

Falls dies nicht gewünscht ist, kann das Verfalldatum hier verlängert werden: <a href="{$a->settingsurl}">{$a->settingsurl}</a>. Bei Fragen wenden Sie sich bitte an eine <a href="{$a->contacturl}">zuständige Ansprechperson Ihrer Hochschule</a>

Freundliche Grüsse
Moodle Systemadministration';

$string['mail_will_be_deleted_soon_subject'] = 'Ihr Kursraum {$a->coursefullname} wurde in den Papierkorb verschoben';
$string['mail_will_be_deleted_soon_body'] = 'Liebe Moodle-Kursraum-Verantwortliche /
Lieber Moodle-Kursraum-Verantwortlicher {$a->userfullname}

Der Moodle-Kursraum {$a->coursefullname} ( {$a->courseurl} ) wurde in den Papierkorb verschoben und wird am {$a->deletiondate} endgültig gelöscht.

Falls Sie den Moodle-Kursraum weiterhin benötigen oder bei anderen Fragen wenden Sie sich bitte an eine zuständige Ansprechperson Ihrer Hochschule ( {$a->contacturl} ).

Freundliche Grüsse
Moodle Systemadministration';
$string['mail_will_be_deleted_soon_body_html'] = 'Liebe Moodle-Kursraum-Verantwortliche /
Lieber Moodle-Kursraum-Verantwortlicher {$a->userfullname}

Der Moodle-Kursraum <a href="{$a->courseurl}">{$a->coursefullname}</a> wurde in den Papierkorb verschoben und wird am {$a->deletiondate} endgültig gelöscht.

Falls Sie den Moodle-Kursraum weiterhin benötigen oder bei anderen Fragen wenden Sie sich bitte an eine <a href="{$a->contacturl}">zuständige Ansprechperson Ihrer Hochschule</a>

Freundliche Grüsse
Moodle Systemadministration';


$string['mail_was_deleted_subject'] = 'Ihr Kursraum {$a->coursefullname} wurde gelöscht.';
$string['mail_was_deleted_body'] = 'Liebe Moodle-Kursraum-Verantwortliche /
Lieber Moodle-Kursraum-Verantwortlicher {$a->userfullname}

Der Moodle-Kursraum «{$a->coursefullname}» wurde endgültig gelöscht und ist im Moodle nun nicht mehr verfügbar.

Bei Fragen wenden Sie sich bitte an eine zuständige Ansprechperson Ihrer Hochschule ( {$a->contacturl} ).

Freundliche Grüsse
Moodle Systemadministration';
$string['mail_was_deleted_body_html'] = 'Liebe Moodle-Kursraum-Verantwortliche /
Lieber Moodle-Kursraum-Verantwortlicher {$a->userfullname}

Der Moodle-Kursraum «{$a->coursefullname}» wurde endgültig gelöscht und ist im Moodle nun nicht mehr verfügbar.

Bei Fragen wenden Sie sich bitte an eine <a href="{$a->contacturl}">zuständige Ansprechperson Ihrer Hochschule</a>

Freundliche Grüsse
Moodle Systemadministration';
