<?php

$tasks = array(
    array(
        'classname' => 'local_alfa\task\sync_users_task', 
        'blocking' => 0,
        'minute' => '*',
        'hour' => '*',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*'
    ),
    array(
        'classname' => 'local_alfa\task\sync_courses_task', 
        'blocking' => 0,
        'minute' => '*',
        'hour' => '*',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*'
    ),
    array(
        'classname' => 'local_alfa\task\sync_courses_tcc_task',
        'blocking' => 0,
        'minute' => '*',
        'hour' => '*',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*'
    ),
    array(
        'classname' => 'local_alfa\task\sync_curriculum_task', 
        'blocking' => 0,
        'minute' => '30',
        'hour' => '*',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*'
    ),
    array(
        'classname' => 'local_alfa\task\sync_users_ambientacao_task',
        'blocking' => 0,
        'minute' => '*',
        'hour' => '12',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*'
    )
);
