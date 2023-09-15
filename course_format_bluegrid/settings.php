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

defined('MOODLE_INTERNAL') || die;

global $CFG;

require_once($CFG->dirroot . '/course/format/bluegrid/classes/settings_controller.php');

if ($ADMIN->fulltree) {
    // Get the setting controller.
    $settingcontroller = \format_bluegrid\SettingsController::getinstance();

    // Default presentation video
    $name = 'format_bluegrid/defaultcoursepresentationvideo';
    $title = get_string('coursepresentationvideo', 'format_bluegrid');
    $description = get_string('coursepresentationvideo_help', 'format_bluegrid');
    $default = '';
    $settings->add(new admin_setting_configtext($name, $title, $description, $default, PARAM_RAW));

    // Default section name
    $name = 'format_bluegrid/defaultsectionname';
    $title = get_string('defaultsectionname', 'format_bluegrid');
    $description = get_string('defaultsectionname_help', 'format_bluegrid');
    $default = 1;
    $choices = array(
        0 => get_string('sectionnamenone', 'format_bluegrid'),
        1 => get_string('sectionnameunit', 'format_bluegrid'),
        2 => get_string('sectionnameclass', 'format_bluegrid'),
        3 => get_string('sectionfullblank', 'format_bluegrid'),
    );
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

    // Default test section
    $name = 'format_bluegrid/testsection';
    $title = get_string('testsection', 'format_bluegrid');
    $description = get_string('testsection_help', 'format_bluegrid');
    $default = 1;
    $choices = array(
        0 => get_string('no'),
        1 => get_string('yes'),
    );
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

    // Show teachers
    $name = 'format_bluegrid/show_teachers';
    $title = get_string('showteachers', 'format_bluegrid');
    $description = get_string('showteachers_help', 'format_bluegrid');
    $default = 1;
    $choices = array(
        0 => get_string('no'),
        1 => get_string('yes'),
    );
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

    $brandcolourdefaults = array(
        '#0F6CBF' => get_string('colourblue', 'format_bluegrid'),
        '#00A9CE' => get_string('colourlightblue', 'format_bluegrid'),
        '#7A9A01' => get_string('colourgreen', 'format_bluegrid'),
        '#009681' => get_string('colourdarkgreen', 'format_bluegrid'),
        '#D13C3C' => get_string('colourred', 'format_bluegrid'),
        '#772583' => get_string('colourpurple', 'format_bluegrid'),
        '#E5AC30' => get_string('colourorange', 'format_bluegrid'),
    );

    // Default test section
    $name = 'format_bluegrid/brandcolor';
    $title = get_string('brandcolor', 'format_bluegrid');
    $description = get_string('brandcolor_help', 'format_bluegrid');
    $default = '#0F6CBF';
    $choices = $brandcolourdefaults;
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

}