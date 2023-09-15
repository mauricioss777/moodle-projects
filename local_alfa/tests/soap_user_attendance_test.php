<?php
/**
 * Testa a criação de cursos via webservice
 */
require_once('soap_test.class.php');
require_once('../classes/alfa.class.php');

class soap_user_attendance_test extends soap_test{

    function __construct(){
        parent::__construct('local_alfa_get_user_attendance');
    }

    function test(){

        $params = Array();

        $params['users'][] = '559573';
        $params['users'][] = 'fabricio.borba';

        parent::execute_test( $params );
    }

}
echo '<pre>';
$test = new soap_user_attendance_test();
$test->test();