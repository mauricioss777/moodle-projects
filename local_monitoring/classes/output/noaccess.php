<?php

namespace local_monitoring\output;

use renderable;
use renderer_base;
use templatable;
use stdClass;

class noaccess implements renderable, templatable {
    /** @var string $sometext Some text to show how to pass data to a template. */
    var $data = [];

    public function __construct($data) {
        $this->data['usercontext'] = $data;
        $this->data['userlist'] = $this->get_user_list($data);
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        return $this->data;
    }

    function render_noaccess($page){
        $data = $page->export_for_template($this);
        return parent::render_from_template('local_monitoring/noaccess', $data);
    }

    function get_user_list($categories){
        global $DB;

        $users_enroled = $DB->get_records_sql("
            SELECT 
              userid, 
              username, 
              firstname || ' ' || lastname as name 
            FROM 
              mdl_role_assignments mro, 
              mdl_user mus 
            WHERE 
              mus.id = mro.userid AND 
             contextid IN (
               SELECT 
                  id 
                FROM 
                  mdl_context 
                WHERE instanceid IN (
                  SELECT 
                    id 
                  FROM 
                    mdl_course 
                  WHERE 
                    category IN ($categories) ) 
                AND contextlevel = 50 ) 
              AND roleid = 5");

        $access = $DB->get_records_sql("SELECT userid, to_timestamp(timeaccess) FROM mdl_user_lastaccess WHERE courseid IN (SELECT id FROM mdl_course WHERE category IN ($categories))");
        $access_list = array_keys( $access );
        
        foreach($users_enroled as $key => $user){
            if(in_array($key, $access_list)){
                unset($users_enroled[$key]);
            }
        }

        $return = "<div><table border='1' style='width:100%;'>";
        
        foreach($users_enroled as $user){
            if(!is_numeric($user->username)){ continue; }
            $return .= "<tr>";
            $return .= "<td>". $user->username ."</td>";
            $return .= "<td>". $user->name ."</td>";
            $return .= "</tr>";
        }

        $return .= "</table></div>";
        return $return;

    }

}
