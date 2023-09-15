<?php
/**
 * Testa as funções de busca os alunos e professores vinculados a um currículo
 *
 */

require_once('../../../config.php');
require_once('../classes/alfa.class.php');
if(!is_siteadmin()){
    die('Você não tem permissão de executar este script.');
}

if(!isset($_GET['curriculumid'])){
    die('Defina um curriculumid para a busca');
}
$curriculumid = $_GET['curriculumid'];

$courseinfo = Alfa::getCurriculumInformation($curriculumid, true);//48102
//$courseinfo = Alfa::getCurriculumInformation('48102');//48102
echo "<pre>\n";
echo print_r($courseinfo);
echo "</pre>\n";
?>

