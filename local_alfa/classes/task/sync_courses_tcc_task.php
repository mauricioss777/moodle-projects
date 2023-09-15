<?php

namespace local_alfa\task;
use Horde\Socket\Client\Exception;

class sync_courses_tcc_task extends \core\task\scheduled_task
{
    private $roles = array();
    private $manual;

    function __construct()
    {
        global $DB;
        $this->roles['teacher'] = $DB->get_record_sql("select id from mdl_role where shortname = 'editingteacher'");
        $this->roles['student'] = $DB->get_record_sql("select id from mdl_role where shortname = 'student'");
        // Pega a instancia de inscrição
        $this->manual = enrol_get_plugin('manual');
    }

    /**
     * Busca o nome descritivo para este agendamento (visível para admins).
     *
     * @return string
     */
    public function get_name(){
        return get_string('synccoursestcctask', 'local_alfa');
    }

    /**
     * Método que será executado pela cron
     */
    public function execute(){
        global $DB, $CFG;

        error_log('antes script: ' . time());

        require_once($CFG->dirroot . '/group/lib.php');
        require_once($CFG->dirroot . '/config.php');
        require_once($CFG->dirroot . '/local/alfa/classes/alfa.class.php');
        require_once($CFG->dirroot . '/local/alfa/classes/alfahelper.php');
        require_once($CFG->dirroot . '/user/lib.php');

        mtrace("======================================================");
        mtrace("Starting synchronization of tcc courses with Alfa");
        mtrace("WARNING: This task works just with mysql or postgres");
        mtrace("======================================================");
        flush();

        // Busca todos os cursos de tcc em aberto
        $query = "SELECT c.id, c.category, c.fullname, tcc.idnumber
                    FROM {local_alfa_tcc} as tcc, {course} as c 
                   WHERE tcc.courseid = c.id 
                     AND (c.enddate > " . $_SERVER['REQUEST_TIME'] . " OR c.enddate = 0)
                ORDER BY c.id";

        $coursestcc = $DB->get_records_sql($query);

        foreach ($coursestcc as $key => $course) {

            if($course->category < 145){
                mtrace("STATUS: Skyping \"{$course->fullname}\"");
                continue;
            }

            //if($course->id == ?????){ continue; } // Courses to skip
            if($course->id == 38484){ continue; } // Courses to skip
            if($course->id == 38569){ continue; } // Courses to skip
            if($course->id == 38574){ continue; } // Courses to skip
            if($course->id == 39609){ continue; } // Courses to skip
            if($course->id == 39949){ continue; } // Courses to skip
            if($course->id == 39950){ continue; } // Courses to skip
            //if($course->id != ?????){ continue; } // Courses to specificaly test

            mtrace("======================================================");
            mtrace("STATUS: Updating course \"{$course->shortname}\" $course->id");
            mtrace("======================================================");

            $alfainfo = \Alfa::getCourseTCCInformation($course->idnumber);

            //The data is not correct from the WebService -> Fixing
            $possible_other = array_pop($alfainfo['groups']);
            if($possible_other[0]['roleid'] == '5'){
                $alfainfo['groups']['sem_orientacao'] = $possible_other;
            }else{
                $alfainfo['groups'][$possible_other[0]['roleid']] = $possible_other;
            }

            $helper = new \tcc_helper();
            $helper->load_context($course->id);
            $helper->load_users($alfainfo['groups']);
            $helper->load_other($alfainfo['otherusers']);
            $helper->manage_users();

            unset($alfainfo['groups']['sem_orientacao']);

            foreach ($alfainfo['groups'] as $group) {
                $helper->remanage_group($group);
            }

            $helper->clear_teachers();
            $helper->clear_students();
            $helper->clear_caches();

        }

        error_log('depois script: ' . time());

        mtrace("Finished synchronization");
        mtrace("=============================================");
    }

}
