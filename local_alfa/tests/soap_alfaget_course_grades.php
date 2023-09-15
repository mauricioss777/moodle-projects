<?php
/**
 * Testa a criação de cursos via webservice
 */
define('CLI_SCRIPT', true);
require_once('soap_test.class.php');
require_once('../classes/alfa.class.php');

class soap_alfaget_course_grades_test extends soap_test{

    function __construct(){
        parent::__construct('local_alfa_alfaget_course_grades');
    }

    function test(){

        $params = Array();

        $params['idoffer']  = '350977';

        parent::execute_test( $params );
    }

}

echo '<pre>';
$test = new soap_alfaget_course_grades_test();
$test->test();
