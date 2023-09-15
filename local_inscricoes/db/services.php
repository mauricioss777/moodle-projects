<?php

/**
 * O array services abaixo cria um novo serviço na lista de web services
 */
$services = array(
    'Web_service_Integracao_com_o_Inscricoes' => array(
        'functions' => array (
            'local_inscricoes_create_course_event',
            'local_inscricoes_remove_user_event',
            'local_inscricoes_get_attendance_event'
        ),
        'requiredcapability' => 'moodle/course:create',
        'restrictedusers' =>0,
        'shortname' => 'Web_service_Integracao_com_o_Inscricoes',
        'enabled' => 1
    ),
);

/**
 * Libera as funções que estão implementadas em alfa/externalib.php
 */
$functions = array(
    'local_inscricoes_create_course_event' => array(
        'classname'   => 'local_inscricoes_external',
        'methodname'  => 'create_course_event',
        'classpath'   => 'local/inscricoes/externallib.php',
        'description' => 'Create new course with simple structure.',
        'capabilities'=> 'moodle/course:create',
        'type'        => 'write'
    ),
    'local_inscricoes_remove_user_event' => array(
        'classname'   => 'local_inscricoes_external',
        'methodname'  => 'remove_user_event',
        'classpath'   => 'local/inscricoes/externallib.php',
        'description' => 'Remove user from event course.',
        'capabilities'=> 'moodle/course:create',
        'type'        => 'write'
    ),
    'local_inscricoes_get_attendance_event' => array(
    'classname'   => 'local_inscricoes_external',
    'methodname'  => 'get_attendance_event',
    'classpath'   => 'local/inscricoes/externallib.php',
    'description' => 'Get attendance from the user on a schedule.',
    'capabilities'=> 'moodle/course:create',
    'type'        => 'write'
    )
);
