<?php

class backup_presentation_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated
        $presentation = new backup_nested_element('presentation', array('id'), array(
            'name', 'intro', 'embed', 'download', 'timecreated', 'timemodified'));

        // Define sources
        $presentation->set_source_table('presentation', array('id' => backup::VAR_ACTIVITYID));

        // Define file annotations
        $presentation->annotate_files('mod_presentation', 'intro', null); // This file area hasn't itemid
        $presentation->annotate_files('mod_presentation', 'content', null); // This file area hasn't itemid
        $presentation->annotate_files('mod_presentation', 'presentation', null); // This file area hasn't itemid

        // Return the root element (presentation), wrapped into standard activity structure
        return $this->prepare_activity_structure($presentation);
    }
}
