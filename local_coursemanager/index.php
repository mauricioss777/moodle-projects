<?php

require_once ('../../config.php');
$user_list = $DB->get_records('local_coursemanager_users');
$users = Array();

foreach ($user_list as $item) {
    $users[] = $item->userid;
}

if(!in_array($USER->id, $users) && !is_siteadmin()){
    die();
}

$id = optional_param('courseid', 0, PARAM_INT);

$course = $DB->get_record('course', Array('id'=>$id) );
$course_name = $course->fullname;
$courses = $DB->get_records_sql("SELECT id, fullname 
                                          FROM {course}
                                        WHERE id IN (SELECT courseid
                                                              FROM {local_coursemanager_watch}
                                                              WHERE userid = $USER->id)" );
$course_list = '';
foreach ($courses as $course){
    $course_list .= "<li> <a data-id='$course->id'> $course->fullname </a></li>\n";
}

$PAGE->set_title(get_string('pluginname', 'local_coursemanager'));
$PAGE->set_heading(get_string('pluginname', 'local_coursemanager'));

echo $OUTPUT->header();
echo "<script> var course = $id; var course_name = '$course_name'</script>";
$categories = $DB->get_records_sql("SELECT id, name FROM mdl_course_categories WHERE parent = (SELECT id from mdl_course_categories where name = 'GRADUAÇÃO') ORDER BY id DESC");
$categories = array_merge($categories, $DB->get_records_sql("SELECT id, name FROM mdl_course_categories WHERE parent = (SELECT id from mdl_course_categories where name = 'TECNÓLOGO') ORDER BY id DESC") );

$categories_temp = Array();
foreach($categories as $category){
    if( !isset($categories_temp[$category->name]) ){
        $categories_temp[$category->name] = Array();
        $categories_temp[$category->name]->name = $category->name;
    }
    $categories_temp[$category->name]['id'] .= "$category->id,";
}

$category_list = '<option value="-1" selected> Select Category </option>';
$categories = $categories_temp;

foreach ($categories as $key => $category){
    $category_list .= "<option value='".rtrim($category['id'], ', ')."'> $key </option>/n";
}

include 'templates/course_report.php';

echo $OUTPUT->footer();