<?php

namespace mod_presentation\output;

require_once($CFG->dirroot . '/mod/presentation/classes/output/presentation.php');

class renderer extends \plugin_renderer_base {

    /**
     * Renders the multiple section page.
     * @param \format_bluegrid\output\format_bluegrid_section $section Object of the Section renderable.
     * @throws moodle_exception
     */
    public function render_presentation(\mod_presentation\output\mod_presentation_presentation $presentation) {
        $templatecontext = $presentation->export_for_template($this);
        return $this->render_from_template('mod_presentation/presentation', $templatecontext);
    }
}