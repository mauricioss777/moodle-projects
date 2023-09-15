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

use action_link;
use action_menu;
use moodle_url;
use navigation_node;
use pix_icon;
use renderable;
use renderer_base;
use stdClass;
use templatable;

/**
 * This file contains the definition for the renderable class for the header of the BlueGrid Course Format.
 *
 * @package   format_bluegrid
 * @copyright 2020 onwards, Univates
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Christian Bayer (christian.bayer@universo.univates.br)
 */
class format_bluegrid_header implements renderable, templatable {

    protected $page;
    protected $output;
    protected $course;
    protected $courserenderer;
    protected $settings;

    /**
     * Constructor
     *
     * Init the required properties.
     */
    public function __construct() {
        global $PAGE, $COURSE;

        $this->page = $PAGE;
        $this->course = $COURSE;
        $this->settings = course_get_format($this->course)->get_settings();
    }

    /**
     * Function to export the renderer data in a format that is suitable for the mustache template.
     *
     * @param renderer_base $output Used to do a final render of any components that need to be rendered for export.
     * @return stdClass|array
     */
    public function export_for_template(renderer_base $output) {
        // Set renderer
        $this->courserenderer = $output;

        // Export properties
        $export = new \stdClass();
        if($this->settings['show_course_name']) {
            $export->coursename = $this->course->fullname;
        }
        $export->tabs = $this->navigation_tabs();
        $export->image = $this->get_course_image();

        return $export;
    }

    /**
     * Returns the navigation tab items for the BlueGrid banner.
     *
     * @return array The tabs.
     */
    private function navigation_tabs() {
        // Get the active tab
        $activetab = '';
        $currenturl = $this->page->url;
        if(strstr($currenturl->get_path(), 'grade')) {
            $activetab = 'grades';
        } else if(strstr($currenturl->get_path(), 'user') > - 1) {
            $activetab = 'participants';
        } else if(strstr($currenturl->get_path(), 'calendar') > - 1) {
            $activetab = 'calendar';
        }

        $tabs = [];

        // Overview tab
        if($this->settings['show_overview_on_menu']) {
            if(strstr($currenturl->get_path(), 'course/view')) {
                $tabs[] = [
                    'url'   => '#overview',
                    'title' => get_string('overview', 'format_bluegrid'),
                ];
            } else {
                $tabs[] = [
                    'url'   => new moodle_url('/course/view.php?id=' . $this->course->id . '#overview'),
                    'title' => get_string('overview', 'format_bluegrid'),
                ];
            }
        }

        // Content tab
        if( ! ($this->settings["show_overview_on_menu"] == 0 &&
            $this->settings["show_participants_on_menu"] == 0 &&
            $this->settings["show_grades_on_menu"] == 0 &&
            $this->settings["show_calendar_on_menu"] == 0)) {
            if(strstr($currenturl->get_path(), 'course/view')) {
                $tabs[] = [
                    'url'   => '#content',
                    'title' => get_string('content', 'format_bluegrid'),
                ];
            } else {
                $tabs[] = [
                    'url'   => new moodle_url('/course/view.php?id=' . $this->course->id . '#content'),
                    'title' => get_string('content', 'format_bluegrid'),
                ];
            }
        }

        // Participants tab
        if($this->settings['show_participants_on_menu']) {
            $tabs[] = [
                'url'   => new moodle_url('/user/index.php?id=' . $this->course->id),
                'title' => get_string('participants'),
                'class' => $activetab == 'participants' ? ' active' : '',
            ];
        }

        // Grades tab
        if($this->settings['show_grades_on_menu']) {
            $tabs[] = [
                'url'   => new moodle_url('/grade/report/user/index.php?id=' . $this->course->id),
                'title' => get_string('grades'),
                'class' => $activetab == 'grades' ? ' active' : '',
            ];
        }

        // Calendar tab
        if($this->settings['show_calendar_on_menu']) {
            $tabs[] = [
                'url'   => new moodle_url('/calendar/view.php?view=month&course=' . $this->course->id),
                'title' => get_string('calendar', 'calendar'),
                'class' => $activetab == 'calendar' ? ' active' : '',
            ];
        }

        // Settings tab
        if($settings = $this->context_header_settings_menu()){
            $tabs[] = [
                'url'   => new moodle_url('/course/admin.php?courseid=' . $this->course->id),
                'title' => get_string('settings', 'format_bluegrid'),
                'class' => $activetab == 'settings' ? ' active' : '',
            ];
        }

        return $tabs;
    }

    /**
     * Returns the context header settings menu for the BlueGrid Course Format.
     *
     * @return mixed
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    private function context_header_settings_menu() {
        global $USER;

        // Get the settings node for this page
        if( ! $settingsnode = $this->page->settingsnav->find('courseadmin', navigation_node::TYPE_COURSE)) {
            return false;
        }

        // Create a new action menu
        $menu = new action_menu();

        //Turn edit on/off
        if($this->page->user_allowed_editing()) {
            if($this->page->user_is_editing()) {
                $text = get_string('turneditingoff');
                $url = new moodle_url('/course/view.php', array('id'      => $this->course->id,
                                                                'sesskey' => $USER->sesskey,
                                                                'edit'    => 'off',
                ));
                $link = new action_link($url, $text, null, null, new pix_icon('i/edit', $text));
                $menu->add_secondary_action($link);
            } else {
                $text = get_string('turneditingon');
                $url = new moodle_url('/course/view.php', array('id'      => $this->course->id,
                                                                'sesskey' => $USER->sesskey,
                                                                'edit'    => 'on',
                ));
                $link = new action_link($url, $text, null, null, new pix_icon('i/edit', $text));
                $menu->add_secondary_action($link);
            }
        }

        // Build the course menu
        if($this->build_action_menu_from_navigation($menu, $settingsnode, false, true)) {
            $text = get_string('morenavigationlinks');
            $url = new moodle_url('/course/admin.php', array('courseid' => $this->page->course->id));
            $link = new action_link($url, $text, null, null, new pix_icon('t/edit', $text));
            $menu->add_secondary_action($link);
        }

        // We don't want the class icon there!
        foreach ($menu->get_secondary_actions() as $action) {
            if($action instanceof \action_menu_link && $action->has_class('icon')) {
                $action->attributes['class'] = preg_replace('/(^|\s+)icon(\s+|$)/i', '', $action->attributes['class']);
            }
        }

        return $menu->export_for_template($this->courserenderer);
    }

    /**
     * Take a node in the nav tree and make an action menu out of it.
     * The links are injected in the action menu.
     *
     * @param action_menu     $menu
     * @param navigation_node $node
     * @param boolean         $indent
     * @param boolean         $onlytopleafnodes
     * @return boolean nodesskipped - True if nodes were skipped in building the menu
     */
    private function build_action_menu_from_navigation(action_menu $menu,
                                                       navigation_node $node,
                                                       $indent = false,
                                                       $onlytopleafnodes = false) {
        $skipped = false;
        // Build an action menu based on the visible nodes from this navigation tree.
        foreach ($node->children as $menuitem) {
            if($menuitem->display) {
                if($onlytopleafnodes && $menuitem->children->count()) {
                    $skipped = true;
                    continue;
                }
                if($menuitem->action) {
                    if($menuitem->action instanceof action_link) {
                        $link = $menuitem->action;
                        // Give preference to setting icon over action icon.
                        if( ! empty($menuitem->icon)) {
                            $link->icon = $menuitem->icon;
                        }
                    } else {
                        $link = new action_link($menuitem->action, $menuitem->text, null, null, $menuitem->icon);
                    }
                } else {
                    if($onlytopleafnodes) {
                        $skipped = true;
                        continue;
                    }
                    $link = new action_link(new moodle_url('#'), $menuitem->text, null, ['disabled' => true], $menuitem->icon);
                }
                if($indent) {
                    $link->add_class('ml-4');
                }
                if( ! empty($menuitem->classes)) {
                    $link->add_class(implode(" ", $menuitem->classes));
                }

                $menu->add_secondary_action($link);
                $skipped = $skipped || $this->build_action_menu_from_navigation($menu, $menuitem, true);
            }
        }

        return $skipped;
    }

    private function get_course_image() {
        global $CFG;
        // Get course list instance.
        $courseobj = new \core_course_list_element($this->course);
        foreach ($courseobj->get_course_overviewfiles() as $file) {
            $isimage = $file->is_valid_image();
            $courseimage = file_encode_url(
                "$CFG->wwwroot/pluginfile.php",
                '/' . $file->get_contextid() . '/' . $file->get_component() . '/' .
                $file->get_filearea() . $file->get_filepath() . $file->get_filename(),
                ! $isimage
            );
            if($isimage) {
                break;
            }
        }
        if(empty($courseimage)) {
            $courseimage = $this->courserenderer->get_generated_image_for_id($this->course->id);
        }
        return $courseimage;
    }

}
