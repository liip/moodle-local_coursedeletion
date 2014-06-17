<?php
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