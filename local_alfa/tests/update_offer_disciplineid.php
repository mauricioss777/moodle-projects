<?php

define('CLI_SCRIPT', true);

require_once('../../../config.php');
require_once('../classes/alfa.class.php');

$courses = $DB->get_records_sql('select * from mdl_local_alfa WHERE disciplineid is null');

foreach($courses as $course){
    error_log( print_r($course->id . ' ---' . $course->idnumber, true) );
    $courseinfo = Alfa::getCourseInformation($course->idnumber);
    $course->disciplineid = $courseinfo['disciplineid'];
    $DB->update_record('local_alfa', $course);
}
