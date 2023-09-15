<?php
/**
 * Testa a criação de cursos via webservice
 */
require_once('soap_test.class.php');
require_once('../classes/inscricoes.class.php');

class soap_user_attendance_event_test extends soap_test{

    function __construct(){
        parent::__construct('local_inscricoes_get_attendance_event');
    }

    function test(){

        //$a = $_GET['schedule'];
        //$params = Array();
        //$params['schedule'] = $a;
        //$params['schedule'] = '5060';

        //var_dump( parent::execute_test( $params ) );

        Inscricoes::sendAttendenceSchedule(66779, [2846, 42091]);
    }

}

echo '<pre>';
$test = new soap_user_attendance_event_test();
$test->test();
