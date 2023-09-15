<?php

require_once('../../../config.php');
require_once('../classes/alfa.class.php');

global $DB;

// Check if is admin
if( ! is_siteadmin()) {
    die('Você não tem permissão de executar este script.');
}

// Get the polo and course field id to use later
$polofieldid = $DB->get_record('user_info_field', array('shortname' => 'Polo'))->id;
$coursefieldid = $DB->get_record('user_info_field', array('shortname' => 'Curso'))->id;

echo "Removing old values...<br/>";

// Remove old values
$query = "DELETE FROM {user_info_data} WHERE fieldid IN ({$polofieldid}, {$coursefieldid})";
$DB->execute($query);

echo "Old values removed!<br/>";

// Get all users with LDAP with login after 01/01/2018
$query = "SELECT * FROM {user} WHERE auth = 'ldap' AND lastlogin > 1514764800 ORDER BY id ASC";
$moodleusers = $DB->get_records_sql($query);

echo "Number of users to be updated: " . count($moodleusers) . "<br/>";

foreach ($moodleusers as $muser) {

    // If user has description equals to string 1 (trash), reset description
    if($muser->description === '1') {
        echo "Reseting description for user {$muser->firstname} {$muser->lastname}<br/>";
        $muser->description = '';
        $DB->update_record('user', $muser);
    }

    // Get alfa information
    $auser = Alfa::getUserInformation($muser->username);

    // If has polo, create
    if( ! empty($auser->polo)) {
        echo "Creating Polo information for user {$muser->firstname} {$muser->lastname}<br/>";
        $record = new \stdClass();
        $record->userid = $muser->id;
        $record->fieldid = $polofieldid;
        $record->data = $auser->polo;
        $DB->insert_record('user_info_data', $record);
    }

    // If has course, create
    if( ! empty($auser->course)) {
        echo "Creating Course information for user {$muser->firstname} {$muser->lastname}<br/>";
        $record = new \stdClass();
        $record->userid = $muser->id;
        $record->fieldid = $coursefieldid;
        $record->data = $auser->course;
        $DB->insert_record('user_info_data', $record);
    }

}