<?php

require_once('../classes/alfa.class.php');
require_once('../../../config.php');

if(!is_siteadmin()){
        die('Você não tem permissão de executar este script.');
}

$user = isset($_GET['username']) ? $_GET['username'] : false;

if(!$user){
    echo 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']. '?username=xxxxx';
    die;
}

echo '<pre>';
print_r( Alfa::getUserInformation($user) );

if(Alfa::pessoaPrecisaRecadastrarSenha($user)){
    echo 'Precisa recadastrar senha';
}else{
    echo 'Senha Ok';
}

