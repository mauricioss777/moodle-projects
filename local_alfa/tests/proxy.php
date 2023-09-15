<?php

require_once('../../../config.php');
 
require_login();
 
if(!is_siteadmin() && !in_array($USER->username, $CFG->eadusers)){
    die();                 
}

error_reporting(0); 

@$ch = curl_init();
@curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
@curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
@curl_setopt($ch, CURLOPT_TIMEOUT, 60);
@curl_setopt($ch, CURLOPT_COOKIESESSION, true);
@curl_setopt($ch, CURLOPT_VERBOSE, true);
  
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    @curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($_POST));
}
 
$get_data = explode('?', $_SERVER['REQUEST_URI'])[1];
  
if($USER->username == 'monique.fick' || $USER->username == 'pkirst'){
    @curl_setopt($ch, CURLOPT_URL, '10.100.0.100/sistemas/disciplinas/index.php?'.$get_data);
}else{
    @curl_setopt($ch, CURLOPT_URL, '10.100.0.100/sistemas/checklist_ead/index.php?'.$get_data);
}
                                                                                                                                                                                       
@$content = curl_exec($ch);

$content = str_replace('index.php', 'proxy.php', $content);
  
echo $content;

