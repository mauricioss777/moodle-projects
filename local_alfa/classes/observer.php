<?php

defined('MOODLE_INTERNAL') || die();

require_once('alfa.class.php');

class local_alfa_observer
{
    public static function local_alfa_user_login(core\event\user_loggedin $user_event_data)
    {
        global $DB, $USER;

        // Get the username from event
        $username = array_pop($user_event_data->get_data()['other']);

        // Check if is the first time that this user is logging on (empty name)
        $result = $DB->get_record('user', array(
            'auth'     => 'ldap',
            'username' => $username,
        ));

        // If is the first time
        if( ! empty($result)) {

            // Get user information from Alfa
            $data = Alfa::getUserInformation($username);

            // Update data
            $result->firstname = $data->firstname;
            $result->lastname = $data->lastname;
            $result->email = $data->email;
            $result->city = $data->city;
            $result->idnumber = $data->idnumber;
            $result->department = strip_tags($data->course);
            $result->institution = $data->polo;
            $result->lastlogin = time();

            // Update user
            $DB->update_record('user', $result);

            // Update global user
            $USER = $result;
        }

        //Get the message register from user preferences
        $messages = $DB->get_record_sql(
            "SELECT * 
               FROM {user_preferences}
             WHERE 
               userid = ".$USER->id." AND 
               name = 'local_page_message'"
        );
        
        //If there is no register, create one
        if(!$messages){
            $messages = new stdClass();
            $messages->userid = $USER->id;
            $messages->name = 'local_page_message';
            $messages->value = "[]";
        }

        //Gamb Senhas 
        $messages->value = str_replace('"1,"', '', $messages->value);
        $messages->value = str_replace('"1"', '', $messages->value);
        $messages->value = str_replace('[,', '[', $messages->value);
        $messages->value = str_replace(',]', ']', $messages->value);

        // Decode history messages for user
        $messages->value = (array) json_decode($messages->value);

        $missing_document = Alfa::getDocumentosPendentesPessoa($username)['documentacao_pendente'];
        $message_id = $DB->get_record_sql("SELECT id FROM {local_pages_messages} WHERE name = 'documentos_faltantes'");
        if(strpos($USER->email, '@univates.br') > -1 && $USER->username != 771942){
            $missing_document = false;
        }
        //$missing_document = true; //Check for documents on Alfa
        if($missing_document){
            $messages->value[] = $message_id->id;
            $_SESSION['documentos'] = $missing_document;
        }else if(array_search($message_id->id, $messages->value ) != null){
            unset( $messages->value[array_search($message_id->id, $messages->value )]);
        }

        $change_password = Alfa::pessoaPrecisaRecadastrarSenha($username);
        $message_id = $DB->get_record_sql("SELECT id FROM {local_pages_messages} WHERE name = 'mudar_senha'");
        //$change_password = true; //Check for old password in alfa
        if($change_password){
            $messages->value[] = $message_id->id;
        }

        $titulos = sizeof(Alfa::titulosAbertos($username));
        $_SESSION['titulos_abertos'] = $titulos;

        $messages->value = json_encode(array_values(array_unique($messages->value)));

        if($messages->value != '[]' && isset($messages->id)){
            $DB->update_record('user_preferences', $messages);
        } else if($messages->value != '[]' && !isset($messages->id)){
            $DB->insert_record('user_preferences', $messages);
        }else if($messages->value == '[]' && isset($messages->id)){
            $DB->delete_records('user_preferences', ['id' => $messages->id]);
        }

    }

    public static function local_alfa_course_deleted(core\event\course_deleted $event)
    {
        global $DB;

        // Get data from event
        $data = $event->get_data();

        // Get all local_alfa links to this course
        $query = "SELECT * FROM {local_alfa} WHERE courseid = ?";
        $results = $DB->get_records_sql($query, array(
            $data['courseid'],
        ));

        // Run through every record
        foreach ($results as $result) {
            // Delete from local_alfa
            $DB->delete_records('local_alfa', array(
                'id' => $result->id,
            ));
            // Trigger Alfa link change
            Alfa::updateLinkEaD(array($result->idnumber), null);
        }

        // Get all local_alfa_tcc links to this course
        $query = "SELECT * FROM {local_alfa_tcc} WHERE courseid = ?";
        $results = $DB->get_records_sql($query, array(
            $data['courseid'],
        ));

        // Run through every record
        foreach ($results as $result) {
            // Delete from local_alfa_tcc
            $DB->delete_records('local_alfa_tcc', array(
                'id' => $result->id,
            ));
            // Trigger Alfa link change
            Alfa::updateLinkEaD(array($result->idnumber), null);
        }
    }
}
