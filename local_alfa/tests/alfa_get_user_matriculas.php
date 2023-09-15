<head> <meta http-equiv="Content-Type" content="text/html; charset=utf-8">  </head>

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

if(!isset($_GET['idnumber']) && isset($_GET['period'])){
    die('Defina um idnumber e período para a busca');
}

$idnumber = $_GET['idnumber'];
$period = $_GET['period'];

$courseinfo = Alfa::buscarMatriculasUsuario($idnumber , $period); //85752

foreach($courseinfo as $course){
    echo '<pre>';
    var_dump($course);die;
    echo utf8_encode($course->descricao_disciplina).'----'.$course->carga_horaria_disciplina."<br />";
}
