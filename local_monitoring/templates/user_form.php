<?php

defined('MOODLE_INTERNAL') || die;

require_once ($CFG->dirroot.'/course/moodleform_mod.php');

class local_monitoring_user_form extends moodleform {

    function definition() {
        global $PAGE, $CFG;

        $PAGE->force_settings_menu();

        $mform = $this->_form;
        
        $mform->addElement('text', 'codigo', 'Codigo', ['class' => 'codigo']);
        $mform->addElement('text', 'nome',   'Nome', ['class' => 'name']);
        $select = $mform->addElement('select', 'colors', get_string('colors'), array(), $attributes);
        $select->setMultiple(true);

        $PAGE->requires->js( new moodle_url( $CFG->wwwroot . '/local/monitoring/templates/search.js') );

    }

}
