<?php

namespace local_inscricoes\task;

class sync_presences_task extends \core\task\scheduled_task {

    public function get_name() {
        return get_string('syncpresencetask', 'local_inscricoes');
    }

    public function execute() {
        global $DB,$CFG;

        require_once(__DIR__.'/../inscricoes.class.php');

        $schedules = $DB->get_records_sql("
                                SELECT mcm.idnumber as schedule, mli.idnumber as eventid, mli.courseid, mcm.instance, mmod.name 
                                    FROM mdl_course_modules mcm, mdl_local_inscricoes mli, mdl_modules mmod
                                WHERE course IN (
                                    select id from mdl_course mcu WHERE mcu.id IN (
                                        SELECT courseid FROM mdl_local_inscricoes WHERE sync = 0) AND mcu.enddate < ".time().") 
                                    AND mli.courseid = mcm.course AND mli.idnumber != '' AND mcm.idnumber != '' AND mmod.id = mcm.module
                              ");

        foreach ($schedules as $schedule){
            $schedule->schedule = str_replace('schedule-', '', $schedule->schedule);
            
            $users = $DB->get_records_sql("
                                    SELECT mus.username, count(mus.username)
                                     FROM 
                                      {logstore_standard_log} mlsl, 
                                      {user} mus 
                                     WHERE 
                                      mlsl.courseid = ? AND 
                                      mlsl.userid = mus.id AND 
                                      mlsl.objecttable = ? AND 
                                      mlsl.objectid = ? GROUP BY mus.username", Array($schedule->courseid, $schedule->name, $schedule->instance));

            foreach ($users as $user){
                if(!is_numeric($user->username)){
                    continue;
                }
                try{
                    @\Inscricoes::sendAttendenceSchedule($schedule->schedule, [$user->username]);
                    mtrace("Usuário: $user->username registrado no Horário: $schedule->schedule do Evento: $schedule->eventid ");
                    error_log("Usuário: $user->username registrado no Horário: $schedule->schedule do Evento: $schedule->eventid");
                }catch (Exception $msg){
                    //mtrace('Presença já registrada'); 
                    //$msg = unserialize( base64_decode ($e->getMessage()));
                    if( strpos($msg, 'Presenca já registrada' ) > -1 ){ mtrace('Presenca já registrada'); }else{ mtrace($msg); }
                }
            }
            $DB->execute("UPDATE {local_inscricoes} SET sync = 1 WHERE courseid = ".$schedule->courseid);
        }

    }
}
