<?php
/**
 * Testa a criação de cursos via webservice
 */
define('CLI_SCRIPT', true);
require_once('soap_test.class.php');
require_once('../classes/alfa.class.php');

class soap_get_access_on_period_test extends soap_test{

    function __construct(){
        parent::__construct('local_alfa_get_access_on_period');
    }

    function test(){

        $params = Array();

        $params['username']  = 'admin';
        $params['startdate'] = '2021-01-01';
        $params['enddate']   = '2023-12-31';

        parent::execute_test( $params );
    }

}

echo '<pre>';
$test = new soap_get_access_on_period_test();
$test->test();
