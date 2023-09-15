<?php

namespace local_monitoring\output;

use renderable;
use renderer_base;
use templatable;
use stdClass;

class form implements renderable, templatable {
    /** @var string $sometext Some text to show how to pass data to a template. */
    var $data = [];

    public function __construct($user) {
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        return $this->data;
    }

    function render_form(){
        return parent::render_from_template('local_monitoring/user_selection', $data);
    }

}
