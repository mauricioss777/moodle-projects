<?php                                                                                                                                                                                
header("Access-Control-Allow-Origin: *");
 
require_once('../../../config.php');
 
require_login();
 
if(!is_siteadmin() && !in_array($USER->username, $CFG->eadusers)){
    die();
}

error_reporting(0);
ini_set("display_errors", 0);

$ch = curl_init();
 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
 
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($_POST));
}
 
$get_data = '?' . explode('?', $_SERVER['REQUEST_URI'])[1];
  
if($USER->username == 'monique.fick' || $USER->username == "pkirst"){
    $url = '10.100.0.100/sistemas/disciplinas/ajax.php' . ($get_data == '?' ? '' : $get_data);
}else{
    $url = '10.100.0.100/sistemas/checklist_ead/public/ajax.php' . ($get_data == '?' ? '' : $get_data);
}
 
@curl_setopt($ch, CURLOPT_URL, $url );
 
@$content = curl_exec($ch);
 
echo $content;

