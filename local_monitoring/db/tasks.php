<?php

$tasks = array(
    array(
        'classname' => 'local_monitoring\task\delete_legate_fat_task',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '6',
        'day' => '1',
        'dayofweek' => '*',
        'month' => '*'
    ),
    array(
        'classname' => 'local_monitoring\task\fill_actual_period_task',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '1',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*'
    )
);
