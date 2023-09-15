<?php

define('CLI_SCRIPT', 1);

require_once('../../../config.php');
require_once('../lib.php');

cron_setup_user();

$meets = $DB->get_records_sql("SELECT * FROM {meet} WHERE timeend < " . time() . " ORDER BY id DESC" );
$meets = $DB->get_records_sql("SELECT * FROM {meet} WHERE id IN (3787, 3743, 4001, 4002, 4048, 4166, 4341, 4388)" );
$meets = $DB->get_records_sql("SELECT * FROM {meet} WHERE id IN (4388)" );
// $meets = $DB->get_records_sql("SELECT * FROM {meet} WHERE gmeetid = 'aap-ksrt-yhg'" );


foreach($meets as $meet){
    error_log("Buscando logs para o meet " . $meet->id . " do curso: ". $meet->course);

    /*if( $DB->record_exists('meet_logs', ['meetid' => $meet->id]) ){ 
        error_log('Skiping '.$meet->id. '. Allready Done');
        continue; 
    }*/

    $logs = Array();

    $local_log = $DB->get_records_sql("SELECT 
                                        id,
                                        objectid as meetid, 
                                        courseid, 
                                        userid, 
                                        timecreated, 0 as timeduration, 
                                        'Join Moodle' as log, 
                                        '' as meta 
                                       FROM {logstore_standard_log} 
                                       WHERE 
                                        objectid = ? AND 
                                        component = 'mod_meet' AND 
                                        action = 'joined' AND 
                                        courseid = ?", [$meet->id, $meet->course] );

    foreach($local_log as $key => $value){
        unset($local_log[$key]->id);
    }

    $local_log = array_values($local_log);

    $context = get_context_instance(CONTEXT_COURSE, $meet->course)->id;
    $users = $DB->get_records_sql('SELECT email, id FROM {user} WHERE id IN (SELECT userid FROM {role_assignments} WHERE contextid = ?)', Array($context));

    $report = meet_get_google_reports_meet(str_replace('-', '', $meet->gmeetid));
    $report_data = $report->getItems();

    foreach($report_data as $item){
        $log = Array();

        $parameters = $item->getEvents()[0]->getParameters();

        $log['meetid']   = $meet->id;
        $log['courseid'] = $meet->course;

        foreach ($parameters as $parameter) {
            if($parameter->getName() === 'display_name') {
                $log['display_name'] = $parameter->getValue();
                break;
            }
        }

        $log['email'] = $item->getActor()->getEmail();

        foreach ($parameters as $parameter) {
            if($parameter->getName() === 'duration_seconds') {
                $log['timeduration'] = $parameter->getIntValue();
                break;
            }
        }

        // Set joined at
        $datetimejoin = new DateTime($item->getId()->getTime());
        $datetimejoin->setTimezone(new DateTimeZone(core_date::get_user_timezone()));
        $datetimejoin = $datetimejoin->setTimestamp($datetimejoin->getTimestamp() - $duration);
        $log['timecreated'] = $datetimejoin->format('U');

        $log['log'] = 'Join Meet';

        if($log['email'] == ''){
            $log['meta'] = json_encode( Array('display_name' => $log['display_name'] ) );
            unset($log['email']);
        }else{
            $log['userid'] = $users[$log['email']]->id;
        }

        unset($log['display_name']);

        $logs[] = (object)$log;
    }

    $logs = array_merge($logs, $local_log);

    $course = $DB->get_record('course', ['id' => $meet->course]);
    $modinfo = get_fast_modinfo($course);
    $cm = $modinfo->get_cm( $modinfo->get_instances_of( 'meet' )[$meet->id]->id ); 
    $completion = new completion_info( $course );


    foreach($logs as $log){
        var_dump( $completion->get_data( $cm, false, $log->userid)->completionstate );
        error_log('Inserindo log ' . $log->log);
        //$DB->insert_record('meet_logs', $log);
        // var_dump($course);
    }die;

}
