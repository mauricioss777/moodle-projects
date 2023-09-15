<?php

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . "/../../lib.php");


class backup_format_bluegrid_plugin extends backup_format_plugin {
    
    /**
     * Esta função mapeia os ids das seções antigas e disponibiliza os valores
     * na função after_restore_section do restore.
     */
    protected function define_section_plugin_structure() {

        $plugin = $this->get_plugin_element(null, $this->get_format_condition(), 'bluegrid');

        $wrapper = new backup_nested_element('bluegrid', [ 'id' ], [ 'section' ]);
        $wrapper->set_source_table('course_sections', [ 'id' => backup::VAR_SECTIONID ]);

        $plugin->add_child($wrapper);
        return $plugin;
    }

}
