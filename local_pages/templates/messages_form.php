<?php

require_once("$CFG->libdir/formslib.php");

class local_messages_message_form extends moodleform {

    function definition()
    {
        global $USER, $DB, $CFG;
        
        $mensagens = [];

        if( $_GET['allmessages'] == 1 ){
            $mensagens = $DB->get_records_sql('SELECT *, to_timestamp(timestart) as timestart, to_timestamp(timeend) as timeend FROM {local_pages_messages} ');
        }else{
            $mensagens = $DB->get_records_sql('SELECT *, to_timestamp(timestart) as timestart, to_timestamp(timeend) as timeend 
                                               FROM {local_pages_messages} WHERE timeend > '.time(). 'OR timeend = 0');
        }
        $mform = $this->_form;

        $mform->addElement('header', 'message', get_string('message', 'local_pages'));

        $table = "
                  <a href='".$CFG->wwwroot."/local/pages/messages.php?allmessages=1'>Mostrar todos</a>
                  <table class='table'> 
                  <tr> 
                  <th>Id</th> 
                  <th>Nome</th> 
                  <th> Data Inicio</th> 
                  <th>Data fim</th> 
                  <th>Serviço</th> 
                  <th>Global</th> 
                </tr>";
        
        foreach($mensagens as $mensagem){
            $table .= "<tr> 
                        <td><a href='".$CFG->wwwroot."/local/pages/messages.php?id=$mensagem->id'>$mensagem->id</a></td>
                        <td>$mensagem->name</td> 
                        <td>$mensagem->timestart</td>
                        <td>$mensagem->timeend</td>
                        <td>$mensagem->service</td>
                        <td>$mensagem->sitewise</td>
                    </tr>";
        }

        $table .= '</table>';
        $mform->addElement('html', $table);

        $mform->addElement('header', 'message', get_string('message', 'local_pages'));

        $mform->addElement('hidden', 'id', $this->_customdata['id']);

        $mform->addElement('text', 'name', get_string('name', 'local_pages'));
        $mform->setType('name', PARAM_RAW_TRIMMED);
        $mform->setDefault('name', $this->_customdata['name']);

        $mform->addElement('date_selector', 'timestart',
            get_string('timestart', 'local_pages'), array('optional' => true));
        $mform->setType('timestart', PARAM_INT);
        $mform->setDefault('timestart', $this->_customdata['timestart']);

        $mform->addElement('date_selector', 'timeend',
            get_string('timeend', 'local_pages'), array('optional' => true));
        $mform->setType('timeend', PARAM_INT);
        $mform->setDefault('timeend', $this->_customdata['timeend']);

        $mform->addElement('text', 'service', get_string('service', 'local_pages'), array('optional' => true));
        $mform->setType('service', PARAM_INT);
        $mform->setDefault('service', $this->_customdata['service']);
        
        $checkbox = []; 
        if( $this->_customdata['sitewise'] ){
            $checkbox = [1, 0];
        }else{
            $checkbox = [0, 1];
        }
        $mform->addElement('advcheckbox', 
                           'sitewise', 
                           get_string('global',             'local_pages'), 
                           get_string('global_description', 'local_pages'), 
                           array('group' => 1), $checkbox);

        $mform->addElement('editor', 'intro', get_string('description'),array('rows' => 10), array('maxfiles' => 2, 'noclean' => false, 'context' => $this->context, 'subdirs' => true) );
        if(!$this->_customdata['intro']){
            $this->_customdata['intro'] = get_string('base_message', 'local_pages');
        }

        $mform->setDefault('intro', array('text'=>$this->_customdata['intro']));
        $mform->addElement('header', 'users', get_string('users', 'local_pages'));
        $mform->addElement('textarea', 'usernames', get_string("sql", "local_pages"), 'wrap="virtual" rows="3" cols="50"');

        if($this->_customdata['users']){
            $table = "<table class='table'> <tr><th>Id</th><th>Username</th><th>Nome</th></tr>";
            foreach ($this->_customdata['users'] as $user){
                $table .= "<tr> <td>$user->id</td> <td>$user->username</td> <td>$user->name</td>  </tr>";
            }
            $table .= '</table>';
            $mform->addElement('html', $table);
        }
        $this->add_action_buttons(true);
    }

    function get_data() {
        global $DB;
        $data = parent::get_data();

        return $data;
    }

    function validation($data, $files) {
        global $DB;

        $errors = parent::validation($data, $files);

        return $errors;
    }
}
