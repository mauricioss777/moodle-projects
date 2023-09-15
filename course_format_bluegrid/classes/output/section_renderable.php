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

namespace format_bluegrid\output;

defined('MOODLE_INTERNAL') || die();

use context_course;
use core_completion\progress;
use moodle_url;
use renderable;
use renderer_base;
use stdClass;
use templatable;

/**
 * This file contains the definition for the renderable classes for the sections page.
 *
 * @package   format_bluegrid
 * @copyright 2020 onwards, Univates
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Christian Bayer (christian.bayer@universo.univates.br)
 */
class format_bluegrid_section implements renderable, templatable
{

    protected $page;
    protected $course;
    protected $courseformat;
    protected $courserenderer;
    protected $coursecontext;
    protected $formatrenderer;
    protected $settings;
    protected $modinfo;
    protected $sectionnames;
    protected $defaultsectionname;

    /**
     * Constructor
     *
     * Init the required properties.
     */
    public function __construct() {
        global $PAGE, $COURSE;

        $this->page = $PAGE;
        $this->course = $COURSE;
        $this->courseformat = course_get_format($this->course);
        $this->courserenderer = $PAGE->get_renderer('format_bluegrid', 'course');
        $this->coursecontext = context_course::instance($this->course->id);
        $this->formatrenderer = $PAGE->get_renderer('format_bluegrid');
        $this->settings = $this->courseformat->get_settings();
        $this->modinfo = get_fast_modinfo($this->course);

        // Get section names
        $this->sectionnames = $this->section_names();
        $this->defaultsectionname = $this->default_section_name();
    }

    /**
     * Function to export the renderer data in a format that is suitable for the mustache template.
     *
     * @param renderer_base $output Used to do a final render of any components that need to be rendered for export.
     * @return stdClass|array
     */
    public function export_for_template(renderer_base $output) {
        // Get necessary default values required to display the UI.
        $editing = $this->page->user_is_editing();

        //error_log( print_r($output, true) );

        $export = new \stdClass();
        $export->editing = $editing;
        $export->brandcolor = $output->brandcolor;

        // Get context info
        $this->get_format_context($export, $editing);

        // Get course progress
        $this->get_course_progress($export);

        // Add section button
        if($editing) {
            $export->addsection = $this->formatrenderer->change_number_sections($this->course, 0);
        }

        return $export;
    }

    /**
     * Returns the context with the details required by the mustache.
     *
     * @param Object  $export
     * @param Boolean $editing
     * @throws \moodle_exception
     */
    protected function get_format_context(&$export, $editing) {

        // Get the course summary, presentation video, teaching plan and section names
        $export->coursesummary = $this->get_formatted_summary();
        $export->coursepresentationvideo = $this->settings['coursepresentationvideo'];
        $export->teachingplan = $this->get_teaching_plan();

        // Get the course teachers and tutors
        if($this->settings['show_teachers']) {
            $export->teachers = $this->get_users_info('editingteacher');
            $export->tutors = $this->get_users_info('teacher');
            $export->team = $this->get_merged_users_info($export->teachers, $export->tutors);
            $export->teamclass = $this->get_team_class(count($export->team));
        }

        // Setting up data for remaining sections.
        $export->section_zero  = $this->get_section_zero($editing);
        $export->sections = $this->get_sections_data($editing);
        $export->singlesection = (count($this->modinfo->get_section_info_all()) - 1) == 1;
    }

    /**
     * Get the formatted summary.
     *
     * @return string
     */
    protected function get_formatted_summary() {
        $summary = $this->course->summary;
        $summary = file_rewrite_pluginfile_urls($summary, 'pluginfile.php', $this->coursecontext->id, 'course', 'summary', null);
        $summary = format_text($summary, $this->course->summaryformat, array(), $this->course->id);

        return $summary;
    }

    /**
     * Get the default section name.
     *
     * @return false|\lang_string|string
     */
    protected function default_section_name() {
        switch ($this->settings['sectionname']) {
            case 0:
                return '';
            case 1:
                return get_string('sectionnameunit', 'format_bluegrid');
            case 2:
                return get_string('sectionnameclass', 'format_bluegrid');
            default:
                return '';
        }
    }

    /**
     * Get the section names defined in course configuration.
     *
     * @return array
     * @throws \dml_exception
     */
    protected function section_names() {
        global $DB;

        $sectionnamesjson = $DB->get_record('course_format_options', array(
            'courseid' => $this->course->id,
            'name'     => 'name_sections_json',
        ));

	if (!$sectionnamesjson) {
            return array();
	}

        return (array) json_decode($sectionnamesjson->value);
    }

    /**
     * Get the teaching plan
     *
     * @return moodle_url|null
     */
    protected function get_teaching_plan() {
        return '';
    }

    /**
     * Get the course progress for the user.
     *
     * @param $export
     */
    protected function get_course_progress(&$export) {
        global $USER;

        $completion = new \completion_info($this->course);

        // First, let's make sure completion is enabled.
        if( ! $completion->is_enabled()) {
            $export->completionenabled = false;

            return null;
        }

        $export->completionenabled = true;

        $percentage = progress::get_course_progress_percentage($this->course);
        if( ! is_null($percentage)) {
            $percentage = floor($percentage);
        }

        $export->completed = $completion->is_course_complete($USER->id);
        $export->courseprogress = $percentage;
    }

    /**
     * Get the users with by the given role.
     *
     * @param $roleshortname
     * @return array
     */
    protected function get_users_info($roleshortname) {
        global $PAGE;
        $roles = role_get_names($this->coursecontext);

        // Get the role id
        $roleid = null;
        $rolename = '';
        foreach ($roles as $role) {
            if($role->shortname == $roleshortname) {
                $roleid = $role->id;
                $rolename = $role->localname;
            }
        }
        if( ! $roleid) {
            return array();
        }

        // Get users with role
        $users = get_role_users($roleid, $this->coursecontext);
        foreach ($users as $key => $user) {
            $user->fullname = fullname($user);
            $user->imagealt = fullname($user);
            $pix = new \user_picture($user);
            $pix->size = 200;
            $user->picture = $pix->get_url($PAGE);
            $user->picture_alt = get_string('pictureof', '', $user->fullname);
            $user->url = (new moodle_url('/user/view.php', array(
                'id'     => $user->id,
                'course' => $this->course->id,
            )))->out(false);
            $user->rolename = $rolename;
        }

        return array_values($users);
    }

    /**
     * Get teachers and tutors information together.
     *
     * @param mixed ...$infos
     * @return array
     */
    protected function get_merged_users_info(...$infos) {
        $newinfos = array();
        foreach ($infos as $info) {
            foreach ($info as $user) {
                $key = array_search($user->fullname, array_column($newinfos, 'fullname'));
                if($key === false) {
                    $newinfos[] = $user;
                } else {
                    $newinfos[$key]->rolename = "{$newinfos[$key]->rolename} / {$user->rolename}";
                }
            }
        }

        return $newinfos;
    }

    /**
     * Get the right class for the team section.
     *
     * @param $count
     * @return string
     */
    protected function get_team_class($count) {
        switch ($count) {
            case 1:
                return 'presentation-team-member-wrapper-1';
            case 2:
                return 'presentation-team-member-wrapper-2';
            default:
                return 'presentation-team-member-wrapper-3';
        }
    }

    /**
     * Get the class for the section 0
     *
     * @param $count
     * @return string
     */
    protected function get_section_zero($editing){
        global $COURSE;
        $sectiondetails = new \stdClass();
        $sectiondetails->index = 0;
        $sectiondetails->sectionactivities = $this->courserenderer->course_section_cm_list($this->course, 0, 0);
        $sectiondetails->sectionactivities .= $this->courserenderer->course_section_add_cm_control($this->course, 0, 0);
        return $sectiondetails;
    }

    /**
     * Get the data for each section.
     *
     * @param $editing
     * @return array
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    protected function get_sections_data($editing) {
        $startfrom = 1;
        $end = $this->courseformat->get_last_section_number();
        $sections = array();
        for ($section = $startfrom; $section <= $end; $section ++) {
            $sectiondetails = new \stdClass();
            $sectiondetails->index = $section;

            // Get current section info.
            $currentsection = $this->modinfo->get_section_info($section);

            // Check if the user has permission to view this section or not.
            $showsection = $currentsection->uservisible || ($currentsection->visible && ! $currentsection->available && ! empty($currentsection->availableinfo));

            // Define if user can see the section
            $sectiondetails->showsection = $showsection;
            $sectiondetails->id = $currentsection->id;

            // If user cannot view section, go next
            if( ! $showsection) {
                continue;
            }

            // Get the title and description of the section
            if($editing) {
                $sectiondetails->title = $this->formatrenderer->section_title($currentsection, $this->course);
                $sectiondetails->optionmenu = $this->formatrenderer->section_right_content($currentsection, $this->course, false);
            } else {
                $sectiondetails->title = $this->courseformat->get_section_name($currentsection);
            }
            $sectiondetails->summary = $currentsection->summary;

            // Availability
            $sectiondetails->hiddenmessage = $this->formatrenderer->section_availability_message($currentsection, has_capability(
                'moodle/course:viewhiddensections',
                $this->coursecontext
            ));
            if($sectiondetails->hiddenmessage != "") {
                $sectiondetails->hidden = 1;
            } else {
                $sectiondetails->hidden = 0;
            }

            if($currentsection->availability){
                $restrictions = (array) json_decode($currentsection->availability);
            }else{
                $restrictions = array();
            }

            // Check Course Manager Plugin time configuration
            if($restrictions && count($restrictions['c']) == 1 && $restrictions['c'][0]->type == 'date') {
                eval("\$sectionreleased = " . time() . " " . $restrictions['c'][0]->d . " " . $restrictions['c'][0]->t . ";");
                if(sectionreleased) {
                    $sectiondetails->hidden = 0;
                }
            }

            // Get progress
            $sectiondetails->progress = $this->get_section_progress($currentsection);
            $sectiondetails->hasprogress = $sectiondetails->progress >= 0 && $sectiondetails->progress !== null;

            // Get activities
            $sectiondetails->sectionactivities = $this->courserenderer->course_section_cm_list($this->course, $currentsection, 0);
            $sectiondetails->sectionactivities .= $this->courserenderer->course_section_add_cm_control($this->course, $currentsection->section, 0);

            // Push to sections
            $sections[] = $sectiondetails;
        }

        foreach ($sections as $k => $section) {

            // Set name
            $section->name = $this->section_name($section->index, $section->id);

            // Previous
            if($k > 0) {
                $previoussection = $sections[$k - 1];
                $section->previous = $previoussection->index;
                $section->previousname = $this->section_name($previoussection->index, $previoussection->id);
                $section->previousishidden = $previoussection->hidden;
            }

            // Next
            if($k < count($sections) -1 ) {
                $nextsection = $sections[$k + 1];
                $section->next = $nextsection->index;
                $section->nextname = $this->section_name($nextsection->index, $nextsection->id);
                $section->nextishidden = $nextsection->hidden;
            }
        }

        return $sections;
    }

    /**
     * Get the course progress.
     *
     * @param $section
     * @return float|int|null
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    protected function get_section_progress($section) {
        $completioninfo = new \completion_info($this->course);
        if(empty($this->modinfo->sections[$section->section]) || ! $completioninfo->is_enabled()) {
            return null;
        }
        $total = 0;
        $complete = 0;
        $cancomplete = isloggedin() && ! isguestuser();
        foreach ($this->modinfo->sections[$section->section] as $cmid) {
            $thismod = $this->modinfo->cms[$cmid];
            // Labels are not interesting for students
            if($thismod->modname == 'label') {
                continue;
            }

            if($thismod->uservisible && $cancomplete && ($completioninfo->is_enabled($thismod) != COMPLETION_TRACKING_NONE)) {
                $total ++;
                $completiondata = $completioninfo->get_data($thismod, true);
                if($completiondata->completionstate == COMPLETION_COMPLETE || $completiondata->completionstate == COMPLETION_COMPLETE_PASS) {
                    $complete ++;
                }
            }
        }
        if($total > 0) {
            return round(($complete / $total) * 100, 0);
        }

        return 0;
    }

    /**
     * Get the section name defined in course format settings or the default name.
     * @param int $index The section index.
     * @param int $sectionid The section id.
     * @return string The name.
     */
    protected function section_name($index, $sectionid) {
        if(key_exists('name_section_' . $sectionid, $this->sectionnames)) {
            $sectionname = $this->sectionnames['name_section_' . $sectionid];
        }

        return isset($sectionname) && !empty($sectionname) ? $sectionname : $this->defaultsectionname . ' ' . $index;
    }
}
