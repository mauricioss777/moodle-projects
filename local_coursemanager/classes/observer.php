<?php

defined('MOODLE_INTERNAL') || die();

require_once('coursemanager.class.php');

class local_coursemanager_observer{

    public static function local_coursemanager_course_restored(core\event\course_restored $event){
        global $DB;

        //error_log( print_r($event, true) );
        return;
        $data = $event->get_data();

        if($data->other['mode'] != 20){
            return;
        }

        $newCourse = $DB->get_record('course', ['id' => $data['courseid']]);
        $originalCourse = $DB->get_record('course', ['id' => $data['other']['originalcourseid']]);

        // The new course is of a specific type
        if( strpos($newCourse->fullname, 'ESTÁGIO SUPERVISIONADO') > -1 ){
            return;
        }

        if( strpos($newCourse->fullname, 'TRABALHO DE CONCLUSÃO DE CURSO') > -1 ){
            return;
        }

        if( strpos($newCourse->fullname, 'SEMINÁRIO INTEGRADOR') > -1 ){
            return;
        }

        if( strpos($newCourse->fullname, 'PROJETO INTEGRADOR') > -1 ){
            return;
        }

        // The new course is not on any EAD category, ABORTING
        $newCategory = $DB->get_record('course_categories', ['id' => $newCourse->category]);
        if(!strpos($newCategory->name, 'EAD')){
            return;
        }

        // The old course is not on any EAD category, ABORTING
        $oldCategory = $DB->get_record('course_categories', ['id' => $originalCourse->category]);
        if(!strpos($oldCategory->name, 'EAD')){
            return;
        }

        //Calculate last friday
        $startDate = $newCourse->startdate;
        $formaDate = date("Y:m:d", $startDate);
        $dayOfWeek = date("w", $startDate);
        $month     = date("m", $startDate);
        $returnDays = $dayOfWeek + 2;
        $correctedStartDate = $startDate - ($returnDays * 86400);
        $correctedFormaDate = date("Y:m:d", $correctedStartDate);

        $i = 1;
        $lenght = sizeof($topics); 

        $sections = $DB->get_records_sql('SELECT * FROM {course_sections} WHERE course = ? AND section > 0', [$newCourse->id]);
       
        $stepDate = $correctedStartDate;
        foreach($sections as $section){
            if($i == (sizeof($sections)-1)){
                $i = 0;
                break;
            }
            $tmpAval = $stepDate + (19 * 60 * 60);
            $section->availability = "{\"op\":\"&\",\"c\":[{\"type\":\"date\",\"d\":\">=\",\"t\":$tmpAval}],\"showc\":[false]}";
            if($i != (sizeof($sections)-3) && sizeof($sections) > 6){
                $stepDate += (86400 * 7);
            }
            $DB->update_record('course_sections', $section);
            $i++;
        }

        //Update start date from course
        $newCourse->startdate = $correctedStartDate;
        $DB->update_record('course', $newCourse);
        cache_helper::invalidate_by_definition('core', 'groupdata', array(), array($newCourse->id));

    }

}
