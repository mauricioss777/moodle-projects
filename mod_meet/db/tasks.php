<?php

$tasks = array(
    array(
        'classname' => 'mod_meet\task\revalidate_permission_task',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '*',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*'
    ),
    array(
        'classname' => 'mod_meet\task\load_presences_from_meet_task',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '12',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*'
    ),
);
