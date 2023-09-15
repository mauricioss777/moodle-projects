<?php

require_once('../../config.php');

require_login();

$message = optional_param('message', 0, PARAM_INT);

$element = $DB->get_record('user_preferences', ['userid' => $USER->id, 'name' => 'local_page_message']);
$element->value = str_replace($message, '', $element->value);
$element->value = str_replace('""', '', $element->value);
$element->value = str_replace(',,', ',', $element->value);
$element->value = str_replace(',]', ']', $element->value);
$element->value = str_replace(', ]', ']', $element->value);
$DB->update_record('user_preferences', $element);

$ip = $ip = isset($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['HTTP_X_REAL_IP'] : $_SERVER["REMOTE_ADDR"];
$log = (object) [
    'eventname' => '\local\pages\message_dismissed',
    'component' => 'local_pages',
    'action' => 'dismissed',
    'target' => 'user',
    'objecttable' => '',
    'objectid' => $message,
    'crud' => 'd',
    'edulevel' => 1,
    'contextid' => 0,
    'contextlevel' => 10,
    'contextinstanceid' => 0,
    'userid' => $USER->id,
    'courseid' => 0,
    'relateduserid' => $USER->id,
    'other' => 'N;',
    'timecreated' => time(),
    'origin' => 'web',
    'ip' => $ip 
]; $DB->insert_record('logstore_standard_log', $log);

if(isset($_SERVER['HTTP_REFERER'])) {
    redirect($_SERVER['HTTP_REFERER']);
}else{
    redirect($CFG->wwwroot);
}
