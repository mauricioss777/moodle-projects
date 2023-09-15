<?php
/**
 * Testa a criação de cursos via webservice
 */
require_once('soap_test.class.php');

class soap_user_internal_code_test extends soap_test{

    function __construct(){
        parent::__construct('local_monitoring_user_internal_code');
    }

    function test(){
        $params = Array();

        $estudantes = Array();

        $estudante = new stdClass();
        $estudante->codigo = 544677;
        $estudantes[] = $estudante;

        $estudante = new stdClass();
        $estudante->codigo = 527813;
        $estudantes[] = $estudante;

        $params['estudantes'] = $estudantes;

        parent::execute_test( $params );
    }

}
echo '<pre>';
$test = new soap_user_internal_code_test();
$test->test();
