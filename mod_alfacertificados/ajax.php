<?php

define('AJAX_SCRIPT', true);

require_once('../../config.php');
require_once('lib.php');

//if(!local_monitoring_user_can_access()){ die; }

$method = (function(){
    if(isset($_POST['method'])){ return $_POST['method']; }
    if(isset($_GET['method'])){ return $_GET['method']; }
    return 'noop';
})();

call_user_func($method);

function noop(){
}

function params(){
    global $DB;

    $ret = alfacertificados_get_methods($_POST['value'], false, $_POST['course']);
    echo $ret; die;

}
