<?php
/**
 * 
 */
require_once('soap_test.class.php');

class soap_permanencia_semanal_test extends soap_test{

    function __construct(){
        parent::__construct('local_monitoring_permanencia_semanal_historico');
    }

    function test(){

        $params = Array();

        $estudantes = Array();

        $estudante = new stdClass();
        $estudante->codigo = 596546;
        $estudantes[] = $estudante;

        $estudante = new stdClass();
        $estudante->codigo = 667341;
        $estudantes[] = $estudante;

        $estudante = new stdClass();
        $estudante->codigo = 624491;
        $estudantes[] = $estudante;

        $estudante = new stdClass();
        $estudante->codigo = 588404;
        $estudantes[] = $estudante;

        $estudantes[] = $estudante;
        $params['estudantes'] = $estudantes;
        $params['data_inicial'] = '2020-07-01';
        $params['data_final']   = '2020-12-31';

        parent::execute_test( $params );

    }

}

echo '<pre>';
$test = new soap_permanencia_semanal_test();
$test->test();
