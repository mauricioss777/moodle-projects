<?php
/**
 * 
 */
require_once('soap_test.class.php');

class soap_lexp_acessos_lms_dados extends soap_test{

    function __construct(){
        parent::__construct('local_monitoring_lexp_lms_dados');
    }

    function test(){

        parent::execute_test( '2020A-EAD1' );

    }

}

echo '<pre>';
$test = new soap_lexp_acessos_lms_dados();
$test->test();
