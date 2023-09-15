<?php
/**
 * Testa a criação de cursos via webservice
 */
require_once('soap_test.class.php');
require_once('../classes/inscricoes.class.php');

class soap_create_course_event_test extends soap_test{

    function __construct(){
        parent::__construct('local_inscricoes_create_course_event');
    }

    function test(){

        $params = Array();

        $params['idoffer']   = "3176";
        $params['fullname']  = "SER UNIVATES: TÉCNICO-ADMINISTRATIVOS 20/08";
        $params['inidate']   = "2019-01-01";
        $params['enddate']   = "2020-07-12";

        $activity = Array();
        $activity['activityid'] = '7556';
        $activity['criteria'] = '64';
        $activity['activityfree'] = '1';
        $activity['activityname'] = 'Aula Inicial';
        $activity['teacher'] = Array();

        $activity['teacher'][0] = ['username'=>'ahwelp'];
        $activity['teacher'][1] = ['username'=>'527813'];

        $activity['students'] = Array();
        //$activity['students'][0] = ['username'=>'648409'];
        $activity['students'][0] = ['username'=>'516897'];

        $activity['schedules'] = Array();

        $schedules = Array();
        $schedules['scheduleid'] = '5070';
        $schedules['schedulename'] = 'Atividade 1';
        $schedules['initime'] = '2019-05-20 19:30';
        $schedules['endtime'] = '2019-05-20 21:30';

        $activity['schedules'][] = $schedules;

        $schedules = Array();
        $schedules['scheduleid'] = '5072';
        $schedules['schedulename'] = 'Atividade 2';
        $schedules['initime'] = '2019-05-21 19:30';
        $schedules['endtime'] = '2019-05-21 21:30';

        $activity['schedules'][] = $schedules;

        $params['activities'][] = $activity;

        $activity = Array();
        $activity['activityid'] = '7557';
        $activity['criteria'] = '60';
        $activity['activityfree'] = '1';
        $activity['activityname'] = 'Aula dois';
        $activity['teacher'] = Array();

        $activity['teacher'][0] = ['username'=>'ahwelp'];
        $activity['teacher'][1] = ['username'=>'527813'];

        $activity['students'] = Array();
        $activity['students'][0] = ['username'=>'527813'];

        $activity['schedules'] = Array();

        $schedules = Array();
        $schedules['scheduleid'] = '5074';
        $schedules['schedulename'] = 'Atividade 12';
        $schedules['initime'] = '2019-12-20 19:30';
        $schedules['endtime'] = '2019-12-20 21:30';

        $activity['schedules'][] = $schedules;

        $schedules = Array();
        $schedules['scheduleid'] = '5075';
        $schedules['schedulename'] = 'Atividade 2';
        $schedules['initime'] = '2019-12-21 19:30';
        $schedules['endtime'] = '2019-12-21 21:30';

        $activity['schedules'][] = $schedules;

        $params['activities'][] = $activity;

        parent::execute_test( $params );
    }

}

echo '<pre>';
$test = new soap_create_course_event_test();
$test->test();
