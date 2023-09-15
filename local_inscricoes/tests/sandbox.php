<?php

require_once('../../../config.php');
die();
$task = $DB->get_record('task_scheduled', Array('id' => 84));

$task->component = 'local_inscricoes';
$task->classname = '\local_inscricoes\task\sync_presence_task';

$DB->update_record('task_scheduled', $task);
