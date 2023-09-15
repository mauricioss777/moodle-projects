<?php

require_once ('../../../config.php');
require_once ('../lib.php');

if(!is_siteadmin()){
    redirect('/');
}

require_once("$CFG->libdir/formslib.php");

class vinculatecurriculm_form extends moodleform {

    public function definition() {
        global $CFG;
       
        $mform = $this->_form;

        $courseGroup = Array();
        $courseGroup[0] =& $mform->createElement('text', 'course', ""); // Add elements to your form.
        $courseGroup[1] =& $mform->createElement('text', 'coursee', ""); // Add elements to your form.
        $mform->addGroup($courseGroup, 'coursegroup', 'Ambiente a ser vinculado', array(' '), false);

        $mform->addElement('text', 'curriculo', "Curriculo"); // Add elements to your form.

        $this->add_action_buttons(true);
    }

    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }
}

$mform = new vinculatecurriculm_form();

$PAGE->set_title(get_string('pluginname', 'local_alfa'));
$PAGE->set_heading(get_string('pluginname', 'local_alfa'));

if ($mform->is_cancelled()) {
    redirect('index.php');
} else if ($fromform = $mform->get_data()) {

    local_alfa_add_curriculim($fromform->course, $fromform->curriculo);

    redirect('index.php', 'Ofertas vinculadas');

} else {
    echo $OUTPUT->header();

    $mform->display();

    echo $OUTPUT->footer();
}
