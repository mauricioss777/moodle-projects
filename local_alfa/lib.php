<?php

defined('MOODLE_INTERNAL') || die();

require_once('classes/alfa.class.php');

/**
 *
 *
 */
function local_alfa_add_offer($courseid, $idnumber)
{
    global $DB;

    if($DB->record_exists('local_alfa', ['courseid' => $courseid, 'idnumber' => $idnumber])){
        return false;
    }

    $alfa = Alfa::getCourseInformation($idnumber);

    $DB->insert_record('local_alfa', (object)['courseid' => $courseid, 'idnumber' => $idnumber, 'disciplineid' => $alfa['disciplineid']]);

    return;
}

/**
 *
 *
 */
function local_alfa_add_curriculim($courseid, $curriculumid)
{
    global $DB;

    if($DB->record_exists('local_alfa_curriculum', ['courseid' => $courseid, 'curriculum' => $curriculumid])){
        return false;
    }

    $DB->insert_record('local_alfa_curriculum', ['courseid' => $courseid, 'curriculum' => $curriculumid]);

    return;
}

// Add the menu item
function local_alfa_extend_navigation(global_navigation $nav)
{
    global $CFG, $PAGE, $DB, $USER;

    if(!is_siteadmin()){
        return;
    }

    $PAGE->requires->css('/local/pages/font/fontello.css');
    $PAGE->requires->css('/local/pages/style/custom.css');

    $node = navigation_node::create(get_string('pluginname', 'local_alfa'),
        new moodle_url('/local/alfa/actions'),
        navigation_node::NODETYPE_LEAF,
        'alfa',
        'alfa',
        new pix_icon('univates', get_string('pluginname', 'local_alfa'), 'local_alfa')
    );

    if($USER->username == 'ahwelp'){
        //error_log( print_r($navigation->find('grades', 0), true) );
        //error_log( print_r($nav->get_children_key_list(), true) );
        //error_log( print_r(get_class_methods($nav->get('currentcourse')), true) );
    }

    $node->showinflatnavigation = true;
    $nav->add_node($node);
}

/**
 * This function extends the navigation with the report items
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $course The course to object for the report
 *
 * @param stdClass $context The context of the course
 */
function local_alfa_extend_navigation_course($navigation, $course, $context) {
    global $CFG, $OUTPUT, $USER, $PAGE;

    $url = new moodle_url('/local/alfa/actions/courseinfo.php', array('id'=>$course->id));
    $navigation->add(get_string('pluginname','local_alfa'), $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('univates', get_string('pluginname', 'local_alfa'), 'local_alfa'));

    if($USER->username == 'ahwelp'){
        //error_log( print_r($PAGE->navigation->find($courseid, navigation_node::TYPE_COURSE), true) );
        //error_log( print_r($PAGE->navigation->get_children_key_list(), true) );
        //error_log( print_r($navigation->find('grades', 0), true) );
        //error_log( print_r($navigation->get_children_key_list(), true) );
    }

}

function local_alfa_get_fontawesome_icon_map()
{
    return array(
        'local_alfa:univates' => 'icon-univates',
    );
}

