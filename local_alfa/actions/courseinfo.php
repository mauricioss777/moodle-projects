<?php

require_once ('../../../config.php');
require_once('../classes/alfa.class.php');

$id = required_param('id', PARAM_INT);

$course = $DB->get_record('course', ['id' => $id]);
require_course_login($course, true);

$idnumbers = $DB->get_records('local_alfa', ['courseid' => $course->id]);
$references = Array();

foreach($idnumbers as $idnumber){
    $references[] = Alfa::getCourseInformation($idnumber->idnumber);
}

echo $OUTPUT->header();

echo "<pre>" . utf8_decode(var_dump($references)) . "</pre>";

echo $OUTPUT->footer();
