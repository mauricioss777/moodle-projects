<?php

defined('MOODLE_INTERNAL') || die();

// List of legacy event handlers.
$handlers = array();

// List of events_2 observers.
$observers = array(
    array(
        'eventname'   => 'core\event\course_module_viewed',
        'includefile' => 'local/monitoring/classes/observer.php',
        'callback'    => 'local_monitoring_observer::local_monitoring_course_module_viewed',
    ),
    array(
        'eventname'   => '\mod_bigbluebuttonbn\event\meeting_joined',
        'includefile' => 'local/mobileapp/classes/observer.php',
        'callback'    => 'local_monitoring_observer::local_monitoring_bbb_joined',
    ),
    array(
        'eventname'   => '\mod_meet\event\meeting_joined',
        'includefile' => 'local/mobileapp/classes/observer.php',
        'callback'    => 'local_monitoring_observer::local_monitoring_meet_joined',
    )
);
