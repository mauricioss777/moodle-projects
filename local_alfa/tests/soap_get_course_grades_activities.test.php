<?php
/**
 * Testa a criação de cursos via webservice
 */
define('CLI_SCRIPT', true);


require_once('soap_test.class.php');
require_once('../classes/alfa.class.php');
class soap_get_course_grades_activities extends soap_test{

    function __construct(){
        parent::__construct('local_alfa_get_course_grades_activities');
    }

    function test(){
        //294494
        //299907
        $c['idoffer'] = '294494';
        parent::execute_test($c);
    }
}

$test = new soap_get_course_grades_activities();
$test->test();
?>
