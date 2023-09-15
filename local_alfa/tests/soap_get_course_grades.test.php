<?php
/**
 * Testa a criação de cursos de tcc via webservice
 */
require_once('soap_test.class.php');
require_once('../classes/alfa.class.php');

class soap_alfa_get_course_grades extends soap_test
{
    function __construct()
    {
        parent::__construct('local_alfa_get_course_grades');
    }

    function test()
    {
        global $DB;


        $course      = isset($_GET['courseid']) ? $_GET['courseid'] : false;
        $scale       = isset($_GET['scale']) ? true : false;
        $onegrade    = isset($_GET['one']) ? true : false;
        $frequence   = isset($_GET['frequence']) ? true : false;
        $reference   = isset($_GET['ref']) ? $_GET['ref'] : false;
        $allownull   = isset($_GET['allownull']) ? true : false;
        $independent = isset($_GET['independent']) ? $_GET['independent'] : false;
        /*
        if(!$course && !$reference){
            echo 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']. '?courseid=xxxxx&ref=xxxxx&one=1&scale=1&allownull=1';
            die;
        }*/
        $reference = '326424';
        if(!$course){
            $course = $DB->get_record_sql("SELECT *
                                            FROM {course}
                                            WHERE id = (
                                                SELECT courseid FROM {local_alfa} WHERE idnumber = ?)", [$reference])->id;
        }

        $context = context_course::instance($course, MUST_EXIST);
        $students = get_role_users(5, $context);
        $usernames = [];

        foreach ($students as $student) {
            $usernames[] = array('username' => $student->username);
        }

        $data = array(
            'idoffer'      => $reference,
            'courseid'     => $course,
            'onegrade'     => $onegrade,
            'frequence'    => $frequence,
            'scale'        => $scale,
            'allownull'    => $allownull,
            'students'     => $usernames,
            'independent'  => $independent 
        );
        var_dump($data);

        parent::execute_test($data);
    }
}

$test = new soap_alfa_get_course_grades();
$test->test();

