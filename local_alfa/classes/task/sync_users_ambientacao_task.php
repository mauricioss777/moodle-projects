<?php

namespace local_alfa\task;

require_once($CFG->dirroot . '/local/alfa/classes/alfa.class.php');
require_once($CFG->dirroot . '/lib/gdlib.php');

/**
 * This function:
 * updates user information according to the modifications made in Alfa;
 * add new users created in Alfa;
 * complete incomplete user profiles;
 *
 * For debug purposes:
 * usr/bin/php  /var/www/html/moodle/admin/cli/cron.php|less
 */
class sync_users_ambientacao_task extends \core\task\scheduled_task
{
    /**
     * Get the name for this task (only for admins)
     *
     * @return string
     * @throws \coding_exception
     */
    public function get_name()
    {
        return get_string('syncuserambientacaotask', 'local_alfa');
    }

    /**
     * Method that will be executed by crontab
     */
    public function execute()
    {
        global $DB;

        mtrace("=============================================");
        mtrace("Starting synchronization of user information with Alfa");

        // Get the new information from the last 24 hours
        $users = \Alfa::usuarioEmAdaptacao();

        mtrace("Users informations updated.");
        mtrace("Starting synchronization new users with Alfa");

         foreach(array_keys($users) as $info){
             $DB->execute("UPDATE {user} set imagealt = 'ambientacao' WHERE username = ?", [$info]);
             mtrace("sync_users_task == $info");
        }

        $users = "'".implode("', '", array_keys($users) )."'";
        $records = $DB->get_records_sql("SELECT * FROM {user} WHERE imagealt = 'ambientacao' AND username NOT IN ($users)");

        //Remove pessoas que não estão mais em ambientação
        foreach($records as $record){
            $record->imagealt = 'Foto de ' . strtolower( $record->firstname . ' ' . $record->lastname );
            $DB->update_record('user', $record);
        }
        mtrace( print_r($records, true) );

        mtrace("sync_users_task finished");
        mtrace("=============================================");
    }

}
