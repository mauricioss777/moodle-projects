<?php

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/externallib.php");

class local_monitoring_external extends external_api {

    public static $datedMods = [''];


    //Todo pegar dt_base 

    public static function acesso_material_parameters(){
        return new external_function_parameters(array(
            'user' => new external_single_structure(array(
                'estudantes' => new external_multiple_structure(new external_single_structure(array(
                    'codigo' => new external_value(PARAM_INT, 'Codigo do usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED)
                ))),
                'turmas' => new external_multiple_structure(new external_single_structure(array(
                    'courseid' => new external_value(PARAM_INT, 'Código da turma, ferencia do Moodle ', VALUE_REQUIRED, '', NULL_NOT_ALLOWED)
                ))),
                'dt_base' => new external_value(PARAM_TEXT, 'Data retroativa para avaliar', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
            ), 'Informações do usuários'),
        ));
    }

    public static function acesso_material($info){

        global $DB;

        $turmas = '';
        $user_data = [];
        $estudantes = '';
        $estudantes_id = '';
        $return = ['user' => ['estudantes' => [] ] ];

        $turmas_lote = [];

        foreach( $info['turmas'] as $turma){ $turmas_lote[] = $turma['courseid'];  $turmas .= $turma['courseid'] . ', '; }
        foreach( $info['estudantes'] as $estudante){ $estudantes .= "'" . $estudante['codigo']  . "', "; }

        $turmas = rtrim($turmas, ', ');
        $estudantes = rtrim($estudantes, ", ");
        
        $estudantes_ids = $DB->get_records_sql("SELECT id, username FROM mdl_user WHERE username IN ($estudantes)");
        foreach( $estudantes_ids as $e){ $estudantes_id .= $e->id  . ", "; }
        $estudantes_id = rtrim($estudantes_id, ", ");

        if(!$info['dt_base']){

            $sql= "SELECT 
                     id, 
                     courseid, 
                     username, 
                     userid, 
                     to_timestamp(max(lasttime)) 
                   FROM 
                     mdl_local_monitoring_history 
                   WHERE 
                     username IN ($estudantes) AND 
                     courseid IN($turmas) 
                   GROUP BY 
                     id, 
                     courseid, 
                     username, 
                     userid";

            $registers = $DB->get_records_sql($sql);

            $user_ids = [];
            $user_courses = [];


            foreach($registers as $register){
                if( in_array($register->courseid, $users_courses[$register->userid]) ){ continue; }
                $user_data[$register->userid][] = ['courseid' => $register->courseid, 'lastacess' => $register->to_timestamp];
                $user_ids[$register->userid] = $register->username;
                $users_courses[$register->userid][] = $register->courseid;
            }

            foreach($user_ids as $key => $value){
                $return['user']['estudantes'][] = ['codigo' => $value, 'userid' => $key, 'turmas' => $user_data[$key]];
            }

            return $return;

        }else{
            $resources_list = array_keys($DB->get_records_sql("SELECT 'mod_' || name FROM {modules}"));
            $resources = '';
            foreach($resources_list as $r){ $resources .= "'$r',"; }
            $resources = rtrim($resources, ',');

            $chunks = array_chunk($turmas_lote, 100);

            //foreach($chunks as $chunk){
            $time_ref = (strtotime($info['dt_base']) + 60 * 60 * 24);
            $turma_lote = implode(',', $chunk);
            foreach( $info['turmas'] as $turma ){
                $turma = $turma['courseid'];
                $sql = "SELECT 
                            userid,
                            to_timestamp( max(mlog.timecreated) ) as timestamp
                        FROM 
                            mdl_logstore_standard_log mlog
                        WHERE 
                            component IN ($resources) AND 
                            userid IN ($estudantes_id) AND 
                            courseid = $turma AND 
                            mlog.timecreated < $time_ref  
                        GROUP BY userid";
                //error_log( "BUSCANDO MATERIAL - ".$turma );
                $registers = $DB->get_records_sql($sql);

                foreach($registers as $reg){
                    $user_data[$reg->userid][] = ['courseid' => $turma, 'lastacess' => $reg->timestamp];
                }
            }
            foreach( $estudantes_ids as $key => $value){
                if( !isset($user_data[$key]) ){ $user_data[$key] = []; }
                $return['user']['estudantes'][] = ['codigo' => $value->username, 'userid' => $key, 'turmas' => $user_data[$key] ];
            }
        }

        return $return;
        /*return ['user' => [ 'estudantes' => [
            ['codigo' => 555, 'userid'=>'50', 'turmas' => [ ['courseid' => 50, 'lastacess' => '112312'], ['courseid' => 50, 'lastacess' => '112312']  ] ],
            ['codigo' => 555, 'userid'=>'50', 'turmas' => [ ['courseid' => 50, 'lastacess' => '112312'] ] ]
        ] ] ];*/

    }

    public static function acesso_material_returns(){
        return new external_function_parameters(array(
            'user' => new external_single_structure(array(
                'estudantes' => new external_multiple_structure(new external_single_structure(array(
                    'codigo' => new external_value(PARAM_TEXT, 'Codigo do usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                    'userid' => new external_value(PARAM_TEXT, 'Codigo do usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                    'turmas' => new external_multiple_structure(new external_single_structure(array(
                        'courseid' => new external_value(PARAM_INT, 'Código da turma, ferencia do Moodle ', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                        'lastacess' => new external_value(PARAM_TEXT, 'Data do ultimo acesso', VALUE_REQUIRED, '', NULL_NOT_ALLOWED)
                    ))),
                ))),
            ), 'Informações do usuários'),
        ));
    }
//=======================================
    //
    public static function performance_atividades_parameters(){
        return new external_function_parameters(array(
            'user' => new external_single_structure(array(
                'estudantes' => new external_multiple_structure(new external_single_structure(array(
                    'codigo' => new external_value(PARAM_INT, 'Codigo do usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED)
                ))),
                'turmas' => new external_multiple_structure(new external_single_structure(array(
                    'courseid' => new external_value(PARAM_INT, 'Código da turma, ferencia do Moodle ', VALUE_REQUIRED, '', NULL_NOT_ALLOWED)
                ))),
            ), 'Informações do usuários'),
        ));
    }

    public static function performance_atividades($info){

        global $DB;

        $turmas = $estudantes = $userIds = '';

        foreach( $info['turmas'] as $turma){ $turmas .= $turma['courseid'] . ', '; }
        $turmas = rtrim($turmas, ', ');

        foreach( $info['estudantes'] as $estudante){ $estudantes .= "'" . $estudante['codigo']  . "', "; $return[$estudante['codigo']] = (object)['turmas' => []]; }
        $estudantes = rtrim($estudantes, ", ");

        $users = [];
        foreach($DB->get_records_sql("SELECT * FROM {user} WHERE username IN ($estudantes)") as $user){
            $users[$user->id] = Array('username' => $user->username, 'userid' => $user->id, 'turmas' => []);
            $userIds .= $user->id . ', ';
        }
        $userIds = rtrim($userIds, ", ");

        $cache = []; //Keep the mods, so no need for DB so many times

        $sql = "SELECT mgg.id, userid, COALESCE(rawgrade, 0) as rawgrade, rawgrademax, courseid, itemmodule, iteminstance FROM mdl_grade_grades mgg INNER JOIN mdl_grade_items mgi ON mgi.id = mgg.itemid WHERE mgi.courseid IN ($turmas) AND mgi.itemtype = 'mod' AND userid IN ($userIds)";
        //error_log($sql); die;
        $records = $DB->get_records_sql($sql);

        foreach($records as $record){

            $instance = self::get_mod_instance($record->itemmodule, $record->iteminstance, $cache);
            $data = 0;
            switch ($record->itemmodule){
            case 'assign':
                if( $instance->duedate + 14*86400 < time() ){ $instance->duedate;  }
                break;
            case 'quiz':
                if( $instance->timeclose < time() ){ $data = $instance->timeclose; }
            case 'lti':
                break;
            default :
                break;
            }

            if($data == 0){ continue; }

            if( !isset($users[$record->userid]['turmas'][$record->courseid] ) ){
                $users[$record->userid]['turmas'][$record->courseid] = ['courseid' => $record->courseid, 'porcentagem' => 0/*, 'total' => 0*/];
            }

            $users[$record->userid]['turmas'][$record->courseid]['porcentagem'] += $record->rawgrade;
            $users[$record->userid]['turmas'][$record->courseid]['total'] += $record->rawgrademax;

        }

        $return = ['estudantes' => [] ];
        foreach($users as $i => $ret){
            $temp = [];
            foreach($ret['turmas'] as $turma){
                $turma['porcentagem'] = intval(100 * $turma['porcentagem'] / $turma['total']);
                unset($turma['total']);
                $temp[] = $turma;
            }
            if($temp == []){ continue; }
            $users[$i]['turmas'] = $temp;
            $return['estudantes'][] = $users[$i];
        }

        return ['user' => $return];
    }

    public static function performance_atividades_returns(){
        return new external_function_parameters(array(
            'user' => new external_single_structure(array(
                'estudantes' => new external_multiple_structure(new external_single_structure(array(
                    'username' => new external_value(PARAM_INT, 'Codigo do usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                    'userid' => new external_value(PARAM_INT, 'Codigo do usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                    'turmas' => new external_multiple_structure(new external_single_structure(array(
                        'courseid' => new external_value(PARAM_INT, 'Código da turma, ferencia do Moodle ', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                        'porcentagem' => new external_value(PARAM_INT, 'Data do ultimo acesso', VALUE_REQUIRED, '', NULL_NOT_ALLOWED)
                    ))),
                ))),
            ), 'Informações do usuários'),
        ));
    }

    private static function get_mod_instance($module, $instance, &$cache){
        global $DB;
        if(isset($cache[$module][$instance])){ return $cache[$module][$instance]; } //Cache HIT
        $cache[$module][$instance] = $DB->get_record($module, ['id' => $instance]); //Cache MISS
        return $cache[$module][$instance];
    }

//=======================================

    public static function dias_sem_acesso_parameters(){
        return new external_function_parameters(array(
            'user' => new external_single_structure(array(
                'estudantes' => new external_multiple_structure(new external_single_structure(array(
                    'codigo' => new external_value(PARAM_INT, 'Codigo do usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED)
                ))),
                'turmas' => new external_multiple_structure(new external_single_structure(array(
                    'courseid' => new external_value(PARAM_INT, 'Código da turma, ferencia do Moodle ', VALUE_REQUIRED, '', NULL_NOT_ALLOWED)
                ))),
                'dt_base' => new external_value(PARAM_TEXT, 'Data retroativa para avaliar', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
            ), 'Informações do usuários'),
        ));
    }

    public static function dias_sem_acesso($info){
        global $DB;

        $turmas = $estudantes = '';
        $turmas_lote = [];

        foreach( $info['turmas'] as $turma){ $turmas_lote[] = $turma['courseid'];  $turmas .= $turma['courseid'] . ', '; }
        foreach( $info['estudantes'] as $estudante){ $estudantes .= "'" . $estudante['codigo']  . "', "; }

        $turmas = rtrim($turmas, ', ');
        $estudantes = rtrim($estudantes, ", ");
        $sql = "";
        $times = [];

        if($info['dt_base'] != ''){
            $time = strtotime($info['dt_base']) + (60 * 60 * 24); 
            $userids = implode(',', array_keys($DB->get_records_sql("SELECT id, username FROM {user} WHERE username IN ($estudantes)")));

            $chunks = array_chunk($turmas_lote, 1000);

            foreach($chunks as $chunk){

            $turma_lote = implode(',', $chunk);

            //foreach( $info['turmas'] as $turma){ 
                $sql = "SELECT 
                          max(mlog.id) as mlogid, 
                          mlog.userid as id, 
                          max(to_timestamp(mlog.timecreated)) as to_timestamp, 
                          mus.username,
                          mlog.courseid 
                        FROM 
                          mdl_logstore_standard_log mlog,
                          mdl_user mus,
                          mdl_course mcu
                        WHERE 
                          mus.id = mlog.userid AND 
                          mcu.id IN ($turma_lote) AND 
                          mlog.courseid = mcu.id AND 
                          mlog.userid IN ($userids) AND 
                          mlog.timecreated < $time AND 
                          mlog.timecreated > mcu.timecreated  
                        GROUP BY userid, courseid, username;";

                //error_log( "BUSCANDO ACESSO - ".$turma['courseid'] );
                $times = array_merge( $times, $DB->get_records_sql($sql) ); 
            //}
            }
            $sql = '';
        }else{
            $sql = "SELECT 
                      mul.id, 
                      mus.username, 
                      mus.id, 
                      mul.courseid, 
                      to_timestamp(mul.timeaccess) 
                     FROM {user_lastaccess} mul INNER JOIN {user} mus ON mus.id = mul.userid 
                     WHERE userid IN ( 
                          SELECT id 
                          FROM {user} 
                          WHERE username IN ($estudantes) ) AND 
                       courseid IN ($turmas)";
        }

        if($sql != ''){
            $times = $DB->get_records_sql($sql); 
        }

        $estudantes = $estudantesIds = [];

        foreach($times as $time){
            $estudantesIds[$time->username] = $time->id;
            if(!isset($estudantes[$time->username])){ $estudantes[$time->username] = ["turmas" => [] ]; }
            $estudantes[$time->username]['turmas'][] = [ 'courseid' => $time->courseid, 'lastacess' => $time->to_timestamp ];
        }

        $return = ['user' => [ 'estudantes' => [] ] ];

        foreach($estudantes as $key => $estudante){
            $return['user']['estudantes'][] = ['codigo' => $key, 'userid' => $estudantesIds[$key], 'turmas'=>$estudante['turmas'] ] ;
        }

        return $return;

    }

    public static function dias_sem_acesso_returns(){
        return new external_function_parameters(array(
            'user' => new external_single_structure(array(
                'estudantes' => new external_multiple_structure(new external_single_structure(array(
                    'codigo' => new external_value(PARAM_TEXT, 'Codigo do usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                    'userid' => new external_value(PARAM_TEXT, 'Codigo do usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                    'turmas' => new external_multiple_structure(new external_single_structure(array(
                        'courseid' => new external_value(PARAM_INT, 'Código da turma, ferencia do Moodle ', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                        'lastacess' => new external_value(PARAM_TEXT, 'Data do ultimo acesso', VALUE_REQUIRED, '', NULL_NOT_ALLOWED)
                    ))),
                ))),
            ), 'Informações do usuários'),
        ));
    }

    //=======================================

    public static function permanencia_semanal_parameters(){
        return new external_function_parameters(array(
            'user' => new external_single_structure(array(
                'estudantes' => new external_multiple_structure(new external_single_structure(array(
                    'codigo' => new external_value(PARAM_INT, 'Codigo do usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED)
                ))),
            ), 'Informações do usuários'),
        ));
    }

    public static function permanencia_semanal($infos){

        global $DB;

        $return = new stdClass();
        $return->user->estudantes = [];

        $day = (date('w'))*86400;
        $now = new DateTime();
        $now = $now->getTimestamp();
        $date = ($now - $day) + (3600 * 3);

        $users;
        foreach($infos['estudantes'] as $info){
            $users .= "'".$info['codigo']."', ";
        }
        $users = rtrim($users, ', ');

        $times = $DB->get_records_sql("SELECT 
                                        mus.username, 
                                        mus.id, SUM( (timeend - timestart)/60 ) 
                                       FROM 
                                        {local_monitoring_sessions} mls, 
                                        {user} mus 
                                       WHERE userid IN (
                                           SELECT id 
                                           FROM mdl_user 
                                           WHERE username IN ($users) ) AND 
                                        timestart > $date AND 
                                        mus.id = mls.userid 
                                       GROUP BY username, mus.id");

        foreach($times as $time){
            $return->user->estudantes[] = ['codigo' => $time->username, 'time' => $time->sum, 'userid' => $time->id];
        }

        return $return;

    }

    public static function permanencia_semanal_returns(){
        return new external_function_parameters(array(
            'user' => new external_single_structure(array(
                'estudantes' => new external_multiple_structure(new external_single_structure(array(
                    'codigo' => new external_value(PARAM_TEXT, 'Codigo do usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                    'userid' => new external_value(PARAM_TEXT, 'Codigo do usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                    'time'     => new external_value(PARAM_INT, 'Permanencia em minutos', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                ))),
            ), 'Informações do usuários'),
        ));
    }

    //=======================================

    public static function permanencia_semanal_historico_parameters(){
        return new external_function_parameters(array(
            'user' => new external_single_structure(array(
                'estudantes' => new external_multiple_structure(new external_single_structure(array(
                    'codigo' => new external_value(PARAM_INT, 'Codigo do usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED)
                ))),
                'data_inicial' => new external_value(PARAM_TEXT, 'Data retroativa para avaliar', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'data_final'   => new external_value(PARAM_TEXT, 'Data retroativa para avaliar', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
            ), 'Informações do usuários'),
        ));
    }

    public static function permanencia_semanal_historico($info){
        global $DB;

        $return = [];

        $estudantes = '';
        foreach( $info['estudantes'] as $estudante){ $estudantes .= "'" . $estudante['codigo']  . "', "; }
        $estudantes = rtrim($estudantes, ", ");

        $week = (60 * 60 * 24 * 7);
        $week_initial = strtotime($info['data_inicial']);
        $week_limit   = strtotime($info['data_final']);
        $week_end     = $week_initial + $week; 

        $estudantes = $DB->get_records_sql("SELECT id, username FROM {user} WHERE username IN ($estudantes)");
        $users = [];
        $user_list = '';

        foreach($estudantes as $estudante){
            $users[$estudante->id] = ['codigo' => $estudante->username, 'userid' => $estudante->id, 'semanas' => [] ];
        }
        
        $userlist = implode(', ', array_keys($users));

        while($week_end < $week_limit){

            $sql = "SELECT 
                     userid, 
                     SUM( logout - login ) 
                    FROM {local_monitoring_session_temp} 
                    WHERE userid IN ($userlist) AND 
                     login > $week_initial AND 
                     login < $week_end 
                    GROUP BY userid";
            $records = $DB->get_records_sql($sql);

            foreach($records as $record){
                $users[$record->userid]['semanas'][] = [
                    'data_inicial' => gmdate('Y-m-d', $week_initial), 
                    'data_final'   => gmdate('Y-m-d', $week_end),
                    'tempo' => $record->sum
                ];
            }

            $week_initial += $week;
            $week_end     += $week;
        }

        $return['user']['estudantes'] = array_values($users);
        return $return;

        return ['user' => [ 'estudantes' => [
            ['codigo' => 555, 'userid'=>'50', 'semanas' => [ ['data_inicial' => 50, 'data_final' => '112312', 'tempo' => 10], ['data_inicial' => 50, 'data_final' => '112312', 'tempo' => 10]] ],
            ['codigo' => 555, 'userid'=>'50', 'semanas' => [ ['data_inicial' => 50, 'data_final' => '112312', 'tempo' => 10] ] ]
        ] ] ];

    }

    public static function permanencia_semanal_historico_returns(){
        return new external_function_parameters(array(
            'user' => new external_single_structure(array(
                'estudantes' => new external_multiple_structure(new external_single_structure(array(
                    'codigo' => new external_value(PARAM_TEXT, 'Codigo do usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                    'userid' => new external_value(PARAM_TEXT, 'Codigo do usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                    'semanas' => new external_multiple_structure(new external_single_structure(array(
                        'data_inicial' => new external_value(PARAM_TEXT, 'Codigo do usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                        'data_final'   => new external_value(PARAM_TEXT, 'Codigo do usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                        'tempo'        => new external_value(PARAM_INT,  'Codigo do usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                    ))),
                ))),
            ), 'Informações do usuários'),
        ));
    }

    //=======================================
    
    public static function atividades_realizadas_parameters(){
        return new external_function_parameters(array(
            'user' => new external_single_structure(array(
                'estudantes' => new external_multiple_structure(new external_single_structure(array(
                    'codigo' => new external_value(PARAM_INT, 'Codigo do usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED)
                ))),
                'turmas' => new external_multiple_structure(new external_single_structure(array(
                    'courseid' => new external_value(PARAM_INT, 'Código da turma, ferencia do Moodle ', VALUE_REQUIRED, '', NULL_NOT_ALLOWED)
                ))),
                'dt_base' => new external_value(PARAM_TEXT, 'Data retroativa para avaliar', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
            ), 'Informações do usuários'),
        ));
    }

    public static function atividades_realizadas($info){
        global $DB, $CFG;
        
        $turmas = $estudantes = '';

        foreach( $info['turmas'] as $turma){ $turmas .= $turma['courseid'] . ', '; }
        foreach( $info['estudantes'] as $estudante){ $estudantes .= "'" . $estudante['codigo']  . "', "; }

        $turmas = rtrim($turmas, ', ');
        $estudantes = rtrim($estudantes, ", ");

        $usersTemp = $DB->get_records_sql("SELECT id as userid, username as username FROM mdl_user WHERE username IN ($estudantes)");
        foreach($usersTemp as $key => $item){
            $usersTemp[$key] = (array)$usersTemp[$key];
            $usersTemp[$key]['turmas'] = []; 
        }

        $enroledUsers = $DB->get_records_sql("SELECT 
                                               mco.instanceid course, 
                                               string_agg(mus.id || '', ', ') as users, 
                                               0 as ammount
                                              FROM mdl_role_assignments mra INNER JOIN mdl_context mco on mra.contextid = mco.id 
                                                INNER JOIN mdl_user mus ON mra.userid = mus.id
                                                WHERE mco.contextlevel = 50 AND 
                                                 mco.instanceid IN ($turmas) AND 
                                                 mus.username IN ($estudantes) 
                                              GROUP BY instanceid;");
        
        foreach($enroledUsers as $enroledUser){
            foreach( explode(', ', $enroledUser->users) as $user ){
                $usersTemp[$user]['turmas'][$enroledUser->course] = ['courseid' => $enroledUser->course, 'porcentagem' => 0];
            }
        }

        $courses = [];
        foreach($info['turmas'] as $turma){
            $graded_items = $DB->get_records_sql("
                SELECT 
                  itemmodule, 
                  string_agg(mgi.iteminstance || '', ', ') as instances
                 FROM 
                  mdl_grade_items mgi, 
                  mdl_grade_categories mgc 
                 WHERE 
                  mgi.categoryid = mgc.id AND 
                  mgc.courseid = ".$turma['courseid']." AND 
                  depth = 2 AND 
                  itemmodule IN ('quiz', 'assign') 
                 GROUP BY itemmodule;");
    
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
                    mgc.courseid = ".$turma['courseid']." AND 
                    depth = 1 AND 
                    itemmodule IN ('quiz', 'assign') AND 
                    itemname NOT LIKE '%recuperação%' 
                    GROUP BY itemmodule;");
            }
            
            $ref_time = time();

            if($info['dt_base'] != ''){ $ref_time = strtotime($info['dt_base']) + (60 * 60 * 24); }

            $sql = "";
            if($graded_items['assign']->instances){
                $sql .= "SELECT id, 'assign' as mod, nosubmissions as type FROM mdl_assign WHERE nosubmissions = 0 AND duedate < $ref_time AND id IN (".$graded_items['assign']->instances.")";
            }
            if($graded_items['assign']->instances && $graded_items['quiz']->instances){
                $sql .= " UNION ";
            }
            if($graded_items['quiz']->instances){
                $sql .= "SELECT id, 'quiz'   as mod, 1 as type FROM mdl_quiz   WHERE timeclose < $ref_time AND id IN (".$graded_items['quiz']->instances.")";
            }
            
            //If there is no grade item
            if($sql == ''){ continue; }
            $real_items = $DB->get_records_sql($sql);

            $courses[ $turma['courseid'] ] = sizeof($real_items);

            foreach($real_items as $real_item){
                $attempts = [];
                if($real_item->mod == 'assign' && $real_item->type == 0){
                    $attempts = $DB->get_records_sql("SELECT userid FROM mdl_assign_submission WHERE assignment = ? AND status = 'submitted' AND timemodified < ?", [$real_item->id, $ref_time]);
                }
                if($real_item->mod == 'assign' && $real_item->type == 1){
                    //$attempts = $DB->get_records_sql("SELECT userid FROM mdl_assign_submission WHERE assignment = ? AND status = 'new' AND timemodified < ?", [$real_item->id, $ref_time]);
                }
                if($real_item->mod == 'quiz'){
                    $attempts = $DB->get_records_sql("SELECT userid FROM mdl_quiz_attempts WHERE quiz = ? AND state = 'finished' AND timemodified < ?", [$real_item->id, $ref_time]);
                }
                foreach($attempts as $attempt){
                    if(!isset($usersTemp[$attempt->userid])){ continue; }
                    $usersTemp[$attempt->userid]['turmas'][$turma['courseid']]['porcentagem']++;
                }
            }
        }

        foreach($usersTemp as $key => $value){
            foreach($usersTemp[$key]['turmas'] as $key1 => $value1){
                //Probably canceled enrolment. But send at least one file, so ignore
                if(!$usersTemp[$key]['turmas'][$key1]['courseid']){ 
                    unset($usersTemp[$key]['turmas'][$key1]); 
                    continue; 
                }

                $percentage = intval( 100 * $usersTemp[$key]['turmas'][$key1]['porcentagem'] / $courses[$key1] );
                $usersTemp[$key]['turmas'][$key1]['porcentagem'] = $percentage;
            }
        }

        // error_log( $CFG->dataroot."logs/local_monitoring.log" );
        // file_put_contents($CFG->dataroot."/logs/local_monitoring.log", print_r( ['user' => [ 'estudantes' => $usersTemp ] ], true) );
        return ['user' => [ 'estudantes' => $usersTemp ] ];
        
    }

    public static function atividades_realizadas_returns(){
        return new external_function_parameters(array(
            'user' => new external_single_structure(array(
                'estudantes' => new external_multiple_structure(new external_single_structure(array(
                    'username' => new external_value(PARAM_INT, 'Codigo do usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                    'userid' => new external_value(PARAM_INT, 'Codigo do usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                    'turmas' => new external_multiple_structure(new external_single_structure(array(
                        'courseid' => new external_value(PARAM_INT, 'Código da turma, ferencia do Moodle ', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                        'porcentagem' => new external_value(PARAM_INT, 'Data do ultimo acesso', VALUE_REQUIRED, '', NULL_NOT_ALLOWED)
                    ))),
                ))),
            ), 'Informações do usuários'),
        ));
    }

    //=======================================

    // #Todo Receber parametro de data. Inicio dt_base dalí pra tras

    public static function participacao_videoconferencia_parameters(){
        return new external_function_parameters(array(
            'user' => new external_single_structure(array(
                'estudantes' => new external_multiple_structure(new external_single_structure(array(
                    'codigo' => new external_value(PARAM_INT, 'Codigo do usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED)
                ))),
                'turmas' => new external_multiple_structure(new external_single_structure(array(
                    'courseid' => new external_value(PARAM_INT, 'Código da turma, ferencia do Moodle ', VALUE_REQUIRED, '', NULL_NOT_ALLOWED)
                ))),
                'dt_base' => new external_value(PARAM_TEXT, 'Data retroativa para avaliar', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
            ), 'Informações do usuários'),
        ));
    }

    public static function participacao_videoconferencia($info){

        global $DB;

        $return = [];

        $turmas = $estudantes = $urlList = '';

        $estudantesIds = $estudantesTemp = $recordingCount = [];

        $ref_time = time();
        if($info['dt_base'] != ''){ $ref_time = strtotime($info['dt_base']);  }

        foreach( $info['turmas'] as $turma){ $turmas .= $turma['courseid'] . ', '; }
        $turmas = rtrim($turmas, ', ');
        foreach( $info['estudantes'] as $estudante){ $estudantes .= "'" . $estudante['codigo']  . "', "; }
        $estudantes = rtrim($estudantes, ", ");

        $enroledUsers = $DB->get_records_sql(" SELECT mco.instanceid, string_agg(mus.username||'', ', ') as users FROM mdl_role_assignments mra INNER JOIN mdl_context mco on mra.contextid = mco.id INNER JOIN mdl_user mus ON mra.userid = mus.id WHERE mco.contextlevel = 50 AND mco.instanceid IN ($turmas) GROUP BY instanceid;");

        //Load resourses to be searched
        $sql  = "SELECT id, course, 'bbb' as module FROM mdl_bigbluebuttonbn WHERE course IN ($turmas) AND closingtime < $ref_time ";
        $sql .= " UNION ";
        $sql .= "SELECT instanceid, course, 'url' as module FROM mdl_local_monitoring_videos WHERE course IN ($turmas) AND time < $ref_time ";
        $sql .= " UNION ";
        $sql .= "SELECT id, course, 'meet' as module FROM mdl_meet WHERE course IN ($turmas) AND timeend <".  $ref_time;
        $sql .= " UNION ";
        $sql .= "SELECT id, course, 'assign' as module FROM mdl_assign WHERE course IN ($turmas) AND name ILIKE ('%Videoconferência%') and name ILIKE ('%Ao vivo%') AND duedate <". $ref_time;
        $recordingList = $DB->get_records_sql( $sql );
        
        //Create resources
        foreach($recordingList as $record){
            if($record->module == 'url'){
                $urlList .= $record->id . ', ';
            }
            if(!isset($recordingCount[$record->course])){
                $recordingCount[$record->course] = 0;
            }
            $recordingCount[$record->course]++;
        }
        $urlList = rtrim ($urlList, ', ');

        //Load latest acess to specific users on resource
        $sql  = "SELECT MAX(id), objectid, userid, courseid, username, objectid, module FROM mdl_local_monitoring_history WHERE module = 'bigbluebuttonbn' AND username IN ($estudantes) AND courseid IN ($turmas) GROUP BY courseid, username, objectid, module, userid, objectid";
        $sql .= " UNION ";
        if($urlList){
            $sql .= "SELECT MAX(id), objectid, userid, courseid, username, objectid, module FROM mdl_local_monitoring_history WHERE module = 'url' AND objectid IN ($urlList) AND username IN ($estudantes) AND courseid IN ($turmas) GROUP BY courseid, username, objectid, module, userid, objectid";
            $sql .= " UNION ";
        }
        $sql .= "SELECT max(id), objectid, userid, courseid, username, objectid, module FROM mdl_local_monitoring_history WHERE module = 'meet' AND username IN ($estudantes) AND courseid IN ($turmas) GROUP BY courseid, username, objectid, module, userid, objectid";

        $registers = $DB->get_records_sql($sql);
        //error_log( print_r($registers, true) );

        //Calculate ammount of resorces on courses
        foreach($registers as $register){
            if(!isset($estudanteTemp[$register->username][$register->courseid])){
                $estudanteTemp[$register->username][$register->courseid] = 0;
            }

            $estudantesIds[$register->username] = $register->userid;
            $estudanteTemp[$register->username][$register->courseid]++;
        }

        //Calculate and store values on the return variable
        foreach($estudanteTemp as $key => $temp){
            $percent = [];
            foreach($recordingCount as $key_ => $recording){
                if( !strpos( $enroledUsers[$key_]->users, $key.'') ){ continue; }
                if($recording > 1){
                    $percent[] = ['courseid' => $key_, 'porcento' =>  ( 100 * $temp[$key_] / $recording ) ];
                }else{
                    $percent[] = ['courseid' => $key_, 'porcento' => 99 ];
                }
            }
            $return['user']['estudantes'][] = ['codigo' => $key, 'userid' => $estudantesIds[$key], 'turmas'=>$percent ] ;
        }

        return $return;

    }

    public static function participacao_videoconferencia_returns(){
        return new external_function_parameters(array(
            'user' => new external_single_structure(array(
                'estudantes' => new external_multiple_structure(new external_single_structure(array(
                    'codigo' => new external_value(PARAM_TEXT, 'Codigo do usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                    'userid' => new external_value(PARAM_TEXT, 'Codigo do usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                    'turmas' => new external_multiple_structure(new external_single_structure(array(
                        'courseid' => new external_value(PARAM_INT, 'Código da turma, ferencia do Moodle ', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                        'porcento' => new external_value(PARAM_TEXT, 'Data do ultimo acesso', VALUE_REQUIRED, '', NULL_NOT_ALLOWED)
                    ))),
                ))),
            ), 'Informações do usuários'),
        ));
    }

    //=======================================
    //
    public static function get_potential_users_parameters (){
        return new external_function_parameters(
            array(
                'search' => new external_value(PARAM_RAW, 'query'),
                'perpage' => new external_value(PARAM_INT, 'Number per page')
            )
        );
    }

    public static function get_potential_users ($info){
        global $DB;

        //error_log( print_r($info, true) );
        //$records = $DB->get_records_sql("SELECT firstname || ' ' || lastname as fullname FROM {user} WHERE fullname LIKE '%$info%'");
        //error_log( print_r($info, true) );
        //$context = context_user::instance(19562, IGNORE_MISSING)->id;
        return ['estudantes' => [
            ['id' => 10, 'email' => 'aaaa@aaaa.com', 'fullname' => 'dcasca', 'profileimageurlsmall' => "https://www.univates.br/virtual/pluginfile.php/$context/user/icon/remui/f3?rev=1" ]
            ]
         ];
    }

    public static function get_potential_users_returns (){
        return new external_function_parameters(array(
            'estudantes' => new external_multiple_structure(new external_single_structure(array(
                'id' => new external_value(PARAM_INT, 'Codigo do usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'email' => new external_value(PARAM_TEXT, 'Codigo do usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'fullname' => new external_value(PARAM_TEXT, 'Codigo do usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'profileimageurlsmall' => new external_value(PARAM_TEXT, 'Codigo do usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED)
            ))),
        ));
    }

    //####################################################
    //REPLICANDO FUNÇÃO DO ALFA ##########################
    public static function user_internal_code_parameters(){
        return new external_function_parameters(array(
            'user' => new external_single_structure(array(
                'estudantes' => new external_multiple_structure(new external_single_structure(array(
                    'codigo' => new external_value(PARAM_INT, 'Codigo do usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED)
                ))),
            ), 'Informações do usuários'),
        ));
    }

    public static function user_internal_code($info){

        global $DB;

        $return = [];

        $return['users'] = [];
        $users = '';

        foreach($info['estudantes'] as $i){
            $users .= "'".$i['codigo']."', ";
        }

        $users = rtrim($users, ', ');

        $userss = $DB->get_records_sql("SELECT id, username FROM {user} WHERE username IN ($users)");

        foreach($userss as $user){
            $return['users'][] = ['codigo' => $user->username, 'id' => $user->id];
        }

        return $return;

    }

    public static function user_internal_code_returns(){
        return new external_function_parameters(array(
            'users' => new external_multiple_structure(new external_single_structure(array(
                'codigo'    => new external_value(PARAM_INT, 'Código do usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'id' => new external_value(PARAM_INT, 'ID interno do MOODLE', VALUE_REQUIRED, '', NULL_NOT_ALLOWED)
            ))),
        ));
    }

    /*
     *
     */
    public static function get_users($users = [], $course = [], $return = 0){
        global $DB;
    
        if($return == 0){
            
        }

    }

    /*
     * ============================================================
     *
     * WebServices LEXP
     * */

    
    public static function lexp_acessos_po_dias_parameters(){
        return new external_function_parameters (
            array(
                'dia' => new external_value(PARAM_TEXT, 'Data retroativa para avaliar', VALUE_REQUIRED, '', NULL_NOT_ALLOWED)
            )
        );
    }

    public static function lexp_acessos_po_dias($data){
        global $DB;

        $return = [];

        $timeStart = strtotime($data);
        $timeEnd   = $timeStart + (60 * 60 * 24);
        
        $eadStudents = $DB->get_records_sql("SELECT distinct(mra.userid), mus.username FROM mdl_role_assignments mra, mdl_user mus WHERE mus.id = mra.userid AND contextid IN ( select id FROM mdl_context WHERE contextlevel = 50 AND instanceid IN (SELECT id FROM mdl_course WHERE category IN (SELECT id FROM mdl_course_categories WHERE name LIKE ('%EAD%') AND parent IN (147, 149)))) AND roleid = 5");
        $accesses = $DB->get_records_sql("select userid, count(id), SUM(timeend) - SUM(timestart) as sum from mdl_local_monitoring_sessions WHERE timestart > ? AND timestart < ? GROUP BY userid;", [$timeStart, $timeEnd]);

        foreach($accesses as $access){
            if(!isset($eadStudents[$access->userid]) || !is_numeric($eadStudents[$access->userid]->username)){
                continue;
            }
            $return['acessos'][] = ['matricula' => $eadStudents[$access->userid]->username, 'dia' => $data, 'qtd_acessos' => $access->count, 'duracao_acessos' => $access->sum];
        }

        return $return;
    }

    public static function lexp_acessos_po_dias_returns(){
        return new external_single_structure(
            array(
                'acessos' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'matricula'   => new external_value(PARAM_TEXT, 'Course id'),
                            'dia'         => new external_value(PARAM_TEXT, 'Grade formatted'),
                            'qtd_acessos' => new external_value(PARAM_INT, 'Raw grade, not formatted'),
                            'duracao_acessos' => new external_value(PARAM_INT, 'Your rank in the course', VALUE_OPTIONAL),
                        )
                    )
                )
            )
        );
    }

    public static function lexp_lms_dados_parameters(){
        return new external_function_parameters (
            array(
                'periodo' => new external_value(PARAM_TEXT, 'Periodo a ser buscado', VALUE_REQUIRED, '', NULL_NOT_ALLOWED)
            )
        );
    }

    public static function lexp_lms_dados($data){
        global $DB;

        $return = [];

        //$eadStudents = $DB->get_records_sql("SELECT distinct(mra.userid), mus.username FROM mdl_role_assignments mra, mdl_user mus WHERE mus.id = mra.userid AND contextid IN ( select id FROM mdl_context WHERE contextlevel = 50 AND instanceid IN (SELECT id FROM mdl_course WHERE category IN (SELECT id FROM mdl_course_categories WHERE name = ? ))) AND roleid = 5", Array($data));
        //error_log( print_r($eadStudents, true) );

        $sql = "
            select
                username as matricula,
                sum(qtd_atividades_total) as qtd_atividades_total,
                sum(qtd_atividades_nao_realizadas) as qtd_atividades_nao_realizadas,
                sum(qtd_total_videoconferencia) as qtd_total_videoConferencia,
                sum(qtd_videoconferencia_nao_participada) as qtd_videoConferencia_nao_participada,
                sum(qtd_total_material_didatico) as qtd_total_material_didatico,
                sum(qtd_acesso_material_didatico) as qtd_acesso_material_didatico
                from mdl_local_monitoring_user_temp WHERE period = '$data' GROUP BY username;
        ";

        $records = $DB->get_records_sql($sql);

        foreach($records as $record){
            $return['dados'][] = (array)$record; 
        }

        /*error_log( print_r($return, true) );
        for($i = 0; $i< 100; $i++){
            $return['dados'][] = [
                'matricula' => '1000',
                'qtd_atividades_total' => 10,    
                'qtd_atividades_nao_realizadas' => 10,    
                'qtd_total_videoConferencia' => 10,    
                'qtd_videoConferencia_nao_participada' => 10,    
                'qtd_total_material_didatico' => 10,    
                'qtd_acesso_material_didatico' => 10,    
            ];
        }*/
        return $return;
    }

    public static function lexp_lms_dados_returns(){
        return new external_single_structure(
            array(
                'dados' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'matricula'   => new external_value(PARAM_TEXT, 'Course id'),
                            'qtd_atividades_total' => new external_value(PARAM_INT, 'Raw grade, not formatted'),
                            'qtd_atividades_nao_realizadas' => new external_value(PARAM_INT, 'Raw grade, not formatted'),
                            'qtd_total_videoconferencia' => new external_value(PARAM_INT, 'Raw grade, not formatted'),
                            'qtd_videoconferencia_nao_participada' => new external_value(PARAM_INT, 'Raw grade, not formatted'),
                            'qtd_total_material_didatico'  => new external_value(PARAM_INT, 'Raw grade, not formatted'),
                            'qtd_acesso_material_didatico' => new external_value(PARAM_INT, 'Raw grade, not formatted'),
                        )
                    )
                )
            )
        );
    }
}
