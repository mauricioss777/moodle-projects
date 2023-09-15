<?php


namespace local_alfa\task;

class sync_courses_task extends \core\task\scheduled_task {

    /**
     * Busca o nome descritivo para este agendamento (visível para admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('synccoursestask', 'local_alfa');
    }

    /**
     * Método que será executado pela cron
     */
    public function execute() {
        global $DB,$CFG;
        require_once($CFG->dirroot.'/config.php');
        require_once($CFG->dirroot.'/local/alfa/classes/alfa.class.php');
        require_once($CFG->dirroot.'/user/lib.php');

        mtrace("=====================================================");
        mtrace("Starting synchronization of courses with Alfa");
        mtrace("WARNING: This task works just with mysql or postgres");
        mtrace("=====================================================");
        flush();

        $sql = "SELECT a.idnumber, c.fullname, c.id, c.startdate  
                  FROM {course} as c, {local_alfa} as a 
                 WHERE c.id = a.courseid 
                   AND (c.enddate > ".$_SERVER['REQUEST_TIME']." OR c.enddate = 0)
                   AND a.idnumber <> 999 
                   AND a.idnumber <> 999999
              ORDER BY c.id";

        $courses = $DB->get_records_sql($sql);
        $tutorRamap = (array)json_decode($DB->get_record('config_plugins', ['name' => 'tutorremaping'] )->value);

        foreach($courses as $course){

            //if($course->id == '') { continue; }
            //if($course->id != '') { continue; }

            flush();
            mtrace("### CourseId: ".$course->id." - Curso: ".$course->fullname);
            $courseinfo = \Alfa::getCourseInformation($course->idnumber);
            
            //Componentes EAD devem ignorar os tutores
            if($courseinfo['dayofweek'] == 'Distância' || $courseinfo['ead'] || $courseinfo['dayshift'] == 'EAD'){
                foreach($courseinfo['users'] as $key => $user){
                    if($user['roleid'] == 3){
                        if(array_key_exists($user['username'], $tutorRamap)){
                            $courseinfo['users'][$key]['username'] = $tutorRamap[$user['username']];
                        }else{
                            unset($courseinfo['users'][$key]);
                        }
                    }
                    if($user['username'] == '506562'){
                        unset($courseinfo['users'][$key]);
                    }
                }
            }

            if($courseinfo !== FALSE){

                $maninstance = $DB->get_record('enrol', array('courseid'=>$course->id, 'enrol'=>'manual'), '*', MUST_EXIST);
                $manual = enrol_get_plugin('manual');
                /**
                * Este SQL busca no Moodle os usuários vinculados ao curso (maninstance->id) que tiveram este vinculo adicionado pelo Alfa
                */
                $sql = "SELECT u.username, u.id 
                          FROM {user} u, {user_enrolments} e 
                         WHERE u.id=e.userid 
                               AND e.enrolid = ".$maninstance->id." 
                               AND e.timeend = 0 
                               AND modifierid = 2";//modifierid = 2 // Identifica que o usuário foi adicionado com o usuário Admin (utilizado pelo alfa para vincular os usuários)
                $moodleusers = $DB->get_records_sql($sql);

                $diffusernames = array();
                foreach($courseinfo['users'] as $alfauser){//adicionando novos usuários
                    if(!isset($moodleusers[$alfauser['username']])){//se não encontou na lista do moodle o usuário que está no alfa
                        $diffusernames[] = $alfauser['username'];
                        if(!$u = $DB->get_record('user',array('username'=>$alfauser['username']))){//se o usuário não está cadatrado no moodle
                            $newuser = \Alfa::getUserInformation($alfauser['username']);
                            $userId = \user_create_user($newuser);
                            mtrace("****** Adding user: '".$newuser->username." - ".$newuser->firstname." ".$newuser->lastname."' to Moodle");
                            if($alfauser['duration']){ //Cursos de extensão
                                $manual->enrol_user($maninstance, $userId, $alfauser['roleid'], 0, time() + ( 60 * 60 * 24 * 30 ), null, true); // 30 Dias fixado
                            }else{
                                $manual->enrol_user($maninstance, $userId, $alfauser['roleid'], 0, 0, null, true);
                            }
                            mtrace("****** User '".$newuser->username." - ".$newuser->firstname." ".$newuser->lastname."' enrolled to course ".$course->fullname);
                        }else{//se o usuário já está cadastrado no moodle
                            if($alfauser['duration']){ //Cursos de extensão
                                $manual->enrol_user($maninstance, $u->id, $alfauser['roleid'], 0, time() + ( 60 * 60 * 24 * 30 ), null, true);
                            }else{
                                $manual->enrol_user( $maninstance, $u->id, $alfauser['roleid'], 0, 0, null, true  );
                            }
                            mtrace("****** User '".$u->username." - ".$u->firstname." ".$u->lastname."' enrolled to course ".$course->fullname);
                        }
                    }
                }

                if(!empty($diffusernames)){
                    $in = "'". implode("','",$diffusernames) ."'";
                    /**
                     * Este SQL identifica no Moodle quais usuários foram vinculados via alfa.
                     * Como este script é rodado pela cron, enrolid é cadastrado como 0, quando na verdade precisa ser 2
                     */
                    $sql = "UPDATE {user_enrolments} 
                               SET modifierid = 2 
                             WHERE enrolid = ".$maninstance->id." 
                                   AND userid in ( SELECT id FROM {user} WHERE username in (".$in."))";
                    $DB->execute($sql);
                }

                // Remove os usuários se tiver somente um registro no alfa, se houver mais de um registro
                // (como pós graduação e medicina que possuem várias turmas no mesmo ambiente)
                // não executa a remoção de usuários
                if(count(array_keys(array_column($courses, 'id'), $course->id)) == 1){

                    //Agora é preciso remover os usuários que não estão mais na lista do alfa e que estão na lista do moodle
                    $alfausernames = array();
                    foreach($courseinfo['users'] as $alfauser){
                        $alfausernames[] = $alfauser['username'];
                    }
                    $notIn = "'". implode("','",$alfausernames) ."'";

                    /**
                     * Este SQL busca no moodle os usuários que NÃO estão na lista de matriculados do Alfa
                     * Os usuários resultantes deste SQL devem ser removidos do curso.
                     */
                    $sql = "SELECT u.id, u.username, u.firstname, u.lastname, ro.roleid
                            FROM {user} u, {role_assignments} ro
                            WHERE u.id = ro.userid 
                            AND ro.contextid = (
                              SELECT id 
                              FROM {context} 
                              WHERE contextlevel = 50 AND instanceid = $course->id)
                            AND u.username not in (".$notIn.")
                            AND ro.modifierid = 2";

                    $moodleusers = $DB->get_records_sql($sql);
                    if(!empty($moodleusers)){
                        foreach($moodleusers as $muser){
                            //Vículos com menos de duas semanas e professores são excluídos
                            if( ($muser->roleid == '5' && (time() - $course->startdate) < 1209600) || $muser->roleid == '8' ){
                                $manual->unenrol_user($maninstance, $muser->id);//Desvincula usuários do curso
                                mtrace("****** User '".$muser->username." - ".$muser->firstname." ".$muser->lastname."' was unenrolled from course ".$course->fullname);
                            }else{
                                $manual->unenrol_user($maninstance, $muser->id);//Desvincula usuários do curso
                                //Vinculos com mais de duas semanas são mantidos, mas desabilitados
                                //$manual->update_user_enrol($maninstance, $muser->id, 1, null, time()); //Suspende a inscrição do usuário
                                mtrace("****** User '".$muser->username." - ".$muser->firstname." ".$muser->lastname."' was ommited from course ".$course->fullname);
                            }
                        }
                    }
                } else {
                    mtrace("Skipping users removal on course {$course->fullname}");
                }

            }
        }
        mtrace("Finished synchronization");
        mtrace("=============================================");
    }
}
