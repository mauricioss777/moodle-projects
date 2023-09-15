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
 * BlueGrid Course Format - A topics based format that uses card layout to display the content.
 *
 * @package   format_bluegrid
 * @copyright 2020 onwards, Univates
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Christian Bayer (christian.bayer@universo.univates.br)
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->dirroot . '/course/format/bluegrid/classes/output/section_renderable.php');

// Backward Compatibility.
if($topic = optional_param('topic', 0, PARAM_INT)) {
    $url = $PAGE->url;
    $url->set_anchor('section-' . $topic);
    debugging('Outdated topic param passed to course/view.php', DEBUG_DEVELOPER);
    redirect($url);
}
if($section = optional_param('section', 0, PARAM_INT)) {
    $url = $PAGE->url;
    $url->remove_params('section');
    $url->set_anchor('section-' . $section);
    debugging('Outdated topic param passed to course/view.php', DEBUG_DEVELOPER);
    redirect($url);
}
// End Backward Compatibility.

// Make sure section 0 is created.
course_create_sections_if_missing($course, 0);

// Render the sections
$renderer = $PAGE->get_renderer('format_bluegrid');
$renderer->render_sections(new \format_bluegrid\output\format_bluegrid_section($course));

// Include course format js module
$PAGE->requires->js('/course/format/bluegrid/format.js');