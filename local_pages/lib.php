<?php

defined('MOODLE_INTERNAL') || die();

include_once(__DIR__ . '/classes/servicos.class.php');
include_once($CFG->dirroot . '/local/alfa/classes/alfa.class.php');

function local_pages_extend_navigation_user_settings(navigation_node $parentnode, stdClass $user, context_user $context, stdClass $course, context_course $coursecontext)
{
    $node = $parentnode->parent->find('useraccount', navigation_node::TYPE_CONTAINER);
    $node->add('Mostrar avisos', new moodle_url('/local/pages/reset.php', array('id' => $user->id)));
}

// Add the menu item
function local_pages_extend_navigation(global_navigation $nav)
{
    global $CFG, $PAGE;

    $PAGE->requires->css('/local/pages/font/fontello.css');
    $PAGE->requires->css('/local/pages/style/custom.css');

    // Google for Education
    $node = navigation_node::create(
        get_string('googleforeducation', 'local_pages'),
        new moodle_url('/local/pages/googleforeducation.php'),
        navigation_node::NODETYPE_LEAF,
        'googleforeducation',
        'googleforeducation',
        new pix_icon('google', 'Google for Education', 'local_pages')
    );
    $node->showinflatnavigation = true;
    $nav->add_node($node);

    // Universo Univates
    $node = navigation_node::create("Universo Univates",
        $CFG->wwwroot . '/auth/ssounivates/redirect.php?target=https://www.univates.br/universounivates',
        navigation_node::NODETYPE_LEAF,
        'universounivates',
        'universounivates',
        new pix_icon('universo', "Universo Univates", 'local_pages')
    );
    $node->showinflatnavigation = true;
    $nav->add_node($node);

    //Show option for users to create popup messages
    if( local_pages_get_messages_can_access() ){
        $node = navigation_node::create("Mensagens",
            $CFG->wwwroot . '/local/pages/messages.php',
            navigation_node::NODETYPE_LEAF,
            'messages',
            'messages',
            new pix_icon('triangle', "Mensagens", 'local_pages')
        );
        $node->showinflatnavigation = true;
        $nav->add_node($node);
    }

}

function local_pages_user_has_messages(){
    global $USER, $DB;

    // return false;
    
    if(isset( $_SESSION['local_pages_messages']) ){
        return false;
    }
     
    $messages = local_pages_get_messages();

    if(!$messages){
        return false;
    }

    $_SESSION['local_pages_messages'] = 1;
    return true;

}

function local_pages_get_messages_json(){
    global $USER;
    $messages = local_pages_get_messages();
    $return = [];
    foreach($messages as $message){
        $return[] = ['value' => $message->intro];
    }
    return $return;
}

function local_pages_get_messages(){
    global $USER, $DB, $CFG;

    $message = $DB->get_record('user_preferences',
        Array(
            'userid' => $USER->id,
            'name' => 'local_page_message'
        )
    );

    $messages = implode(',', json_decode($message->value));
    if($messages == '') {$messages = '0'; }

    $sql = "SELECT *
                FROM {local_pages_messages}
                WHERE ( id IN ($messages) OR sitewise = 1 )
                AND (( timestart < ".time()."
                AND  timeend  > ".time()." ) OR relative = 1 ) ORDER BY id DESC";

    $messages = $DB->get_records_sql($sql);

    $return = [];

    foreach ($messages as $key => $message){
        $item = local_pages_manage_message($message); 
        if($item){ $return[] = $item; }
    }

    return $return;

}

function local_pages_manage_message($message){
    global $DB, $USER;

    if($message->timeend == 0 && $message->relative && $message->id != 2){ return false; } //Make sure the results are the right ones

    //There is a service attached to this service, so look for it
    $enrolments = '';
    if($message->service != 0){
        if(is_number($message->service) && $message->service > 0){
            if( Servicos::respondeuAvaliacaoAtiva($USER->username, $message->service) ){
                return false;
            }
        }else if(is_number($message->service)){
            if($message->service == -1 && ($_SESSION['titulos_abertos'] < 3 || $_SESSION['titulos_abertos'] > 4) ){
                return false;
            }

            if(!strpos($USER->department, '- EAD') ){ return; }

            if($message->service == -2 && $_SESSION['titulos_abertos'] < 5 ){
                return false;
            }
        }else{
            // Buscar matrículas para o usuário
            $enrolments = local_pages_get_enrolment_table( Alfa::buscarMatriculasUsuario($USER->username, $message->service) );
            if(!$enrolments){ return false; }
        }
    }


    //May have a list of documents to iterate
    $documents = '';
    if($message->id == 2 ){
        foreach($_SESSION['documentos'] as $document){ $documents .= "<li>$document</li>"; }
        if( $documents == '' ){ return false; }
    }

    //Replace placeholders on text
    $message->intro = str_replace("{{www}}", $CFG->wwwroot, $message->intro);
    $message->intro = str_replace("{{message_id}}", $message->id, $message->intro);
    $message->intro = str_replace("{{fristname}}", $USER->firstname, $message->intro);
    $message->intro = str_replace("{{documents}}", $documents, $message->intro);
    $message->intro = str_replace("{{componentes}}", $enrolments, $message->intro);

    return $message;
}

function  local_pages_get_messages_can_access(){
    global $DB, $USER;

    if( is_siteadmin() ){
        return true;
    }

    // if( $DB->get_record('local_pages_messages_users', ['userid' => $USER->id]) ){
    // return true;
    // }

    return false;
}

function local_pages_get_fontawesome_icon_map() {
    return array(
        'local_pages:google'   => 'fa-google',
        'local_coursemanager:eye' => 'fa-eye',
        'local_pages:universo' => 'icon-universo-univates',
        'local_pages:triangle'    => 'fa-exclamation-triangle'
    );
}

function local_pages_get_enrolment_table($elements) {
    $total = 0;
    $ret = "<div><table style='width: 80%; margin: 0 auto;'>";
    foreach($elements as $el){
        $ret .= "<tr> <td>".utf8_encode( $el->descricao_disciplina )."</td> <td style='text-align:center;'>".$el->carga_horaria_disciplina."h</td> </tr>";
        $total += intval($el->carga_horaria_disciplina);
    }
    $ret .= "<tr style='text-align:center;'> <td colspan='2'><b>Total: ".$total."h </b> </td> </tr>";
    $ret .= '</table></div>';
    if($total == 0){ return false; }
    return $ret;
}
