<?php


namespace local_alfa\task;

class sync_curriculum_task extends \core\task\scheduled_task {
    private $roles = array();
    
    function __construct() {
        global $DB;
        
        $this->roles['coordenadores'] = $DB->get_record_sql("select id from mdl_role where shortname = 'editingteacher'");
        $this->roles['professores'] = $DB->get_record_sql("select id from mdl_role where shortname = 'coordenador'");
        $this->roles['alunos'] = $DB->get_record_sql("select id from mdl_role where shortname = 'student'");
        
    }

    /**
     * Busca o nome descritivo para este agendamento (visível para admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('synccoursestaskcurriculum', 'local_alfa');
    }

    /**
     * Método que será executado pela cron 
     */
    public function execute(){
        global $DB,$CFG;
        require_once($CFG->dirroot.'/config.php');
        require_once($CFG->dirroot.'/local/alfa/classes/alfa.class.php');
        require_once($CFG->dirroot.'/user/lib.php');
    
        mtrace("=====================================================");
        mtrace("Starting synchronization of courses with Alfa");
        mtrace("WARNING: This task works just with mysql or postgres");
        mtrace("=====================================================");
        flush();

        $courses = $DB->get_records_sql("SELECT courseid FROM {local_alfa_curriculum}");
        
        foreach($courses as $course){
            $users = Array();

            // Busca todos os usuários para os currículos vinculados ao curso
            $curriculums = $DB->get_records_sql("SELECT * FROM {local_alfa_curriculum} WHERE courseid = ?", Array($course->courseid));
            foreach($curriculums as $curriculum){
                $users = array_merge_recursive($users, \Alfa::getCurriculumInformation($curriculum->curriculum, $curriculum->skipdatecheck));
            }

            // Carrega os usuários inscritos no curso
            $context = \context_course::instance($course->courseid, MUST_EXIST);
            $maninstance = $DB->get_record('enrol', array('courseid'=>$course->courseid, 'enrol'=>'manual'), '*', MUST_EXIST);

            $sql = "SELECT u.username, u.id 
                      FROM {user} u, {user_enrolments} e, {enrol} en, {course} co 
                     WHERE u.id = e.userid 
                           AND co.id = en.courseid 
			   AND e.enrolid = en.id 
			   AND ( co.enddate > ".time()." OR co.enddate = 0 )
                           AND e.enrolid = ".$maninstance->id." 
                           AND modifierid = 2";

            // Carrega usuários do banco e faz uma junção dos usuários do WS
            $allusers = array_merge($users['coordenadores'], $users['professores'], $users['alunos']);
            $moodleusers = $DB->get_records_sql($sql);

            // Remove os usuários que nao tem codigo
            foreach($moodleusers as $key => $user){
                if(!is_numeric( $user->username) ){
                    unset($moodleusers[$key]);
                    continue;
                }
            }

            // Calcula usuários a adicionar e remover
            $remove = array_values(array_diff(array_keys($moodleusers), $allusers));
            $add    = array_values(array_diff($allusers, array_keys($moodleusers)));

            // Remove os usuários que nao tem codigo
            foreach($moodleusers as $key => $user){
                if(!is_numeric( $user->username) ){
                    unset($moodleusers[$key]);
                    continue;
                }
            }

            foreach($remove as $user){
                $this->unenrol_user_by_username($user, $course->courseid);
            }

            foreach($add as $user){
                if(in_array($user, $users['coordenadores'])){
                    $this->enrol_user_by_username($user, $course->courseid, $this->roles['coordenadores']->id);
                    $this->enrol_user_by_username($user, $course->courseid, $this->roles['professores']->id);
                }

                if(in_array($user, $users['professores'])){
                    $this->enrol_user_by_username($user, $course->courseid, $this->roles['professores']->id);
                }

                if(in_array($user, $users['alunos'])){
                    $this->enrol_user_by_username($user, $course->courseid, $this->roles['alunos']->id);
                }
            }
        }
        mtrace("Finished synchronization");
        mtrace("=============================================");
        mtrace("=============================================");
        mtrace("=============================================");
    }


    /*
     * Enrol user
     */
    function enrol_user_by_username($username, $course, $role){
        global $DB, $CFG;

        if(!is_numeric($username)){
            return;
        }

        $id = $DB->get_record( 'user', Array('username' => $username) )->id;

        if(!$id){
            $id = user_create_user( \Alfa::getUserInformation( $username ) );
            mtrace("###### Criando usuário: $username com o id -> $id");
        }

        $maninstance = $DB->get_record('enrol', array('courseid'=>$course, 'enrol'=>'manual'), '*', MUST_EXIST);
        $manual = enrol_get_plugin('manual');
        $manual->enrol_user( $maninstance, $id, $role );
        mtrace("###### $course Adicionando usuário: $username");
    }

    /*
     * UnEnrol user
     */
    function unenrol_user_by_username($username, $course){
        global $DB, $CFG;

        if(!is_numeric($username)){
            return;
        }

        $id = $DB->get_record( 'user', Array('username' => $username) )->id;

        $maninstance = $DB->get_record('enrol', array('courseid'=>$course, 'enrol'=>'manual'), '*', MUST_EXIST);
        $manual = enrol_get_plugin('manual');
        $manual->unenrol_user($maninstance, $id);
        mtrace("###### Removendo usuário: $username");
    }
}
