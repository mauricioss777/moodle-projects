<?php

/**
 * O array services abaixo cria um novo serviço na lista de web services
 */
$services = array(
    'Web_service_Integração_com_o_Alfa' => array(
        'functions' => array (
            'local_alfa_create_course',
            'local_alfa_create_course_array',
            'local_alfa_create_course_tcc',
            'local_alfa_add_offer_to_course',
            'local_alfa_get_course_grades',
            'local_alfa_get_course_all_grades',
            'local_alfa_get_user_attendance',
            'local_alfa_set_test_grade',
            'local_alfa_get_access_on_period',
            'local_alfa_user_internal_code',
            'local_alfa_course_internal_code',
            'local_alfa_add_user_to_course',
            'local_alfa_get_course_grades_activities',
            'local_alfa_alfaget_course_grades',
        ),
        'requiredcapability' => 'moodle/course:create',
        'restrictedusers' =>0,
        'shortname' => 'Webservice_de_integracao_com_o_Alfa',
        'enabled' => 1
    ),
);


/**
 * Libera as funções que estão implementadas em alfa/externalib.php
 */
$functions = array(
    'local_alfa_create_course' => array(
        'classname'   => 'local_alfa_external',
        'methodname'  => 'create_course',
        'classpath'   => 'local/alfa/externallib.php',
        'description' => 'Create new course.',
        'capabilities'=> 'moodle/course:create',
        'type'        => 'write'
    ),
    'local_alfa_create_course_array' => array(
        'classname'   => 'local_alfa_external',
        'methodname'  => 'create_course_array',
        'classpath'   => 'local/alfa/externallib.php',
        'description' => 'Create new course based on offers array.',
        'capabilities'=> 'moodle/course:create',
        'type'        => 'write'
    ),
    'local_alfa_create_course_tcc' => array(
        'classname'   => 'local_alfa_external',
        'methodname'  => 'create_course_tcc',
        'classpath'   => 'local/alfa/externallib.php',
        'description' => 'Create new course TCC.',
        'capabilities'=> 'moodle/course:create',
        'type'        => 'write'
    ),
    'local_alfa_add_offer_to_course' => array(
        'classname'   => 'local_alfa_external',
        'methodname'  => 'add_offer_to_course',
        'classpath'   => 'local/alfa/externallib.php',
        'description' => 'Add a new offer to a course array.',
        'capabilities'=> 'moodle/course:update',
        'type'        => 'write'
    ),
    'local_alfa_get_course_grades' => array(
        'classname'   => 'local_alfa_external',
        'methodname'  => 'get_course_grades',
        'classpath'   => 'local/alfa/externallib.php',
        'description' => 'Get the final grade of all students.',
        'capabilities'=> 'moodle/course:managegrades',
        'type'        => 'write'
    ),
    'local_alfa_get_course_all_grades' => array(
        'classname'   => 'local_alfa_external',
        'methodname'  => 'get_course_all_grades',
        'classpath'   => 'local/alfa/externallib.php',
        'description' => 'Get structured grades for users',
        'capabilities'=> 'moodle/course:managegrades',
        'type'        => 'write'
    ),
    'local_alfa_get_user_attendance' => array(
        'classname'   => 'local_alfa_external',
        'methodname'  => 'get_user_attendance',
        'classpath'   => 'local/alfa/externallib.php',
        'description' => 'Get the last user access.',
        'capabilities'=> 'moodle/course:managegrades',
        'type'        => 'write'
    ),
    'local_alfa_set_test_grade' => array(
        'classname'   => 'local_alfa_external',
        'methodname'  => 'set_test_grade',
        'classpath'   => 'local/alfa/externallib.php',
        'description' => 'Get the last user access.',
        'capabilities'=> 'moodle/course:managegrades',
        'type'        => 'write'
    ),
    'local_alfa_get_access_on_period' => array(
        'classname'   => 'local_alfa_external',
        'methodname'  => 'get_access_on_period',
        'classpath'   => 'local/alfa/externallib.php',
        'description' => 'Get the last user access.',
        'capabilities'=> 'moodle/course:managegrades',
        'type'        => 'write'
    ),
    'local_alfa_user_internal_code' => array(
        'classname'   => 'local_alfa_external',
        'methodname'  => 'user_internal_code',
        'classpath'   => 'local/alfa/externallib.php',
        'description' => 'Get the user internal code.',
        'capabilities'=> 'moodle/course:managegrades',
        'type'        => 'write'
    ),
    'local_alfa_course_internal_code' => array(
        'classname'   => 'local_alfa_external',
        'methodname'  => 'course_internal_code',
        'classpath'   => 'local/alfa/externallib.php',
        'description' => 'Get the course internal code.',
        'capabilities'=> 'moodle/course:managegrades',
        'type'        => 'write'
    ),
    'local_alfa_add_user_to_course' => array(
        'classname'   => 'local_alfa_external',
        'methodname'  => 'add_user_to_course',
        'classpath'   => 'local/alfa/externallib.php',
        'description' => 'Add user to course.',
        'capabilities'=> 'moodle/course:managegrades',
        'type'        => 'write'
    ),
    'local_alfa_get_course_grades_activities' => array(
        'classname'   => 'local_alfa_external',
        'methodname'  => 'get_course_grades_activities',
        'classpath'   => 'local/alfa/externallib.php',
        'description' => 'Get activities grades.',
        'capabilities'=> 'moodle/course:managegrades',
        'type'        => 'write'
    ),
    'local_alfa_alfaget_course_grades' => array(
        'classname'   => 'local_alfa_external',
        'methodname'  => 'alfaget_course_grades',
        'classpath'   => 'local/alfa/externallib.php',
        'description' => 'Get activities grades.',
        'capabilities'=> 'moodle/course:managegrades',
        'type'        => 'write'
    )
);
