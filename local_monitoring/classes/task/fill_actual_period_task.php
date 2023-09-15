<?php


namespace local_monitoring\task;

class fill_actual_period_task extends \core\task\scheduled_task {

    /**
     * Busca o nome descritivo para este agendamento (visível para admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('fillactualperiod', 'local_monitoring');
    }

    /**
     * Método que será executado pela cron
     */
    public function execute() {
        global $DB,$CFG;
        require_once($CFG->dirroot.'/config.php');
        require_once($CFG->dirroot.'/user/lib.php');

        mtrace("=====================================================");
        mtrace("Starting synchronization of courses with Alfa");
        mtrace("WARNING: This task works just with mysql or postgres");
        mtrace("=====================================================");
        flush();

        $period = self::get_actual_period();

        // Clear registers
        $DB->execute("DELETE FROM mdl_local_monitoring_user_temp WHERE period = ?", [self::get_actual_period()]);

        // return;
        // Load courses
        $courses = $DB->get_records_sql("SELECT id FROM mdl_course WHERE category IN (SELECT id FROM mdl_course_categories WHERE name = ?) AND fullname not ilike ('%INTEGRADOR%') AND fullname not like ('%ESTÁGIO%') AND fullname not like ('%CONCLUSÃO%')", [$period]);

        // Iterate
        foreach($courses as $course){
            //if($course->id != 38402) { continue; }
            //if($course->id != 39356) { continue; }
            //if($course->id != 38421) { continue; }
            mtrace($course->id);

            $students = $DB->get_records_sql("select userid, username, ? as period from mdl_role_assignments mra, mdl_user mus WHERE mus.id = mra.userid AND mra.contextid = (SELECT id from mdl_context WHERE instanceid = ? AND contextlevel = 50) AND roleid = 5", [$period, $course->id]);

            foreach($students as $key => $value){
                if(!is_numeric($value->username)){
                    unset($students[$key]);
                    continue;
                } 
                $students[$key]->courseid = $course->id;
            }

            mtrace("Activities");
            $activities = self::get_gradeble_items($course->id);
            // mtrace( print_r($activities, true) );

            mtrace("Materials");
            $materials = self::get_sections($course->id);
            //mtrace( print_r($materials, true) );

            mtrace("Videos");
            $videos = self::get_videos($course->id);
            //mtrace( print_r($videos, true) );

            foreach($students as $key => $value){
                $students[$key]->qtd_atividades_total = $activities['sum'];
                $students[$key]->qtd_atividades_nao_realizadas = $activities['sum'];
                $students[$key]->qtd_total_material_didatico = $materials['sum'];
                $students[$key]->qtd_acesso_material_didatico = 0;
                $students[$key]->qtd_total_videoconferencia = sizeof( explode(',', $videos) );
                $students[$key]->qtd_videoconferencia_nao_participada = sizeof( explode(',', $videos) );
            }

            mtrace("Gather Activities");
            foreach($activities as $activity){
                $submissions = [];
                switch ($activity->itemmodule) {
                    case 'assign':
                        $submissions = $DB->get_records_sql("SELECT userid FROM mdl_assign_submission WHERE assignment IN ($activity->instances) AND status = 'submitted' AND latest = 1");
                        break;
                    case 'quiz':
                        $submissions = $DB->get_records_sql("select userid from mdl_quiz_attempts WHERE quiz IN ($activity->instances) AND attempt = 1 AND state = 'finished'");
                        break;
                }
                foreach($submissions as $submission){
                    if(!isset($students[$submission->userid])){ continue; }
                    $students[$submission->userid]->qtd_atividades_nao_realizadas--;
                }
            }

            mtrace("Gather Materials");
            foreach($materials as $material){
                if($material->name == ''){ continue; }
                $materials_accesses = $DB->get_records_sql("select userid, string_agg(objectid || '', ',') as instances from mdl_logstore_standard_log WHERE courseid = $course->id AND objecttable = '$material->name' AND objectid IN ($material->instances) GROUP BY userid;", [$course->id, $material->name]);
                foreach($materials_accesses as $material_acess){
                    if(!isset($students[$material_acess->userid])){ continue; }
                    $students[$material_acess->userid]->qtd_acesso_material_didatico += sizeof(array_unique(explode(',', $material_acess->instances)));
                }
            }

            mtrace("Gather Videos");
            $attendanceses = $DB->get_records_sql("SELECT userid, string_agg(meetid || '', ',') as instances FROM mdl_meet_logs WHERE meetid IN ($videos) AND userid > 0 GROUP BY userid");
            foreach($attendanceses as $attendance){
                if(!isset($students[$attendance->userid])){ continue; }
                $students[$attendance->userid]->qtd_videoconferencia_nao_participada -= sizeof(array_unique(explode(',', $attendance->instances)));
            }

            // return;
            // mtrace( print_r($students, true) );
            $DB->insert_records('local_monitoring_user_temp', $students);

        }

        mtrace("Finished synchronization");
        mtrace("=============================================");
    }

    private static function get_actual_period(){
        return '2022A-EAD2';
    }


    private static function get_gradeble_items($course){
        global $DB;
    
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
             GROUP BY itemmodule;", [$course]);
        
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
                GROUP BY itemmodule;", [$course]);
        }

        foreach($graded_items as $key => $graded_item){
            switch ($graded_item->itemmodule) {
                case 'assign':
                    $graded_items[$key]->instances = implode(',', array_keys($DB->get_records_sql("SELECT id FROM mdl_assign WHERE id IN ($graded_item->instances) AND nosubmissions = 0 AND allowsubmissionsfromdate < ? AND duedate < ?", [time(), time()])));
                    break;
                case 'quiz':
                    $graded_items[$key]->instances = implode(',', array_keys($DB->get_records_sql("SELECT id FROM mdl_quiz WHERE id IN ($graded_item->instances) and timeopen < ? AND timeclose < ?", [time(), time()])));
                    break;
            }
            if($graded_items[$key]->instances != ''){
                $graded_items['sum'] += sizeof(explode(',', $graded_items[$key]->instances));
            }
        }

        // mtrace( print_r($graded_items, true) );

        if(!isset($graded_items['assign']) || empty($graded_items['assign']->instances) ){
            $graded_items['assign'] = (object)['itemmodule' => 'assign', 'instances' => '0'];
        }

        if(!isset($graded_items['quiz']) || empty($graded_items['quiz']->instances) ){
            $graded_items['quiz'] = (object)['itemmodule' => 'quiz', 'instances' => '0'];
        }

        return $graded_items;
    }

    private static function get_sections($course){
        global $DB;

        $sections = $DB->get_records_sql('select * from mdl_course_sections WHERE course = ? and visible = 1', [$course]);
        
        foreach($sections as $key => $value){
            if($value->availability == ''){
                continue;
            }
            if(json_decode($value->availability)->c[0]->t > time()){
                unset($sections[$key]);
            }
        }

        $sections = implode(',', array_keys($sections));

        $materials = $DB->get_records_sql("select mmo.name, string_agg(mcm.instance || '', ',') as instances from mdl_course_modules mcm, mdl_modules mmo WHERE mmo.id = mcm.module AND section IN ($sections) AND mmo.name in ('file', 'url', 'lti') AND mcm.visible = 1 GROUP BY mmo.name");

        foreach($materials as $material){
            $materials['sum'] += sizeof(explode(',', $material->instances));
        }

        return $materials;

    }

    public static function get_videos($course){
        global $DB;

        $videos = $DB->get_records_sql("SELECT id FROM mdl_meet WHERE course = ? and timeend < ?", [$course, time()]);

        if(empty($videos)){
            $videos = [0];
        }

        return implode(',', array_keys($videos));
    }

}
