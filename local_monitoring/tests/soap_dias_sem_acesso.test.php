<?php
/**
 * 
 */
require_once('soap_test.class.php');

class soap_dias_sem_acesso_test extends soap_test{

    function __construct(){
        parent::__construct('local_monitoring_dias_sem_acesso');
    }

    function test(){

        $params = Array();

        $estudante_list = explode(',', file_get_contents('alunos.txt'));
        $estudantes = Array();

        /*$estudante = new stdClass();
        $estudante->codigo = 544677;
        $estudantes[] = $estudante;*/

        foreach($estudante_list as $l){
            $estudante = new stdClass();
            $estudante->codigo = $l;
            $estudantes[] = $estudante;
        }

        $oferta_list = explode(',', file_get_contents('ofertas.txt'));
        $turmas = Array();

        /*$turma = new stdClass();
        $turma->courseid = 33523;
        $turmas[] = $turma;*/

        foreach($oferta_list as $o){
            $turma = new stdClass();
            $turma->courseid = $o;
            $turmas[] = $turma;
        }

        $params['turmas'] = $turmas;
        $params['estudantes'] = $estudantes;
        $params['dt_base'] = '2019-01-01';

        parent::execute_test( $params );

    }

}

echo '<pre>';
$test = new soap_dias_sem_acesso_test();
$test->test();
