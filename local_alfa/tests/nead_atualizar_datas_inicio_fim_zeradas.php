<?php

define('CLI_SCRIPT', true);

require_once('../../../config.php');
require_once('../classes/alfa.class.php');

ini_set("error_log", "/tmp/nead_atualizar_datas_inicio_fim_zeradas.log");

$disciplinas = $DB->get_records_sql("SELECT id, fullname FROM mdl_course WHERE enddate = 0 AND fullname ILIKE '%REF%' AND fullname NOT ILIKE '%REF999%' AND fullname NOT ILIKE '%PÓS-GRADUAÇÃO%' AND fullname NOT ILIKE '%MBA%' ORDER BY id DESC");

foreach ($disciplinas as $disciplina){

    $ref = substr($disciplina->fullname, strpos($disciplina->fullname, 'REF') + 3);

    if($preg = preg_pos($ref, '\D')){
        $ref = substr($ref, 0, $preg);
    }

    if(!empty($ref) AND is_number($ref)){
        $info = Alfa::getCourseInformation($ref);

        error_log("#{$disciplina->id} - {$disciplina->fullname} || REF: {$ref} - {$info['enddate']}");

        if(!empty($info)){
            $disc = new stdClass();
            $disc->id = $disciplina->id;
            $disc->timemodified = time();

            if(!empty($info['inidate'])){
                $disc->startdate = strtotime($info['inidate']);
            }
            if(!empty($info['enddate'])){
                $disc->enddate= strtotime('+1 day', strtotime($info['enddate']));
            }

        error_log("updating_record::course::#".$disc->id);

        $DB->update_record('course', $disc);

        } else {
            error_log("*** empty \$info for ref: {$ref} || {$disciplina->fullname} ***");
        }
    } else {
        error_log("*** empty \$ref or is not number: {$ref} || {$disciplina->fullname}***");
    }

}

function preg_pos( $subject, $regex )
{
    if( preg_match( '/^(.*?)'.$regex.'/', $subject, $matches ) )
        return strlen( $matches[ 1 ] );

    return false;
}

