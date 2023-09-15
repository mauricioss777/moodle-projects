<?php

namespace mod_meet\task;

class revalidate_permission_task extends \core\task\scheduled_task {

    public function get_name() {
        return get_string('revalidate_permissions', 'mod_meet');
    }

    /**
     * Método que será executado pela cron
     */
    public function execute() {
        global $DB, $CFG;

        require_once($CFG->dirroot . '/mod/meet/lib.php');

        $upper_bound = time() + (60 * 60 * 4);
        $lower_bound = time() - (60 * 60 * 6);

        $meets = $DB->get_records_sql("SELECT * FROM {meet} WHERE timestart > ? AND timestart < ?", Array($lower_bound, $upper_bound) );

        foreach($meets as $meet){
            mtrace($meet->name);
            meet_refresh_users($meet->id);
        }

    }

}
