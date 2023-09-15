<?php

namespace local_monitoring\output;

use renderable;
use renderer_base;
use templatable;
use stdClass;

class tests implements renderable, templatable {
    /** @var string $sometext Some text to show how to pass data to a template. */
    var $data = [];

    public function __construct($data) {
        $this->data['usercontext'] = $data;
        $this->data['courses'] = $this->get_course_list($data);
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        return $this->data;
    }

    function render_tests($page){
        $data = $page->export_for_template($this);
        return parent::render_from_template('local_monitoring/tests', $data);
    }

    function get_course_list($categories){
        global $DB;

        $sql = " SELECT id, courseid, iteminstance FROM mdl_grade_items WHERE categoryid IN (SELECT id FROM mdl_grade_categories WHERE courseid IN (SELECT id FROM mdl_course WHERE category IN ($categories) ) AND fullname = 'Nota 3') AND itemmodule = 'quiz' ORDER BY courseid;";
        $test_list =  $DB->get_records_sql( $sql );

        $courses = [];
        $output = "";

        foreach($test_list as $test){
            if( !isset( $courses[ $test->courseid ] ) ){
                $courses[$test->courseid] = [
                    'course' => $test->courseid,
                    'coursename' => $DB->get_record('course', ['id' => $test->courseid])->fullname,
                    'instances' => [],
                    'users' => [] ];
            }
            $courses[ $test->courseid ]['instances'][] = $test->iteminstance;
            $courses[ $test->courseid ]['users'] = array_keys($DB->get_records_sql('select userid from mdl_role_assignments WHERE contextid = (SELECT id FROM mdl_context WHERE instanceid = '.$test->courseid.' AND contextlevel = 50) AND roleid = 5'));
        }

        foreach($courses as $c){
            $output .= "<h3>".$c['coursename']."</h3>";

            $c['instances'] = rtrim( implode(', ', $c['instances']), ', ');
            $sql = "SELECT distinct(userid) FROM {quiz_attempts} WHERE quiz IN (".$c['instances'].") ";
            $attempts = $DB->get_records_sql($sql);
            $output .= "<h4>".sizeof($c['users'])." estudantes";
            $c['users'] = array_diff( $c['users'], array_keys($attempts) );
            $output .= "/".sizeof($c['users'])." Faltantes</h4>";
            if( empty($c['users']) ){ continue; }

            $sql = "SELECT * FROM mdl_user WHERE id IN (".rtrim( implode(', ', $c['users']), ', ').")";
            $users = $DB->get_records_sql($sql); 

            foreach($users as $user){
                $output .= "<p> ".$user->username.' - '.$user->firstname.' '.$user->lastname." </p>";
            }

        }

        return $output;

    }

}
