<?php

namespace local_monitoring\output;                                                                                                         
 
defined('MOODLE_INTERNAL') || die;
 
use plugin_renderer_base;
 
class renderer extends \plugin_renderer_base {

    public function render_index($page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('local_monitoring/index', $data);
    }

    public function render_form() {
        return parent::render_from_template('local_monitoring/user_selection', [] );
    }

    public function render_tests($page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('local_monitoring/tests', $data );
    }

}
