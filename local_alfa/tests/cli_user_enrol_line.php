<?php

define('CLI_SCRIPT', true);

require_once('../../../config.php');

$courses = Array(30163, 32412, 32578, 32718, 32722, 32723, 32741, 32758, 33726, 33727, 33729, 33794, 33795, 34117, 35372, 35379, 35393, 35417, 35456, 35457, 35541, 35545, 35552, 35667, 35742, 36447, 36448, 36449, 36458, 36528, 36618, 36621, 36685, 36704);
$users = Array('adriani.rodrigues', 'rafaela.valduga');
$userid = '0';
$role = 'manager'; //coordenador, editingteacher, student

$tmp_user = '';
if($userid){
    $tmp_user = $USER->id;
    $USER->id = $userid;
}

$sql = "SELECT shortname,id FROM {role} ";
$roles = $DB->get_records_sql($sql);

$user_list = "'" . implode("', '", $users) . "'";
$sql = "SELECT username, id, firstname || ' ' || lastname as fullname FROM {user} WHERE username IN ($user_list)";
$users = $DB->get_records_sql($sql);

foreach ($courses as $course){

    $maninstance = $DB->get_record('enrol', array('courseid'=>$course, 'enrol'=>'manual'), '*', MUST_EXIST);
    $manual = enrol_get_plugin('manual');

    foreach ($users as $user){
        $manual->enrol_user($maninstance, $user->id, $roles[$role]->id);
        echo "$user->fullname Inscrito no curso: $course\n";
    }

}

$USER->id = $tmp_user;
