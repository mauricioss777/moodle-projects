<?php

defined('MOODLE_INTERNAL') || die();

// List of legacy event handlers.
$handlers = array();

// List of events_2 observers.
$observers = array(
    array(
        'eventname'   => 'core\event\course_deleted',
        'includefile' => 'local/inscricoes/classes/observer.php',
        'callback'    => 'local_inscricoes_observer::local_inscricoes_course_deleted',
    )
);