<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Multiple Evaluations add/edit form
 *
 * @package   mod_multievaluations
 * @copyright 2020 onwards, Univates
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Thomas Marcel Guarnieri  (thomas.guarnieri@universo.univates.br)
 * @author    Artur Henrique Welp      (ahwelp@universo.univates.br)
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/multievaluations/lib.php');

class mod_alfacertificados_mod_form extends moodleform_mod {

    /*
     * Called to define this moodle form
     *
     * @return void
     */
    function definition() {
        global $CFG, $COURSE, $PAGE, $DB;

        $mform =& $this->_form;

        $mform->addElement('header', 'general', get_string('form_block_general', 'multievaluations'));

        $mform->addElement('text', 'name', get_string('description', 'alfacertificados'));

        $mform->setType('name', PARAM_TEXT);
        $useroptions = alfacertificados_get_methods();
        $select = $mform->addElement('select', 'type', get_string('certificate', 'alfacertificados'), $useroptions, null, array());

        $mform->addElement('header', 'parameters', get_string('form_parameters', 'alfacertificados'));

        $mform->addElement('html', alfacertificados_load_params($this->get_instance()) );

        $this->standard_coursemodule_elements();

        $this->add_action_buttons();
        $PAGE->requires->js_call_amd( 'mod_alfacertificados/multi_params' );
    }

    /**
    * Add any custom completion rules to the form.
    * 
    * @return array Contains the names of the added form elements
    */
    public function validation($data, $files) {
        global $DB, $COURSE;

        $alfa = $DB->record_exists('local_alfa', ['courseid' => $COURSE->id]);
        $inscricoes = $DB->record_exists('local_inscricoes', ['courseid' => $COURSE->id]);

        if(!$alfa && !$inscricoes){
            $errors['name'] = get_string('validation_connection', 'alfacertificados');
        }

        if( strlen($data['name']) < 3 ){
            $errors['name'] = get_string('validation_description', 'alfacertificados');
        }
        
        if( $data['type'] == -1 ){
            $errors['type'] = get_string('validation_certificate', 'alfacertificados');
        }

        return $errors; 
    }

}
