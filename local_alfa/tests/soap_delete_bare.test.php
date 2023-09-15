<?php
/**
 * Testa a criação de cursos via webservice
 */
require_once('soap_test.class.php');
require_once('../classes/alfa.class.php');

class soap_remove_user_event_test extends soap_test{

    function __construct(){
        parent::__construct('local_alfa_remove_user_event');
    }

    function test(){

        $params = Array();

        $params['idoffer'] = "cet-005462";

        $users = Array();

        $user = new stdClass();
        $user->username = '673611';
        $users[] = $user;

        $params['users'] = $users;

        parent::execute_test( $params );
    }

}

echo '<pre>';
$test = new soap_remove_user_event_test();
$test->test();
