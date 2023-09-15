<?php

namespace local_monitoring\output;

use renderable;
use renderer_base;
use templatable;
use stdClass;

class index implements renderable, templatable {
    /** @var string $sometext Some text to show how to pass data to a template. */                                                  
    var $data = [];

    public function __construct($user) {
        $this->data['usercontext'] = $user->context; 
        $this->data['userid'] = $user->id;
        $this->data['name'] = "$user->firstname $user->lastname";
        $this->data['course'] = $this->get_user_course();
        $this->data['username'] = $user->username;
        $this->data['componentes'] = $this->get_courses();
        $this->data['lastacess'] = $this->get_last_access($user);
    }

    /**
     * Export this data so it can be used as the context for a mustache template.                                                   
     *                                                                                                                              
     * @return stdClass                                                                                                             
     */                                                                                                                             
    public function export_for_template(renderer_base $output) {
        return $this->data;
    }

    function render_index($page){
        $data = $page->export_for_template($this);
        return parent::render_from_template('local_monitoring/index', $data);
    }
    
    function get_last_access_corse($user, $course){
        global $DB;

        $time = $DB->get_record_sql( 'SELECT to_timestamp(timeaccess) as date, now() FROM {user_lastaccess} WHERE userid = ? AND courseid = ? ORDER BY id DESC LIMIT 1', [$user, $course] ); 
        $diff = date_diff( date_create(substr( $time->date, 0, 19) ), date_create( substr( $time->now, 0, 19) ) )->format("%a dias %h horas %i minutos");

    }
    
    function get_last_access($user){
        global $DB;

        $time = $DB->get_record_sql( "SELECT to_timestamp(lastaccess) as date, lastaccess, now() FROM {user} WHERE id = $user->id " ); 
        $diff = date_diff( date_create(substr( $time->date, 0, 19) ), date_create( substr( $time->now, 0, 19) ) )->format("%a dias %h horas %i minutos");
        return substr( $time->date, 0, 19) . "<br />". $diff ; 
    }

    function get_user_course(){
        global $DB;

        $list = $DB->get_record_sql("select * from {user_info_data} WHERE fieldid = (SELECT id FROM {user_info_field} WHERE name = 'Curso') AND userid = ?", [$this->data['userid']]);
        if($list){
            return $list->data;
        }
        return;
    }

    function get_courses(){
        global $DB;

        $list = [];
        $sql = "SELECT * FROM {course} WHERE id IN (SELECT instanceid FROM {context} WHERE id IN (select contextid from {role_assignments} WHERE userid = ? AND enddate > ?))";
        $courses = $DB->get_records_sql($sql, [$this->data['userid'], time()]);

        foreach($courses as $course){
            $list[] = [
                'id' => $course->id,
                'selected' => (isset($_GET['course']) && $course->id == $_GET['course']) ? 1 : 0,
                'fullname' => $course->fullname
            ];
        }
        return $list;
    }
}
