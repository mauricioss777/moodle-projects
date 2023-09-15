<?php
/**
 * 
 */
require_once('soap_test.class.php');

class soap_participacao_videoconferencia_test extends soap_test{

    function __construct(){
        parent::__construct('local_monitoring_participacao_videoconferencia');
    }

    function test(){

        global $DB;

        $params = Array();

        $turmas = Array();
        $estudantes = Array();

        $courses = [36028];
        foreach($courses as $course){
            $turma = new stdClass();
            $turma->courseid = $course;
            $turmas[] = $turma;
        }

        $users = [566506, 564907, 527813];
        foreach($users as $user){
            $estudante = new stdClass();
            $estudante->codigo = $user;
            $estudantes[] = $estudante;
        }


        $params['turmas'] = $turmas;
        $params['estudantes'] = $estudantes;
        // $params['dt_base'] = '2021-02-26';
        
        parent::execute_test( $params );

    }

}

echo '<pre>';
$test = new soap_participacao_videoconferencia_test(); 
$test->test();
