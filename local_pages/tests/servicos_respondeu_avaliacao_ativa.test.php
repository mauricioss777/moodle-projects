<?php
/**
 * Testa as funções de busca do plano de Ensino.
 *
 */

//define('CLI_SCRIPT', true);

require_once('../../../config.php');
require_once('../classes/servicos.class.php');

if(!is_siteadmin()){
    die('Você não tem permissão de executar este script.');
}

$user = $_GET['user'];
$process = $_GET['process'];

if(!$user || !$process){
    echo $CFG->wwwroot . '/local/pages/tests/servicos_respondeu_avaliacao_ativa.test.php?user=555555&process=100';die;
}

echo '<pre>'; var_dump(Servicos::respondeuAvaliacaoAtiva($user, $process));die;

