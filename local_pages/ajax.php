<?php

define('AJAX_SCRIPT', true);

require_once('../../config.php');
require_once('lib.php'); 

$method = (function(){
    if(isset($_POST['method'])){ return $_POST['method']; }
    if(isset($_GET['method'])){ return $_GET['method']; }
    return 'noop';
})();

call_user_func($method);

function noop(){

}

function messages(){
    global $DB, $CFG, $USER;
    header('Content-Type: application/json');

    if(local_pages_user_has_messages()){
        $messages = local_pages_get_messages_json();
        $messages_encode = json_encode($messages); 
        if($messages_encode == ''){ echo $messages[0]['value']; die; }
        echo $messages_encode;
    }else{
        echo json_encode([]);
    }

}

