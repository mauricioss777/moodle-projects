<?php

define('CLI_SCRIPT', true);

require_once(__DIR__.'/../../../config.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir . '/filestorage/file_system.php');
require_once($CFG->libdir . '/filestorage/file_system_filedir.php');

// Define cliscript user
\core\session\manager::init_empty_session();
\core\session\manager::set_user($DB->get_record('user', ['id' => 2]));

//Declare categories to clear
//$categories = [42];
//$categories = array_merge($categories, [38, 39, 37, 40, 67, 68, 31, 69, 4, 96, 9, 30, 27, 32, 42, 46, 51, 63, 71, 75, 82, 86] );
//$categories = array_merge($categories, [33, 44, 48, 52, 65, 73, 78, 83, 87] );
//$categories = array_merge($categories, [66, 19, 50, 41, 26, 34, 43, 47, 53, 64, 72, 79, 81, 88] );

//Prepare and load courses
$categories_list = implode(', ', $categories);
$courses = $DB->get_records_sql("SELECT mco.id, mco.fullname, mcc.name 
                                   FROM {course} mco, {course_categories} mcc 
                                   WHERE mcc.id = mco.category AND category IN ($categories_list)");

$ammount = sizeof($courses);
$item = 1;

//Do the stuff
foreach($courses as $key => $course){
    try{
        if($course->id % 2 == 0){ continue; }
        echo "Category $course->name -- Course $course->fullname $item / $ammount \n";
        delete_course($course->id);
        $item++;
    } catch(Exception $e) { }
}

//Clear the category trash
foreach($categories as $category){
    try{
        $context = context_coursecat::instance($category); 
        $recyclebin = new \tool_recyclebin\category_bin($context->instanceid);
        $recyclebin->delete_all_items();
    } catch(Exception $e) { }
}

//Delete files on the dir
$fs = new file_system_filedir();
$fs->cron();
