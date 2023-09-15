<?php


namespace local_monitoring\task;

class delete_legate_fat_task extends \core\task\scheduled_task {

    /**
     * Busca o nome descritivo para este agendamento (visível para admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('deletelegatefat', 'local_monitoring');
    }

    /**
     * Método que será executado pela cron
     */
    public function execute() {
        global $DB,$CFG;
        require_once($CFG->dirroot.'/config.php');
        require_once($CFG->dirroot.'/user/lib.php');

        mtrace("=====================================================");
        mtrace("Starting synchronization of courses with Alfa");
        mtrace("WARNING: This task works just with mysql or postgres");
        mtrace("=====================================================");
        flush();

        mtrace("Finished synchronization");
        mtrace("=============================================");
    }
}
