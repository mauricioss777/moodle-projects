<?php
/**
 * 
 */
require_once('soap_test.class.php');

class soap_lexp_acessos_po_dias extends soap_test{

    function __construct(){
        parent::__construct('local_monitoring_lexp_acessos_po_dias');
    }

    function test(){

        parent::execute_test( '2022-05-24' );

    }

}

echo '<pre>';
$test = new soap_lexp_acessos_po_dias();
$test->test();
