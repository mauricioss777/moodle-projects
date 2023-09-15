<?php


define('CLI_SCRIPT', true);
require_once('../classes/alfa.class.php');
require_once('../../../config.php');

/*if(!is_siteadmin()){
    die('Você não tem permissão de executar este script.');
}*/
$userid;
if(isset($argv[1]) && is_number($argv[1]) ){
    $userid = $argv[1];
}else{
    die;
}

echo print_r(Alfa::getUserInformation($userid), true);
?>
