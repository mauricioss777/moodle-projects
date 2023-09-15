<?php

defined('MOODLE_INTERNAL') || die();

class local_inscricoes_observer{

    public static function local_inscricoes_course_deleted(core\event\course_deleted $event)
    {
        global $DB;

        // Get data from event
        $data = $event->get_data();

        // Get all local_alfa_tcc links to this course
        $query = "SELECT * FROM {local_inscricoes} WHERE courseid = ?";
        $results = $DB->get_records_sql($query, array(
            $data['courseid'],
        ));

        // Run through every record
        foreach ($results as $result) {
            // Delete from local_alfa_tcc
            $DB->delete_records('local_inscricoes', array(
                'id' => $result->id,
            ));

        }
    }

}
