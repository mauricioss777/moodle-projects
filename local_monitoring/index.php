<?php

require_once('../../config.php');
include __DIR__.'/classes/output/renderer.php';
include __DIR__.'/classes/output/index.php';
include __DIR__.'/lib.php';

if(!local_monitoring_user_can_access()){
    redirect($CFG->wwwroot.'/my');
}


/*
 *  Filtros
 *  - Período dos ambientes / Categoria / Ambiente
 *
 * Emails enviados / Recebidos / Lidos
 *
 * */

require_once($CFG->dirroot . '/local/monitoring/templates/user_form.php');

$action = optional_param('action', '' , PARAM_RAW);
$payload = optional_param('payload', '' , PARAM_RAW);
$renderable = null;

switch ($action) {
    case 'user':
        $user = $DB->get_record('user', ['username' => $payload] );
        $user->context = context_user::instance($user->id, IGNORE_MISSING)->id;
        $renderable = new \local_monitoring\output\index( $user );
        break;
    case 'categories':
        $payload = str_replace( '-', ',', rtrim($payload, '-') );
        $renderable = new \local_monitoring\output\tests( $payload );
        break;
    case 'categories_with_no_access':
        $payload = str_replace( '-', ',', rtrim($payload, '-') );
        $renderable = new \local_monitoring\output\noaccess( $payload );
        break;
    default:
        $renderable = new \local_monitoring\output\form( [] );
        break;
}

$output = $PAGE->get_renderer('local_monitoring');

echo $OUTPUT->header();
echo $output->render($renderable);
echo $OUTPUT->footer();
