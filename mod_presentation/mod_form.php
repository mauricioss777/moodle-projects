<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

class mod_presentation_mod_form extends moodleform_mod {
    function definition() {
        global $CFG, $COURSE;

        $mform =& $this->_form;
        $mform->addElement('header', 'general', get_string('general'));

        $mform->addElement('text', 'name', get_string('name', 'presentation'));

        // Intro field
        $this->standard_intro_elements(get_string('description', 'presentation'));

        $filemanager_options = array();
        $filemanager_options['accepted_types'] = 'pdf, ppt, pptx';
        $filemanager_options['maxbytes'] = 0;
        $filemanager_options['maxfiles'] = 1;
        $filemanager_options['mainfile'] = true;
        $mform->addElement('filemanager', 'files', get_string('selectfiles'), null, $filemanager_options);

        $visualization = [0 => 'Normal', 1 => 'Embed', 2 => 'Inplace Open'];
        $mform->addElement('select', 'embed', get_string('form:embed', 'presentation'), $visualization, []);

        $download = [0 => get_string('no'), 1 => get_string('yes')];
        $mform->addElement('select', 'download', get_string('form:download', 'presentation'), $download, []);

        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }

    function data_preprocessing(&$default_values) {
        $draftitemid = file_get_submitted_draft_itemid('files');
        file_prepare_draft_area($draftitemid, $this->context->id, 'mod_presentation', 'content', 0, array('subdirs'=>true));
        $default_values['files'] = $draftitemid;
        $default_values['subtitles'] = Array('text' => $default_values['subtitles'], 'format' => 1);
    }

    function validation($data, $files) {
        global $USER;

        $errors = parent::validation($data, $files);

        $usercontext = context_user::instance($USER->id);
        $fs = get_file_storage();
        if (!$files = $fs->get_area_files($usercontext->id, 'user', 'draft', $data['files'], '', false)) {
            $errors['files'] = get_string('required');
            return $errors;
        }
    }
}