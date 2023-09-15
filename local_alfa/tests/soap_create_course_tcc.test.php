<?php
/**
 * Testa a criação de cursos de tcc via webservice
 */
require_once('soap_test.class.php');
require_once('../classes/alfa.class.php');

class soap_alfa_create_course_tcc_test extends soap_test
{
    function __construct()
    {
        parent::__construct('local_alfa_create_course_tcc');
    }

    function test()
    {
//        $data = array(
//            'idoffer'   => '93730',
//            'fullname'  => 'TRABALHO DE CONCLUSÃO DE CURSO - ETAPA I',
//            'shortname' => 'TRABALHO DE CONCLUSÃO DE CURSO - ETAPA I',
//            'category'  => 'GRADUAÇÃO',
//            'period'    => '2017A',
//            'inidate'   => '16-02-2017',
//            'enddate'   => '15-07-2017',
//            'dayshift'  => 'Alternativo',
//            'dayofweek' => 'Estágio/TCC',
//            'groups'    =>
//                array(
//                    205702           =>
//                        array(
//                            0 =>
//                                array(
//                                    'roleid'   => '8',
//                                    'username' => '205702',
//                                ),
//                            1 =>
//                                array(
//                                    'roleid'   => '5',
//                                    'username' => '553861',
//                                ),
//                            2 =>
//                                array(
//                                    'roleid'   => '5',
//                                    'username' => '341067',
//                                ),
//                            6 =>
//                                array(
//                                    'roleid'   => '5',
//                                    'username' => '545720',
//                                ),
//                        ),
//                    507410           =>
//                        array(
//                            0 =>
//                                array(
//                                    'roleid'   => '8',
//                                    'username' => '507410',
//                                ),
//                            1 =>
//                                array(
//                                    'roleid'   => '5',
//                                    'username' => '509592',
//                                ),
//                        ),
//                    501921           =>
//                        array(
//                            0 =>
//                                array(
//                                    'roleid'   => '8',
//                                    'username' => '501921',
//                                ),
//                            1 =>
//                                array(
//                                    'roleid'   => '5',
//                                    'username' => '330074',
//                                ),
//                        ),
//                    'sem_orientacao' =>
//                        array(
//                            5 =>
//                                array(
//                                    'roleid'   => '5',
//                                    'username' => '551827',
//                                ),
//                            8 =>
//                                array(
//                                    'roleid'   => '5',
//                                    'username' => '553385',
//                                ),
//                        ),
//                ),
//            'otherusers' =>
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
//                ),
//        );
//        parent::execute_test($data);
        $c = Alfa::getCourseTCCInformation(100448);//busca informações de uma oferta e cria o ambiente
        parent::execute_test($c);
    }
}

$test = new soap_alfa_create_course_tcc_test();
$test->test();
?>
