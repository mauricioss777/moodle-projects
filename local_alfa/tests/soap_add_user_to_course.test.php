<?php
/**
 * Testa a criação de cursos via webservice
 */
define('CLI_SCRIPT', true);


require_once('soap_test.class.php');
require_once('../classes/alfa.class.php');
class soap_alfa_add_user_to_course_test extends soap_test{

    function __construct(){
        parent::__construct('local_alfa_add_user_to_course');
    }

    function test(){
        $c['idoffer']   = 99;
        $c['username'] = '341039';
        $c['roleid'] = '5'; 
        $c['startdate'] = '0';
        $c['enddate'] = '231320'; 

//        $c = Alfa::getCourseInformation(101337);//busca informações de uma oferta e cria o ambiente
        echo '<pre>';
	echo "executando";
        parent::execute_test($c);
    }
}

$test = new soap_alfa_add_user_to_course_test();
$test->test();
?>
