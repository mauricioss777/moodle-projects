<?php
/**
 * Testa a criação de cursos via webservice
 */
require_once('soap_test.class.php');
require_once('../classes/alfa.class.php');

class soap_course_internal_code_test extends soap_test{

    function __construct(){
        parent::__construct('local_alfa_course_internal_code');
    }

    function test(){

        $params = Array();

        $params['idoffer']  = '206420';
        //$params['idoffer']  = '223925';

        parent::execute_test( $params );
    }

}

echo '<pre>';
$test = new soap_course_internal_code_test();
$test->test();
