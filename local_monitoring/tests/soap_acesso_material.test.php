<?php
/**
 * 
 */
require_once('soap_test.class.php');

class soap_acesso_material_test extends soap_test{

    function __construct(){
        parent::__construct('local_monitoring_acesso_material');
    }

    function test(){
        global $DB;

        $params = Array();

        $estudantes_list = explode(',', file_get_contents('alunos.txt'));
        $turmas_list = explode(',', file_get_contents('ofertas.txt'));

        $estudantes = Array();
        $turmas = Array();

        foreach($estudantes_list as $stud){
            $estudante = new stdClass();
            $estudante->codigo = $stud;
            $estudantes[] = $estudante;
        }

        foreach($turmas_list as $turm){
            $turma = new stdClass();
            $turma->courseid = $turm;
            $turmas[] = $turma;
        }

        $params['turmas'] = $turmas;
        $params['estudantes'] = $estudantes;
        $params['dt_base'] = '2019-01-01';

        parent::execute_test( $params );

    }

}

echo '<pre>';
$test = new soap_acesso_material_test();
$test->test();
