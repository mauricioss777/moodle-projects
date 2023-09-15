<?php

$tasks = array(
    array(
        'classname' => '\local_inscricoes\task\sync_presences_task',
        'blocking' => 1,
        'minute' => '0',
        'hour' => '1',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*'
    )
);
