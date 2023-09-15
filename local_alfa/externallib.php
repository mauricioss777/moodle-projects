<?php
/**
 * Plugin de integração entre Alfa-Moodle
 *
 * @package    local_alfa
 * @author     Maurício S. Silva - mss@univates.br
 * @author     Artur H. Welp - ahwelp@univates.br
 * @author     Alexandre S. Wolf - awolf@univates.br
 * @author     Núcleo de Educação a Distância
 * @copyright  2014 Centro Universitário UNIVATES
 * @since      Moodle 2.7
 */
defined('MOODLE_INTERNAL') || die;
require_once("$CFG->libdir/externallib.php");
require_once('classes/alfa.class.php');
require_once('classes/alfahelper.php');

class local_alfa_external extends external_api
{
    /**
     * Retorna a descrição dos parâmetros para o método local_alfa_create_course
     *
     * @return external_function_parameters
     * @since Moodle 2.7
     */
    public static function create_course_parameters()
    {
        return new external_function_parameters(array(
            'course' => new external_single_structure(array(
                'idoffer'      => new external_value(PARAM_INT, 'idoffer', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'fullname'     => new external_value(PARAM_TEXT, 'Nome completo da disciplina', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'category'     => new external_value(PARAM_TEXT, 'Categoria na qual o curso deverá ser inserido(graduação, técnico,...', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'period'       => new external_value(PARAM_TEXT, '2014B, 2014BT..', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'inidate'      => new external_value(PARAM_TEXT, 'Data de início do curso/disciplina', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'enddate'      => new external_value(PARAM_TEXT, 'Data de encerramento do curso/disciplina', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'dayshift'     => new external_value(PARAM_TEXT, 'Turno', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'dayofweek'    => new external_value(PARAM_TEXT, 'Dia da semana', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'numsections'  => new external_value(PARAM_INT,  'Quantidade de tópicos no curso', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'workload'     => new external_value(PARAM_INT,  'Carga horária da disciplina', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'eadworkload'  => new external_value(PARAM_INT,  'Carga horária EAD', VALUE_REQUIRED, '0', NULL_NOT_ALLOWED),
                'ead'          => new external_value(PARAM_BOOL, 'Disciplina EAD', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'format'       => new external_value(PARAM_TEXT, 'Aula+m tipo de ambiente', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'disciplineid' => new external_value(PARAM_INT,  'Id da disciplina no Alfa', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'syllabus'     => new external_value(PARAM_TEXT, 'Ementa da turma', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'users'      => new external_multiple_structure(new external_single_structure(array(
                    'username' => new external_value(PARAM_INT, 'Código de usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                    'roleid'   => new external_value(PARAM_INT, 'Função no sistema (5=Estudante 3=Professor)', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                ), 'Lista de usuários e suas funções no curso')),
                'otherusers' => new external_multiple_structure(new external_single_structure(array(
                    'username' => new external_value(PARAM_TEXT, 'Código do usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                    'roleid'   => new external_value(PARAM_INT, 'Função no sistema (5=Estudante 3=Professor)', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                )), 'Usuários opcionais (secretárias, diretor, coordenador, etc)', VALUE_OPTIONAL),
            ), 'Informações do curso que deverá ser criado'),
        ));
    }

    /**
     * Método utilizado pelo Alfa para criar cursos no Moodle
     *
     * @param array $course_info Informações para a criação do curso
     * @return int $courseid Id do curso criado no Moodle
     * @since Moodle 2.7
     */
    public static function create_course($course_info)
    {
        error_log('CREATE COURSE===============================' . $course_info['idoffer']);

        global $DB, $CFG;

        require_once($CFG->dirroot . '/local/alfa/locallib.php');
        require_once($CFG->dirroot . '/user/lib.php');
        require_once($CFG->dirroot . '/course/lib.php');
        require_once($CFG->dirroot . '/lib/accesslib.php');
        require_once($CFG->libdir . '/coursecatlib.php');

        $selectPlaceHolder = array();
        $userCodes = array();
        $allUserRoles = array();
        $users = array();
        $debugFile = '|moodle/local/alfa/externallib.php|';

        //Componentes EAD não adicionar Tutores
        if($c['dayofweek'] == 'Distância' || $c['ead'] || $c['dayshift'] == 'EAD'){
            foreach($c['users'] as $key => $user){
               if($user['roleid'] == 3){
                   unset($c['users'][$key]);
               }
            }
        }

        $c = $course_info;

        if( ($c['dayofweek'] == 'Distância' || $c['ead']) && strpos($c['period'], 'EAD') > 0 ){
            $c['otherusers'][] = ['username' => "tutoriaead", 'roleid' => 1]; //Requisição da vivi
            $c['otherusers'][] = ['username' => "vivian.petter", 'roleid' => 1];
            $c['otherusers'][] = ['username' => "polo.bentogoncalves", 'roleid' => 4]; //Polos Próprios
            $c['otherusers'][] = ['username' => "polo.guapore", 'roleid' => 4];
            $c['otherusers'][] = ['username' => "polo.teutonia", 'roleid' => 4];
            $c['otherusers'][] = ['username' => "polo.novamutum", 'roleid' => 4];
            $c['otherusers'][] = ['username' => "polo.carlosbarbosa", 'roleid' => 4];
        }

        /*
         * Validando os dados
         * Validating data
         */

        try {
            if( ! isset($c['idoffer'])) {
                error_log("LOCAL_ALFA_ERROR::create_course:: idoffer not found");
                throw new moodle_exception('idoffer not found');
            }
            if($c['idoffer'] != 'ARRAY') {
                if($courses = $DB->get_records('local_alfa', array('idnumber' => $c['idoffer']))) {
                    //pode ser útil || ALTER TABLE mdl_local_alfa ALTER COLUMN idnumber TYPE bigint USING (idnumber::integer);
                    $courseid = array_shift($courses)->courseid;
                    $course = $DB->get_record('course', array('id' => $courseid));
                    if($course) {
                        return $courseid; //se o curso já existe apenas retorna o link já existente.
                    }
                }
            }
            if( ! isset($c['fullname']) /*|| !isset($c['shortname'])*/) {
                $c['shortname'] = $c['fullname'];
                error_log("LOCAL_ALFA_ERROR::create_course:: fullname are not set");
                throw new moodle_exception('fullname or shortname are not set');
            }
            if( ! isset($c['period'])) {
                error_log("LOCAL_ALFA_ERROR::create_course:: period not found");
                throw new moodle_exception('period not found');
            }
            if( ! isset($c['inidate']) || ! isset($c['enddate']) || ! isset($c['dayshift']) || ! isset($c['dayofweek'])) {
                error_log("LOCAL_ALFA_ERROR::create_course:: course dates are not set");
                throw new moodle_exception('course dates are not set');
            }

            // Verifica as datas
            $dates = array_map('trim', explode('/', $c['inidate']));
            usort($dates, 'Alfa::_usort_alfa_date');
            $c['inidate'] = array_shift($dates);//pega a menor data
            $dates = array_map('trim', explode('/', $c['enddate']));
            usort($dates, 'Alfa::_usort_alfa_date');
            $c['enddate'] = end($dates);//pega a maior data

            //convertendo as datas para timestamp //converting dates to timestamp
            $c['inidate'] = strtotime($c['inidate']);
            $c['enddate'] = strtotime('+1 day', strtotime($c['enddate']));

            // Não ter fórum de avisos
            $c['newsitems'] = 0;

            foreach ($c['users'] as $user) {
                if( ! isset($user['username']) || ! is_number($user['roleid'])) {
                    error_log("LOCAL_ALFA_ERROR::create_course:: user information not found", 3, "/tmp/create_course.log");
                    throw new moodle_exception('user information not found');
                }
                //Array with the permission id, used in the assignment
                $allUserRoles[$user['username']] = $user['roleid'];
                //Array with just the user names, used on the WHERE in on the SELECT
                $userCodes[] = $user['username'];
                $selectPlaceHolder[] = "?";
            }
            $userCodes = array_unique($userCodes);//garante que não tenham registros duplicados
            $selectPlaceHolder = array_slice($selectPlaceHolder, 0, count($userCodes));//garante que os dois arrays tenham o mesmo tamanho.
            unset($user);
            /* ^
             * Dados são validos
             * Data valid
             */
            /*
             *  Substitui '??' por '?, ?' Para ser usado na função de busca do moodle
             *  Replace ?? for ?, ? To be used in the moodle db function
             */
            $selectPlaceHolderString = implode($selectPlaceHolder, ", ");
            $query = "SELECT * FROM {user} WHERE username IN (" . $selectPlaceHolderString . ")";
            $result = $DB->get_records_sql($query, $userCodes);
            error_log("### [ " . date('Y-m-d H:i:s') . " ] externallib.php::create_course::result: " . json_encode($result) . "\n\n", 3, "/tmp/create_course.log");
            /*
             * Varre o array de resultados do banco, para cada resultado encontrado remove esse mesmo registro do array dos username
             * Walk on the result array, for each result, erase the same ocorrence in the username array
             */

            foreach ($result as $line) {
                $index = array_search($line->username, $userCodes);
                $users[$line->id]['id'] = $line->id;
                $users[$line->id]['username'] = $line->username;
                $users[$line->id]['roleid'] = $allUserRoles[$line->username];
                unset($userCodes[$index]);
            }

            /* ^
             * Se ainda existe algo no vetor de username, esses registros não existem no moodle
             * If there is something in the username array, this ocorrence don't exist in moodle
             */
            /*
             * Se exite algo no vetor de username, vamos buscar esses dados no Alfa, e apos
             * vamos criar os usuários no moodle
             * If there is something on the username array, we will search it in Alfa and then
             * create the users in moodle
             */
            if( ! empty($userCodes)) {
                $newusers = Alfa::getUsersInformation($userCodes);
                if( ! empty($newusers) && $newusers !== false) {
                    foreach ($newusers as $user) {
                        $userId = user_create_user($user);

                        //organiza o vetor que adicionará os usuário no curso
                        $users[$userId]['id'] = $userId;
                        $users[$userId]['username'] = $user->username;
                        $users[$userId]['roleid'] = $allUserRoles[$user->username];
                    }
                }
            }

            // Define a categoria do curso 
            $coursecategory = self::get_course_category($c["category"], $c["period"], $c['ead'], $c['workload']);
            $c['category'] = $coursecategory->id;

            //reorganizando informações do curso
            $c['timecreated'] = time();
            $c['startdate'] = $c['inidate'];

            // Se for curso de POS, disponível desde agora
            if($c['idoffer'] == 'ARRAY' && time() < $c['startdate']) {
                $c['startdate'] = mktime(0, 0, 0);
            }

            // Resolve formato, visibilidade, conclusão e tipo caso aulamais
            list($c['format'], $c['visible'], $c['enablecompletion'],  $course_type) = alfa_helper::resolve_course_format($c);

            // Busca quantos tópicos o curso precisa
            $c['numsections'] = alfa_helper::resolve_topics_ammount($c);

            // Organiza descrição do curso 
            $c['summary'] = alfa_helper::resolve_course_syllabus($c);

            $idoffer = (is_array($c['idoffer'])) ? $c['idoffer'][0] . 'ARR' : $c['idoffer'];
            $c['fullname'] = $c['fullname'] . ' - REF ' . $idoffer;
            $c['shortname'] = $c['fullname'];


            $course = create_course((object) $c);//Curso criado // Course created

            /* Aqui se insere os dados na outra tabela
               Fazendo a relação do idnumber na tabela local_alfa
            */
            if($c['idoffer'] != 'ARRAY') {
                $params = Array();
                $params['courseid'] = $course->id;
                $params['idnumber'] = $c['idoffer'];
                $params['disciplineid'] = $c['disciplineid'];
                $DB->insert_record('local_alfa', $params);
            }

            /*
             * Adicionado os usuário no curso
             * Adding users to course
             */
            $context = context_course::instance($course->id, MUST_EXIST);
            $maninstance = $DB->get_record('enrol', array(
                'courseid' => $course->id,
                'enrol'    => 'manual',
            ), '*', MUST_EXIST);
            $manual = enrol_get_plugin('manual');
            foreach ($users as $user) {
                //$manual->enrol_user($maninstance, $user['id'], $user['roleid'], $c['startdate']);
                $manual->enrol_user($maninstance, $user['id'], $user['roleid']);
            }
            // Adiciona os outros usuários ao curso
            $otherusers = array_values($c['otherusers']);
            error_log("### [ " . date('Y-m-d H:i:s') . " ] externallib.php::create_course::otherusers: " . json_encode($otherusers) . "\n\n", 3, "/tmp/create_course.log");
            $wherein = implode("', '", array_column($c['otherusers'], 'username'));
            $query = "SELECT username, id, (firstname || ' ' || lastname) as fullname FROM {user} WHERE username IN ('" . $wherein . "')";
            $moodleusers = $DB->get_records_sql($query);
            error_log("### [ " . date('Y-m-d H:i:s') . " ] externallib.php::create_course::moodleusers: " . json_encode($moodleusers) . "\n\n", 3, "/tmp/create_course.log");

            //Hack para os centros. Rever em um futuro breve
            $override_users = [
                '15401' => '97738',
                '93705' => '103760',
                '60105' => '81924',
                '32641' => '101236',
                '28313' => '58230',
                '65773' => '104748',
                '94938' => '106661'
            ];

            foreach ($moodleusers as $username => $moodleuser) {
                if( isset($override_users[$moodleuser->id]) ){
                    $userkey = array_search($username, array_column($otherusers, 'username'));
                    $manual->enrol_user($maninstance, $override_users[$moodleuser->id], $otherusers[$userkey]['roleid']);
                    $query = "UPDATE {user_enrolments} SET modifierid = 0 WHERE enrolid = ? AND userid = ?";
                    $DB->execute($query, array(
                        $maninstance->id,
                        $override_users[$moodleuser->id],
                    ));
                }else{
                    $userkey = array_search($username, array_column($otherusers, 'username'));
                    $manual->enrol_user($maninstance, $moodleuser->id, $otherusers[$userkey]['roleid']);
                    $query = "UPDATE {user_enrolments} SET modifierid = 0 WHERE enrolid = ? AND userid = ?";
                    $DB->execute($query, array(
                        $maninstance->id,
                        $moodleuser->id,
                    ));
                }
            }

            /**
             * Adiciona as categorias de notas
             * _______________________________________
             */
	    If($c["category"] == "TÉCNICO"){//Apenas os cursos técnicos ficarão com 3 notas
                alfa_helper::course_create_grade_item($course, $coursecategory->category_grades, $c);
	    }
            /**
             * Criar os recursos para disciplinas do EAD
             * _______________________________________
             */
            alfa_helper::course_create_bluegrid($course, $c, $context);

            /**
             * Criar os recursos para disciplinas do aulamais
             * _______________________________________
             */
            local_alfa_external::_course_create_aulamais($course, $c, $course_type);

            /**
             * Criar os recursos para disciplinas de período especial
             * _______________________________________
             */
            //local_alfa_external::_course_create_special_regime($course, $c);

            /**
             * Reordenando os blocos na página
             * _______________________________________
             */
            $activity_modules = $DB->get_record('block_instances', array(
                'parentcontextid' => $context->id,
                'blockname'       => 'activity_modules',
            ));
            $participants = $DB->get_record('block_instances', array(
                'parentcontextid' => $context->id,
                'blockname'       => 'participants',
            ));
            if($activity_modules) {
                $activity_modules->defaultweight = '3';
                $DB->update_record('block_instances', $activity_modules);
            }
            if($participants) {
                $participants->defaultweight = '4';
                $DB->update_record('block_instances', $participants);
            }

        } catch (Exception $ex) {
            error_log(json_encode($course));
            if(isset($course)) {//antes de retornar a mensagem na tela apaga o lixo do banco
                delete_course($course);
                $DB->execute('DELETE FROM {local_alfa} WHERE courseid = ?', array(
                    $course->id,
                ));
               fix_course_sortorder();
            }
            error_log("LOCAL_ALFA_ERROR::create_course:: " . $ex->getMessage());
            throw new moodle_exception($ex->getMessage());
        }

        return $course->id; //em caso de erro retorna zero
    }

    /**
     * Retorna a descrição do valor retornado na função local_alfa_create_course
     * @return external_value
     * @since Moodle 2.7
     */
    public static function create_course_returns()
    {
        return new external_value(PARAM_INT, 'Retorna o ID do curso no moodle. Utilizado para gerar o link_ead.');
    }

    /**
     * Retorna a descrição dos parâmetros para o método local_alfa_create_course_array
     *
     * @return external_function_parameters
     * @since Moodle 2.7
     */
    public static function create_course_array_parameters()
    {
        return new external_function_parameters(array(
            'course' => new external_single_structure(array(
                'idofferarray' => new external_value(PARAM_SEQUENCE, 'Sequência de oferas separadas por virgula ex. 777,888,999', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'fullname'     => new external_value(PARAM_TEXT, 'Nome completo da disciplina/curso', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'shortname'    => new external_value(PARAM_TEXT, 'Nome breve da disciplina/curso', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'category'     => new external_value(PARAM_TEXT, 'Categoria na qual o curso deverá ser inserido(graduação, técnico,...', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'period'       => new external_value(PARAM_TEXT, '2014B, 2014BT..', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'inidate'      => new external_value(PARAM_TEXT, 'Data de início do curso/disciplina', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'enddate'      => new external_value(PARAM_TEXT, 'Data de encerramento do curso/disciplina', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                /*
                'dayshift' => new external_value(PARAM_TEXT, 'Turno', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'dayofweek' => new external_value(PARAM_TEXT, 'Dia da semana', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                */
                'createlabels' => new external_value(PARAM_BOOL, 'Criar rótulos para cada disciplina? True/False', VALUE_REQUIRED, true, NULL_NOT_ALLOWED),
                'users'        => new external_multiple_structure(new external_single_structure(array(
                    'username' => new external_value(PARAM_INT, 'Código de usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                    'roleid'   => new external_value(PARAM_INT, 'Função no sistema (5=Estudante 3=Professor)', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                ), 'Lista de usuários e suas funções no curso')),
                'offersinfo'   => new external_multiple_structure(new external_single_structure(array(
                    'teacher'    => new external_value(PARAM_INT, 'Código de usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                    'coursename' => new external_value(PARAM_INT, 'Delete comeplete series if repeated event', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                ), 'Lista de professores e suas respectivas disciplinas(utilizado para gerar os rótulos personalizados).')),
            ), 'Informações do curso que deverá ser criado'),
        ));
    }

    /**
     * Método utilizado pelo Alfa para criar cursos no Moodle vinculados a diversas ofertas
     *
     * @param array $course_info Informações para a criação do curso
     * @return int $courseid Id do curso criado no Moodle
     * @since Moodle 2.7
     */
    public static function create_course_array($courseinfo)
    {
        global $DB;
        error_log("CREATE_COURSE_ARRAY::::::::::: ");
        //error_log( print_r($courseinfo,true) );

        //converte a sequência em array
        $courseinfo['idofferarray'] = explode(',', $courseinfo['idofferarray']);
        //Antes de criar o curso é preciso formatar as informações
        //excluido pois usamos uma tabela para isso
        $coursebase['idoffer'] = 'ARRAY';
        $coursebase['fullname'] = $courseinfo['fullname'];
        $coursebase['shortname'] = $courseinfo['shortname'];
        $coursebase['category'] = $courseinfo['category'];
        $coursebase['period'] = $courseinfo['period'];
        $coursebase['inidate'] = $courseinfo['inidate'];
        $coursebase['enddate'] = $courseinfo['enddate'];
        /*
        $coursebase['dayshift']     = $courseinfo['dayshift'];
        $coursebase['dayofweek']    = $courseinfo['dayofweek'];
        */
        $coursebase['dayshift'] = ' ';
        $coursebase['dayofweek'] = ' ';
        $coursebase['users'] = $courseinfo['users'];
        $courseid = null;
        if($courses = $DB->get_records('local_alfa', array('idnumber' => $courseinfo['idofferarray'][0]))) {
            //pode ser útil || ALTER TABLE mdl_local_alfa ALTER COLUMN idnumber TYPE bigint USING (idnumber::integer);
            return array_shift($courses)->courseid; //se o curso já existe apenas retorna o id já existente.
        } else {
            //cria o curso com base na primeira oferta do array
            $courseid = local_alfa_external::create_course($coursebase);
        }
        //Cria registros de relação na tabela local_alfa
        foreach ($courseinfo['idofferarray'] as $idoffer) {
            $params = new stdClass;
            $params->courseid = $courseid;
            $params->idnumber = $idoffer;
            $params->createlabels = ($courseinfo['createlabels']) ? '1' : '0';
//            $params->timeclosed     = strtotime($courseinfo['enddate']);
            $DB->insert_record('local_alfa', $params);
        }
        try {
            //Ajusta as informações do curso
            $course = new stdclass;
            $course->id = $courseid;
            $course->fullname = $courseinfo['fullname'] . ' -REF' . strtoupper(substr(md5(serialize($courseinfo['idofferarray'])), 0, 7));
            $course->shortname = $courseinfo['shortname'] . ' -REF' . strtoupper(substr(md5(serialize($courseinfo['idofferarray'])), 0, 7));
            $course->summary = '&nbsp;';
            $DB->update_record('course', $course);
            if($courseinfo['createlabels']) {
                $course->numsections = count($courseinfo['idofferarray']);
                //Apaga os tópicos que já foram criados
                $DB->delete_records_select('course_sections', "course = '{$courseid}' AND section > '0'");
                $newsections = array();
                foreach ($courseinfo['offersinfo'] as $key => $offer) {//cria as sections personalizadas
		    $newsection  = local_alfa_external::_course_create_section($courseid, $offer['coursename'], $courseinfo['idofferarray'][$key], $offer['teacher'], $key + 1);
                    $newsections[] = $newsection;
                }

                //Modifica o sumário dos tópicos
                //Busca no banco as informações referentes a quantidade de tópicos e atualiza para o número de ofertas

                $sectioninfo = $DB->get_record('course_format_options', array(
                    'courseid' => $courseid,
                    'name'     => 'numsections',
                ));
                $sectioninfo->value = count($newsections);
                $sectioninfo->name = 'numsectionr';
                $sectioninfo->format = 'topics';
                $sectioninfo->sectionid= 0;

                $sectioninfo->courseid = $courseid;

                if(isset($sectioninfo->id)) {
                    $DB->update_record('course_format_options', $sectioninfo);
                } else {
                    $DB->insert_record('course_format_options', $sectioninfo);
                }

                $DB->insert_records('course_sections', $newsections);

                rebuild_course_cache($courseid, true);
            }
        } catch (Exception $ex) {
            if(isset($course)) {//antes de retornar a mensagem na tela apaga o lixo do banco
                delete_course($course);
            }
            error_log("LOCAL_ALFA_ERROR::create_course_array:: " . $ex->getMessage());
            throw new moodle_exception($ex->getMessage());
        }

        return $courseid;
    }

    /**
     * Retorna a descrição do valor retornado na função local_alfa_create_course_array
     * @return external_value
     * @since Moodle 2.7
     */
    public static function create_course_array_returns()
    {
        return new external_value(PARAM_INT, 'Retorna o ID do curso no moodle. Utilizado para gerar o link_ead. Em caso de erro retorna zero.');
    }


    /**
     * Retorna a descrição dos parâmetros para o método local_alfa_create_course_tcc
     *
     * @return external_function_parameters
     * @since Moodle 3.3
     */
    public static function create_course_tcc_parameters()
    {
        return new external_function_parameters(array(
            'course' => new external_single_structure(array(
                'idoffer'    => new external_value(PARAM_INT, 'Referência do curso/disciplina', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'fullname'   => new external_value(PARAM_TEXT, 'Nome completo da disciplina', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'category'   => new external_value(PARAM_TEXT, 'Categoria na qual o curso deverá ser inserido(graduação, técnico,...', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'period'     => new external_value(PARAM_TEXT, '2014B, 2014BT..', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'inidate'    => new external_value(PARAM_TEXT, 'Data de início do curso/disciplina', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'enddate'    => new external_value(PARAM_TEXT, 'Data de encerramento do curso/disciplina', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'groups'     => new external_multiple_structure(new external_multiple_structure(new external_single_structure(array(
                    'username' => new external_value(PARAM_TEXT, 'Código do usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                    'roleid'   => new external_value(PARAM_INT, 'Função no sistema (5=Estudante 3=Professor)', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                )))),
                'otherusers' => new external_multiple_structure(new external_single_structure(array(
                    'username' => new external_value(PARAM_TEXT, 'Código do usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                    'roleid'   => new external_value(PARAM_INT, 'Função no sistema (5=Estudante 3=Professor)', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                )), 'Usuários opcionais (secretárias, diretor, coordenador, etc)', VALUE_OPTIONAL),
            ), 'Informações do curso que deverá ser criado'),
        ));
    }

    /**
     * Método utilizado pelo Alfa para criar cursos de TCC no Moodle
     *
     * @param array $data Informações para a criação do curso
     * @return int $courseid Id do curso criado no Moodle
     * @since Moodle 3.3
     */
    public static function create_course_tcc($data)
    {
        global $DB, $CFG;
        // error_log(print_r(str_replace('  ', ' ', $data), true));

        //Classe que auxilia
        require_once($CFG->dirroot . '/course/lib.php');

        // Valida os parâmetros
        unset($data['groups']['sem_orientacao']);
        $params = self::validate_parameters(self::create_course_tcc_parameters(), array('course' => $data))['course'];

        $rec = $DB->get_record('local_alfa_tcc', Array('idnumber'=>$params['idoffer']));

        //Hack para os centros. Rever em um futuro breve
        $user_rewrite = [
            '208098' =>  'adriani.rodrigues',
            '707691' =>  'evilin.agostini',
            '504934' =>  'sandrib',
            '616834' =>  'rafaela.valduga',
            '529533' =>  'pamallmann',
            '658603' =>  'erica.fick',
            '691691' =>  'dalila.pumpmacher',
            '690947' =>  'isadora.agnol',
            '606443' =>  'gabriel.godoy',
            '607564' =>  'julia.daroit',
            '570008' =>  'jennifer.hermes'
        ];

        foreach($params['otherusers'] as $key => $value){
            if( isset( $user_rewrite[ $value['username'] ] )){
                $params['otherusers'][$key]['username'] = $user_rewrite[$value['username']];
            }
        }

        if( $rec ){
            $helper = new tcc_helper();
            $helper->load_context($rec->id);
            if(!empty($params['otherusers'])){
                $helper->load_users( Array( $params['otherusers'] ) );
                $helper->manage_other_users( $params['otherusers'] );
            }

            return $rec->courseid;
        }

        try {
            $c = Array();
            $c['fullname'] = mb_strtoupper($params['fullname']) ."REF - {$params['idoffer']}";
            $c['shortname'] = mb_strtoupper($params['fullname']) ."REF - {$params['idoffer']}";
            $coursecategory = self::get_course_category($data["category"], $data["period"]);

            self::_check_course_shortname( $c['shortname'] );

            $c['category'] = $coursecategory->id;
            $c['numsections'] = 0;
            $c['format'] = 'topics';
            $c['timecreated'] = time();
            $c['startdate'] = strtotime(explode('/',$params['inidate'])[0]);
            $c['enddate'] = strtotime('+1 day', explode('/', strtotime($params['enddate'])[0]));
            $c['summary'] = "";
            $course = create_course( (object) $c );

            $alfa_link = new stdClass();
            $alfa_link->courseid = $course->id;
            $alfa_link->idnumber = $params['idoffer'];
            $DB->insert_record('local_alfa_tcc', $alfa_link);

            //The data is not correct from the WebService
            //Fixing
            $possible_other = array_pop($params['groups']);
            if($possible_other[0]['roleid'] == '5'){
                $params['groups']['sem_orientacao'] = $possible_other;
            }else{
                $params['groups'][$possible_other[0]['roleid']] = $possible_other;
            }

            $helper = new tcc_helper();
            $helper->load_users($params['groups']);
            if(!empty($params['otherusers'])){
                $helper->load_users( Array( $params['otherusers'] ) );
            }

            $helper->set_name($c['fullname']);
            $helper->load_context($course->id);
            $helper->add_restrictions();
            $helper->manage_other_users( $params['otherusers'] );
            $helper->manage_users();
            $helper->build_header();

            foreach ($params['groups'] as $key => $group) {
                $helper->manage_group($group);
            }

        } catch (\Exception $exception) {
            // Antes de retornar a mensagem na tela apaga o lixo do banco
            if(isset($course)) {
                error_log('### Excluding course #' . $course->id, 3, '/tmp/create_course_tcc.log');
                delete_course($course);
                $DB->execute('DELETE FROM {local_alfa_tcc} WHERE courseid = ?', array(
                    $course->id,
                ));
            }
            error_log("LOCAL_ALFA_ERROR::create_course_tcc:: " . $exception->getMessage(), 3, '/tmp/create_course_tcc.log');
            throw new moodle_exception($exception->getMessage());
        }

        return $course->id;
    }

    /**
     * Retorna a URL do curso no moodle
     * @return external_value
     * @since Moodle 3.3
     */
    public static function create_course_tcc_returns()
    {
        return new external_value(PARAM_INT, 'Retorna o ID do curso no moodle');
    }



    /**
     * Return the required and optional parameters for the method local_alfa_add_offer_to_course.
     *
     * @return external_function_parameters;
     * @since Moodle 2.7;
     */
    public static function add_offer_to_course_parameters()
    {
        return new external_function_parameters(array(
            'offerinfo' => new external_single_structure(array(
                'courseid'   => new external_value(PARAM_SEQUENCE, 'ID do ambiente existente no moodle', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'idoffer'    => new external_value(PARAM_SEQUENCE, 'ID da oferta da disciplina que será adicionada', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'coursename' => new external_value(PARAM_TEXT, 'Nome completo da disciplina/curso', VALUE_OPTIONAL, '', NULL_ALLOWED),
                'teacher'    => new external_value(PARAM_TEXT, 'Nome completo do professor da disciplina (quando dois ou mais professores, separar por vírgula)', VALUE_OPTIONAL, '', NULL_ALLOWED),
            ), 'Vincula uma nova oferta a um curso existente no moodle.'),
        ));
    }

    /**
     * Method used by Alfa to add an offer to a course.
     *
     * @param array $data WebService call parameters;
     * @return boolean If the relation was or not created;
     * @throws invalid_parameter_exception;
     * @throws moodle_exception;
     * @since Moodle 2.7;
     */
    public static function add_offer_to_course($data)
    {
        global $DB;

        // Validate parameters
        $params = self::validate_parameters(self::add_offer_to_course_parameters(), array('offerinfo' => $data))['offerinfo'];

        try {
            // If the course does not exists
            if(!$DB->get_record('course', array('id' => $params['courseid']))){
                return false;
            }

            // If the offer is already linked
            if($DB->get_record('local_alfa', array('courseid' => $params['courseid'], 'idnumber' => $params['idoffer']))){
                return true;
            }

            // Get the alfa record
            $alfacourse = $DB->get_record('local_alfa', array('courseid' => $params['courseid']));

            // Assign the new offer to this course
            $newalfacourse = new stdClass;
            $newalfacourse->courseid = $params['courseid'];
            $newalfacourse->idnumber = $params['idoffer'];
            $newalfacourse->createlabels = $alfacourse->createlabels;

            // Create the new course relation with Alfa
            $DB->insert_record('local_alfa', $newalfacourse);

            // If the course has automatic label creation
            if($newalfacourse->createlabels == '1') {
                // Adds a summary to last section
                $newsection = local_alfa_external::_course_create_section($params['courseid'], $params['coursename'], $params['idoffer'], $params['teacher']);
                $DB->insert_record('course_sections', $newsection);

                // Rebuild cache of this course
                rebuild_course_cache($params['courseid'], true);
            }

        } catch (Exception $ex) {
            error_log("LOCAL_ALFA_ERROR::add_offer_to_course:: " . $ex->getMessage());
            throw new moodle_exception($ex->getMessage());
        }

        return true;
    }

    /**
     * Returns a boolean value for the local_alfa_add_offer_to_course function.
     *
     * @return external_value;
     * @since Moodle 2.7;
     */
    public static function add_offer_to_course_returns()
    {
        return new external_value(PARAM_BOOL, 'Retorna "true" se o vínculo foi criado ou "false" caso não tenha sido criado.');
    }


    /**
     * Cria o html para as sections personalizadas dos cursos de pós-graduação (uma disciplina por tópico)
     * Esta função é utilizada em local_alfa_external::create_course_array e local_alfa_external::add_offer_to_course
     * @param $courseid   int
     * @param $coursename string
     * @param $idnumber   int local_alfa idnumber
     * @param $teacher    string
     * @param $key        int
     * @return $section Object
     */
    public static function _course_create_section($courseid, $coursename, $idnumber, $teacher, $key = null)
    {
        global $DB, $CFG;
	    error_log("::::- Create sections");
        /**
         * Ajustar este html dconforme orientações da comunicação ou propex.
         */
        $planourl = $CFG->wwwroot . '/local/alfa/ajax.php?action=getTeachingPlan&idnumber=' . $idnumber;
        $htmlsummary = '<style> h3.sectionname, .summary > a { display: none; } </style>';
        $htmlsummary = '<div class="cabecalho-disciplina-pos">';
        $htmlsummary .= '    <div class="titulo-pos">';
        $htmlsummary .= '        <strong>' . $coursename . '</strong>';
        $htmlsummary .= '        <p>Professor(a): ' . $teacher . '</p>';
        $htmlsummary .= '    </div>';
        $htmlsummary .= '    <div class="plano-de-ensino">';
        $htmlsummary .= '        <a href="' . $planourl . '" target="_blank">';
        $htmlsummary .= '            <img src="' . $CFG->wwwroot . '/theme/virtual/pix/teaching_plan.png" alt="Acesse aqui o plano de ensino" id="teaching_plan">';
        $htmlsummary .= '            <span>Plano de Ensino</span>';
        $htmlsummary .= '        </a>';
        $htmlsummary .= '    </div>';
        $htmlsummary .= '</div>';
        $section = new stdClass;
        $section->course = $courseid;
        if( ! isset($key)) {//busca no banco a quantidade de sections já existentes para este curso e soma 1
            $key = count((array) $DB->get_records('course_sections', array('course' => $courseid))) + 1;
        }
        $section->section = $key;
        $section->summaryformat = 1;
        $section->summary = $htmlsummary;
        return $section;
    }

    /**
     * Se for uma disciplina de Regime Especial criar algumas coisas atumaticamente
     *
     * @param $course object
     * @param $c array
     */
    public static function _course_create_special_regime($course, $c)
    {
        global $DB;

        if( strpos( $c['dayofweek'], 'Regime Especial' ) === false ){ return; }

        $topics = $DB->get_records('course_sections', ['course' => $course->id]);
        $last_grade_position = 0;

        $course_module_template = $DB->get_record('course_modules', ['idnumber' => 'TmplRegEsp_encontro']);
        $course_module_template->added = time();
        $course_module_template->idnumber = '';
        $course_module_template->course = $course->id;
        unset($course_module_template->id);

        $categoria_presenca = $DB->get_record_sql("SELECT * FROM {grade_categories} WHERE courseid = ? ORDER BY id", [$course->id]);
        $nota_presenca_template = $DB->get_record_sql("SELECT * FROM {grade_items} WHERE itemmodule = 'assign' AND iteminstance = ".$course_module_template->instance);

        $nota_presenca_template->courseid = $course->id;
        $nota_presenca_template->categoryid = $categoria_presenca->id;
        unset($nota_presenca_template->id);
        unset($nota_presenca_template->idnumber);
        unset($nota_presenca_template->instance);

        $module_configs_template = $DB->get_records('assign_plugin_config', ['assignment' => $course_module_template->instance]);
        foreach($module_configs_template as $key => $config){
            $module_configs_template[$key]->assignment = 0;
            unset($configs_template[$key]->assignment);
        }

        $module_template = $DB->get_record('assign', ['id' => $course_module_template->instance]);
        $module_template->course = $course->id;
        $module_template->timemodified = time();
        unset($module_template->id);
        unset($course_module_template->instance);

        foreach($topics as $topic){
            // Cabeçalho e idependentes não inserimos nada
            if($topic->section == 0 ){ continue; }
            if( (( $topic->section + 1 ) == sizeof($topics)) && ($c['eadworkload'] != 0) ){ continue; }

            $course_module_instance = $course_module_template;
            $module_instance = $module_template;
            $grade_instance = $nota_presenca_template;
            $module_config = $module_configs_template;

            $topic->name = get_string('special_regime_encouter', 'local_alfa', $topic->section);
            $DB->update_record('course_sections', $topic);

            $module_instance->id = $DB->insert_record('assign', $module_instance);
            $course_module_instance->instance = $module_instance->id;
            $course_module_instance->section  = $topic->id;
            $course_module_instance->id = $DB->insert_record('course_modules', $course_module_instance);

            foreach($module_config as $config){
                $config->assignment = $module_instance->id;
                $DB->insert_record('assign_plugin_config', $config);
            }

            $grade_instance->iteminstance = $module_instance->id;
            $grade_instance->sortorder = $topic->section + 4;
            $last_grade_position = $grade_instance->sortorder;
            //$DB->insert_record('grade_items', $grade_instance);

            if($topic->sequence == ''){
                $topic->sequence = $course_module_instance->id;
            }else{
                $topic->sequence .= ','. $course_module_instance->id;
            }
            $DB->update_record('course_sections', $topic);
        }

        if($c['eadworkload'] == 0 ){ return; }

        $topic = $DB->get_record_sql('SELECT * FROM {course_sections} WHERE course = ? ORDER BY id DESC', ['course' => $course->id] );

        $course_module_template = $DB->get_record('course_modules', ['idnumber' => 'TmplRegEsp_horas']);
        $course_module_template->added = time();
        $course_module_template->idnumber = 'EI';
        $course_module_template->course = $course->id;
        unset($course_module_template->id);

        $categoria_idependentes = $DB->get_record_sql("SELECT * FROM {grade_categories} WHERE courseid = ? ORDER BY id DESC", [$course->id]);
        $nota_idependentes_template = $DB->get_record_sql("SELECT * FROM {grade_items} WHERE itemmodule = 'assign' AND iteminstance = ".$course_module_template->instance);

        $nota_idependentes_template->courseid = $course->id;
        $nota_idependentes_template->categoryid = $categoria_idependentes->id;
        unset($nota_idependentes_template->id);
        unset($nota_idependentes_template->idnumber);
        unset($nota_idependentes_template->instance);

        $module_template = $DB->get_record('assign', ['id' => $course_module_template->instance]);
        $module_template->course = $course->id;
        $module_template->grade = $c['eadworkload'];
        $module_template->timemodified = time();
        unset($module_template->id);
        unset($course_module_template->instance);
        $module_template->id = $DB->insert_record('assign', $module_template);

        foreach($module_configs_template as $key => $config){
            $config->assignment = $module_template->id;
            $DB->insert_record('assign_plugin_config', $config);
        }

        $course_module_template->instance = $module_template->id;
        $course_module_template->section  = $topic->id;
        $course_module_template->id = $DB->insert_record('course_modules', $course_module_template);

        $nota_idependentes_template->courseid = $course->id;
        $nota_idependentes_template->categoryid = $categoria_idependentes->id;
        unset($nota_idependentes_template->id);
        unset($nota_idependentes_template->idnumber);
        $nota_idependentes_template->iteminstance = $course_module_template->instance;
        $nota_idependentes_template->sortorder = $last_grade_position + 1;
        $nota_idependentes_template->idnumber = 'EI';
        $nota_idependentes_template->grademax = $c['eadworkload'];
        $item_id = $DB->insert_record('grade_items', $nota_idependentes_template);

        $topic->sequence = $course_module_template->id;
        $topic->name = get_string('special_regime_idependent', 'local_alfa', $topic->section);
        $DB->update_record('course_sections', $topic);

    }

    /*
     * Se for uma disciplina for aula+ criar tópicos automatiamente
     *
     * @param $course object
     * @param $c array
     * @param $course_type int
     */
    private static function _course_create_aulamais($course, $c, $course_type)
    {
        global $DB;

        if($c['format'] != 'aulamais'){ return; }

        // Alterar a tabela para colocar o tipo de componente
        $DB->execute("UPDATE {course_format_options}
                       SET value = ?
                      WHERE
                       format = 'aulamais' AND
                       name = 'coursetype' AND
                       courseid = ? ",
                [$course_type, $course->id] );

        $template_course = null;

        if($course_type == 1){
            $template_course = $DB->get_record('course', ['shortname' => 'Template Aula+ Seminario']);
        }else if ($course_type == 2){
            $template_course = $DB->get_record('course', ['shortname' => 'Template Aula+ Atelier']);
        } else {
            return;
        }

        $template_modules = $DB->get_records('course_modules', Array('course' => $template_course->id) );
        $course_sections  = $DB->get_records_sql( "SELECT * FROM {course_sections} WHERE course = ? AND section > 0", Array($course->id) );

        foreach($course_sections as $section){
            foreach($template_modules as $module){

                $label = $DB->get_record('label', Array('id' => $module->instance) );

                unset($label->id);
                $label->course       = $course->id;
                $label->timemodified = time();
                $label->id           = $DB->insert_record('label', $label);

                unset($module->id);
                $module->course   = $course->id;
                $module->instance = $label->id;
                $module->section  = $section->id;
                $module->added    = time();
                $module->id       = $DB->insert_record('course_modules', $module);

                $section->sequence .= $module->id . ',';
            }

            $section->sequence = rtrim($section->sequence, ',');
            $DB->update_record('course_sections', $section);
        }
    }

    /*********************************************************************************************************************************************************
     *********************************************************************************************************************************************************
     *********************************************************************************************************************************************************
     ********************************************************************************************************************************************************/
    /**
     * Retorna a descrição dos parâmetros para o método local_alfa_get_course_grades
     *
     * @return external_function_parameters
     * @since Moodle 3.3
     */
    public static function get_course_grades_parameters()
    {
        return new external_function_parameters(array(
            'course' => new external_single_structure(array(
                'idoffer'      => new external_value(PARAM_INT, 'Referência do curso/disciplina', VALUE_REQUIRED, 0, NULL_NOT_ALLOWED),
                'courseid'     => new external_value(PARAM_INT, 'ID interno do curso/disciplina', VALUE_REQUIRED, 0, NULL_NOT_ALLOWED),
                'onegrade'     => new external_value(PARAM_BOOL, 'Flag que identifica se é nota única ou conjunto de notas', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'scale'        => new external_value(PARAM_BOOL, 'Flag que identifica se será exportada por conceito A-B-C-D-E', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'frequence'    => new external_value(PARAM_BOOL, 'Flag que identifica se será exportado a conclusão do curso', VALUE_REQUIRED, false, NULL_NOT_ALLOWED),
                'independent'  => new external_value(PARAM_BOOL, 'Flag que identifica se será exportado estudos idependentes', VALUE_REQUIRED, false, NULL_NOT_ALLOWED),
                'allownull'    => new external_value(PARAM_BOOL, 'Flag que converte ou não notas nulas para 0.', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'students'     => new external_multiple_structure(new external_single_structure(array(
                    'username' => new external_value(PARAM_TEXT, 'Código do usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                ))),
            ), 'Informações do curso'),
        ));
    }

    /**
     * Método utilizado pelo Alfa para obter as notas finais dos estudantes de um curso
     *
     * @param array $data Informações para a criação do curso
     * @return array $students Array com usuários e notas
     * @throws dml_exception
     * @throws moodle_exception
     * @since Moodle 3.3
     */
    public static function get_course_grades($data)
    {
        global $DB, $CFG;

        error_log("\n\n\n\n\n\n\n\n\n\n\n\n\nNew data received\n", 3, '/tmp/get_course_grades.log');
        error_log(print_r($data, true), 3, '/tmp/get_course_grades.log');


		if($data['courseid'] == null && $data['idoffer'] == null){
            throw new moodle_exception('É necessário um courseid ou um idoffer para buscar as notas. Verifique a documentação do WebService e adicione os valores necessários');
		}

		if($data['courseid'] == null && $data['idoffer'] == null){
            throw new moodle_exception('É necessário um courseid ou um idoffer para buscar as notas');
		}

        // Valida os parâmetros
        // $params = self::validate_parameters(self::get_course_grades_parameters(), array('course' => $data))['course'];
        $params = $data;

        error_log("##############################################################\n", 3, '/tmp/get_course_grades.log');
        error_log('### Getting final grades for course with idoffer : ' . $params['idoffer'] . "\n", 3, '/tmp/get_course_grades.log');
        error_log("##############################################################\n", 3, '/tmp/get_course_grades.log');

        $results = [];
        $users = [];
        $course = null;

        if($data['courseid'] != 0){
            $course = $DB->get_record( 'course', ['id' => $data['courseid'] ] );
        }else{
            $course = $DB->get_record_sql( "SELECT * FROM {course} mco, {local_alfa} malf WHERE mco.id = malf.courseid AND malf.idnumber = ?", [$data['idoffer']] );
        }

        // Busca todas as notas do curso
        $query = "SELECT gg.id, gi.courseid, gi.itemtype AS itemtype, gc.fullname AS categoryname, u.username AS username, gg.finalgrade AS finalgrade
            FROM mdl_grade_grades AS gg
            INNER JOIN mdl_grade_items AS gi ON gi.id = gg.itemid
            INNER JOIN mdl_grade_categories AS gc ON gc.id = gi.iteminstance
            INNER JOIN mdl_user AS u ON gg.userid = u.id
            INNER JOIN mdl_course AS c ON c.id = gi.courseid
            WHERE (itemtype = 'category' OR itemtype = 'course')
            AND gi.courseid = ?";

        $results = $DB->get_records_sql($query, array( $course->id ));

        // Reorganiza os índices do array da consulta
        $results = array_values($results);

        error_log(print_r($results, true), 3, '/tmp/get_course_grades.log');

        // Array que irá armazenar as notas
        $grades = array();

        // Percorre todos os alunos solicitados pelo Alfa
        foreach ($params['students'] as $student) {
            // Busca a chave do array correspondente
            $keys = array_keys(array_column($results, 'username'), $student['username']);
            $users[] = $student['username'];
            if($keys) {
                // Cria o objeto que irá armazenar as notas do aluno
                $grade = new stdClass();
                $grade->username = $student['username'];

                // Percorre as chaves encontradas
                foreach ($keys as $key) {
                    // Faz a conversão para escala de 10 ou define como NULL caso esteja vazio
                    $finalgrade = $results[$key]->finalgrade ? (string) round($results[$key]->finalgrade / 10, 1) : null;
                    // Se for só uma nota
                    if($params['onegrade']) {
                        if($results[$key]->categoryname == '?') {
                            $grade->grade1 = null;
                            $grade->grade2 = null;
                            $grade->grade3 = null;
                            //CORREÇÃO DO ERRO DE IMPORTAÇÃO || OU SE O ALFA QUISER NÃO
                            if($finalgrade == null && !$data['allownull']){
                                $grade->finalgrade = 0;
                            }else{
                                $grade->finalgrade = $finalgrade;
                            }
                        }
                        if($results[$key]->categoryname == get_string('special_regime_idependent', 'local_alfa')) {
                            $grade->independent = $finalgrade;
                        }
                    } else {
                        // Define a n1, n2, n3 ou nf
                        switch ($results[$key]->categoryname) {
                            case get_string('note', 'local_alfa', 1):
                                $grade->grade1 = $finalgrade;
                                break;
                            case get_string('note', 'local_alfa', 2):
                                $grade->grade2 = $finalgrade;
                                break;
                            case get_string('note', 'local_alfa', 3):
                                $grade->grade3 = $finalgrade;
                                break;
                            case get_string('special_regime_idependent', 'local_alfa'):
                                $grade->independent = $finalgrade;
                                break;
                            case '?':
                                $grade->finalgrade = $finalgrade;
                                break;
                        }
                    }
                }
                // Adiciona ao array
                $grades[] = $grade;
            } else {
                $grade = new stdClass();
                $grade->username = $student['username'];
                // Se for mais de uma nota
                if($data['allownull']){
                    $grade->grade1 = null;
                    $grade->grade2 = null;
                    $grade->grade3 = null;
                    $grade->finalgrade = null;
                }else{
                    $grade->grade1 = 0;
                    $grade->grade2 = 0;
                    $grade->grade3 = 0;
                    $grade->finalgrade = 0;
                }
                // Adiciona ao array
                $grades[] = $grade;
            }
        }

        if($params['frequence']){
            $userlist = '';
            foreach($users as $user){
                $userlist .= "'" . $user . "', ";
            }
            $userlist = rtrim($userlist, ', ');
            $users = $DB->get_records_sql( "SELECT username, id FROM {user} WHERE username IN ($userlist)" );
        }

        foreach ($grades as $grade){
            if(!$params['onegrade']){
                $grade->grade1     = (isset($grade->grade1)) ? $grade->grade1 : $grade->finalgrade;
                $grade->grade2     = (isset($grade->grade2)) ? $grade->grade2 : $grade->finalgrade;
                $grade->grade3     = (isset($grade->grade3)) ? $grade->grade3 : $grade->finalgrade;
                $grade->finalgrade = (isset($grade->finalgrade)) ? $grade->finalgrade : null;
            }else{
                $grade->grade1     = null;
                $grade->grade2     = null;
                $grade->grade3     = null;
                $grade->finalgrade = (isset($grade->finalgrade)) ? $grade->finalgrade : null;
            }

            if($params['frequence']){
                $grade->frequence = intval(core_completion\progress::get_course_progress_percentage($course, $users[$grade->username]->id));
            }

            if($params['independent']){
                $grade->independent  = (isset($grade->independent)) ? intval($grade->independent * 10)  : 0;
            }else{
                unset($grade->independent);
            }

        }

        if($params['scale']){
            $letters = $DB->get_records_sql("SELECT * from {grade_letters} WHERE contextid = 1 ORDER BY lowerboundary");
            foreach ($grades as $grade){

                if($grade->finalgrade == 0 || $grade->finalgrade == null){
                    $grade->finalgrade = ($grade->grade1 + $grade->grade2 + $grade->grade3) / 3;
                }

                $grade->grade1 = null;
                $grade->grade2 = null;
                $grade->grade3 = null;

                foreach ($letters as $letter){
                    if($grade->finalgrade >= ($letter->lowerboundary / 10) ){
                        $grade->letter = $letter->letter;
                    }
                }

                if($grade->letter == ''){
                    $grade->letter = 'E';
                }

            }
        }

        error_log("##############################################################\n", 3, '/tmp/get_course_grades.log');
        error_log(print_r($grades, true), 3, '/tmp/get_course_grades.log');
        error_log("##############################################################\n", 3, '/tmp/get_course_grades.log');

        return array('students' => $grades);
    }

    /**
     * Retorna as notas de cada usuário e a escala do curso se configurada
     * @return external_function_parameters
     * @since Moodle 3.3
     */
    public static function get_course_grades_returns()
    {
        return new external_function_parameters(
            array(
                'students' => new external_multiple_structure(new external_single_structure(array(
                    'username'     => new external_value(PARAM_TEXT,  'Código do usuário'),
                    'grade1'       => new external_value(PARAM_FLOAT, 'Nota 1', VALUE_REQUIRED, null, NULL_ALLOWED),
                    'grade2'       => new external_value(PARAM_FLOAT, 'Nota 2', VALUE_REQUIRED, null, NULL_ALLOWED),
                    'grade3'       => new external_value(PARAM_FLOAT, 'Nota 3', VALUE_REQUIRED, null, NULL_ALLOWED),
                    'finalgrade'   => new external_value(PARAM_FLOAT, 'Nota final', VALUE_REQUIRED, null, NULL_ALLOWED),
                    'independent'  => new external_value(PARAM_FLOAT, 'Estudos idependentes %', VALUE_OPTIONAL, null, NULL_ALLOWED),
                    'letter'       => new external_value(PARAM_TEXT,  'Letra', VALUE_OPTIONAL, null, NULL_ALLOWED),
                    'frequence'    => new external_value(PARAM_INT,   'Porcentagem de conclusão', VALUE_OPTIONAL, null, NULL_ALLOWED),
                )))
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * 
     */
    public static function get_course_all_grades_parameters()
    {
        return new external_function_parameters (
            array(
                'idoffer' => new external_value(PARAM_INT, 'Codigo da turma', VALUE_REQUIRED),
                'username'   => new external_value(PARAM_INT, 'Return grades only for this user (optional)', VALUE_DEFAULT, 0)
            )
        );
    }
    
    public static function get_course_all_grades($idoffer)
    {
        global $CFG, $USER, $DB;

		$courseid = $DB->get_record("local_alfa", Array('idnumber' => $idoffer))->courseid;

        $items = $DB->get_records_sql("
            SELECT 
            mgi.id as itemid, 
            mgi.sortorder,
            mgi.hidden as itemvisible,
            mgc.hidden as categoryvisible,
            mgc.id as categoryid, 
            mgc.fullname as categoryname,
            mgi.itemname
            FROM mdl_grade_items mgi, mdl_grade_categories mgc 
            WHERE mgc.id = mgi.categoryid AND mgc.courseid  = ? ORDER BY mgi.sortorder;
        ", Array($courseid));

        $grades = $DB->get_records_sql("
            SELECT mgg.id, username, rawgrade, rawgrademax, rawgrademin, finalgrade, categoryid, itemid
            FROM 
              mdl_grade_grades mgg, mdl_user mus, mdl_grade_items mgi
            WHERE 
            mgg.userid = mus.id AND 
            mgg.itemid = mgi.id AND 
            itemid IN (
                select id from mdl_grade_items WHERE courseid  = ?);
		", Array($courseid));

		return Array('items' => array_values($items), "students" => array_values($grades));
    }
    
    public static function get_course_all_grades_returns()
    {
        return new external_function_parameters(array(
            'items' => new external_multiple_structure(new external_single_structure(array(
                'itemid' => new external_value(PARAM_INT, 'Porcentagem de conclusão', VALUE_OPTIONAL, null, NULL_ALLOWED),
                'sortorder' => new external_value(PARAM_INT, 'Porcentagem de conclusão', VALUE_OPTIONAL, null, NULL_ALLOWED),
                'itemvisible' => new external_value(PARAM_BOOL, 'Flag que converte ou não notas nulas para 0.', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'categoryvisible' => new external_value(PARAM_BOOL, 'Flag que converte ou não notas nulas para 0.', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'categoryid' => new external_value(PARAM_INT, 'Porcentagem de conclusão', VALUE_OPTIONAL, null, NULL_ALLOWED),
                'categoryname' => new external_value(PARAM_TEXT, 'Código do usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'itemname' => new external_value(PARAM_TEXT, 'Código do usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
            ))),
            'students' => new external_multiple_structure(new external_single_structure(array(
                'id' => new external_value(PARAM_INT, 'Porcentagem de conclusão', VALUE_OPTIONAL, null, NULL_ALLOWED),
                'username' => new external_value(PARAM_TEXT, 'Código do usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'rawgrade'  => new external_value(PARAM_FLOAT, 'Nota 2', VALUE_REQUIRED, null, NULL_ALLOWED),
                'rawgrademax'  => new external_value(PARAM_FLOAT, 'Nota 2', VALUE_REQUIRED, null, NULL_ALLOWED),
                'rawgrademin'  => new external_value(PARAM_FLOAT, 'Nota 2', VALUE_REQUIRED, null, NULL_ALLOWED),
                'finalgrade'  => new external_value(PARAM_FLOAT, 'Nota 2', VALUE_REQUIRED, null, NULL_ALLOWED),
                'categoryid' => new external_value(PARAM_INT, 'Porcentagem de conclusão', VALUE_OPTIONAL, null, NULL_ALLOWED),
                'itemid' => new external_value(PARAM_INT, 'Código do usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
            ))),
        ));
    }

    /**
     * @return external_function_parameters
     */
    public static function get_user_attendance_parameters()
    {
        return new external_function_parameters(array(
            'users' => new external_multiple_structure(new external_single_structure(array(
                'username' => new external_value(PARAM_TEXT, 'Código do usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
            ))),
        ));
    }

    /**
     * @param $users
     * @return array
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function get_user_attendance($users)
    {
        global $DB;

        if( ! array_key_exists('users', $users)) {
            throw new moodle_exception("Param needs 'users' key");
        }

        $users = $users['users'];

        $placeholder = "";

        foreach ($users as $user) {
            $placeholder .= "?, ";
        }
        $placeholder = rtrim($placeholder, ", ");

        $records = $DB->get_records_sql("SELECT username, lastaccess from {user} WHERE username IN({$placeholder})", $users);

        $return = Array();

        $students = Array();
        $student = Array();

        foreach ($records as $record) {
            $student['username'] = $record->username;
            if($record->lastaccess == 0){
                $student['last_acess'] = Null;
                $student['days'] = Null;
            }else{
                $student['last_acess'] = date("Y-m-d H:i:s", $record->lastaccess);
                $student['days'] = intval( ( time() - $record->lastaccess ) / 86400 ) ;
            }
            $students[] = $student;
        }
        $return['students'] = $students;

        return $return;
    }

    /**
     * @return external_function_parameters
     */
    public static function get_user_attendance_returns()
    {
        return new external_function_parameters(array(
            'students' => new external_multiple_structure(new external_single_structure(array(
                'username' => new external_value(PARAM_TEXT, 'Código do usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'last_acess' => new external_value(PARAM_TEXT, 'Ultimo Acesso'),
                'days'       => new external_value(PARAM_TEXT, 'Dias sem acesso'),
            ))),
        ));
    }

    /**
     * @return external_function_parameters
     */
    public static function set_test_grade_parameters()
    {
        return new external_function_parameters(array(
            'users' => new external_multiple_structure(new external_single_structure(array(
                'username' => new external_value(PARAM_TEXT,  'Código do usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'idoffer'  => new external_value(PARAM_TEXT,  'Código do usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'moduleid' => new external_value(PARAM_TEXT,  'Código do modulo',  VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'grade'    => new external_value(PARAM_FLOAT, 'Código do usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
            ))),
        ));
    }

    public static function set_test_grade($params)
    {
		global $DB, $CFG;

        require_once($CFG->libdir . '/gradelib.php');
        require_once($CFG->dirroot.'/grade/lib.php');

        $create = false;

        $userid   = $DB->get_record_sql("SELECT id FROM {user} WHERE username = ?", [ $params['username'] ])->id;
        $courseid = $DB->get_record('local_alfa', ['idnumber' => $params['idoffer']])->courseid;

        $item = false;
        if($params['moduleid'] != ''){
            $module = $DB->get_record('course_modules', ['id' => $params['moduleid']]);
            $item = $DB->get_record_sql("SELECT * from {grade_items} WHERE courseid = $courseid AND itemtype = 'mod' and iteminstance = $module->instance");
        }else{
            $item = $DB->get_record_sql("SELECT * from {grade_items} WHERE courseid = $courseid AND itemtype = 'category' ORDER BY sortorder DESC LIMIT 1");
        }

        if(!$item){
            throw new moodle_exception('Não há categorias de notas cadastradas');
        }

        $grade_item = $DB->get_record_sql("
            SELECT * 
            FROM {grade_grades} 
            WHERE 
                itemid = ? AND 
                userid = ? 
        ", Array($item->id, $userid));


        if(!$grade_item){
            $create = true;

            $grade_item = $DB->get_record_sql("
                SELECT * 
                FROM {grade_grades} 
                WHERE 
                    itemid = ? 
            ", Array($item->id));

            //hack - em algumas situações, não há item de nota, apenas a categoria
            if(!$grade_item){
                $grade_item->itemid             = $item->id;
                $grade_item->rawgrademax        = $item->grademax;
                $grade_item->rawgrademin        = $item->grademin;
                $grade_item->rawscaleid         = '';
                $grade_item->hidden             = '0';
                $grade_item->locked             = '0';
                $grade_item->locktime           = '0';
                $grade_item->exported           = '0';
                $grade_item->excluded           = '0';
                $grade_item->feedback           = '';
                $grade_item->feedbackformat     = '0';
                $grade_item->information        = '';
                $grade_item->informationformat  = '0';
                $grade_item->timecreated        = time();
                $grade_item->aggregationstatus  = 'unknown';
                $grade_item->aggregationweight  = '';
            }


            unset($grade_item->id);
            $grade_item->userid = $userid;
        }

        $adjustedgrade = ($params['grade'] / 100) * $grade_item->rawgrademax;

        $grade_item->usermodified = 2;
        $grade_item->rawgrade     = unformat_float($adjustedgrade);
        $grade_item->finalgrade   = unformat_float($adjustedgrade);
        $grade_item->overridden   = time();
        $grade_item->timemodified = time();
        if($create){
            $grade_item->id = $DB->insert_record('grade_grades', $grade_item);
        } else{
            $DB->update_record('grade_grades', $grade_item);
        }

        grade_item::fetch(['id' => $item->id, 'courseid' => $courseid])->update_final_grade($userid, $adjustedgrade, 'singleview', '', FORMAT_MOODLE);
        grade_regrade_final_grades($courseid, $userid, grade_item::fetch(['id' => $item->id, 'courseid' => $courseid]));

        return $grade_item->id; 
    }

    /**
     * @return external_function_parameters
     */
    public static function set_test_grade_returns()
    {
        return new external_value(PARAM_INT, 'Retorna 1 para ok e exceotion para falha.');
    }

    /**
     * @return external_function_parameters
     */
    public static function get_access_on_period_parameters()
    {
        return new external_function_parameters(array(
            'users' => new external_multiple_structure(new external_single_structure(array(
                'username'  => new external_value(PARAM_TEXT, 'Código do usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'startdate' => new external_value(PARAM_TEXT, 'Data inicial', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'enddate'   => new external_value(PARAM_TEXT, 'Data final',   VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
            ))),
        ));
    }

    public static function get_access_on_period($params)
    {
        global $DB;

        $userid = $DB->get_record('user', ['username' => $params['username'] ])->id;
        $startdate = strtotime( $params['startdate'] );
        $enddate   = strtotime( $params['enddate'] ) + (24 * 60 * 60);

        $logins = $DB->get_record_sql("
            SELECT count(id)
            FROM {logstore_standard_log}
            WHERE 
              userid = $userid AND 
              action = 'loggedin' AND 
              target = 'user' AND  
              objecttable = 'user' AND
              timecreated > $startdate AND 
              timecreated < $enddate
              ")->count;

        return $logins;
    }

    /**
     * @return external_function_parameters
     */
    public static function get_access_on_period_returns()
    {
        return new external_value(PARAM_INT, 'Quantidade de logins para o usuários no período');
    }

    /*========================================*/

    /**
     * @param $shortname
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function _check_course_shortname($shortname)
    {
        // Importa as variáveis globais
        global $DB;
        // Verifica se já existe
        if($DB->record_exists('course', array('shortname' => $shortname))) {
            error_log('There is already an course with that shortname.');
            throw new moodle_exception('There is already an course with that shortname.');
        }
    }

    /**
     * @param $directory
     * @param bool $empty
     * @return bool
     */
    public static function _remove_tmp_course_dir($directory, $empty = false)
    {
        if(substr($directory, - 1) == "/") {
            $directory = substr($directory, 0, - 1);
        }
        if( ! file_exists($directory) || ! is_dir($directory)) {
            return false;
        } else if( ! is_readable($directory)) {
            return false;
        } else {
            $directoryHandle = opendir($directory);
            while ($contents = readdir($directoryHandle)) {
                if($contents != '.' && $contents != '..') {
                    $path = $directory . "/" . $contents;
                    if(is_dir($path)) {
                        self::_remove_tmp_course_dir($path);
                    } else {
                        unlink($path);
                    }
                }
            }
            closedir($directoryHandle);
            if($empty == false) {
                if( ! rmdir($directory)) {
                    return false;
                }
            }

            return true;
        }
    }

    /**
     * @param $category
     * @param $period
     * @return coursecat|mixed
     * @throws dml_exception
     * @throws moodle_exception
     */
    private static function get_course_category($category, $period, $ead = false, $workload = 80)
    {

        global $DB;

        $category_grades = 3;
        if($workload == 80){
            $category_grades = 3;
        }else if ($workload == 40 && $ead){
            $category_grades = 2;
        }

        // Get the parent category name as uppercase, eg: "GRADUAÇÂO", "TECNÓLOGO"
        $parentcategoryname = mb_strtoupper($category);

        // Change the name in some cases
        if(in_array($parentcategoryname, ['MESTRADO', 'DOUTORADO', 'MESTRADO/DOUTORADO'])){
            $parentcategoryname = 'MESTRADO E DOUTORADO';
        }  else if(in_array($parentcategoryname, ['PÓS-GRADUAÇÃO', 'ESPECIALIZAÇÃO', 'MBA', 'ESPECIALIZAÇÃO EAD', 'ESPECIALIZAÇÃO AULA+'])){
            $parentcategoryname = 'PÓS-GRADUAÇÃO';
        } else if(in_array($parentcategoryname, [
            'EXTENSÃO MENOS DE 50 HORAS', 'EXTENSÃO MAIS DE 50 HORAS', 'CURSOS DIVERSOS MENOS DE 50 HORAS', 'CURSOS DIVERSOS MAIS DE 50 HORAS', 
            'CURSOS PROGRAMA DE QUALIFICAÇÃO EM SAÚDE', 'IDIOMAS MAIS DE 50 HORAS', 'IDIOMAS MENOS DE 50 HORAS' ]
        )){
            $parentcategoryname = 'EXTENSÃO';
        }  else if(in_array($parentcategoryname, ['GASTRONOMIA MAIS DE 50 HORAS'])){
            $parentcategoryname = 'GASTRONOMIA';
        } else if (in_array($parentcategoryname, ['EDUCAÇÃO CONTINUADA MENOS DE 50 HORAS', 'EDUCAÇÃO CONTINUADA MAIS DE 50 HORAS', 'CURSOS DE QUALIFICAÇÃO PROFISSIONAL'])){
            $parentcategoryname = 'EDUCAÇÃO CONTINUADA';
        } else if (in_array($parentcategoryname, ['TÉCNICO - UNIVATES'])){
            $parentcategoryname = 'TÉCNICO';
        }

        // Get the parent category record
        if(!$parentcategory = $DB->get_record('course_categories', array('name' => $parentcategoryname))){
            // Or create it
            $newcategory = new stdClass();
            $newcategory->name = $parentcategoryname;
            $newcategory->description = $parentcategoryname;
            $parentcategory = coursecat::create($parentcategory);
        }

        // Get the current category name as uppercase, eg: "2019A", "2019A-EAD1", "2019A-MED"
        $currentcategoryname = mb_strtoupper($period);

        // PÓS-GRADUAÇÃO has diferences on EAD an Presencial
        if($parentcategoryname == 'PÓS-GRADUAÇÃO'){
            if($ead){
                $category_grades = 1;
                $parentcategory = $DB->get_record('course_categories',
                    array('name' => 'EAD', 'parent'=>$parentcategory->id));
            }else{
                $parentcategory = $DB->get_record('course_categories',
                    array('name' => 'Presencial', 'parent'=>$parentcategory->id));
            }
        }

        // Get the parent category record
        if(!$currentcategory = $DB->get_record('course_categories', array('name' => $currentcategoryname, 'parent' => $parentcategory->id))){
            // Or create it
            $newcategory = new stdClass();
            $newcategory->name = $currentcategoryname;
            $newcategory->description = $currentcategoryname;
            $newcategory->parent = $parentcategory->id;
            $currentcategory = coursecat::create($newcategory);
        }
        $currentcategory->category_grades = $category_grades;
        return $currentcategory;
    }

    //####################################################
    //
    public static function course_internal_code_parameters()
    {
        return new external_function_parameters(array(
            'course' => new external_single_structure(array(
                'idoffer' => new external_value(PARAM_INT, 'Codigo da turma', VALUE_REQUIRED, '', NULL_NOT_ALLOWED)
            ), 'Informações da turma'),
        ));
    }

    public static function course_internal_code($info)
    {
        global $DB;

        if(!$info['idoffer']){ throw new moodle_exception('idoffer vazio'); }

        $course = $DB->get_record('local_alfa', Array('idnumber' => $info['idoffer']));

        if($course){
            return ['course' => ['id' => $course->courseid] ];
        }

        return ['course' => ['id' => 0] ];

    }

    public static function course_internal_code_returns()
    {
        return new external_function_parameters(array(
            'course' => new external_single_structure(array(
                'id' => new external_value(PARAM_INT, 'ID interno do MOODLE / 0 caso não exista', VALUE_REQUIRED, '', NULL_NOT_ALLOWED)
            ))
        ));
    }

    /*========================================*/

    public static function user_internal_code_parameters()
    {
        return new external_function_parameters(array(
            'user' => new external_single_structure(array(
                'estudantes' => new external_multiple_structure(new external_single_structure(array(
                    'codigo' => new external_value(PARAM_INT, 'Codigo do usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED)
                ))),
            ), 'Informações do usuários'),
        ));
    }

    public static function user_internal_code($info)
    {

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

    public static function user_internal_code_returns()
    {
        return new external_function_parameters(array(
            'users' => new external_multiple_structure(new external_single_structure(array(
                'codigo'    => new external_value(PARAM_INT, 'Código do usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'id' => new external_value(PARAM_INT, 'ID interno do MOODLE', VALUE_REQUIRED, '', NULL_NOT_ALLOWED)
            ))),
        ));
    }

   /**
     * Adiciona um usuário a um curso específico
     *
     * @return external_function_parameters
     * @since Moodle 3.9.2
     */
    public static function add_user_to_course_parameters()
    {
	   return new external_function_parameters(array(
		    'course' => new external_single_structure(array(
			'idoffer'  => new external_value(PARAM_INT, 'idoffer', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
			'username' => new external_value(PARAM_INT, 'Código de usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
			'roleid'   => new external_value(PARAM_INT, 'Função no sistema (5=Estudante 3=Professor)', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
			'startdate' => new external_value(PARAM_TEXT, 'Data inicial', VALUE_REQUIRED, '0', NULL_NOT_ALLOWED),
			'enddate'   => new external_value(PARAM_TEXT, 'Data final',   VALUE_REQUIRED, '0', NULL_NOT_ALLOWED)
		    )),
	    ));
    }



    /**
     * Método utilizado pelo Alfa para adicionar usuários específicos em cursos de livres
     *
     * @param array $info Informações para a inserção do usuário
     * @return bool $return Retorna se a solicitação foi feita ou não.
     * @since Moodle 3.9.2
     */
    public static function add_user_to_course($info)
    {
	global $DB, $CFG;
	try{
		$course = $DB->get_record_sql("select c.id,c.fullname from {course} as c, {local_alfa} as a where a.idnumber=".$info['idoffer']." and a.courseid = c.id");	   
        	$context = context_course::instance($course->id, MUST_EXIST);
        	$maninstance = $DB->get_record('enrol', array(
	            'courseid' => $course->id,
        	    'enrol'    => 'manual',
	        ), '*', MUST_EXIST);
        	$manual = enrol_get_plugin('manual');

		// se o usuário não existe no moodle, cria
		$user = new stdClass();
		if(!$user = $DB->get_record_sql("select * from {user} where username = '".$info['username']."'")){
			$user = Alfa::getUserInformation($info['username']);
			$user->id = user_create_user($user);
		}

		// se o usuário possui uma data limite de acesso
		if(isset($info['enddate']) && $info['enddate'] != 0){
        		$manual->enrol_user($maninstance, $user->id, $info['roleid'], $info['startdate'], $info['enddate']);
		}else{
			$manual->enrol_user($maninstance, $user->id, $info['roleid']);
		}
	}catch(Exception $e){
		$info['exception'] = $e;
                $insert = new stdClass();
		$insert->function = 'externallib::add_user_to_course::catch';
		$insert->debuginfo = print_r($info, true);
		$insert->datetime = time();
		$errorid = $DB->insert_record('log_debug',$insert);
		throw new Exception('ERRO: Exception: '.get_class($e->exception).'|. Erro registrado na tabela log_debug com id: '.$errorid );
	}
	//vincula o usuário ao curso
        //$manual->enrol_user($maninstance, $user['id'], $user['roleid'], $c['startdate']);
    	return true;

    }

    /**
     * @return true or false
     * @since Moodle 3.9.2
     */
    public static function add_user_to_course_returns()
    {
        return new external_value(PARAM_TEXT, 'Retorna o status da solicitacao (true or false)');
    }



    /**
     * Busca informações das atividades dos estudantes para que o alfa mostre para o professor
     * @return external_function_parameters
     * @since Moodle 3.9.2
     */
    public static function get_course_grades_activities_parameters()
    {
       	return new external_function_parameters(array(
		    'course' => new external_single_structure(array(
			'idoffer'  => new external_value(PARAM_INT, 'idoffer', VALUE_REQUIRED, '', NULL_NOT_ALLOWED)
		    )),
	    )); 
    }

    public static function get_course_grades_activities($idoffer)
    {
        global $DB, $CFG;
         
        if($idoffer['courseid'] == null && $idoffer['idoffer'] == null){
            throw new moodle_exception('É necessário um idoffer para buscar as notas');
		}
        
		//$courseid = $DB->get_record("local_alfa", Array('idnumber' => $idoffer['idoffer']));
        $courseid = $DB->get_record_sql("SELECT * FROM {local_alfa} WHERE idnumber = ?", Array('idnumber' => $idoffer['idoffer']))->courseid;

        if($courseid === null ){
            throw new moodle_exception('O vínculo entre o moodle e o alfa para o idoffer ('.$idoffer['idoffer'].') informado não foi encontrado.');
        }

        $grades = $DB->get_records_sql("
            SELECT
                gg.id,
                u.username AS username,
                gi.itemname as \"nome_atividade\",
                gg.finalgrade::INTEGER AS nota,
                gi.grademin::INTEGER as \"nota_minima\",
                gi.grademax::INTEGER as \"nota_maxima\",
                TO_CHAR(TO_TIMESTAMP(CONCAT(a.duedate, q.timeclose)::INTEGER), 'DD/MM/YYYY HH24:MI') as \"data_limite\"
            FROM mdl_grade_grades AS gg
                LEFT JOIN mdl_grade_items AS gi ON gi.id = gg.itemid
                LEFT JOIN mdl_grade_categories AS gc ON gc.id = gi.iteminstance
                LEFT JOIN mdl_user AS u ON gg.userid = u.id
                LEFT JOIN mdl_course AS c ON c.id = gi.courseid
                LEFT JOIN mdl_assign AS a ON (a.id = gi.iteminstance AND gi.itemmodule = 'assign')
                LEFT JOIN mdl_quiz AS q ON (q.id = gi.iteminstance AND gi.itemmodule = 'quiz')
            WHERE (gi.itemtype != 'category' AND gi.itemtype != 'course')
            AND gi.courseid = ?;
        ", Array($courseid));

        
        return Array("students" => array_values($grades));


    }

    public static function get_course_grades_activities_returns()
    {
        return new external_function_parameters(array(
            'students' => new external_multiple_structure(new external_single_structure(array(
                'id'                => new external_value(PARAM_TEXT, 'Índice da tabela de notas do moodle', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'username'          => new external_value(PARAM_TEXT, 'Código do usuário', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                'nome_atividade'    => new external_value(PARAM_TEXT, 'Nome da atividade no moodle'),
                'nota'              => new external_value(PARAM_TEXT, 'Nota que o estudante tirou na atividade'),
                'nota_minima'       => new external_value(PARAM_TEXT, 'Nota mínima que o estudante pode tirar nesta atividade.'),
                'nota_maxima'       => new external_value(PARAM_TEXT, 'Nota máxima que o estudante pode tirar nesta atividade.'),
                'data_limite'       => new external_value(PARAM_TEXT, 'Data limite em que o estudante poderia entregar a atividade sem penalidade na nota.')
            ))),
        ));
    }



    /**
     * Busca as notas de uma turma
     *
     * @return external_function_parameters
     * @since Moodle 3.9.2
     */
    public static function alfaget_course_grades_parameters()
    {
	   return new external_function_parameters(array(
		    'course' => new external_single_structure(array(
			'idoffer'  => new external_value(PARAM_INT, 'idoffer', VALUE_REQUIRED, '', NULL_NOT_ALLOWED)
		    )),
	    ));
    }


    /**
     * Busca as notas de uma turma
     *
     * @since Moodle 3.9.2
     */
    public static function alfaget_course_grades($data)
    {
        global $DB;

        //busca o curso para o qual as notas serão exportadas
        $course = $DB->get_record_sql("SELECT course.* 
                                         FROM {local_alfa} as alfa,
                                              {course} as course
                                        WHERE alfa.courseid = course.id AND
                                              alfa.idnumber = ?", array('idnumber'=> $data['idoffer']));

        if(!$course){
            throw new moodle_exception('Não foi encontrado nenhum curso relacionado à oferta: '.$data['idoffer']);
        }


        //busca as notas dos estudantes do curso
        $sql = "SELECT g.id,
                       u.id as userid,
                       u.username,
                       g.rawgrade,
                       g.rawgrademax,
                       g.finalgrade,
                       gi.id as itemid,
                       gi.itemname,
                       gi.itemtype,
                       gi.sortorder,
                       gi.idnumber,
                       gi.categoryid,
                       gi.calculation
                  FROM {grade_items} gi,
                       {grade_grades} g,
                       {user} as u
                 WHERE g.itemid = gi.id AND
                       u.id = g.userid AND
                       gi.courseid = :courseid
                       ORDER BY u.id";
        

        $grades = $DB->get_records_sql($sql, array('courseid'=> $course->id));

        if(!$grades){
            throw new moodle_exception('Não há atividades nem registro de notas relacionados à oferta: '.$data['idoffer']);
        }

        $processed = array();//array que vai receber as informações para retornar no webservice
        foreach($grades as $grade){

            //preenche as informações básicas apenas uma vez
            if(empty($processed[$grade->userid]['id'])){
                $processed[$grade->userid]['userid'] = $grade->userid;
                $processed[$grade->userid]['usuario'] = $grade->username;
            }

            //grava as categorias
            if($grade->itemtype == 'category'){
                $processed[$grade->userid]['categorias_de_notas'][$grade->categoryid]['id_categoria'] = $grade->itemid;
                $processed[$grade->userid]['categorias_de_notas'][$grade->categoryid]['nome_categoria'] = $grade->idnumber;
                continue;
            }

            //armazena a nota final no curso
            if($grade->itemtype == 'course'){
                $processed[$grade->userid]['nota_final'] = $grade->finalgrade; 
                $processed[$grade->userid]['nota_maxima'] = $grade->rawgrademax; 
                $processed[$grade->userid]['formula_calculo'] = empty($grade->calculation)? 'Sem fórmula de cálculo' : $grade->calculation; 
                continue;
            }

            //organiza atividades
            $processed[$grade->userid]['atividades'][$grade->itemid]['itemid'] = $grade->itemid;
            $processed[$grade->userid]['atividades'][$grade->itemid]['nome_atividade'] = $grade->itemname;
            $processed[$grade->userid]['atividades'][$grade->itemid]['nota'] = $grade->finalgrade ? $grade->finalgrade : $grade->rawgrade;//se tem uma noa substituída pelo profe, adiciona, se não, pega da atividade
            $processed[$grade->userid]['atividades'][$grade->itemid]['nota_maxima'] = $grade->rawgrademax;
            $processed[$grade->userid]['atividades'][$grade->itemid]['categoria_de_notas'] = $grade->categoryid;
            $processed[$grade->userid]['categorias_de_notas'][$grade->categoryid]['id_categoria'] = $grade->categoryid;
        }

        //confere se há alguma categoria de notas
        $firstkey = array_key_first($processed);
        $firstkeycat = array_key_first($processed[$firstkey]['categorias_de_notas']);
        if(empty($processed[$firstkey]['categorias_de_notas'][$firstkeycat]['nome_categoria'])){
            foreach($processed as $key => $userprocessed){//se não houver, retorna que não tem
                $processed[$key]['categorias_de_notas'][$firstkeycat]['id_categoria'] = $firstkeycat;
                $processed[$key]['categorias_de_notas'][$firstkeycat]['nome_categoria'] = 'Sem categoria de notas';
            }
            
        }

        return array('estudantes' => $processed);
    }

    /**
     * Busca as notas de uma turma
     *
     * @return external_function_parameters
     * @since Moodle 3.9.2
     */
    public static function alfaget_course_grades_returns()
    {
        return new external_function_parameters(array(
            'estudantes' => new external_multiple_structure(new external_single_structure(array(
                'userid'          => new external_value(PARAM_TEXT, 'Código do usuário no moodle', VALUE_OPTIONAL),
                'usuario'         => new external_value(PARAM_TEXT, 'Código do usuário no alfa', VALUE_OPTIONAL),
                'nota_final'      => new external_value(PARAM_TEXT, 'Nota que o estudante tirou no final', VALUE_OPTIONAL),
                'nota_maxima'     => new external_value(PARAM_TEXT, 'Nota máxima que poderia ter tirado', VALUE_OPTIONAL),
                'formula_calculo' => new external_value(PARAM_TEXT, 'Fórmula de cálculo do moodle', VALUE_OPTIONAL),
                'atividades' => new external_multiple_structure(new external_single_structure(array(
                    'itemid'            => new external_value(PARAM_TEXT, 'Código do item de nota no moodle',VALUE_OPTIONAL),
                    'nome_atividade'    => new external_value(PARAM_TEXT, 'Nome da atividade no moodle',VALUE_OPTIONAL),
                    'nota'              => new external_value(PARAM_TEXT, 'Nota que o estudante tirou na atividade',VALUE_OPTIONAL),
                    'nota_maxima'       => new external_value(PARAM_TEXT, 'Nota máxima que o estudante pode tirar nesta atividade.',VALUE_OPTIONAL),
                    'categoria_de_notas'=> new external_value(PARAM_TEXT, 'Categoria a qual esta nota pertence.',VALUE_OPTIONAL)
                ))),
                'categorias_de_notas' => new external_multiple_structure(new external_single_structure(array(
                    'id_categoria'            => new external_value(PARAM_TEXT, 'Código da categoria no moodle',VALUE_OPTIONAL),
                    'nome_categoria'          => new external_value(PARAM_TEXT, 'Nome da categoria',VALUE_OPTIONAL)
                )))
            ))
        )));
    }

}



