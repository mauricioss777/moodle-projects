<?php

require_once ('../../../config.php');

if(!is_siteadmin()){
    redirect('/');
}

require_once("$CFG->libdir/formslib.php");

class resetconclusion_form extends moodleform {

    public function definition() {
        global $CFG;
       
        $mform = $this->_form; // Don't forget the underscore! 

        $courseGroup = Array();
        $courseGroup[0] =& $mform->createElement('text', 'course', "Curso a ser limpo"); // Add elements to your form.
        $courseGroup[1] =& $mform->createElement('text', 'coursee', "Curso a ser limpo"); // Add elements to your form.
        $mform->addGroup($courseGroup, 'coursegroup', 'Curso a ser limpo', array(' '), false);

        $userGroup = Array();
        $userGroup[0] =& $mform->createElement('text', 'userid', "Usuário a ser limpo, id do usuário, não username"); // Add elements to your form.
        $userGroup[1] =& $mform->createElement('text', 'useerid', "Usuário a ser limpo, id do usuário, não username"); // Add elements to your form.
        $mform->addGroup($userGroup, 'usergroup', 'Usuário a ser limpo, id do usuário, não username', array(' '), false);

        $this->add_action_buttons(true);
    }

    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }
}

$mform = new resetconclusion_form();

$PAGE->set_title(get_string('pluginname', 'local_alfa'));
$PAGE->set_heading(get_string('pluginname', 'local_alfa'));


if ($mform->is_cancelled()) {
    redirect('index.php');
} else if ($fromform = $mform->get_data()) {
    $sql = "DELETE FROM {course_modules_completion} WHERE coursemoduleid IN (select id FROM mdl_course_modules WHERE course = ?) AND userid = ?";
    $DB->exec($sql, [$fromform->course, $fromform->userid]);
    redirect('index.php', 'Progresso apagado');
} else {
    echo $OUTPUT->header();

    $mform->display();

    echo $OUTPUT->footer();
}
