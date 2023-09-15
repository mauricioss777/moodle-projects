<?php
die;
require_once(__DIR__.'/../classes/inscricoes.class.php');

$users = [];
$schedule = '';


foreach($users as $user){
    try{
        echo "Adicionando $user no schedule $schedule <br />";
        Inscricoes::sendAttendenceSchedule($schedule, [$user]);
    }catch (Exception $msg){

    }
}


