<?php

$services = array(
    'Web_service_Integração_com_o_monitoramento' => array(
        'functions' => array (
            'local_monitoring_dias_sem_acesso',
            'local_monitoring_permanencia_semanal',
            'local_monitoring_permanencia_semanal_historico',
            'local_monitoring_acesso_material',
            'local_monitoring_performance_atividades',
            'local_monitoring_atividades_realizadas',
            'local_monitoring_participacao_videoconferencia',
            'local_monitoring_user_internal_code',
            'local_monitoring_lexp_acessos_po_dias',
            'local_monitoring_lexp_lms_dados'
        ),
        'requiredcapability' => 'moodle/course:create',
        'restrictedusers' => 0,
        'shortname' => 'Webservice_de_integracao_com_monitoramento',
        'enabled' => 1
    ),
);

$functions = array(
    'local_monitoring_dias_sem_acesso' => array(
        'classname'   => 'local_monitoring_external',
        'methodname'  => 'dias_sem_acesso',
        'classpath'   => 'local/monitoring/externallib.php',
        'description' => 'Retorna ultimo acesso para as turmas',
        'capabilities'=> 'moodle/course:create',
        'type'        => 'write'
    ),
    'local_monitoring_permanencia_semanal' => array(
        'classname'   => 'local_monitoring_external',
        'methodname'  => 'permanencia_semanal',
        'classpath'   => 'local/monitoring/externallib.php',
        'description' => 'Retorna permanencia para a semana atual do usuário',
        'capabilities'=> 'moodle/course:create',
        'type'        => 'write'
    ),
    'local_monitoring_permanencia_semanal_historico' => array(
        'classname'   => 'local_monitoring_external',
        'methodname'  => 'permanencia_semanal_historico',
        'classpath'   => 'local/monitoring/externallib.php',
        'description' => 'Retorna permanencia para semanas anteriores',
        'capabilities'=> 'moodle/course:create',
        'type'        => 'write'
    ),
    'local_monitoring_performance_atividades' => array(
        'classname'   => 'local_monitoring_external',
        'methodname'  => 'performance_atividades',
        'classpath'   => 'local/monitoring/externallib.php',
        'description' => 'Retorna performance dos usuários nas atividades do curso',
        'capabilities'=> 'moodle/course:create',
        'type'        => 'write'
    ),
    'local_monitoring_acesso_material' => array(
        'classname'   => 'local_monitoring_external',
        'methodname'  => 'acesso_material',
        'classpath'   => 'local/monitoring/externallib.php',
        'description' => 'Retorna ultimo acesso do usuário ao material didático de cada turma',
        'capabilities'=> 'moodle/course:create',
        'type'        => 'write'
    ),
    'local_monitoring_atividades_realizadas' => array(
        'classname'   => 'local_monitoring_external',
        'methodname'  => 'atividades_realizadas',
        'classpath'   => 'local/monitoring/externallib.php',
        'description' => 'Retorna porcentagem de atividades efetuadas para turmas',
        'capabilities'=> 'moodle/course:create',
        'type'        => 'write'
    ),
    'local_monitoring_participacao_videoconferencia' => array(
        'classname'   => 'local_monitoring_external',
        'methodname'  => 'participacao_videoconferencia',
        'classpath'   => 'local/monitoring/externallib.php',
        'description' => 'Retorna porcentagem de participação de videoconferências',
        'capabilities'=> 'moodle/course:create',
        'type'        => 'write'
    ),
    'local_monitoring_user_internal_code' => array(
        'classname'   => 'local_monitoring_external',
        'methodname'  => 'user_internal_code',
        'classpath'   => 'local/monitoring/externallib.php',
        'description' => 'Get the user internal code.',
        'capabilities'=> 'moodle/course:managegrades',
        'type'        => 'write'
    ),
    'local_monitoring_potential_users' => array(
        'classname' => 'local_monitoring_external',
        'methodname' => 'get_potential_users',
        'classpath' => 'local/monitoring/externallib.php',
        'description' => 'Get the list of potential users to enrol',
        'ajax' => true,
        'type' => 'read',
        'capabilities' => 'moodle/course:enrolreview'
    ),
    'local_monitoring_lexp_acessos_po_dias' => array(
        'classname' => 'local_monitoring_external',
        'methodname' => 'lexp_acessos_po_dias',
        'classpath' => 'local/monitoring/externallib.php',
        'description' => 'Get access by a given day',
        'capabilities' => 'moodle/course:enrolreview',
        'type' => 'read'
    ),
    'local_monitoring_lexp_lms_dados' => array(
        'classname' => 'local_monitoring_external',
        'methodname' => 'lexp_lms_dados',
        'classpath' => 'local/monitoring/externallib.php',
        'description' => 'Get data by a given period',
        'capabilities' => 'moodle/course:enrolreview',
        'type' => 'read'
    )
);
