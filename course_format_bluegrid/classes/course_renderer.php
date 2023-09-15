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

defined('MOODLE_INTERNAL') || die();

/**
 * Format BlueGrid Course Renderer Class.
 *
 * @package   format_bluegrid
 * @copyright 2020 onwards, Univates
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Christian Bayer (christian.bayer@universo.univates.br)
 */
class format_bluegrid_course_renderer extends \core_course_renderer
{

    /**
     * Renders HTML to display one course module for display within a section.
     *
     * This function calls:
     * {@link core_course_renderer::course_section_cm()}
     *
     * @param stdClass        $course
     * @param completion_info $completioninfo
     * @param cm_info         $mod
     * @param int|null        $sectionreturn
     * @param array           $displayoptions
     * @return String
     */
    public function course_section_cm_list_item($course, &$completioninfo, cm_info $mod, $sectionreturn, $displayoptions = array()) {
        $output = '';
        if($modulehtml = $this->course_section_cm($course, $completioninfo, $mod, $sectionreturn, $displayoptions)) {
            $modclasses = 'activity ' . $mod->modname . ' modtype_' . $mod->modname;
            if($mod->extraclasses) {
                $modclasses .= ' ' . $mod->extraclasses;
            }
            if(strpos($modulehtml, 'course-resource-title') !== false) {
                $modclasses .= ' main-label';
            }
            if(( ! $mod->get_section_info()->visible && $mod->visible) || ($mod->get_section_info()->visible && ( ! $mod->visible || ! $mod->visibleoncoursepage))) {
                $modclasses .= ' mod-hidden';
            }
            if( ! $mod->available) {
                $modclasses .= ' mod-restricted';
            }
            $output .= html_writer::tag('li', $modulehtml, array(
                'class' => $modclasses,
                'id'    => 'module-' . $mod->id,
            ));
        }

        return $output;
    }

}
