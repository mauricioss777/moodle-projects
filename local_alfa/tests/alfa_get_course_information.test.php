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

if(!isset($_GET['idnumber'])){
    die('Defina um idnumber para a busca');
}
$idnumber = $_GET['idnumber'];


//$courseinfo = Alfa::getCourseInformation('86788');//85752
$courseinfo = Alfa::getCourseInformation($idnumber);//85752
echo "<pre>\n";
echo print_r($courseinfo);
echo "</pre>\n";
?>

