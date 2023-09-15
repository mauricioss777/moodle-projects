<?php
/**
 * Testa a criação de cursos via webservice
 */
require_once('soap_test.class.php');
require_once('../classes/alfa.class.php');

class soap_set_test_grade_test extends soap_test{

    function __construct(){
        parent::__construct('local_alfa_set_test_grade');
    }

    function test(){

        $params = Array();

        $params['grade']    = '70';
        $params['idoffer']  = '206420';
        $params['idoffer']  = '246695';
        // $params['idoffer']  = '246736';
        $params['username'] = '692046';
        $params['username'] = '526202';
        $params['username'] = '629685';

        parent::execute_test( $params );
    }

}

echo '<pre>';
$test = new soap_set_test_grade_test();
$test->test();
