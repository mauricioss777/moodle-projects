<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Mandatory public API of url module
 *
 * @package    mod_alfacertificados
 * @copyright  2009 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Add url instance.
 * @param object $data
 * @param object $mform
 * @return int new url instance id
 */
function alfacertificados_add_instance($data, $mform) {
    global $CFG, $DB;

    $params = [];

    foreach($_POST['param'] as $key => $param){
        $params[] = (object)[ $_POST['param'][$key] => trim($_POST['value'][$key]) ];
    }

    $data->params       = json_encode($params);
    $data->timemodified = time();
    $data->id = $DB->insert_record('alfacertificados', $data);

    return $data->id;
}

/**
 * Update url instance.
 * @param object $data
 * @param object $mform
 * @return bool true
 */
function alfacertificados_update_instance($data, $mform) {
    global $CFG, $DB;

    $data->timemodified = time();
    $data->id           = $data->instance;

    foreach($_POST['param'] as $key => $param){
        $params[] = (object)[ $_POST['param'][$key] => trim($_POST['value'][$key]) ];
    }
    $data->params = json_encode($params);

    $DB->update_record('alfacertificados', $data);

    return true;
}

/**
 * Delete url instance.
 * @param int $id
 * @return bool true
 */
function alfacertificados_delete_instance($id) {
    global $DB;

    if (!$url = $DB->get_record('alfacertificados', array('id'=>$id))) {
        return false;
    }

    $cm = get_coursemodule_from_instance('alfacertificados', $id);

    // note: all context files are deleted automatically

    $DB->delete_records('alfacertificados', array('id'=>$url->id));

    return true;
}


function alfacertificados_get_methods($id = null, $generate = false, $course = false){
    global $DB, $COURSE;

    if(!$course){
        $course = $COURSE;
    }else{
        $course = $DB->get_record('course', ['id' => $course]);
    }

    $alfa_record = $DB->get_record('local_alfa', ['courseid' => $course->id]);
    $general_info = [
        'category_name' => $DB->get_record('course_categories', ['id' => $course->category])->name,
        'idnumber'      => $alfa_record->idnumber,
        'disciplineid'  => $alfa_record->disciplineid
    ];

    $data = [
        -1  => [
            'id' => -1,
            'name' => get_string('type_select', 'alfacertificados')
        ],
        0  => [
            'id' => 0,
            'name'   => get_string('type_conclusion', 'alfacertificados'),
            'params' => [
                ['min_completion' => 90 ],
                ['send_grade' => false ]
            ],
            'callback' => function($module, $user){
                global $DB, $CFG;

                $user = $DB->get_record('user', ['username' => $user]);
                $course = $DB->get_record('course', ['id' => $module->course]);

                require_once($CFG->dirroot . "/local/alfa/classes/alfa.class.php");
                require_once($CFG->dirroot . "/local/inscricoes/classes/inscricoes.class.php");

                $filtered = [];
                $module->params = json_decode($module->params);
                foreach($module->params as $param){
                    $param = (array)$param;
                    $filtered[array_keys($param)[0]] = $param[array_keys($param)[0]];
                }
                $module->params = $filtered;

                $content = '';

                //Load the local_alfa instance, case it is a Extension course
                $offer = $DB->get_record('local_alfa', ['courseid' => $course->id]);
                if($offer){
                    $course->idoffer = $offer->idnumber;
                    $course->method = 'alfa';
                }

                //Load the local_inscricoes instance, case it is a Event. Also, it will overwrite the alfa instance
                $offer = $DB->get_record('local_inscricoes', ['courseid' => $course->id]);
                if($offer){
                    $course->idoffer = $offer->idnumber;
                    $course->method = 'inscricoes';
                }

                $grade = 10;
                if($module->params['send_grade'] == 'true' ) {
                    require_once($CFG->libdir . '/gradelib.php');
                    require_once($CFG->dirroot . '/grade/querylib.php');
                    $grade = doubleval( grade_get_course_grade($user->id, [$course->id])[$course->id]->grade );
                }

                $completion = core_completion\progress::get_course_progress_percentage($course, $user->id);
                if(!$completion){ $completion = 0; }

                try{
                    if( $completion < $module->params['min_completion'] ){
                        $content = " <div style='text-align:center;'> <h3> O curso não foi completo </h3> </div>";
                    }else{
                        if($course->method == 'alfa'){
                            \Alfa::registrarAprovacaoEmExtensao($course->idoffer, $user->username, $grade);
                            sleep(3);
                            $frame = base64_decode( \Alfa::geraCertificadoMatriculaExtensaoPorAlunoTurma($course->idoffer, $user->username) );
                            if($frame == ''){ $content = " <div style='text-align:center;'> <h3> O curso não foi completo ou o certificado ainda não foi liberado </h3> </div>"; }
                            else{
                                header('Content-type: application/pdf');
                                @header('Content-Disposition: inline; filename="carta_apresentacao_.pdf"');
                                return $frame;
                            }
                        }else if($course->method == 'inscricoes'){
                            \Inscricoes::sendAttendenceSchedule( str_replace('schedule-', '', $module->idnumber), $user->username);
                            sleep(3);
                            $frame = \Inscricoes::getCertificatePDF( $course->idoffer, $user->username);
                            $content  = "<iframe style='width: 100%; height: 500px;' src='$frame'></iframe>";
                            $content .= "<a href='$frame' class='btn btn-success' target='_BLANK'> Download </a>";
                            if($frame == ''){ $content = " <div style='text-align:center; margin-top: 30%;'> <h3> O curso não foi completo ou o certificado ainda não foi liberado </h3> </div>"; }
                        }else{
                            $content = " <div style='text-align:center;'> <h3> Não há certificados para esse curso </h3> </div>";
                        }
                    }
                }
                catch(Exception $e) {
                    $content = "<div style='text-align:center;'> <h3> O curso não foi completo ou o certificado ainda não foi liberado </h3> </div>";
                }
                return $content;
            }
            ],
        60 => [
            'id' => '60',
            'name'   => get_string('type_presentation_letter', 'alfacertificados'),
            'params' => [
                ['ref_disciplina'       => $general_info['disciplineid'] ],
                ['ref_periodo'          => $general_info['category_name'] ],
                ['n_periodos'           => 0],
                ['num_horas_atividade'  => 0],
                ['descricao_atividades' => "de estudo de documentos, observação e planejamento"],
                ['descricao_disciplina' => "Ensino Médio, a fim de oportunizar o contato com o cotidiano escolar, qualificando sua atuação na prática docente."],
                ['taxa' => false],
                ['valor_taxa' => 0]
            ],
            'callback' => function($instance, $user){
                global $CFG;

                header('Content-type: application/pdf');
                @header('Content-Disposition: inline; filename="carta_apresentacao_'.$user.'.pdf"');

                require_once($CFG->dirroot . "/local/alfa/classes/alfa.class.php");

                $filtered = [];
                $instance->params = json_decode($instance->params);
                foreach($instance->params as $param){
                    $param = (array)$param;
                    $filtered[array_keys($param)[0]] = $param[array_keys($param)[0]];
                }
                $instance->params = $filtered;

                $params = [
                    'ref_atestado' => intval($instance->type),
                    'ref_pessoa' => $user,
                    'ref_periodo' => $instance->params['ref_periodo'],
                    'ref_disciplina' => intval($instance->params['ref_disciplina']),
                    'descricao_atividades' => $instance->params['descricao_atividades'],
                    'num_horas_atividade' => intval($instance->params['num_horas_atividade']),
                    'n_periodos' => intval($instance->params['n_periodos']),
                    'descricao_disciplina' => $instance->params['descricao_disciplina'],
                    'taxa' => 0,
                    'valor_taxa' => 0
                ];

                if($instance->params->taxa != ''){
                    $params['taxa'] = true;
                    $params['valor_taxa'] = doubleval($instance->params->valor_taxa);
                }
                $params = (object)$params;

                return base64_decode(Alfa::geraCertificadoCartaApresentacaoEstagio( [$params] ) );
            }
        ]
    ];

    if($generate){ return $data[$id]; }
    if($id != null){ return alfacertificados_load_params(false, $data[$id]['params']); }

    $ret = [];
    foreach($data as $key => $value){ $ret[$key] = $value['name']; }

    return $ret;
}


function alfacertificados_load_params($id = false, $params = []){
    global $DB;

    $i = 0;
    $return = "<div id='params'>";

    if($id){
        $params = json_decode( $DB->get_record('alfacertificados', ['id' => $id ])->params );
    }

    foreach($params as $param){
        $param = (array)$param;
        $key   = array_keys($param)[0];

        $return .= '<div id="param-'.$i.'" class="form-group row fitem">';
        $return .= '    <div class="col-md-3" >';
        $return .= '        <label class="col-form-label d-inline " for="id_type"> Param </label>';
        $return .= '    </div>';
        $return .= '    <div class="col-md-3" data-fieldtype="text">';
        $return .= '        <input type="text" class="form-control" readonly name="param['.$i.']" value="'.$key.'">';
        $return .= '    </div>';

        if(is_numeric($param[$key]) || is_double($param[$key]) ){
            $return .= '    <div class="col-md-3" data-fieldtype="text">';
            $return .= '        <input type="text" class="form-control" name="value['.$i.']" value='.$param[$key].' />';
            $return .= '    </div>';
            $return .= '</div>';
        }else if( is_bool($param[$key]) || $param[$key] == 'true' || $param[$key] == 'false' ){
            $return .= '    <div class="col-md-3" data-fieldtype="text">';
            $return .= '        <select class="custom-select" name="value['.$i.']" >';
            $return .= '            <option value="false">Não</option>';
            if($param[$key] == 'true'){
                $return .= '            <option value="true" selected>Sim</option>';
            }else{
                $return .= '            <option value="true">Sim</option>';
            }
            $return .= '        </select>';
            $return .= '    </div>';
            $return .= '</div>';
        }else{
            $return .= '    <div class="col-md-3" data-fieldtype="text">';
            $return .= '        <textarea class="form-control"  name="value['.$i.']" >'.$param[$key].'</textarea>';
            $return .= '    </div>';
            $return .= '</div>';
        }

        $i++;
    }
    $return .= '</div>';
    return $return;
}

function alfacertificados_conclusao($module){
    global $DB, $USER, $COURSE, $CFG;

    require_once($CFG->dirroot . "/local/alfa/classes/alfa.class.php");
    require_once($CFG->dirroot . "/local/inscricoes/classes/inscricoes.class.php");

    $content = '';

    //Load the local_alfa instance, case it is a Extension course
    $offer = $DB->get_record('local_alfa', ['courseid' => $COURSE->id]);
    if($offer){
        $COURSE->idoffer = $offer->idnumber;
        $COURSE->method = 'alfa';
    }

    //Load the local_inscricoes instance, case it is a Event. Also, it will overwrite the alfa instance
    $offer = $DB->get_record('local_inscricoes', ['courseid' => $COURSE->id]);
    if($offer){
        $COURSE->idoffer = $offer->idnumber;
        $COURSE->method = 'inscricoes';
    }

    try{
        if( core_completion\progress::get_course_progress_percentage($COURSE, $USER->id) < 90 && !is_siteadmin()){
            $content = " <div style='text-align:center; margin-top: 30%;'> <h3> O curso não foi completo </h3> </div>";
        }else{
            if($COURSE->method == 'alfa'){
                \Alfa::registrarAprovacaoEmExtensao($COURSE->idoffer, $USER->username);
                sleep(3);
                $frame = \Alfa::geraCertificadoMatriculaExtensaoPorAlunoTurma($COURSE->idoffer, $USER->username);
                $content  = "<iframe style='width: 100%; height: 500px;' src='$frame'></iframe>";
                $content .= "<a href='$frame' class='btn btn-success' target='_BLANK'> Download </a>";
                if($frame == ''){ $content = " <div style='text-align:center; margin-top: 30%;'> <h3> O curso não foi completo ou o certificado ainda não foi liberado </h3> </div>"; }
            }else if($COURSE->method == 'inscricoes'){
                \Inscricoes::sendAttendenceSchedule( str_replace('schedule-', '', $module->idnumber), $USER->username);
                sleep(3);
                $frame = \Inscricoes::getCertificatePDF( $COURSE->idoffer, $USER->username);
                $content  = "<iframe style='width: 100%; height: 500px;' src='$frame'></iframe>";
                $content .= "<a href='$frame' class='btn btn-success' target='_BLANK'> Download </a>";
                if($frame == ''){ $content = " <div style='text-align:center; margin-top: 30%;'> <h3> O curso não foi completo ou o certificado ainda não foi liberado </h3> </div>"; }
            }else{
                $content = " <div style='text-align:center; margin-top: 30%;'> <h3> Não há certificados para esse curso </h3> </div>";
            }
        }
    }
    catch(Exception $e) {
        $content = " <div style='text-align:center; margin-top: 30%;'> <h3> O curso não foi completo ou o certificado ainda não foi liberado </h3> </div>";
    }

    return $content;

}

function alfacertificados_get_students_combo(){
    global $DB, $COURSE;

    $students = $DB->get_records_sql("SELECT id, firstname || ' ' || lastname as name FROM mdl_user WHERE id IN (select userid from mdl_role_assignments WHERE contextid = (SELECT id from mdl_context WHERE instanceid = ? AND contextlevel = 50) AND roleid = (SELECT id FROM mdl_role WHERE shortname = 'student'));", [$COURSE->id]);

    $return = '<select id="users" class="custom-select"> <option value="0">Selecione</option>';
    foreach($students as $student){
        $return .= "<option value='$student->id'> $student->name </option>";
    }
    $return .= '</select>';

    return $return;
}
