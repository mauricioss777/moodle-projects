<?php

class event_helper{

    //Info related to the course
    private $roles;
    private $context;
    private $course;

    //Info related to users
    private $teachers = Array();
    private $student  = 0;

    function __construct($course){
        global $DB;
        $this->roles = $DB->get_records_sql("SELECT shortname, id FROM {role} WHERE shortname IN ('student','editingteacher')");
        $this->course = $course;
        $this->context = $DB->get_record_sql("SELECT id FROM {context} where contextlevel = 50 AND instanceid = ?", Array($course));   
    }

    function get_user_id($username){
        global $DB;
        return $DB->get_record('user', Array('username' => $username))->id;
    }

    function add_teachers($users){
        foreach ($users as $user){
            $this->teachers[] = $user['username'];
        }
        $this->teachers = array_unique($this->teachers);
    }

    function set_student($user){
        global $DB, $CFG;

        require_once($CFG->dirroot . '/user/lib.php');
        include_once $CFG->dirroot.'/local/alfa/classes/alfa.class.php';

        if( !$DB->record_exists('user', Array('username'=>$user)) ){
            $new_user = Alfa::getUserInformation( $user );
            //$new_user->auth = 'manual';
            $userId = user_create_user( $new_user );
            //$user = $DB->get_record('user', ['id'=>$userId]);
            //$user->password = md5($user->email);
            //$DB->update_record('user', $user);
            //$this->add_students( [(array)$DB->get_record('user', Array('id'=>$userId))] );
        }

        $this->student = $user;

    }

    function manage_users(){
        global $DB;

        $manualinstance = $DB->get_record('enrol', array(
            'courseid' => $this->course,
            'enrol'    => 'manual',
        ), '*', MUST_EXIST);

        $manual = enrol_get_plugin('manual');

        $manual->enrol_user( $manualinstance, $this->get_user_id($this->student), $this->roles['student']->id );

        foreach ($this->teachers as $teacher){
            $manual->enrol_user( $manualinstance, $this->get_user_id($teacher), $this->roles['editingteacher']->id );
        }
    }

    function get_teachers_as_id(){
        $teachers = Array();

        foreach ($this->teachers as $teacher){
            $teachers[] = $this->get_user_id($teacher);
        }

        return $teachers;
    }

    function get_students_as_id(){
        $students = Array();

        foreach ($this->students as $student){
            $students[] = $this->get_user_id($student);
        }

        return $students;
    }

    function manage_activities($event_info){
        global $DB, $CFG;
        require_once($CFG->dirroot . '/group/lib.php');

        $group = $DB->get_record('groups', Array("idnumber"=>$event_info['activityid']));

        if( !$group ){
            //Create the group for this grouplist
            $group = new stdClass();
            $group->courseid = $this->course;
            $group->idnumber = $event_info['activityid'];
            $group->name = utf8_decode($event_info['activityname']);
            if($event_info['activityfree'] == '1'){
                $group->enrolmentkey = 'evento-'.$event_info['activityid'];
            }else{ $group->enrolmentkey = ''; }
            $group->timecreated = time();
            $group->timemodified = time();
            $group->id = $DB->insert_record('groups', $group, true);
        } else {
            $group->idnumber = $event_info['activityid'];
            $group->name = utf8_decode($event_info['activityname']);
            if($event_info['activityfree'] == '1'){
                $group->enrolmentkey = 'evento-'.$event_info['activityid'];
            }else{ $group->enrolmentkey = ''; }
            $group->timecreated = time();
            $group->timemodified = time();
            $DB->update_record('groups', $group, true);
        }

        $section_name = utf8_decode("Atividade: " . $event_info['activityname']);

        $section = $DB->get_record_sql("SELECT * 
                                        FROM {course_sections} 
                                        WHERE course = ? AND 
                                        name = '$section_name'", Array($this->course));

        if(!$section){
            //Create the section for the group and add the restriction
            $group_restriction = '{"op":"&","c":[{"type":"group","id":'.$group->id.'}],"showc":[false]}';
            $section = course_create_section($this->course, null);
            $section->availability = $group_restriction;
            $section->name = utf8_decode("Atividade: " . $event_info['activityname']);
            $DB->update_record('course_sections', $section);
        }
        
        foreach($event_info['students'] as $user){
            try{
                if(!$DB->record_exists('groups_members', Array( 'userid' => $this->get_user_id( $user['username'] ), 
                                                                'groupid' => $group->id))){
                    $group_member = new stdClass();
                    $group_member->userid = $this->get_user_id($user['username']);
                    $group_member->groupid = $group->id;
                    $DB->insert_record('groups_members', $group_member);
                }
            }catch (Exception $e) {
            }
        }

        /*$members_ = array_merge( $event_info['teacher'], $event_info['students'] );
        $members = Array();
        $holder = '';
        try{
        foreach ($members_ as $member){
            $holder .= '?, ';
            if(!$DB->record_exists('groups_members', Array('userid' => $this->get_user_id($member['username']), 'groupid' => $group->id))){
                $group_member = new stdClass();
                $group_member->userid = $this->get_user_id($member['username']);
                $group_member->groupid = $group->id;
                $DB->insert_record('groups_members', $group_member);
            }
            $members[] = $this->get_user_id($member['username']);
        }
        $holder = rtrim($holder, ', ');
        //$DB->execute("DELETE from {groups_members} WHERE userid NOT IN ($holder) AND groupid = $group->id", $members);
        } catch (Exception $e) {
            if($member['username'] == '527813'){ error_log("Bugou na insersão de grupos"); } 
        }*/

        $module = $DB->get_record('modules', Array('name'=>'url'));

        foreach ($event_info['schedules'] as $schedule){
            if($schedule['scheduleid'] == 102301){ continue; }
            $course_modules = $DB->get_record('course_modules', Array('idnumber'=>'schedule-'.$schedule['scheduleid']));

            if(!$course_modules){
                $url = $DB->get_record_sql("SELECT * FROM {url} WHERE course = (SELECT id from {course} WHERE shortname = 'TmplEvt') AND name like('%Link%')");
                unset($url->id);

                $url->course = $this->course;
                $url->name = utf8_decode($schedule['schedulename'] . ' - ' . $schedule['initime']);
                $url->intro = utf8_decode($schedule['schedulename'] . ' - ' . $schedule['initime']);
                //$url->externalurl = 'https://example.com';
                $url->id = $DB->insert_record('url', $url);

                $timeopen = DateTime::createFromFormat('Y-m-d H:i', $schedule['initime'])->format('U');
                $avaiability = '{"op":"&","c":[{"type":"date","d":">=","t":'.($timeopen-1800).'}],"showc":[true]}';

                $course_modules = new stdClass();
                $course_modules->course = $this->course;
                $course_modules->module = $module->id;
                $course_modules->idnumber = 'schedule-' . $schedule['scheduleid'];
                $course_modules->instance = $url->id;
                $course_modules->section = $section->id;
                $course_modules->availability = $avaiability;
                $course_modules->added = time();
                try{
                    $course_modules->id = $DB->insert_record('course_modules', $course_modules, true);
                    $section->sequence .= ',' . $course_modules->id;
                    $DB->update_record('course_sections', $section);
                } catch (Exception $e) {
                }
            }
        }

        cache_helper::purge_by_definition('core', 'user_group_groupings');
        rebuild_course_cache($this->course, true);
         
    }

    function remove_activities($my_activities, $user){
        /*global $DB;
        $user = $this->get_user_id($user);
        if($my_activities){
            $my_activities = implode("', '", $my_activities);
            $not_in = $DB->get_records_sql("SELECT id FROM {groups} WHERE idnumber NOT IN ('$my_activities') AND courseid = $this->course");
            foreach ($not_in as $not){
                $DB->execute("DELETE FROM {groups_members} WHERE groupid = $not->id AND userid = $user");
            }
        }*/
        cache_helper::purge_by_definition('core', 'user_group_groupings');
        rebuild_course_cache($this->course, true);
    }

}
