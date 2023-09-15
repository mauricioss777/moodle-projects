<?php
/**
 * Testa a sincronização de cursos com o alfa (adicionando e removendo usuários)
 * Este script roda juntamente com a cron do Moodle
 */
define('CLI_SCRIPT', true);

require_once('../../../config.php');
require_once('../../../lib/classes/task/task_base.php');
require_once('../../../lib/classes/task/scheduled_task.php');
require_once('../../../local/alfa/classes/task/sync_curriculum_task.php');

$update = new local_alfa\task\sync_curriculum_task();
$update->execute();

?>
