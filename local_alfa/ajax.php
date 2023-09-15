<?php
/**
 * Busca o plano de ensino no Alfa e retorna o arquivo binário.
 *
 */
require_once('../../config.php');
require_once('classes/alfa.class.php');

$action     = required_param('action', PARAM_ALPHANUMEXT);
$idnumber   = optional_param('idnumber',0,PARAM_INT);
$courses    = optional_param_array('courses',false, PARAM_INT);

switch ($action) {
    case "getTeachingPlan":
        if(Alfa::isTeachingPlan($idnumber)){
            // Retorna o PDF para o plano de ensino
            header('Content-type: application/pdf');
            @header('Content-Disposition: inline; filename="Plano_de_Ensino_'.$idnumber.'.pdf"');
            echo Alfa::getTeachingPlan($idnumber);
        }
        break;
    case "getCoursesInformation":
        if($courses){
            $courses = implode(',', $courses);
            $sql = "SELECT id,summary FROM {course} WHERE id in (".$courses.")";
            $courses = $DB->get_records_sql($sql);
            $data = array();
            foreach ($courses as $course) {
                $ob = new stdClass();
                $ob->id = $course->id;
                $ob->summary = $course->summary;
                $data[] = $ob;
            }
            echo json_encode($data);
        }
        break;
}
