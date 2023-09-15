<?php

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/mod/presentation/backup/moodle2/backup_presentation_stepslib.php');

/**
 * Provides the steps to perform one complete backup of the Presentation instance
 */
class backup_presentation_activity_task extends backup_activity_task {

    /**
     * No specific settings for this activity
     */
    protected function define_my_settings() {
    }

    /**
     * Defines a backup step to store the instance data in the presentation.xml file
     */
    protected function define_my_steps() {
        $this->add_step(new backup_presentation_activity_structure_step('presentation_structure', 'presentation.xml'));
    }

    /**
     * No content encoding needed for this activity
     *
     * @param string $content some HTML text that eventually contains URLs to the activity instance scripts
     * @return string the same content with no changes
     */
    static public function encode_content_links($content) {
        return $content;
    }
}
