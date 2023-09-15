<?php
/**
 * Testa as funções de busca do plano de Ensino.
 *
 */

//define('CLI_SCRIPT', true);

require_once('../../../config.php');
require_once('../classes/alfa.class.php');

if(!is_siteadmin()){
    die('Você não tem permissão de executar este script.');
}

$id = $_GET['id'];

if($id){
    echo '<pre>';
    var_dump( Alfa::getCurriculumInformation($id) );
    die;
}

$curriculums = $DB->get_records_sql('select curriculum from {local_alfa_curriculum}');

foreach($curriculums as $c){
    echo '<pre>';
    var_dump($c);
    var_dump(Alfa::getCurriculumInformation($c->curriculum));
    echo '<hr />';
}



