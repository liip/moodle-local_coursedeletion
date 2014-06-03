<?php
$string['pluginname'] = 'Automatisiertes Kurslöschen';
$string['coursedeletion'] = 'Automatisiertes Kurslöschen';

$string['coursedeletion:course_autodelete_settings'] = 'Automatisiertes Kurslöschen: Einstellungen für Kurs anpassen';

// course settings:
$string['coursedeletionsettings'] = 'Kurs löschen';
$string['coursedeletionsettingsheader'] = 'Kurs löschen - Einstellungen';
$string['enddate'] = 'Verfalldatum';
$string['enddate_help'] = 'Drei Wochen vor diesem Datum wird eine Erinnerung an die Teachers geschickt. Am Verfalldatum wird der Kurs in den Papierkorb verschoben und für Studierende unsichtbar gemacht. Drei Monate nach diesem Datum wird der Kurs unwiderruflich gelöscht.';
$string['scheduledeletion'] = 'Kurs nach Verfalldatum löschen';
$string['scheduledeletion_help'] = 'Hier kann der Löschprozess gestoppt oder gestartet werden.';

// errors:
$string['enddatemustbeinfuture'] = 'Verfalldatum muss heute oder später sein.';

// messages:
$string['minimum_date_was_forced'] = 'Das Enddatum wurde automatisch auf das nächst mögliche Datum gesetzt.';
$string['scheduled_upcoming_events'] = 'Ablauf-Erinnerungsmail: {$a->maildate}<br/>Kurs wird in den Papierkorb verschoben: {$a->stagedate}<br/>Kurs wird gelöscht: {$a->deletiondate}';
$string['already_sent'] = 'Schon versandt';
$string['mail_sent'] = 'Eine E-mail mit den aktualisierten Daten wurde an die Kursraum-Verantwortlichen geschickt';

// notification mails
$string['mail_will_be_staged_subject'] = 'Ihr Moodle-Kursraum {$a->coursefullname} wird demnächst gelöscht';
$string['mail_will_be_staged_body'] = 'Liebe Moodle-Kursraum-Verantwortliche /
Lieber Moodle-Kursraum-Verantwortlicher {$a->userfullname}

Der Moodle-Kursraum {$a->coursefullname} ( {$a->courseurl} ) hat das Verfalldatum erreicht. Falls nichts unternommen wird, wird er am {$a->stagedate} in den Papierkorb verschoben und am {$a->deletiondate} endgültig gelöscht.

Falls dies nicht gewünscht ist, kann das Verfalldatum hier verlängert werden: {$a->settingsurl} . Bei Fragen wenden Sie sich bitte an eine zuständige Ansprechperson Ihrer Hochschule ( {$a->contacturl} )

Freundliche Grüsse
Moodle Systemadministration';
$string['mail_will_be_staged_body_html'] = 'Liebe Moodle-Kursraum-Verantwortliche /
Lieber Moodle-Kursraum-Verantwortlicher {$a->userfullname}

Der Moodle-Kursraum <a href="{$a->courseurl}">{$a->coursefullname}</a> hat das Verfalldatum erreicht. Falls nichts unternommen wird, wird er am {$a->stagedate} in den Papierkorb verschoben und am {$a->deletiondate} endgültig gelöscht.

Falls dies nicht gewünscht ist, kann das Verfalldatum hier verlängert werden: <a href="{$a->settingsurl}">{$a->settingsurl}</a>. Bei Fragen wenden Sie sich bitte an eine <a href="{$a->contacturl}">zuständige Ansprechperson Ihrer Hochschule</a>

Freundliche Grüsse
Moodle Systemadministration';

$string['mail_will_be_deleted_soon_subject'] = 'Ihr Kursraum {$a->coursefullname} wurde in den Papierkorb verschoben';
$string['mail_will_be_deleted_soon_body'] = 'Liebe Moodle-Kursraum-Verantwortliche /
Lieber Moodle-Kursraum-Verantwortlicher {$a->userfullname}

Der Moodle-Kursraum {$a->coursefullname} ( {$a->courseurl} ) wurde in den Papierkorb verschoben  und wird am {$a->deletiondate} endgültig gelöscht.

Falls Sie den Moodle-Kursraum weiterhin benötigen oder bei anderen Fragen wenden Sie sich bitte an eine zuständige Ansprechperson Ihrer Hochschule ( {$a->contacturl} ).

Freundliche Grüsse
Moodle Systemadministration';
$string['mail_will_be_deleted_soon_body_html'] = 'Liebe Moodle-Kursraum-Verantwortliche /
Lieber Moodle-Kursraum-Verantwortlicher {$a->userfullname}

Der Moodle-Kursraum <a href="{$a->courseurl}">{$a->coursefullname}</a> wurde in den Papierkorb verschoben  und wird am {$a->deletiondate} endgültig gelöscht.

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
