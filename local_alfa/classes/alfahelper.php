<?php

require_once('alfa.class.php');
require_once($CFG->dirroot . '/user/lib.php');

class tcc_helper{
    //Info related to the course
    private $roles;
    private $context;
    private $course;
    private $course_name;

    //Info related to users
    private $users = Array();
    private $teachers = Array();
    private $other = Array();
    private $students = Array();
    private $groups = Array();
    private $sem_orientacao = Array();

    function __construct(){
        global $DB;
        $this->roles = $DB->get_records_sql("SELECT shortname, id FROM {role} WHERE shortname IN ('student','editingteacher')");
    }

    function load_context($course){
        global $DB;
        $this->course = $course;
        $this->context = $DB->get_record_sql("SELECT id FROM {context} where contextlevel = 50 AND instanceid = ?", Array($course));
    }

    function get_teachers(){
        return $this->teachers;
    }

    function set_name($name){
        $this->course_name = $name;
    }

    function add_restrictions(){
        global $DB;
        $restrictions = Array();
        $restrictions[] ='moodle/course:managegroups';
        $restrictions[] ='enrol/manual:unenrolself';
        $restrictions[] ='enrol/manual:unenrol';
        // $restrictions[] ='enrol/manual:enrol';
        $restrictions[] ='enrol/manual:config';
        $restrictions[] ='enrol/ldap:manage';

        $rest = new stdClass();
        $rest->contextid = $this->context->id;
        $rest->roleid = $this->roles['editingteacher']->id;
        $rest->permission = '-1000';
        $rest->timemodified = time();
        $rest->modifierid = '2';

        foreach ($restrictions as $restriction){
            $rest->capability = $restriction;
            $DB->insert_record('role_capabilities', $rest);
        }

        $rest->capability = $restrictions[0];
        $rest->roleid = $this->roles['student']->id;
        $DB->insert_record('role_capabilities', $rest);
    }

    function load_users($groups){
        global $DB;
        $usercodes = Array();

        $placeholder = '';
        foreach ($groups as $key => $group){
            if($key == 'sem_orientacao'){
                $this->sem_orientacao = $group;
            }
            foreach ($group as $user){
                $usercodes[] = $user['username'];
                $placeholder .= '?, ';
                if($user['roleid'] == 8){
                    $this->teachers[] = $user['username'];
                }
                if($user['roleid'] == 5){
                    $this->students[] = $user['username'];
                }
            }
        }
        $placeholder = rtrim($placeholder, ', ');
        $usrs = Array();
        if($placeholder){
        $usrs = $DB->get_records_sql("SELECT username, id, firstname || ' ' || lastname as nome FROM {user} WHERE username IN ($placeholder)", $usercodes);
        }
        foreach ($usrs as $usr){
            $this->users[$usr->username] = $usr;
        }
    }

    function load_other($list){
        global $DB;
        foreach ($list as $key => $item){
            $id = $DB->get_record("user", Array("username"=>$item['username']));
            $this->other[$key] = $id->id;
        }
    }

    function get_user_id($username){
        return $this->users[$username]->id;
    }

    function get_teachers_as_id(){
        $teachers = Array();

        foreach ($this->teachers as $teacher){
            $teachers[] = $this->get_user_id($teacher);
        }

        foreach ($this->other as $user){
            $teachers[] = $user;
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

    function build_header(){
        global $DB;

        $section = $DB->get_record('course_sections', Array('course'=>$this->course, 'section' => 0));

        $label = new stdClass();
        $label->intro = '<h1 style="text-align: center;"><span style="color: #000066; background-color: #ffffff;">'.$this->course_name.'</span></h1>';
        $label->name = substr($label->intro, 0, 50);
        $label->course = $this->course;
        $label->timemodified = time();
        $label->introformat = 1;

        $label->id = $DB->insert_record('label', $label, true);
        $module = $DB->get_record('modules', Array('name'=>'label'));

        $course_modules = new stdClass();
        $course_modules->course = $this->course;
        $course_modules->module = $module->id;
        $course_modules->instance = $label->id;
        $course_modules->section = $section->id;
        $course_modules->added = time();

        $course_modules->id = $DB->insert_record('course_modules', $course_modules, true);
        $section->sequence = $course_modules->id . ',' . $section->sequence;
        $DB->update_record('course_sections', $section);

        $module = $DB->get_record('modules', Array('name'=>'url'));

        $url = $DB->get_record_sql("SELECT * FROM {url} WHERE course = (SELECT id from {course} WHERE shortname = 'TmplTCC') AND name like('%Manual%')");
        unset($url->id);

        $url->course = $this->course;
        $url->id = $DB->insert_record('url', $url);

        $course_modules = new stdClass();
        $course_modules->course = $this->course;
        $course_modules->module = $module->id;
        $course_modules->instance = $url->id;
        $course_modules->section = $section->id;
        $course_modules->added = time();

        $course_modules->id = $DB->insert_record('course_modules', $course_modules, true);

        $section->sequence .= ',' . $course_modules->id;
        $DB->update_record('course_sections', $section);

        $module = $DB->get_record('modules', Array('name'=>'assign'));
        $assigns = $DB->get_records_sql("SELECT * FROM {assign} WHERE course = (SELECT id from {course} WHERE shortname = 'TmplTCC')");

        foreach ($assigns as $assign){
            unset($assign->id);
            $assign->course = $this->course;
            $assign->id = $DB->insert_record('assign', $assign);

            $course_modules = new stdClass();
            $course_modules->course = $this->course;
            $course_modules->visible = 0;
            $course_modules->module = $module->id;
            $course_modules->instance = $assign->id;
            $course_modules->section = $section->id;
            $course_modules->added = time();

            $course_modules->id = $DB->insert_record('course_modules', $course_modules, true);

            $section->sequence .= ',' . $course_modules->id;
            $DB->update_record('course_sections', $section);
        }

        $module = $DB->get_record('modules', Array('name'=>'label'));
        $base_label = $DB->get_record_sql("SELECT * FROM {label} WHERE id IN (SELECT instance FROM mdl_course_modules WHERE idnumber = 'TccCabecalho')");
        $label = new stdClass();
        $label->intro = $base_label->intro;
        $label->name = $base_label->name;
        $label->course = $this->course;
        $label->timemodified = time();
        $label->introformat = 1;

        $label->id = $DB->insert_record('label', $label, true);
        $module = $DB->get_record('modules', Array('name'=>'label'));

        $course_modules = new stdClass();
        $course_modules->course = $this->course;
        $course_modules->module = $module->id;
        $course_modules->instance = $label->id;
        $course_modules->section = $section->id;
        $course_modules->added = time();

        $course_modules->id = $DB->insert_record('course_modules', $course_modules, true);
        $section->sequence .= ',' . $course_modules->id;
        $DB->update_record('course_sections', $section);

    }

    function manage_users(){
        global $DB;

        $manualinstance = $DB->get_record('enrol', array(
            'courseid' => $this->course,
            'enrol'    => 'manual',
        ), '*', MUST_EXIST);

        $manual = enrol_get_plugin('manual');

        foreach ($this->students as $student){
            $manual->enrol_user( $manualinstance, $this->get_user_id($student), $this->roles['student']->id );
        }

        foreach ($this->teachers as $teacher){
            $manual->enrol_user( $manualinstance, $this->get_user_id($teacher), $this->roles['editingteacher']->id );
        }
    }

    function manage_other_users($users){
        global $DB, $USER;
        $use = $USER->id;
        $USER->id = 0;
        $manualinstance = $DB->get_record('enrol', array(
            'courseid' => $this->course,
            'enrol'    => 'manual',
        ), '*', MUST_EXIST);

        $manual = enrol_get_plugin('manual');

        foreach($users as $user){
            $this->other[] = $this->get_user_id( $user['username'] );
            $manual->enrol_user( $manualinstance, $this->get_user_id($user['username']), $this->roles['editingteacher']->id );
        }
        $USER->id = $use;
    }

    function manage_group($grouplist, $force = false, $createdGroup = false){

        global $DB, $CFG;

        require_once($CFG->dirroot.'/course/lib.php');

        if($grouplist[0]['roleid'] == $this->roles['student']->id){
            return;
        }

        $teacher = $this->users[$grouplist[0]['username']];

        if($DB->record_exists('groups', Array('owner'=>$teacher->id, 'courseid'=>$this->course)) && !$force){
            return;
        }

        //Create the group for this grouplist
        
        $new_group = new stdClass();
        if(!$createdGroup){
            $new_group->courseid = $this->course;
            $new_group->name = "Professor(a): ".$teacher->nome;
            $new_group->owner = $teacher->id;
            $new_group->timecreated = time();
            $new_group->timemodified = time();
            $new_group->id = $DB->insert_record('groups', $new_group, true);
        }else{
            $new_group = $createdGroup;
        }

        //Insert the people on the group and create the label
        $label_content = '<ul>';
        foreach ($grouplist as $user){
            $member = new stdClass();
            $member->groupid = $new_group->id;
            $member->userid = $this->get_user_id($user['username']);
            if($user['roleid'] == 5){
                $label_content .= '<li>' . $this->users[$user['username']]->nome .'</li>';
            }
            $member->timeadded = time();
            $DB->insert_record('groups_members', $member);
        }
        $label_content .= '</ul>';

        //Create the section for the group and add the restriction
        $group_restriction = '{"op":"&","c":[{"type":"group","id":'.$new_group->id.'}],"showc":[false]}';
        $section = course_create_section($this->course, null);
        $section->availability = $group_restriction;
        $section->name = "Professor(a): ".$teacher->nome;
        $DB->update_record('course_sections', $section);

        try{
            //Add the created label on the section
            $module = $DB->get_record('modules', Array('name'=>'label'));
            $label = new stdClass();
            $label->intro = $this->clear_chars($label_content);
            $label->name = substr($label->intro, 8, 50);
            $label->course = $this->course;
            $label->timemodified = time();
            $label->introformat = 1;
            $label->id = $DB->insert_record('label', $label, true);        
        } catch (\Exception $exception) {
            //#TOOD -> Warn fail
            return;
        }
        //Add the created label on the course module table
        $course_modules = new stdClass();
        $course_modules->course = $this->course;
        $course_modules->module = $module->id;
        $course_modules->instance = $label->id;
        $course_modules->section = $section->id;
        $course_modules->added = time();
        $course_modules->id = $DB->insert_record('course_modules', $course_modules, true);
        $section->sequence .= ','.$course_modules->id;
        $DB->update_record('course_sections', $section);

        //Clone the necessary databases
        $module = $DB->get_record('modules', Array('name'=>'data'));
        $databases = $DB->get_records_sql("SELECT * FROM {data} WHERE course = (SELECT id from {course} WHERE shortname = 'TmplTCC')");

        foreach ($databases as $database){
            $base_data = $database->id;

            unset($database->id);

            $database->course = $this->course;
            $database->id = $DB->insert_record('data', $database, true);

            $course_modules = new stdClass();
            $course_modules->course = $this->course;
            $course_modules->module = $module->id;
            $course_modules->instance = $database->id;
            $course_modules->section = $section->id;
            $course_modules->added = time();
            $course_modules->id = $DB->insert_record('course_modules', $course_modules, true);
            $section->sequence .= ','.$course_modules->id;
            $DB->update_record('course_sections', $section);

            //Copy the fields
            $fields = $DB->get_records('data_fields', Array('dataid'=>$base_data));
            foreach ($fields as $field){
                unset($field->id);
                $field->dataid = $database->id;
                $DB->insert_record('data_fields', $field);
            }
        }

        //Clone the necessary assigns
        $module = $DB->get_record('modules', Array('name'=>'assign'));
        $assign = $DB->get_record_sql("SELECT * FROM {assign} WHERE course = (SELECT id from {course} WHERE shortname = 'TmplTCC') AND name LIKE('%estágio/TCC%')");

        unset($assign->id);
        $assign->course = $this->course;
        $assign->id = $DB->insert_record('assign', $assign);

        $course_modules = new stdClass();
        $course_modules->course = $this->course;
        $course_modules->module = $module->id;
        $course_modules->instance = $assign->id;
        $course_modules->section = $section->id;
        $course_modules->added = time();
        $course_modules->id = $DB->insert_record('course_modules', $course_modules, true);
        $section->sequence .= ','.$course_modules->id;
        $DB->update_record('course_sections', $section);

    }

    function remanage_group($grouplist){
        global $DB;

        if($grouplist[0]['roleid'] == $this->roles['student']->id){
            return;
        }

        $group = $DB->get_record('groups', Array('courseid' => $this->course, 'owner' => $this->get_user_id( $grouplist[0]['username'] ) ) );

        if(!$group){
            $this->manage_group($grouplist);
            return;
        }

        $members = $DB->get_records('groups_members', Array('groupid' => $group->id));

        $group_copy = Array();

        foreach ($members as $member){
            $group_copy[] = $member->id;
        }

        foreach ($grouplist as $gp){
            $group_copy[] = $this->get_user_id( $gp['username'] );
        }

        $group_copy = array_unique($group_copy);

        //Insert the people on the group and create the label
        $label_content = '<ul>';
        foreach ($grouplist as $member){
            groups_add_member($group->id, $this->get_user_id( $member['username']) );
            if($member['roleid'] == 5){
                $label_content .= '<li>' . $this->users[$member['username']]->nome .'</li>';
            }
            unset($group_copy[array_search($this->get_user_id( $member['username']) ,$group_copy)]);
        }

        $label_content .= '</ul>';

        cache_helper::invalidate_by_definition('core', 'groupdata', array(), array($this->course));
        cache_helper::purge_by_definition('core', 'user_group_groupings');

        foreach ($group_copy as $gp){
            groups_remove_member($group->id, $gp);
        }

        $module = $DB->get_record('modules', Array('name'=>'label'));
        $section = $DB->get_record_sql("SELECT * FROM {course_sections} WHERE course = ? AND availability like ('%".$group->id."%')", Array($this->course));
        if(!$section){
            $this->manage_group($grouplist, $force, $group);
            $section = $DB->get_record_sql("SELECT * FROM {course_sections} WHERE course = ? AND availability like ('%".$group->id."%')", Array($this->course));
        }
        $section->visible = 1;

        $DB->update_record('course_sections', $section);

        $label = $DB->get_record_sql("SELECT * FROM {label} WHERE id = 
                                    ( SELECT instance FROM {course_modules} WHERE course = ? AND module = ? AND section = ? ORDER BY id DESC LIMIT 1)",
                                      Array($this->course, $module->id, $section->id));

        //If Shit happens, charcode problem
        if(!$label){
            //Add the created label on the section
            $module = $DB->get_record('modules', Array('name'=>'label'));
            $label = new stdClass();
            $label->intro = $this->clear_chars( $label_content );
            $label->name = substr($label->intro, 0, 50);
            $label->course = $this->course;
            $label->timemodified = time();
            $label->introformat = 1;

            //Add the created label on the course module table
            $label->id = $DB->insert_record('label', $label, true);
            $course_modules = new stdClass();
            $course_modules->course = $this->course;
            $course_modules->module = $module->id;
            $course_modules->instance = $label->id;
            $course_modules->section = $section->id;
            $course_modules->added = time();
            $course_modules->id = $DB->insert_record('course_modules', $course_modules, true);
            $section->sequence .= ','.$course_modules->id;
            $DB->update_record('course_sections', $section);

            //Clone the necessary databases
            $module = $DB->get_record('modules', Array('name'=>'data'));
            $databases = $DB->get_records_sql("SELECT * FROM {data} WHERE course = (SELECT id from {course} WHERE shortname = 'TmplTCC')");

            foreach ($databases as $database){
                $base_data = $database->id;

                unset($database->id);

                $database->course = $this->course;
                $database->id = $DB->insert_record('data', $database, true);

                $course_modules = new stdClass();
                $course_modules->course = $this->course;
                $course_modules->module = $module->id;
                $course_modules->instance = $database->id;
                $course_modules->section = $section->id;
                $course_modules->added = time();
                $course_modules->id = $DB->insert_record('course_modules', $course_modules, true);
                $section->sequence .= ','.$course_modules->id;
                $DB->update_record('course_sections', $section);

                //Copy the fields
                $fields = $DB->get_records('data_fields', Array('dataid'=>$base_data));
                foreach ($fields as $field){
                    unset($field->id);
                    $field->dataid = $database->id;
                    $DB->insert_record('data_fields', $field);
                }
            }

            //Clone the necessary assigns
            $module = $DB->get_record('modules', Array('name'=>'assign'));
            $assign = $DB->get_record_sql("SELECT * FROM {assign} WHERE course = (SELECT id from {course} WHERE shortname = 'TmplTCC') AND name LIKE('%estágio/TCC%')");

            unset($assign->id);
            $assign->course = $this->course;
            $assign->id = $DB->insert_record('assign', $assign);

            $course_modules = new stdClass();
            $course_modules->course = $this->course;
            $course_modules->module = $module->id;
            $course_modules->instance = $assign->id;
            $course_modules->section = $section->id;
            $course_modules->added = time();
            $course_modules->id = $DB->insert_record('course_modules', $course_modules, true);
            $section->sequence .= ','.$course_modules->id;
            $DB->update_record('course_sections', $section);

        }else{
            $label->intro = $label_content;
            $DB->update_record('label', $label);
        }
    }

    function clear_students(){
        global $DB;

        $student_str = "";

        foreach ($this->get_students_as_id() as $tid){
            $student_str .= $tid.", ";
        }

        $student_str = rtrim($student_str, ', ');
        if(!$student_str){
            return;
        }
        $students_to_remove = $DB->get_records_sql("SELECT * 
                                                         FROM {role_assignments} 
                                                         WHERE contextid = ".$this->context->id." AND 
                                                         roleid = 5 AND
                                                         modifierid = 2 AND  
                                                         userid NOT IN ($student_str)");

        $manualinstance = $DB->get_record('enrol', array(
            'courseid' => $this->course,
            'enrol'    => 'manual',
        ), '*', MUST_EXIST);
        $manual = enrol_get_plugin('manual');

        foreach ($students_to_remove as $student){
            $manual->unenrol_user($manualinstance, $student->userid);
        }

    }

    function clear_teachers(){
        global $DB;

        $teacher_str = "";
        foreach ($this->get_teachers_as_id() as $tid){
            $teacher_str .= $tid.", ";
        }

        $teacher_str = rtrim($teacher_str, ', ');
        if(!$teacher_str){
            return;
        }
        $teachers_to_remove = $DB->get_records_sql("SELECT * 
                                                         FROM {role_assignments} 
                                                        WHERE contextid = ".$this->context->id." AND 
                                                        roleid = 8 AND 
                                                        modifierid = 2 AND 
                                                        userid NOT IN ($teacher_str)");

        $manualinstance = $DB->get_record('enrol', array(
            'courseid' => $this->course,
            'enrol'    => 'manual',
        ), '*', MUST_EXIST);
        $manual = enrol_get_plugin('manual');

        foreach ($teachers_to_remove as $teacher){
            $group = $DB->get_record_sql("SELECT * FROM {groups} WHERE courseid = ? AND owner = ?", Array($this->course, $teacher->userid));
            $section = $DB->get_record_sql("SELECT * FROM {course_sections} WHERE course = ? AND availability like ('%$group->id%')", Array($this->course));
            $section->visible = 1;

            $module = $DB->get_record('modules', Array('name'=>'label'));
            $label = $DB->get_record_sql("SELECT * FROM {label} WHERE id = 
                                    ( SELECT instance FROM {course_modules} WHERE course = ? AND module = ? AND section = ?)",
                Array($this->course, $module->id, $section->id));

            //Clear the label format
            $label->intro = ( "<ul> <li></li> </ul>" );
            $DB->update_record('label', $label);

            //To be in the safeside
            $DB->execute("DELETE FROM {groups_members} WHERE groupid = ?", Array($group->id, $teacher->userid));

            $DB->update_record('course_sections', $section);
            $manual->unenrol_user($manualinstance, $teacher->userid);
        }

    }

    function clear_chars($string = ""){
        $chars['\\xc3\\x80'] = 'À';
        $chars['\\xc3\\x81'] = 'Á';
        $chars['\\xc3\\x82'] = 'Â';
        $chars['\\xc3\\x83'] = 'Ã';
        $chars['\\xc3\\x84'] = 'Ä';
        $chars['\\xc3\\x85'] = 'Å';
        $chars['\\xc3\\x86'] = 'Æ';
        $chars['\\xc3\\x87'] = 'Ç';
        $chars['\\xc3\\x88'] = 'È';
        $chars['\\xc3\\x89'] = 'É';
        $chars['\\xc3\\x8a'] = 'Ê';
        $chars['\\xc3\\x8b'] = 'Ë';
        $chars['\\xc3\\x8c'] = 'Ì';
        $chars['\\xc3\\x8d'] = 'Í';
        $chars['\\xc3\\x8e'] = 'Î';
        $chars['\\xc3\\x8f'] = 'Ï';
        $chars['\\xc3\\x90'] = 'Ð';
        $chars['\\xc3\\x91'] = 'Ñ';
        $chars['\\xc3\\x92'] = 'Ò';
        $chars['\\xc3\\x93'] = 'Ó';
        $chars['\\xc3\\x94'] = 'Ô';
        $chars['\\xc3\\x95'] = 'Õ';
        $chars['\\xc3\\x96'] = 'Ö';
        $chars['\\xc3\\x97'] = '×';
        $chars['\\xc3\\x98'] = 'Ø';
        $chars['\\xc3\\x99'] = 'Ù';
        $chars['\\xc3\\x9a'] = 'Ú';
        $chars['\\xc3\\x9b'] = 'Û';
        $chars['\\xc3\\x9c'] = 'Ü';
        $chars['\\xc3\\x9d'] = 'Ý';
        $chars['\\xc3\\x9e'] = 'Þ';
        $chars['\\xc3\\x9f'] = 'ß';
        $chars['\\xc3\\xa0'] = 'à';
        $chars['\\xc3\\xa1'] = 'á';
        $chars['\\xc3\\xa2'] = 'â';
        $chars['\\xc3\\xa3'] = 'ã';
        $chars['\\xc3\\xa4'] = 'ä';
        $chars['\\xc3\\xa5'] = 'å';
        $chars['\\xc3\\xa6'] = 'æ';
        $chars['\\xc3\\xa7'] = 'ç';
        $chars['\\xc3\\xa8'] = 'è';
        $chars['\\xc3\\xa9'] = 'é';
        $chars['\\xc3\\xaa'] = 'ê';
        $chars['\\xc3\\xab'] = 'ë';
        $chars['\\xc3\\xac'] = 'ì';
        $chars['\\xc3\\xad'] = 'í';
        $chars['\\xc3\\xae'] = 'î';
        $chars['\\xc3\\xaf'] = 'ï';
        $chars['\\xc3\\xb0'] = 'ð';
        $chars['\\xc3\\xb1'] = 'ñ';
        $chars['\\xc3\\xb2'] = 'ò';
        $chars['\\xc3\\xb3'] = 'ó';
        $chars['\\xc3\\xb4'] = 'ô';
        $chars['\\xc3\\xb5'] = 'õ';
        $chars['\\xc3\\xb6'] = 'ö';
        $chars['\\xc3\\xb7'] = '÷';
        $chars['\\xc3\\xb8'] = 'ø';
        $chars['\\xc3\\xb9'] = 'ù';
        $chars['\\xc3\\xba'] = 'ú';
        $chars['\\xc3\\xbb'] = 'û';
        $chars['\\xc3\\xbc'] = 'ü';
        $chars['\\xc3\\xbd'] = 'ý';
        $chars['\\xc3\\xbe'] = 'þ';
        $chars['\\xc3\\xbf'] = 'ÿ';

        foreach ($chars as $key => $char){
            $string = str_replace($key, $char, $string);
        }
        return $string;
    }

    function clear_caches(){
        cache_helper::purge_by_definition('core', 'user_group_groupings');
        rebuild_course_cache($this->course, true);
    }
}
