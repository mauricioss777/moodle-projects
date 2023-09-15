<?php
/**
 * 
 */
require_once('soap_test.class.php');

class soap_atividades_realizadas_test extends soap_test{

    function __construct(){
        parent::__construct('local_monitoring_atividades_realizadas');
    }

    function test(){
        global $DB;

        $params = Array();

        $turmas = Array();
        $estudantes = Array();

        //$users = [566506, 564907, 527813];
        //$users = [544677, 652295];
        $users = [561684,576371,701807,628759,576308,564907,650842,605693,693304,640229,627507,547419,667829,670378,566506,692308,692046 ];
        $users = array_keys($DB->get_records_sql( "SELECT username from mdl_user WHERE id IN (SELECT userid FROM mdl_role_assignments WHERE contextid IN (SELECT id FROM mdl_context WHERE instanceid IN (SELECT id FROM mdl_course WHERE category = 264) AND contextlevel = 50)) and auth = 'ldap'" ));
        foreach($users as $user){
            $estudante = new stdClass();
            $estudante->codigo = $user;
            $estudantes[] = $estudante;
        }
        //var_dump($estudantes);die;

        //$courses = [34950];
        // $courses = [35966, 35952, 35965, 35967];
        $courses = array_keys( $DB->get_records('course', ['category' => 264] ) ); 
        foreach($courses as $course){
            $turma = new stdClass();
            $turma->courseid = $course;
            $turmas[] = $turma;
        }

        /*$turma = new stdClass();
        $turma->courseid = 34697;
        $turmas[] = $turma;//////////////////////////
        $turma = new stdClass();
        $turma->courseid = 34698;
        $turmas[] = $turma;//////////////////////////
        
        $turma = new stdClass();
        $turma->courseid = 34697 ;
        $turmas[] = $turma;
         */

        $params['turmas'] = $turmas;
        $params['estudantes'] = $estudantes;
        // $params['dt_base'] = '2020-08-01';

        var_dump( parent::execute_test( $params ) );
    }

}

echo '<pre>';
$test = new soap_atividades_realizadas_test();
$test->test();
