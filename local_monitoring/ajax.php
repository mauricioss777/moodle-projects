<?php

define('AJAX_SCRIPT', true);

require_once('../../config.php');
require_once('lib.php');

if(!local_monitoring_user_can_access()){ die; }

$method = (function(){
    if(isset($_POST['method'])){ return $_POST['method']; }
    if(isset($_GET['method'])){ return $_GET['method']; }
    return 'noop';
})();

call_user_func($method);

function noop(){

}

function calendar(){
    global $DB;

    $eventss = Array();

    $start = strtotime(explode('T', $_GET['start'])[0]);
    $end = strtotime( explode('T', $_GET['end'])[0] );

    $user = $DB->get_record('user', Array('username' => $_GET['tutor']))->id;

    $events = $DB->get_records_sql("SELECT *
        FROM {local_monitoring_sessions}
        WHERE
        timestart > ? AND
        timeend < ? AND
        userid = ?", Array($start, $end, $user) );

    foreach ($events as $event){
        $ev = [];
        $ev['title'] = "";
        $ev['start'] = date("Y-m-d", $event->timestart ) . 'T'. date("H:i:s", $event->timestart );
        $ev['end'] = date("Y-m-d", $event->timeend). 'T'. date("H:i:s", $event->timeend );
        $eventss[] = $ev;
    }

    echo json_encode($eventss);
}

function course(){
    global $DB, $CFG;
    header('Content-Type: application/json');

    echo json_encode(
        [
            'full' => $CFG->wwwroot . "/report/outline/user.php?id=".$_POST['user']."&course=".$_POST['course']."&mode=complete&headless=1",
            'data'  => get_last_access_corse( $_POST['user'], $_POST['course']),
            'grade' => get_user_grades($_POST['user'], $_POST['course'] ),
            'activities' => get_user_activities_on_course( $_POST['user'], $_POST['course'] )  ]
        );

}

function user(){
    global $DB;
    header('Content-Type: application/json');

    if( !isset($_POST['username'] ) ){
        echo json_encode([]); die;
    }

    $users = $DB->get_records_sql("SELECT username as value, firstname || ' ' || lastname as key FROM {user} WHERE username LIKE ('%".$_POST['username']."%') LIMIT 10" );

    echo json_encode($users);

}

function category(){
    global $DB;
    header('Content-Type: application/json');

    if( !isset($_POST['category'] ) ){
        echo json_encode( [] ); die;
    }

    $users = $DB->get_records_sql("SELECT mcc1.id as value, mcc1.name || ' - ' || mcc2.name as key FROM {course_categories} mcc1, {course_categories} mcc2 WHERE mcc2.id = mcc1.parent AND mcc1.name LIKE ('%".$_POST['category']."%') LIMIT 10" );

    echo json_encode($users);
}

function fin(){
    global $CFG;

    include_once($CFG->dirroot . '/local/alfa/classes/alfa.class.php');
    $return = "";
    foreach( Alfa::titulosAbertos($_POST['user']) as $title){
        $return .= local_monitoring_build_table($title); 
    }

    if($return == ''){ $return = '<h2> Sem títulos em aberto </h2>'; }

    echo $return;

    die;
}
