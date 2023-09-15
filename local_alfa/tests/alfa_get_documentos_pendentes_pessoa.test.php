<?php

//define('CLI_SCRIPT', true);

require_once('../../../config.php');
require_once('../classes/alfa.class.php');

if(!is_siteadmin()){
    die('Você não tem permissão de executar este script.');
}

$username = $_GET['username'];
$category = $_GET['category'];

if(!$username && !$category){
    die('Informe um username ou uma categoria');
} else if($username && $category){
    die('Informe apenas um parâmetro');
}

$users = [];

if($category){
    $users = $DB->get_records_sql("SELECT distinct(mus.username) FROM mdl_role_assignments mra, mdl_user mus WHERE mus.id = mra.userid AND contextid IN ( select id FROM mdl_context WHERE contextlevel = 50 AND instanceid IN (SELECT id FROM mdl_course WHERE category IN (SELECT id FROM mdl_course_categories WHERE id IN ($category)))) AND roleid = 5;");
}

if($username){
    $users = [ (object) ['username' => $username] ];
}

foreach($users as $user){
    $documents = Alfa::getDocumentosPendentesPessoa([$user->username.'']);
    //if($documents['documentacao_pendente'] != NULL){ var_dump($documents); }
    var_dump($documents);
    echo '<br />';
}

