<?php

define('CLI_SCRIPT', true);
require_once('../../../config.php');

$course = $DB->get_record('course', ['id' => 35956]);
$users = $DB->get_records_sql("SELECT id, firstname || ' ' || lastname as name 
                               FROM mdl_user 
                               WHERE id IN (
                                   select userid 
                                   from mdl_role_assignments 
                                   WHERE contextid = (
                                    SELECT id from mdl_context 
                                    WHERE instanceid = ? AND contextlevel = 50) 
                                AND roleid = (SELECT id FROM mdl_role WHERE shortname = 'student'));", [35956]);

foreach($users as $user){
    if( core_completion\progress::get_course_progress_percentage($course, $user->id) > 90){
        echo $user->name . "\n";
    }
}
