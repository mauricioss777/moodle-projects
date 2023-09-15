<?php
/**
 * 
 */
require_once('soap_test.class.php');

class soap_performance_atividades_test extends soap_test{

    function __construct(){
        parent::__construct('local_monitoring_performance_atividades');
    }

    function test(){
        global $DB;

        $params = Array();

        //$users = [/*544677, 527813, 651857,*/ 566506, 564907];
        $users = array_keys($DB->get_records_sql( "SELECT username from mdl_user WHERE id IN (SELECT userid FROM mdl_role_assignments WHERE contextid IN (SELECT id FROM mdl_context WHERE instanceid IN (SELECT id FROM mdl_course WHERE category = 264) AND contextlevel = 50)) and auth = 'ldap'" ));
        foreach($users as $user){
            $estudante = new stdClass();
            $estudante->codigo = $user;
            $estudantes[] = $estudante;
        }
        
        $courses = [34950];
        //$courses = array_keys( $DB->get_records('course', ['category' => 266] ) ); 
        foreach($courses as $course){
            $turma = new stdClass();
            $turma->courseid = $course;
            $turmas[] = $turma;
        }

        $params['turmas'] = $turmas;
        $params['estudantes'] = $estudantes;

        parent::execute_test( $params );

    }

}

echo '<pre>';
$test = new soap_performance_atividades_test();
$test->test();
