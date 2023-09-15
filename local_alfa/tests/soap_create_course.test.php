<?php
/**
 * Testa a criação de cursos via webservice
 */
require_once('soap_test.class.php');
require_once('../classes/alfa.class.php');
class soap_alfa_create_course_test extends soap_test{

    function __construct(){
        parent::__construct('local_alfa_create_course');
    }

    function test(){
        $rand = rand(1,10000) * -1;
//        $rand = '87672';
        $c['idoffer']   = $rand;
        $c['fullname']  = 'TESTE SOAP';
        $c['shortname'] = 'TESTE SOAP - REF'.$rand;
        $c['category']  = 'GRADUAÇÃO';
        $c['period']    = '2020';
        $c['inidate']   = '2020-06-06';
        $c['enddate']   = '2020-12-31';
        $c['dayshift']  = 'EAD';
        $c['format']    = 'Atelier';
        // $c['format']    = 'Seminário';
        $c['dayofweek'] = 'Segunda-Feira';
        // $c['dayofweek'] = 'Regime Especial';
        // $c['numsections'] = '6';
        // $c['workload'] = '40';
        // $c['eadworkload'] = '20';
        $c['users'][0]['username'] = '3030';//código do professor
        $c['users'][0]['roleid'] = '8';// 3== função = professor
        $c['users'][1]['roleid'] = '5';
        $c['users'][1]['username'] = '501921';
        $c['users'][2]['roleid'] = '5';
        $c['users'][2]['username'] = '330074';

//        for($i=1; $i<20; $i++){
//            $rand = rand(25000,30000);
//            $c['users'][$i]['username'] = "$rand";
//            $c['users'][$i]['roleid'] = '5'; //5 == student
//        }
//        $c['otherusers'] =
//                array(
//                    8 =>
//                        array(
//                            'roleid'   => '8',
//                            'username' => '5215',
//                        ),
//                    7 =>
//                        array(
//                            'roleid'   => '8',
//                            'username' => '565650',
//                        ),
//                    1 =>
//                        array(
//                            'roleid'   => '8',
//                            'username' => '557065',
//                        ),
//                    2 =>
//                        array(
//                            'roleid'   => '8',
//                            'username' => '558003',
//                        ),
//                );

 //       $x = Alfa::getCourseInformation(345158);//busca informações de uma oferta e cria o ambiente
//	$x['shortname'] = $x['fullname'];
//	$x['syllabus'] = utf8_encode($x['syllabus']);

//	error_log(print_r($x,true)); die();
        echo '<pre>';
        parent::execute_test($c);
    }
}

$test = new soap_alfa_create_course_test();
$test->test();
?>
