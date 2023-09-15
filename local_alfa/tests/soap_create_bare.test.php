<?php
/**
 * Testa a criação de cursos via webservice
 */
require_once('soap_test.class.php');
require_once('../classes/alfa.class.php');

class soap_create_course_event_test extends soap_test{

    function __construct(){
        parent::__construct('local_alfa_create_course_event');
    }

    function test(){

        $params = Array();

        $params['idoffer'] = "cet-0d0d9";

        $params['fullname'] = 'SER UNIVATES: TÉCNICO-ADMINISTRATIVOS 20/08';

        $params['inidate']   = '2018-01-01';
        $params['enddate']   = '2018-07-12';

        $users = Array();

        $users[] = '527813';
        $users[] = '604858';

        $params['users'] = $users;

        parent::execute_test( $params );
    }

}

echo '<pre>';
$test = new soap_create_course_event_test();
$test->test();
