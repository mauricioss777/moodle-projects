<?php
/**
 * Plugin de integração entre Alfa-Moodle
 *
 * @package    local_inscricoes
 * @author     Maurício S. Silva - mss@univates.br
 * @author     Artur H. Welp - ahwelp@univates.br
 * @author     Alexandre S. Wolf - awolf@univates.br
 * @author     Núcleo de Educação a Distância
 * @copyright  2014 Centro Universitário UNIVATES
 * @since      Moodle 2.7
 */
defined('MOODLE_INTERNAL') || die;
require_once("$CFG->libdir/externallib.php");

require_once('classes/inscricoes.class.php');
require_once('classes/inscricoeshelper.php');

class local_inscricoes_external extends external_api{

    public function create_course_event_parameters(){
        return new external_function_parameters(array(
            'course' => new external_single_structure(array(
                'idoffer'    => new external_value(PARAM_TEXT, 'IDOffer do evento a ser criado', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'fullname'   => new external_value(PARAM_TEXT, 'Nome completo da disciplina', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'inidate'    => new external_value(PARAM_TEXT, 'Data de início do curso/disciplina', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'enddate'    => new external_value(PARAM_TEXT, 'Data de encerramento do curso/disciplina', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'activities' => new external_multiple_structure(new external_single_structure(array(
                    'activityid'         => new external_value(PARAM_INT, 'Código da atividade', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                    'activityfree'       => new external_value(PARAM_INT, 'Atividade é gratuíta?', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                    'activityname'       => new external_value(PARAM_TEXT, 'Nome da atividade', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                    'activitycriteria'   => new external_value(PARAM_INT, 'Critério de inscrição', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                    'teacher'    => new external_multiple_structure(new external_single_structure(array(
                        'username'   => new external_value(PARAM_INT, 'Código de usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED)
                    ), 'Lista dos professores da atividade.') ),
                    'students'     => new external_multiple_structure(new external_single_structure(array(
                        'username' => new external_value(PARAM_INT, 'Código de usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED)
                    ), 'Lista de estudantes da atividade.') ),
                    'schedules'   => new external_multiple_structure(new external_single_structure(array(
                        'scheduleid'   => new external_value(PARAM_INT,  'Id da ocorrencia', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                        'schedulename' => new external_value(PARAM_TEXT, 'Nome ad ocorrencia', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                        'initime'      => new external_value(PARAM_TEXT, 'Início da ocorrência', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                        'endtime'      => new external_value(PARAM_TEXT, 'Fim da ocorrência', VALUE_REQUIRED, '', NULL_NOT_ALLOWED)
                    ), 'Informações das ocorrências')),
                ), 'Lista das Atividades')),
            ), 'Informações do curso que deverá ser criado'),
        ) );
    }

    //http://10.100.0.51/inscricoes/lucas/
    public function create_course_event( $params ){
        global $DB, $CFG;

        require_once($CFG->dirroot . '/lib/accesslib.php');
        require_once($CFG->dirroot . '/course/lib.php');
        require_once($CFG->libdir  . '/coursecatlib.php');

        $course = null;
        $event = $DB->get_record('local_inscricoes', Array('idnumber' => $params['idoffer']) );

        if( !$event ) {
            try {
                //Search category
                $category = $DB->get_record_sql("SELECT id  
                                              FROM {course_categories} 
                                             WHERE name = ? AND 
                                             parent = (SELECT id FROM {course_categories} WHERE name = 'EVENTOS')",
                    Array(substr($params['inidate'], 0, 4)));

                //Create category if not exists
                if(!$category){
                    $event_cat_id = $DB->get_record_sql("SELECT id FROM {course_categories} WHERE name = 'EVENTOS'");
                    $cat = new stdClass();
                    $cat->depth = 2;
                    $cat->parent = $event_cat_id->id;
                    $cat->name = substr($params['inidate'], 0, 4);
                    $cat->id = $DB->insert_record('course_categories', $cat);
                    $category = $cat;
                }

                //Create course
                $c = Array();
                $c['fullname'] = mb_strtoupper(utf8_decode($params['fullname']." - {$params['idoffer']}"));
                $c['shortname'] = mb_strtoupper(utf8_decode($params['fullname']." - {$params['idoffer']}"));
                self::_check_course_shortname( $c['shortname'] );
                $c['category'] = $category->id;
                $c['numsections'] = 0;
                $c['format'] = 'topics';
                $c['timecreated'] = time();
                $c['startdate'] = strtotime($params['inidate']);
                $c['enddate'] = strtotime($params['enddate']) + 604800; // One week
                $c['summary'] = "";
                $course = create_course( (object) $c );

                // Activate autoenrol on course
                $enrol = $DB->get_record_sql("SELECT *  
                                                FROM {enrol} 
                                                WHERE 
                                                  courseid = $course->id AND 
                                                  enrol = 'self';");
                $enrol->password = $params['idoffer'];
                $enrol->status = 0;
                $enrol->customint1 = 1;
                $enrol->customint4 = 0;
                $DB->update_record('enrol', $enrol);

                \core\event\enrol_instance_created::create_from_record($enrol)->trigger();

                $relation = new stdClass();
                $relation->courseid = $course->id;
                $relation->idnumber = $params['idoffer'];
                $DB->insert_record('local_inscricoes', $relation);

            } catch (Exception $ex) {
                if (isset($course)) {
                    delete_course($course);
                    $DB->execute('DELETE FROM {local_inscricoes} WHERE courseid = ?', array(
                        $course->id,
                    ));
                }
                error_log("local_inscricoes_ERROR::create_course_event:: " . $ex->getMessage());
                throw new moodle_exception($ex->getMessage());
            }
        }else{
            $course = $DB->get_record_sql("SELECT mcu.id 
                                                FROM {course} mcu, {local_inscricoes} mlae 
                                               WHERE mcu.id = mlae.courseid AND 
                                                     mlae.idnumber = ?", Array($params['idoffer']));
        }

        $helper = new event_helper($course->id);

        foreach ($params['activities'] as $activity){
            $helper->add_teachers( $activity['teacher'] );
            $helper->set_student ( $activity['students'][0]['username'] );
        }

        $helper->manage_users();
        
        if($event->static){ return $course->id; }

        $my_activities = Array();
        $ticks = 0;

        try{
            foreach ($params['activities'] as $activity){
                $helper->manage_activities($activity);
                $my_activities[] = $activity['activityid'];
                $ticks++;
            }
        }catch (Exception $ex) { $log->error = utf8_decode(print_r($ex, true)); }

        return $course->id;
    }

    public function create_course_event_returns(){
        return new external_value(PARAM_INT, 'Retorna o ID do curso no moodle. Utilizado para gerar o link_ead.');
    }

    public function remove_user_event_parameters(){
        return new external_function_parameters(array(
            'activities' => new external_single_structure(array(
                'idoffer'    => new external_value(PARAM_TEXT, 'activityid', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'activities'   => new external_multiple_structure(new external_single_structure(array(
                    'activityid'   => new external_value(PARAM_INT,  'Id da ocorrencia', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                    'username' => new external_value(PARAM_TEXT, 'Nome ad ocorrencia', VALUE_REQUIRED, '', NULL_NOT_ALLOWED)
                ), 'Informações das ocorrências')),
            ), 'Identificação do curso'),
        ));
    }

    public function remove_user_event( $course_info ){
        global $DB;
        
        error_log("Removing Users");
        error_log(print_r($course_info, true));
return 1;
        foreach ($course_info['activities'] as $activity){
            $user = $DB->get_record('user', Array('username' => $activity['username']) );
            $group = $DB->get_record('groups', Array('idnumber' => $activity['activityid']));

            $DB->execute("DELETE FROM {groups_members}
                        WHERE groupid = $group->id AND                             
                              userid = $user->id");

            $has_group = $DB->get_record_sql("SELECT * 
                                            FROM {groups_members} 
                                          WHERE userid = $user->id AND groupid IN 
                                                    (SELECT id 
                                                      FROM {groups} 
                                                     WHERE courseid = $group->courseid)");
            try {
                if (!$has_group) {
                    $manualinstance = $DB->get_record('enrol', array(
                        'courseid' => $group->courseid,
                        'enrol' => 'manual',
                    ), '*', MUST_EXIST);
                    $manual = enrol_get_plugin('manual');
                    $manual->unenrol_user($manualinstance, $user->id);
                }
            }catch (Exception $ex) {
                //Die silently
            }
        }

        return 1;

    }

    public function remove_user_event_returns(){
        return new external_value(PARAM_INT, 'Retorna 1 se tudo OK.');
    }

    public function get_attendance_event_parameters(){
        return new external_function_parameters(array(
            'idoffer' => new external_single_structure(array(
                'schedule'    => new external_value(PARAM_TEXT, 'ID do schedules a serem buscados', VALUE_REQUIRED, '', NULL_NOT_ALLOWED)
            ), 'Informações do curso que deverá ser criado'),
        ) );
    }

    public function get_attendance_event($offers){
        global $DB;

        $return = Array();

        $instance = $DB->get_record('course_modules', Array('idnumber' => 'schedule-'.$offers['schedule']));
        $low_time = json_decode($instance->availability)->c[0]->t;
        $module = $DB->get_record('modules', Array('id' => $instance->module));

        $users = $DB->get_records_sql("SELECT count(mus.username), mus.username 
                    FROM {logstore_standard_log} mlsl, {user} mus   
                   WHERE mlsl.userid = mus.id AND 
                         mlsl.objecttable = '$module->name' AND
                         mlsl.objectid = $instance->instance AND 
                         mlsl.timecreated > $low_time
                    GROUP BY mus.username");

        $return['scheduleid'] = $offers['schedule'];
        $return['students'] = [];
        foreach ($users as $user){
            $return['students'][]['username'] = $user->username;
        }

        return $return;

    }

    public static function get_attendance_event_returns(){
        return new external_function_parameters(array(
            'scheduleid' => new external_value(PARAM_TEXT, 'Código do usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
            'students' => new external_multiple_structure(new external_single_structure(array(
                'username' => new external_value(PARAM_TEXT, 'Código do usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED)
            ))),
        ));
    }

    private static function _check_course_shortname($shortname)
    {
        // Importa as variáveis globais
        global $DB;
        // Verifica se já existe
        if($DB->record_exists('course', array('shortname' => $shortname))) {
            error_log('There is already an course with that shortname.');
            throw new moodle_exception('There is already an course with that shortname.');
        }
    }
}
