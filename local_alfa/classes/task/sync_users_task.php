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
class sync_users_task extends \core\task\scheduled_task
{
    /**
     * Get the name for this task (only for admins)
     *
     * @return string
     * @throws \coding_exception
     */
    public function get_name()
    {
        return get_string('syncuserstask', 'local_alfa');
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
        $users = \Alfa::getModifiedUsersInformation();

        // Run throug each user modified by Alfa
        foreach ($users as $username => $user) {

            // Get the user from database
            if($u = $DB->get_record('user', array('username' => $username))) {

                // Check if the polo is present
                if(isset($user->polo)) {

                    // Update field
                    $this->update_or_create_user_info_data('Polo', $u->id, $user->polo);
                    $user->institution = $user->polo;

                    // It is no more useful
                    unset($user->polo);
                }

                // Check if the course is present
                if(isset($user->course)) {
                    
                    // Update field
                    $this->update_or_create_user_info_data('Curso', $u->id, $user->course);
                    $user->department = strip_tags($user->course);

                    // It is no more useful
                    unset($user->course);
                }

                // If it has at leas one field to update
                if( ! empty((array) $user)) {
                    // Update the user
                    $user->id = $u->id;
                    $DB->update_record('user', $user);
                    mtrace("User updated: " . $u->username . " - " . $u->firstname . " " . $u->lastname);
                }

            }

        }

        mtrace("Users informations updated.");
        mtrace("Starting synchronization new users with Alfa");

        // Must increase the limit time and memory to process the profile pictures
        \core_php_time_limit::raise();
        \raise_memory_limit(MEMORY_EXTRA);

        // Get new users from Alfa
        if( ! empty($newusers = \Alfa::getNewUsers())) {

            // Run through every new user
            foreach ($newusers as $user) {
                // Check if the user already exists in database
                if( ! $u = $DB->get_record('user', array('username' => $user->username), 'id')) {

                    // Create the user
                    $user->id = $DB->insert_record('user', $user);

                    // Update polo field
                    $this->update_or_create_user_info_data('Polo', $u->id, $user->polo);
                    $user->institution = $user->polo;

                    // Update course field
                    $this->update_or_create_user_info_data('Curso', $u->id, $user->course);
                    $user->department = strip_tags($user->course);

                    // Add picture
                    $this->add_user_picture($user);

                    mtrace("User added: " . $user->username . " - " . $user->firstname . " " . $user->lastname);
                }

            }

        }

        // Get users by LDAP without name inside moodle database
        $sql = "SELECT username,
                       id,
                       picture
                  FROM {user}
                 WHERE auth = 'ldap' AND
                       username ~ '^\d{1,6}' AND
                       (firstname = '' OR lastname = '')";
        $musers = $DB->get_records_sql($sql);

        // Get information for this users from Alfa
        if( ! empty($ausers = \Alfa::getUsersInformation(array_keys($musers)))) {

            // Run through every user
            foreach ($ausers as $username => $user) {

                // Set id
                $user->id = $musers[$user->username]->id;
                // Update user
                $DB->update_record('user', $user);

                // Update polo field
                $this->update_or_create_user_info_data('Polo', $u->id, $user->polo);
                $user->institution = $user->polo;

                // Update course field
                $this->update_or_create_user_info_data('Curso', $u->id, $user->course);
                $user->department = strip_tags($user->course);

                if( ! $musers[$user->username]) {
                    // Add picture
                    $this->add_user_picture($user);
                }
                mtrace("User updated: " . $user->username . " - " . $user->firstname . " " . $user->lastname);

            }

        }

        mtrace("sync_users_task finished");
        mtrace("=============================================");
    }

    private function add_user_picture($user)
    {
        global $DB, $CFG;

        // Create the temp dir
        $picdir = $CFG->tempdir . '/usrpic_alfa/';
        if( ! \file_exists($picdir)) {
            mkdir($picdir);
        }

        // Get the picture from Alfa
        if($userpicture = \Alfa::getUserPicture($user->username)) {
            mtrace("Adding user pictures: " . $user->username . " - " . $user->firstname . " " . $user->lastname);

            // Save to temp dir
            $filepath = $picdir . $user->username . '.jpg';
            $fo = \fopen($filepath, 'wb');
            \fwrite($fo, $userpicture);
            \fclose($fo);

            // Get user context
            $context = \context_user::instance($user->id);

            // Process the image
            $newrev = \process_new_icon($context, 'user', 'icon', 0, $filepath);

            // Update the user field
            $DB->set_field('user', 'picture', $newrev, array('id' => $user->id));
        }
    }

    private function update_or_create_user_info_data($fieldname, $userid, $value)
    {
        global $DB;

        // Get field id
        $fieldid = $DB->get_record('user_info_field', array('shortname' => $fieldname))->id;

        // Get the record
        $record = $DB->get_record('user_info_data', array(
            'userid'  => $userid,
            'fieldid' => $fieldid,
        ));

        // Check if exists
        if($record) {
            // Update record
            $record->data = $value;
            $DB->update_record('user_info_data', $record);
        } else {
            // Create record
            $record = new \stdClass();
            $record->userid = $userid;
            $record->fieldid = $fieldid;
            $record->data = $value;
            $DB->insert_record('user_info_data', $record);
        }
    }

}
