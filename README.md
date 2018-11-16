# local/coursedeletion - A Moodle course lifecycle management plugin

This plugin allows teachers / managers to schedule deletion of their courses.

## Phases

That plugin moves the courses through different steps:

### Course creation

Upon course creation, the course end date is set to **today + 13 months**.

## Notification email

Three weeks before the staging date (which is set to be the course end date), an email is sent to mention that the course will be moved to the staging category soon.

### Course moving to staging category

At the course end date, the course is moved to the staging category, and an email is sent to confirm the course moving and inform of the upcoming course deletion.

### Course deletion

(By default, 3 months after the course end date), the course is completely deleted from the Moodle instance, away from the staging category. A last email is sent to confirm the course deletion.



# Authors

This plugin was developed by [Liip AG](https://www.liip.ch/), the swiss Moodle Partner.

Development and open-sourcing of this plugin was made possible thanks to funding by the *[FHNW University of Applied Sciences and Arts Northwestern Switzerland](https://www.fhnw.ch/)*.
