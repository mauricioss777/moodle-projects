<?php

define('CLI_SCRIPT', true);
require_once('../../../config.php');

die;

$courses = $DB->get_records_sql('');
$userid = "xxxx";

foreach($courses as $course){
    echo "Removendo $userid do curso $course->id \n";
    $maninstance = $DB->get_record('enrol', array('courseid'=>$course->id, 'enrol'=>'manual'), '*', MUST_EXIST);
    $manual = enrol_get_plugin('manual');
    $manual->unenrol_user($maninstance, $userid);
}

