<?php

require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/' . $CFG->admin . '/webservice/forms.php');
require_once($CFG->libdir . '/externallib.php');

require_once('lib.php');
require_once('templates/messages_form.php');

if( !local_pages_get_messages_can_access() ){
    redirect($CFG->wwwroot . '/my');
}

$action  = optional_param('action', 'form', PARAM_ALPHANUMEXT);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$message = optional_param('id', 0, PARAM_INT);

if(!empty($_POST)){
    $action = 'store';
}

switch ($action) {
    default:
        $action = 'form';
    case 'form':
        $data = $DB->get_record('local_pages_messages', Array('id' => $message));
        if($message != 0){
            $data->users = $DB->get_records_sql("SELECT mus.id, mus.username, mus.firstname || ' ' || mus.lastname as name FROM {user_preferences} mup, {user} mus WHERE mup.userid = mus.id AND name = 'local_page_message' AND value ILIKE(?) LIMIT 60", Array('%'.$message.'%'));
        }
        $mform = new local_messages_message_form(null, (array)$data);
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('create_message', 'local_pages'));
        if (!empty($errormsg)) {
            echo $errormsg;
        }
        $mform->display();
        echo $OUTPUT->footer();
        die;
        break;
    case 'store':
        $message = $DB->get_record('local_pages_messages', Array('id' => $message));
        $message->name = optional_param('name', '', PARAM_ALPHANUMEXT);

        $timestart = optional_param('timestart', 0, PARAM_INT);

        if($timestart == 0){
            $message->timestart = 0;
        }else{
            $message->timestart = DateTime::createFromFormat("d/m/Y", $timestart['day'].'/'.$timestart['month'].'/'.$timestart['year'])->getTimestamp();
        }

        $timeend = optional_param('timeend', 0, PARAM_INT);
        if($timeend == 0){
            $message->timeend = 0;
        }else{
            $message->timeend = DateTime::createFromFormat("d/m/Y", $timeend['day'].'/'.$timeend['month'].'/'.$timeend['year'])->getTimestamp();
        }

        if($timeend == 0 && $timestart == 0){
            $message->relative = 1;
        }else{
            $message->relative = 0;
        }

        $service = optional_param('service', 0, PARAM_ALPHANUMEXT);
        if($service != 0){
            $message->service = $service;
        }else{
            $message->service = 0; 
        }

        $sitewise = optional_param('sitewise', '1', PARAM_RAW);

        if($sitewise == 'on'){
            $message->sitewise = 1;
        }else{
            $message->sitewise = 0;
        }

        $message->intro = optional_param('intro', '', PARAM_RAW)['text'];

        if(isset($message->id)){
            $DB->update_record('local_pages_messages', $message);
        }else{
            $message->id = $DB->insert_record('local_pages_messages', $message, true);
        }

        if(optional_param('usernames', null, PARAM_RAW)){
            $users = explode(';', optional_param('usernames', null, PARAM_RAW));
            $holder = '';
            foreach ($users as $user){ $holder .= '?, '; }
            $holder = rtrim($holder, ', ');
            $users = $DB->get_records_sql("SELECT id FROM {user} WHERE username IN ($holder)", $users);
            foreach ($users as $user) {
                $user_prop = $DB->get_record('user_preferences',
                    Array(
                        'userid' => $user->id,
                        'name' => 'local_page_message'
                    )
                );
                $user_prop->value = json_decode($user_prop->value);
                $user_prop->value[] = $message->id;
                $user_prop->value = array_unique($user_prop->value);
                $user_prop->value = json_encode($user_prop->value);
                if($user_prop->id){
                    $DB->update_record('user_preferences', $user_prop);
                }else{
                    $user_prop->userid = $user->id;
                    $user_prop->name = 'local_page_message';
                    $DB->insert_record('user_preferences', $user_prop);
                }
            }
        }

        redirect('messages.php?id='.$message->id);
        break;
}
