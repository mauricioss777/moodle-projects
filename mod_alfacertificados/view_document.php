<?php

require_once('../../config.php');
require_once('lib.php');

// https://stackoverflow.com/questions/20161209/how-can-i-get-cm-id-based-on-the-courseid-in-moodle-2-6-0-version
// https://docs.moodle.org/dev/Access_API#has_capability.28.29

$id     = optional_param('id', 0, PARAM_INT);
$userid = optional_param('userid', 0, PARAM_INT);

$context        = context_module::instance( $id );
$course_id      = $context->get_course_context()->instance;
$course_context = $context->get_course_context()->id;

// If you can't but is trying. Reset the user id
if(!has_capability('mod/alfacertificados:viewother', $context) && $userid != 0 ){
    $userid = 0;
}

if($userid == 0){ $userid = $USER->id; }

if( !$DB->record_exists('role_assignments', [ 'contextid' => $course_context, 'userid' => $userid] ) ){
    redirect("course/view.php?id=$course_id");
}

$instance = $DB->get_record_sql("SELECT * FROM {alfacertificados} WHERE id = (SELECT instance FROM {course_modules} WHERE id = ".$context->instanceid.")");

$userid = $DB->get_record('user', ['id' => $userid])->username;

echo alfacertificados_get_methods($instance->type, true)['callback']($instance, $userid);
