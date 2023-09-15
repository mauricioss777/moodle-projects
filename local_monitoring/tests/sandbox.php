<?php

define('CLI_SCRIPT', true);
require_once('../../../config.php');

//$events = $DB->get_records_sql("SELECT 
$sql = "SELECT 
           mbb.id, 
           mus.id, 
           mus.username, 
           mbb.courseid, 
           mbb.bigbluebuttonbnid,
           mcm.id, 
           mbb.timecreated 
         FROM 
           mdl_bigbluebuttonbn_logs mbb, 
           mdl_course_modules mcm, 
           mdl_user mus 
         WHERE 
             mus.id = mbb.userid AND 
             mcm.module = 35 AND 
             mcm.instance = mbb.bigbluebuttonbnid AND 
             mbb.log = 'Join'";
echo $sql;die;
$events = $DB->get_records_sql($sql);
$i = 0;
foreach($events as $event){
        $class = new stdClass();
        $class->userid = $event->userid;
        $class->mod = 'mod_bigbluebuttonbn';
        $class->course = $event->courseid;
        $class->username = $event->username;
        $class->moduleid = $record->moduleid;
        $class->instanceid= $record->instanceid;
        $class->time = $event->timecreated;
        echo "---- Registrando ".$class->username." \n";
    $i++;
        // $DB->insert_record('local_monitoring_videos_instances', $class);
}
echo $i;
die;
require_once( $CFG->libdir . '/gradelib.php' );
require_once( $CFG->libdir . '/grade/grade_item.php' );
require_once( $CFG->libdir . '/grade/grade_grade.php' );
require_once('../../../grade/querylib.php');

// var_dump( grade_get_course_grade(23118, [37028]) );
$user = array_pop( grade_get_course_grade(23118, [37028]) );
var_dump($user->str_grade);
die;
// mdl_local_monitoring_videos 

$records = $DB->get_records_sql("
    SELECT 
        mcm.id, mcm.instance as instanceid, meet.timeend as time, meet.course
    FROM 
        mdl_course_modules mcm,
        mdl_meet meet
    WHERE 
        meet.id = mcm.instance AND 
        module = 37 AND meet.timeend < " . time() );

foreach($records as $record){

    $sql = "SELECT 
                mlsl.id, userid, 
                mus.username, 
                courseid, 
                mlsl.eventname,
                mlsl.timecreated
            FROM mdl_logstore_standard_log mlsl, 
                 mdl_user mus 
            WHERE mus.id = mlsl.userid AND 
                  component = 'mod_meet' AND 
                  objectid = ".$record->instanceid." AND 
                  courseid = ".$record->course." AND 
                  mlsl.timecreated < ".$record->time." AND 
                  mlsl.timecreated > ".($record->time - (60 * 60 * 2) );
    
    $events = $DB->get_records_sql($sql);

    foreach($events as $event){
        if(!is_numeric($event->username)){ continue; }
        if($event->eventname != '\mod_meet\event\meeting_joined') { continue; }
        $class = new stdClass();
        $class->userid = $event->userid;
        $class->mod = 'mod_meet';
        $class->course = $event->courseid;
        $class->username = $event->username;
        $class->moduleid = $record->moduleid;
        $class->instanceid= $record->instanceid;
        $class->time = $event->timecreated;
        echo "---- Registrando ".$class->username." \n";
    
        $DB->insert_record('local_monitoring_videos_instances', $class);
    }

}

die;
$records = $DB->get_records('local_monitoring_videos');


foreach($records as $record){
    //echo "Buscando dados para a URL: ". $record->moduleid." no curso  ". $record->course . "\n";
    //echo $CFG->wwwroot."/mod/url/view.php?id=". $record->moduleid."\n";
    //$sql = "SELECT mlsl.id, userid, mus.username, courseid, mlsl.timecreated FROM {logstore_standard_log} mlsl, {user} mus WHERE mus.id = mlsl.userid AND component = 'mod_url' AND objectid = ? AND courseid = ? AND mlsl.timecreated < ".$record->time;
    $sql = "SELECT 
                mlsl.id, userid, 
                mus.username, 
                courseid, 
                mlsl.timecreated
            FROM mdl_logstore_standard_log mlsl, 
                 mdl_user mus 
            WHERE mus.id = mlsl.userid AND 
                  component = 'mod_url' AND 
                  objectid = ".$record->instanceid." AND 
                  courseid = ".$record->course." AND 
                  mlsl.timecreated < ".$record->time." AND 
                  mlsl.timecreated > ".($record->time - (60 * 60 * 2) );

    $events = $DB->get_records_sql($sql);
    if( sizeof($events) == 0){
        echo $record->moduleid . "\n";
    }
    continue;
    foreach($events as $event){
        if(!is_numeric($event->username)){ continue; }
        $class = new stdClass();
        $class->userid = $event->userid;
        $class->mod = 'mod_url';
        $class->course = $event->courseid;
        $class->username = $event->username;
        $class->moduleid = $record->moduleid;
        $class->instanceid= $record->instanceid;
        $class->time = $event->timecreated;
        echo "---- Registrando ".$class->username." \n";
    
        //$DB->insert_record('local_monitoring_videos_instances', $class);
    }
}

die;
$records = $DB->get_records_sql( "SELECT 
                                   mcm.id, 
                                   mcm.course, 
                                   mcm.instance, 
                                   murl.name, 
                                   mcm.availability 
                                  FROM 
                                   mdl_course_modules mcm, 
                                   mdl_url murl 
                                  WHERE 
                                   module = 32 AND 
                                   mcm.instance = murl.id AND 
                                   name LIKE ('%Videoconferência: %') AND 
                                   name NOT ILIKE ('%gravada%') AND 
                                   name ILIKE ('%ao vivo%');" );

foreach($records as $record){
    $class = new stdClass();
    $class->course = $record->course;
    $class->mod = 'mod_url';
    $class->moduleid = $record->id;
    $class->instanceid = $record->instance;
    $class->time = json_decode($record->availability)->c[0]->t + (60 * 60 * 1);
    $DB->insert_record('local_monitoring_videos', $class);
}
