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

require_once($CFG->dirroot . '/course/format/renderer.php');
require_once($CFG->dirroot . '/course/format/bluegrid/classes/settings_controller.php');
require_once($CFG->dirroot . '/course/format/bluegrid/classes/output/header_renderable.php');

use format_bluegrid\output\format_bluegrid_header;

/**
 * Basic renderer for BlueGrid Course Format.
 *
 * @package   format_bluegrid
 * @copyright 2020 onwards, Univates
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Christian Bayer (christian.bayer@universo.univates.br)
 */
class format_bluegrid_renderer extends format_section_renderer_base
{

    protected $courseformat;
    protected $settings;

    /**
     * Constructor method, calls the parent constructor
     * @param moodle_page $page
     * @param string      $target one of rendering target constants
     */
    public function __construct(moodle_page $page, $target) {
        parent::__construct($page, $target);

        $this->courseformat = course_get_format($page->course);
        $this->courserenderer = $this->page->get_renderer('format_bluegrid', 'course');
        $this->settings = $this->courseformat->get_settings();
        $this->brandcolor = ($this->settings['brandcolor']) ? $this->settings['brandcolor'] : get_config('format_bluegrid', 'brandcolor');

        // Since format_bluegrid_renderer::section_edit_controls()
        // only displays the 'Set current section' control when editing mode is on
        // we need to be sure that the link 'Turn editing mode on' is available
        // for a user who does not have any other managing capability.
        $page->set_other_editing_capability('moodle/course:setcurrentsection');
    }

    /**
     * Generate the starting container html for a list of sections
     * @return string HTML to output.
     */
    protected function start_section_list() {
        return html_writer::start_tag('ul', array('class' => 'bluegrid'));
    }

    /**
     * Generate the closing container html for a list of sections
     * @return string HTML to output.
     */
    protected function end_section_list() {
        return html_writer::end_tag('ul');
    }

    /**
     * Generate the title for this section page
     * @return string the page title
     * @throws coding_exception
     */
    protected function page_title() {
        return get_string('sectionname', 'format_bluegrid');
    }

    /**
     * Generate the section title, wraps it in a link to the section page if page is to be displayed on a separate page
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course  The course entry from DB
     * @return string HTML to output.
     */
    public function section_title($section, $course) {
        return $this->render(course_get_format($course)->inplace_editable_render_section_name($section));
    }

    /**
     * Generate the section title to be displayed on the section page, without a link
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course  The course entry from DB
     * @return string HTML to output.
     */
    public function section_title_without_link($section, $course) {
        return $this->render(course_get_format($course)->inplace_editable_render_section_name($section, false));
    }

    /**
     * Generate the content to displayed on the right part of a section
     * before course modules are included
     *
     * @param stdClass $section       The course_section entry from DB
     * @param stdClass $course        The course entry from DB
     * @param bool     $onsectionpage true if being printed on a section page
     * @return string HTML to output.
     */
    public function section_right_content($section, $course, $onsectionpage) {
        $controls = $this->section_edit_control_items($course, $section, $onsectionpage);

        return $this->section_edit_control_menu($controls, $course, $section);
    }

    /**
     * If section is not visible, display the message about that ('Not available
     * until...', that sort of thing). Otherwise, returns blank.
     *
     * For users with the ability to view hidden sections, it shows the
     * information even though you can view the section and also may include
     * slightly fuller information (so that teachers can tell when sections
     * are going to be unavailable etc). This logic is the same as for
     * activities.
     *
     * @param section_info $section       The course_section entry from DB
     * @param bool         $canviewhidden True if user can view hidden sections
     * @return string HTML to output
     */
    public function section_availability_message($section, $canviewhidden) {
        return parent::section_availability_message($section, $canviewhidden);
    }

    /**
     * Returns controls in the bottom of the page to increase/decrease number of sections
     *
     * @param stdClass $course
     * @param int|null $sectionreturn
     * @return string
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function change_number_sections($course, $sectionreturn = null) {
        $coursecontext = context_course::instance($course->id);
        if( ! has_capability('moodle/course:update', $coursecontext)) {
            return '';
        }

        $options = course_get_format($course)->get_format_options();
        $supportsnumsections = array_key_exists('numsections', $options);

        $html = '';

        if($supportsnumsections) {
            // Current course format has 'numsections' option, which is very confusing and we suggest course format
            // developers to get rid of it (see MDL-57769 on how to do it).
            // Display "Increase section" / "Decrease section" links.

            $html .= html_writer::start_tag('div', array(
                'id'    => 'changenumsections',
                'class' => 'mdl-right',
            ));

            // Increase number of sections.
            $straddsection = get_string('increasesections', 'moodle');
            $url = new moodle_url('/course/changenumsections.php',
                array(
                    'courseid' => $course->id,
                    'increase' => true,
                    'sesskey'  => sesskey(),
                ));
            $icon = $this->output->pix_icon('t/switch_plus', $straddsection);
            $html .= html_writer::link($url, $icon . get_accesshide($straddsection), array('class' => 'increase-sections'));

            if($course->numsections > 0) {
                // Reduce number of sections sections.
                $strremovesection = get_string('reducesections', 'moodle');
                $url = new moodle_url('/course/changenumsections.php',
                    array(
                        'courseid' => $course->id,
                        'increase' => false,
                        'sesskey'  => sesskey(),
                    ));
                $icon = $this->output->pix_icon('t/switch_minus', $strremovesection);
                $html .= html_writer::link($url, $icon . get_accesshide($strremovesection), array('class' => 'reduce-sections'));
            }

            $html .= html_writer::end_tag('div');

        } else if(course_get_format($course)->uses_sections()) {
            // Current course format does not have 'numsections' option but it has multiple sections suppport.
            // Display the "Add section" link that will insert a section in the end.
            // Note to course format developers: inserting sections in the other positions should check both
            // capabilities 'moodle/course:update' and 'moodle/course:movesections'.

            $html .= html_writer::start_tag('div', array(
                'id'    => 'changenumsections',
                'class' => 'mdl-right',
            ));

            $straddsections = get_string('addsections');

            $url = new moodle_url('/course/changenumsections.php',
                [
                    'courseid'      => $course->id,
                    'insertsection' => 0,
                    'sesskey'       => sesskey(),
                ]);
            $icon = $this->output->pix_icon('t/add', $straddsections);
            $html .= html_writer::link($url, $icon . $straddsections,
                array(
                    'class'             => 'btn btn-primary add-sections',
                    'data-add-sections' => $straddsections,
                ));
            $html .= html_writer::end_tag('div');
        }

        return $html;
    }

    /**
     * Generate the content to displayed on the left part of a section
     * before course modules are included
     *
     * @param stdClass $section       The course_section entry from DB
     * @param stdClass $course        The course entry from DB
     * @param bool     $onsectionpage true if being printed on a section page
     * @return string HTML to output.
     */
    public function section_left_content($section, $course, $onsectionpage) {
        return parent::section_left_content($section, $course, $onsectionpage);
    }

    /**
     * Renders the multiple section page.
     * @param \format_bluegrid\output\format_bluegrid_section $section Object of the Section renderable.
     * @throws moodle_exception
     */
    public function render_sections(\format_bluegrid\output\format_bluegrid_section $section) {
        $templatecontext = $section->export_for_template($this);
        if(isset($templatecontext->error)) {
            print_error($templatecontext->error);
        } else {
            echo $this->render_from_template('format_bluegrid/main', $templatecontext);
        }
    }

    /**
     * Renders the format_bluegrid header.
     *
     * @param format_bluegrid_header $header
     * @return bool|string
     * @throws moodle_exception
     */
    protected function render_format_bluegrid_header(format_bluegrid_header $header) {
        return $this->render_from_template('format_bluegrid/course_header',
            $header->export_for_template($this));
    }

}
