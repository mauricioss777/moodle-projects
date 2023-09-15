<?php

define('CLI_SCRIPT', true);

require_once('../../../config.php');

$cohort = '2';
$users = Array();
$role = 'manager'; //coordenador, editingteacher, student, manager
$categories = Array();
$contextlist = Array(1565939, 1565939, 1565941, 1565943, 1565944, 1370577, 1331745, 1356815, 1366761, 1424784, 1457187, 1487847, 1489925, 1582488, 1583438, 1590310, 1662186);

if(!empty($categories)){  }
$users = $DB->get_records_sql("SELECT userid FROM {cohort_members} WHERE cohortid = ? ", [$cohort]);

foreach ($contextlist as $context){
    foreach ($users as $user){
        $record = new stdClass();
        $record->roleid       = 1;
        $record->contextid    = $context;
        $record->userid       = $user->userid;
        $record->timemodified = time(); 
        $record->modifierid   = 2; 
        $DB->insert_record('role_assignments', $record);

        echo "$context -- $user->userid\n";
    }
}

