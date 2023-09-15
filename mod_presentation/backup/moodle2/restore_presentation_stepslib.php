<?php
/**
 * Define all the restore steps that will be used by the restore_url_activity_task
 */

/**
 * Structure step to restore one presentation activity
 */
class restore_presentation_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $paths[] = new restore_path_element('presentation', '/activity/presentation');

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_presentation($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        // Any changes to the list of dates that needs to be rolled should be same during course restore and course reset.
        // See MDL-9367.

        // insert the presentation record
        $newitemid = $DB->insert_record('presentation', $data);
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);
    }

    protected function after_execute() {
        // Add presentation related files, no need to match by itemname (just internally handled context)
        $this->add_related_files('mod_presentation', 'intro', null);
        $this->add_related_files('mod_presentation', 'content', null);
        $this->add_related_files('mod_presentation', 'presentation', null);
        
    }
}
