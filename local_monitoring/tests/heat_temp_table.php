<?php

define('CLI_SCRIPT', true);

require_once('../../../config.php');

$period = '2021B-EAD2';

$courses = $DB->get_records_sql("SELECT id FROM mdl_course WHERE category IN (SELECT id FROM mdl_course_categories WHERE name = ?) AND fullname not ilike ('%INTEGRADOR%') AND fullname not like ('%ESTÁGIO%')", [$period]);

foreach($courses as $course){

    // if($course->id != 33506){ continue; }

    var_dump($course);

    $students = $DB->get_records_sql("select userid, username, ? as period from mdl_role_assignments mra, mdl_user mus WHERE mus.id = mra.userid AND mra.contextid = (SELECT id from mdl_context WHERE instanceid = ? AND contextlevel = 50) AND roleid = 5", [$period, $course->id]);

    //var_dump($students);

    //---------------------------------------------------------
    // Atividades realizadas

    foreach($students as $key => $value){
        if(!is_numeric($value->username)){
            unset($students[$key]);
            continue;
        } 
        $students[$key]->courseid = $course->id;
    }
   
    //var_dump($students);

    $graded_items = $DB->get_records_sql("
        SELECT 
          itemmodule, 
          string_agg(mgi.iteminstance || '', ', ') as instances
         FROM 
          mdl_grade_items mgi, 
          mdl_grade_categories mgc 
         WHERE 
          mgi.categoryid = mgc.id AND 
          mgc.courseid = ? AND 
          depth = 2 AND 
          itemmodule IN ('quiz', 'assign') AND  
          itemname NOT ILIKE '%prova%' AND 
          itemname NOT ILIKE '%recuperação%' 
         GROUP BY itemmodule;", [$course->id]);
    
    if(!$graded_items){ // In case it is a Seminary
        $graded_items = $DB->get_records_sql("
            SELECT 
            itemmodule, 
            string_agg(mgi.iteminstance || '', ', ') as instances
            FROM 
            mdl_grade_items mgi, 
            mdl_grade_categories mgc 
            WHERE 
            mgi.categoryid = mgc.id AND 
            mgc.courseid = ? AND 
            depth = 1 AND 
            itemmodule IN ('quiz', 'assign') AND 
            itemname NOT ILIKE '%prova%' AND 
            itemname NOT ILIKE '%recuperação%' 
            GROUP BY itemmodule;", [$course->id]);
    }

    if(!isset($graded_items['assign'])){
        $graded_items['assign'] = (object)['itemmodule' => 'assign', 'instances' => '0'];
    }

    if(!isset($graded_items['quiz'])){
        $graded_items['quiz'] = (object)['itemmodule' => 'quiz', 'instances' => '0'];
    }

    $activity_sum = 0; 
    $activity_sum += sizeof(explode(',', $graded_items['assign']->instances));
    $activity_sum += sizeof(explode(',', $graded_items['quiz']->instances));

    $graded_items = $DB->get_records_sql("select userid, count(id) 
                                          from mdl_grade_grades 
                                          WHERE itemid IN (
                                            select id from mdl_grade_items WHERE courseid = ? AND iteminstance IN (
                                            ".$graded_items['assign']->instances . ', ' . $graded_items['quiz']->instances."
                                            )) 
                                          AND finalgrade > 0 GROUP BY userid; ", [$course->id] );

    foreach($students as $key => $value){
        $students[$key]->qtd_atividades_total = $activity_sum;
        $students[$key]->qtd_atividades_nao_realizadas = $activity_sum - $graded_items[$key]->count;
    }

    //---------------------------------------------------------
    // Materiais acessados 


    $materials = $DB->get_records_sql("select mmo.name, string_agg(mcm.instance || '', ',') as instance from mdl_course_modules mcm, mdl_modules mmo WHERE mmo.id = mcm.module and course = ? AND mmo.name in ('file', 'url', 'lti') AND mcm.visible = 1 GROUP BY mmo.name", [$course->id]);

    $materials_sum = 0;
    foreach($materials as $key => $value){
        $materials[$key]->instances = explode(',', $value->instance);
        $materials[$key]->instances = array_combine($materials[$key]->instances, $materials[$key]->instances); 
        $materials_sum += sizeof($materials[$key]->instances);
    }

    foreach($students as $key => $value){
        $students[$key]->qtd_total_material_didatico = $materials_sum;
        $students[$key]->qtd_acesso_material_didatico = $materials;
    }

    foreach($materials as $material){
        $materials_access = $DB->get_records_sql("select userid, string_agg(objectid || '', ',') as instances from mdl_logstore_standard_log WHERE courseid = ? AND objecttable = ? GROUP BY userid;", [$course->id, $material->name]);

        foreach($materials_access as $access){
            if(!isset($students[$access->userid])){ continue; }
            $students[$access->userid]->qtd_acesso_material_didatico['sum'] += sizeof(array_unique(explode(',', $access->instances)));
        }
    }

    foreach($students as $key => $value){
        $students[$key]->qtd_acesso_material_didatico = $students[$key]->qtd_acesso_material_didatico['sum'];
    }

    //---------------------------------------------------------
    // Videoconferências 
    
    //Load resourses to be searched
    $sql  = "SELECT 'bbb' as module, string_agg(id || '', ',') as id FROM mdl_bigbluebuttonbn WHERE course = $course->id ";
    $sql .= " UNION ";
    $sql .= "SELECT 'url' as module, string_agg(instanceid || '', ',') as id FROM mdl_local_monitoring_videos WHERE course = $course->id ";
    $sql .= " UNION ";
    $sql .= "SELECT 'meet' as module, string_agg(id || '', ',') as id FROM mdl_meet WHERE course = $course->id " ;
    $sql .= " UNION ";
    $sql .= "SELECT 'assign' as module, string_agg(id || '', ',') as id FROM mdl_assign WHERE course = $course->id AND name ILIKE ('%Videoconferência%') and name ILIKE ('%Ao vivo%')" ;
    $videoconferences = $DB->get_records_sql( $sql );

    $totalvideos = 0;
    foreach($videoconferences as $videoconference){
        if($videoconference->id == null) { continue; }

        $totalvideos += sizeof(explode(',', $videoconference->id));
        $attendanceses = [];

        switch ($videoconference->module) {
            case 'bbb':
                $attendanceses = $DB->get_records_sql("SELECT userid, string_agg(bigbluebuttonbnid || '', ',') as instances from mdl_bigbluebuttonbn_logs WHERE bigbluebuttonbnid IN ($videoconference->id) AND log IN ('Join', 'Played') GROUP BY userid");
                break;
            case 'url':
                $attendanceses = $DB->get_records_sql("select userid, string_agg(objectid || '', ',') as instances from mdl_logstore_standard_log WHERE courseid = ? AND objecttable = 'url' AND objectid IN ($videoconference->id) GROUP BY userid;", [$course->id]);
                break;
            case 'assign':
                $attendanceses = $DB->get_records_sql("select userid, string_agg(objectid || '', ',') as instances from mdl_logstore_standard_log WHERE courseid = ? AND objecttable = 'assign' AND objectid IN ($videoconference->id) GROUP BY userid;", [$course->id]);
                break;
            case 'meet':
                $attendanceses = $DB->get_records_sql("SELECT userid, string_agg(meetid || '', ',') as instances FROM mdl_meet_logs WHERE meetid IN ($videoconference->id) AND userid > 0 GROUP BY userid");
                break;
        }

        foreach($attendanceses as $attendance){
            if(!isset($students[$attendance->userid])) { continue; }
            $students[$attendance->userid]->qtd_videoconferencia_nao_participada += sizeof(array_unique(explode(',', $attendance->instances)));
        }
    }

    foreach($students as $key => $value){
        if(!isset($students[$key]->qtd_videoconferencia_nao_participada)){
            $students[$key]->qtd_videoconferencia_nao_participada = 0;
        }
        $students[$key]->qtd_total_videoconferencia = $totalvideos;
    }

    $DB->insert_records('local_monitoring_user_temp', $students);

    // var_dump($graded_items);
    // var_dump($students);
    // var_dump($materials);
    // die;
}
