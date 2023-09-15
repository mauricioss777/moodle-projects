<?php
/**
 * Testa a criação de cursos via webservice
 */
require_once('soap_test.class.php');
class soap_alfa_add_offer_to_course_test extends soap_test{
    
    function __construct(){
        parent::__construct('local_alfa_add_offer_to_course');
    }

    function test(){
        $rand = rand(1,10000);
        $c['courseid']   = '31285';
        $c['idoffer']   = '110041';
//        $c['coursename']  = 'Disciplina '.$rand;
//        $c['teacher'] = 'Fulando de Tal '.$rand;

        parent::execute_test($c);
    }
}

$test = new soap_alfa_add_offer_to_course_test();
$test->test();
?>
