<?php

defined('MOODLE_INTERNAL') || die();

// List of legacy event handlers.
$handlers = array();

// List of events_2 observers.
$observers = array(
    array(
        'eventname'   => 'core\event\course_restored',
        'includefile' => 'local/coursemanager/classes/observer.php',
        'callback'    => 'local_coursemanager_observer::local_coursemanager_course_restored',
    )
);
