<?php

defined('MOODLE_INTERNAL') || die();

// List of legacy event handlers.
$handlers = array();

// List of events_2 observers.
$observers = array(
    array(
        'eventname'   => 'core\event\user_loggedin',
        'includefile' => 'local/alfa/classes/observer.php',
        'callback'    => 'local_alfa_observer::local_alfa_user_login',
    ),
    array(
        'eventname'   => 'core\event\course_deleted',
        'includefile' => 'local/alfa/classes/observer.php',
        'callback'    => 'local_alfa_observer::local_alfa_course_deleted',
    )
);