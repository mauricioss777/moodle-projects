<?php

define('CLI_SCRIPT', true);

require_once('../../../config.php');
require_once('../classes/alfa.class.php');

$curriculos = Array(9981, 9980, 9993, 9994, 90021, 90022, 90016, 90017, 90018, 90019, 90020, 90024, 90025, 90033, 90032, 90026, 90027, 90035, 90034, 90028, 90029, 90030, 90031, 90068, 90069, 90067, 90066, 90070, 90071, 90072, 90073, 90082, 90083, 90085, 90086, 90089, 90090, 90092, 90093, 90096, 90097, 90101, 90102);
$students = Array();
$sac = [];

foreach($curriculos as $curriculo){
    $courseinfo = Alfa::getCurriculumInformation($curriculo, true);
    foreach($courseinfo['alunos'] as $user){
        $sac[$user][] = $curriculo;
    }
    $students = array_merge($students, $courseinfo['alunos']);
}

$students = implode("', '", $students);
$cutoffdate = time() - (60 * 60 * 24 * 365);
$students = $DB->get_records_sql("SELECT username, firstname || ' ' || lastname as name, to_char(to_timestamp(lastaccess)::date, 'DD/MM/YYYY')::text as acesso FROM mdl_user WHERE username IN ('$students') AND lastaccess < $cutoffdate");

foreach($students as $student){
    if($student->acesso == '31/12/1969'){ $student->acesso = "Nunca acessou"; }
    echo "$student->username | $student->name | ".implode(', ', $sac[$student->username])." | $student->acesso \n";
}

if(!is_siteadmin()){
    // die('Você não tem permissão de executar este script.');
}

die;

/*$users = $DB->get_records_sql("select * from mdl_user WHERE timemodified > 1640995200 AND department = '';");

foreach($users as $user){
    $alfa_info = Alfa::getUserInformation($user->username);
    if(!$alfa_info->institution){
        continue;
    }
    $user->institution = $alfa_info->institution;
    $user->department = $alfa_info->department;
    $DB->update_record('user', $user);
}


die;
 */
$courses = $DB->get_records_sql("SELECT * FROM {course} WHERE category IN (540, 541);");
foreach($courses as $course){
    $alfa = $DB->get_record('local_alfa', ['courseid' => $course->id]);
    $course->idnumber = $alfa->idnumber;
    $DB->update_record('course', $course);
}

die;
// $courseinfo = Alfa::getCourseInformation(247790)['users'];//85752
$courseinfo = Alfa::getCourseInformation(247712)['users'];//85752
$realusers = [];

// $users = $DB->get_records_sql("SELECT username, id FROM mdl_user WHERE id IN (SELECT userid FROM mdl_role_assignments WHERE contextid IN (select id from mdl_context WHERE contextlevel = 50 AND instanceid = 38277) AND roleid = 5);");
$users = $DB->get_records_sql("SELECT username, id FROM mdl_user WHERE id IN (SELECT userid FROM mdl_role_assignments WHERE contextid IN (select id from mdl_context WHERE contextlevel = 50 AND instanceid = 38280) AND roleid = 5);");

foreach($courseinfo as $user){
    if($user['roleid'] == 5){
        $realusers[$user['username']] = $user;
    }
}

$manual = enrol_get_plugin('manual');
$maninstance = $DB->get_record('enrol', array('courseid' => 38280, 'enrol'=>'manual'), '*', MUST_EXIST);

foreach($users as $u){
    if(!array_key_exists($u->username, $realusers)){
        echo "User: $u->id must go\n";
        //$manual->unenrol_user($maninstance, $u->id);//Desvincula usuários do curso
    }else{
        //echo "User: $u must stay\n";
    }
}
