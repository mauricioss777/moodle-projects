<?php
/**
 * Testa a criação de cursos via webservice
 */
require_once('soap_test.class.php');
require_once('../classes/inscricoes.class.php');

class soap_remove_user_event_test extends soap_test{

    function __construct(){
        parent::__construct('local_inscricoes_remove_user_event');
    }

    function test(){

        $params = Array();

        $params['idoffer'] = '2222';
        $params['activities'] = [];
        $params['activities']['activityid']  = "7557";
        $params['activities']['username']  = "527813";

        parent::execute_test( $params );
    }

}

echo '<pre>';
$test = new soap_remove_user_event_test();
$test->test();
