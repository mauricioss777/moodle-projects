<?php
/**
 * Testa a atualização de informações dos usuários (nome, e-mail e cidade);
 * Este script roda juntamente com a cron do Moodle
 */
require_once('../../../config.php');
require_once('../../../lib/classes/task/task_base.php');
require_once('../../../lib/classes/task/scheduled_task.php');
require_once('../../../local/alfa/classes/task/sync_users_task.php');

if(!is_siteadmin()){
    die('Você não tem permissão de executar este script.');
}
$update = new local_alfa\task\sync_users_task();
$update->execute();

?>
