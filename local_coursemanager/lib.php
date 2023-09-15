<?php

defined('MOODLE_INTERNAL') || die();

function local_coursemanager_user_can_access(){
    global $DB, $USER;

    if( is_siteadmin() ) { return true; }

    $u = $DB->get_record('local_coursemanager_users', ['userid' => $USER->id] );

    if($u){
        return true;
    }else{
        return false;
    }
}


// Add the menu item
function local_coursemanager_extend_navigation(global_navigation $nav)
{
    global $CFG, $PAGE, $DB, $USER;

    if( !local_coursemanager_user_can_access() ){
        return;
    }

    $node = navigation_node::create(get_string('pluginname', 'local_coursemanager'),
        new moodle_url('/local/coursemanager'),
        navigation_node::NODETYPE_LEAF,
        'coursemanager',
        'coursemanager',
        new pix_icon('eye', get_string('pluginname', 'local_coursemanager'), 'local_coursemanager')
    );
    $node->showinflatnavigation = true;
    $nav->add_node($node);
}

function local_coursemanager_get_fontawesome_icon_map()
{
    return array(
        'local_coursemanager:eye' => 'fa-eye',
    );
}
