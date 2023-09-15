<?php

defined('MOODLE_INTERNAL') || die();

class local_monitoring_observer{

    //create table mdl_local_monitoring_history (id serial primary key, userid bigint, username varchar(255), contextid bigint,
   // courseid bigint, objectid bigint, module varchar(255), lasttime bigint);
    public static function local_monitoring_course_module_viewed(core\event\course_module_viewed $user_event_data){
        global $DB, $USER;

        //Do not log admin users
        if(!is_numeric($USER->username)){ return; }
        return;

        //Alias to content
        $event_data = $user_event_data->get_data();
        $course     = $user_event_data->get_record_snapshot('course', $event_data['courseid']); 
        $snapshot   = $user_event_data->get_record_snapshot('course_modules', $event_data['contextinstanceid']); 

        $register = $DB->get_record_sql("SELECT * FROM {local_monitoring_history} WHERE userid = ? AND contextid = ?", Array(
            $user_event_data->get_data()['userid'],
            $user_event_data->get_data()['contextinstanceid']) 
        );

        if($register){
            $register->lasttime = time();
            $DB->update_record('local_monitoring_history', $register);
        }else{
            $register = new stdClass();
            $register->userid = $user_event_data->get_data()['userid'];
            $register->username = $USER->username;
            $register->contextid = $user_event_data->get_data()['contextinstanceid'];
            $register->courseid = $user_event_data->get_data()['courseid'];
            $register->module = $user_event_data->get_data()['objecttable'];
            $register->objectid = $user_event_data->get_data()['objectid'];
            $register->lasttime = time();
            $DB->insert_record('local_monitoring_history', $register);
        }

        try{
            if($event_data['component'] == 'mod_assign'){
                $sql = "SELECT * 
                    FROM mdl_assign 
                    WHERE 
                    id = ".$event_data['objectid']." AND 
                    name ILIKE ('%Videoconferência%') AND 
                    name ILIKE ('%Ao vivo%') AND 
                    allowsubmissionsfromdate < ".( time() - (60 * 30) )." AND 
                    duedate > ". time();
                $item = $DB->get_record_sql($sql);

                if($item){
                    $record = new stdClass();
                    $record->mod = 'mod_assign';
                    $record->userid = $event_data['userid'];
                    $record->username = $DB->get_record('user', ['id' => $event_data['userid']])->username; 
                    $record->course = $event_data['courseid'];
                    $record->moduleid = $snapshot->id;
                    $record->instanceid = $snapshot->instance;
                    $record->time = time();

                    $DB->insert_record('local_monitoring_videos_instances', $record);
                }
            }
        }
        catch(Exception $e) {
            //code to print caught exception
        }

    }

    public static function local_monitoring_bbb_joined(mod_bigbluebuttonbn\event\meeting_joined $event){
        global $DB;

        //Alias to content
        $event_data = $event->get_data();
        $course     = $event->get_record_snapshot('course', $event_data['courseid']); 
        $snapshot   = $event->get_record_snapshot('course_modules', $event_data['contextinstanceid']); 


        $record = new stdClass();
        $record->mod = 'mod_bigbluebuttonbn';
        $record->userid = $event_data['userid'];
        $record->username = $DB->get_record('user', ['id' => $event_data['userid']])->username; 
        $record->course = $event_data['courseid'];
        $record->moduleid = $snapshot->id;
        $record->instanceid = $snapshot->instance;
        $record->time = time();


        $DB->insert_record('local_monitoring_videos_instances', $record);

    }

    public static function local_monitoring_meet_joined(mod_meet\event\meeting_joined $event){
        global $DB;

        //Alias to content
        $event_data = $event->get_data();
        $course     = $event->get_record_snapshot('course', $event_data['courseid']); 
        $snapshot   = $event->get_record_snapshot('course_modules', $event_data['contextinstanceid']); 
        
        $record = new stdClass();
        $record->mod = 'mod_meet';
        $record->userid = $event_data['userid'];
        $record->username = $DB->get_record('user', ['id' => $event_data['userid']])->username; 
        $record->course = $event_data['courseid'];
        $record->moduleid = $snapshot->id;
        $record->instanceid = $snapshot->instance;
        $record->time = time();

        $DB->insert_record('local_monitoring_videos_instances', $record);
    }

}
