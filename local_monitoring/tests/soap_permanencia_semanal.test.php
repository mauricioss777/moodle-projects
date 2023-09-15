<?php
/**
 * 
 */
require_once('soap_test.class.php');

class soap_permanencia_semanal_test extends soap_test{

    function __construct(){
        parent::__construct('local_monitoring_permanencia_semanal');
    }

    function test(){

        $params = Array();

        $estudantes = Array();

        $estudante = new stdClass();
        $estudante->codigo = 544677;
        $estudantes[] = $estudante;

        //$estudante = new stdClass();
        //$estudante->codigo = 'ahwelp';
        //$estudantes[] = $estudante;

        $params['estudantes'] = $estudantes;

        parent::execute_test( $params );

    }

}

echo '<pre>';
$test = new soap_permanencia_semanal_test();
$test->test();
