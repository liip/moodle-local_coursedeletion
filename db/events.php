<?php
$handlers = array (
    'course_created' => array (
        'handlerfile'      => '/local/coursedeletion/lib.php',
        'handlerfunction'  => array('local_coursedeletion_event_handler', 'course_created'),
        'schedule'         => 'instant',
        'internal'         => 1,
    ),

    'course_restored' => array (
        'handlerfile'      => '/local/coursedeletion/lib.php',
        'handlerfunction'  => array('local_coursedeletion_event_handler', 'course_restored'),
        'schedule'         => 'instant',
        'internal'         => 1,
    ),

    // todo: In 2.7, there should be a core course_reset event.
    //       So, the custom local_course_reset event trigger should be removed,
    //       and this handler adapted to handle the core course_reset event.
    'local_course_reset' => array (
        'handlerfile'      => '/local/coursedeletion/lib.php',
        'handlerfunction'  => array('local_coursedeletion_event_handler', 'local_course_reset'),
        'schedule'         => 'instant',
        'internal'         => 1,
    ),

    'course_deleted' => array (
        'handlerfile'      => '/local/coursedeletion/lib.php',
        'handlerfunction'  => array('local_coursedeletion_event_handler', 'course_deleted'),
        'schedule'         => 'instant',
        'internal'         => 1,
    ),
);