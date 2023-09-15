<?php
/**
 * Testa as funções de busca do plano de Ensino.
 *
 */

//define('CLI_SCRIPT', true);

require_once('../classes/alfa.class.php');
require_once('../../../config.php');

if(!is_siteadmin()){
    die('Você não tem permissão de executar este script.');
}

if(!isset($_GET['idnumber'])){
    die('Defina um idnumber para a busca');
}

$idnumber = $_GET['idnumber'];

if(Alfa::isTeachingPlan($idnumber)){//verifica se existe
    header('Content-type: application/pdf');
    @header("Content-Disposition: inline; filename='Plano_de_Ensino_$idnumber.pdf'");

    echo Alfa::getTeachingPlan($idnumber);//gera o pdf
}
?>
