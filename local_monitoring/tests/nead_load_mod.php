
<?php

define('CLI_SCRIPT', true);

require_once(__DIR__.'/../../../config.php');

/*$logs = $DB->get_records_sql( "SELECT mlsl.id, username, userid, courseid, MAX(mlsl.timecreated) as lasttime, contextid, objecttable as module, objectid   
    FROM mdl_logstore_standard_log mlsl inner join mdl_user mus on mlsl.userid = mus.id WHERE courseid = 34711 AND objecttable = 'bigbluebuttonbn' GROUP BY mlsl.id, username, userid, courseid" );

foreach($logs as $log){

    unset($log->id);
    $DB->insert_record('local_monitoring_history', $log);

}

die;*/

$users = $DB->get_records_sql('SELECT id FROM mdl_user WHERE id IN ( select userid from mdl_role_assignments WHERE contextid IN (SELECT id FROM mdl_context WHERE contextlevel = 50 AND instanceid IN (SELECT id FROM mdl_course WHERE category = 264) ) )');
$courses = $DB->get_records_sql('SELECT id FROM mdl_course WHERE category = 264');
var_dump( strpos( implode(', ', array_keys($users) ), '96986' ) );
die;
//$courses = [ (object) ['id' => 34698] ];
//$users = [ (object) ['id' => 21957]];

//$timeLimit = time() - (60 *60 * 60 * 24 * 7);

foreach($users as $user){
    echo "$user->id \n";
    foreach($courses as $course){
        /*$records = $DB->get_records_sql( "SELECT mlsl.id, username, userid, courseid, MAX(mlsl.timecreated) as lasttime, contextid, objecttable as module, objectid  
                                           FROM mdl_logstore_standard_log mlsl INNER JOIN mdl_user mus ON mus.id = mlsl.userid 
                                          WHERE courseid = $course->id AND
                                                userid = $user->id 
                                                AND component = 'mod_bigbluebuttonbn' AND action IN ('viewed', 'join') GROUP BY mlsl.id, username, userid, courseid, contextid, module, objectid ");*/
        $records = $DB->get_records_sql( "SELECT mlsl.id, username, userid, courseid, MAX(mlsl.timecreated) as lasttime, contextid, objecttable as module, objectid  
                                        FROM mdl_logstore_standard_log mlsl INNER JOIN mdl_user mus ON mus.id = mlsl.userid 
                                        WHERE courseid = $course->id AND 
                                              userid = $user->id AND 
                                              component LIKE ('mod_%') AND objecttable NOT IN ('quiz_attempts',
                                                                                               'forum_discussions',
                                                                                               'assign_grades',
                                                                                               'assign_submission',
                                                                                               'forum_discussions',
                                                                                               'quiz_attempts'
                                                                                 ) 
                                              GROUP BY mlsl.id, username, userid, courseid, contextid, module, objectid " );

        if(!$records){ continue; }
        foreach($records as $record){
            if($record->module == null){ continue; } 
            unset($record->id);
            $DB->insert_record('local_monitoring_history', $record);
            echo "ADICIONANDO: $record->userid NO CURSO $record->courseid NO MODULO $record->module \n";
        }
    }
}


