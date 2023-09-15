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
 * Main user interface for AlfaCertificados
 *
 * @package   mod_alfacertificados
 * @copyright 2020 onwards, Univates
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Artur Henrique Welp      (ahwelp@universo.univates.br)
 */

require_once('../../config.php');
require_once('lib.php');
 
$id     = required_param('id', PARAM_INT);
$userid = optional_param('userid', 0, PARAM_INT);

// Get course and course module
list ($course, $cm) = get_course_and_cm_from_cmid($id, 'alfacertificados');
require_course_login($course, true, $cm);

$context = context_module::instance( $cm->id );

//Load the module and the course, also create a tem var for the way it will be generated
$course = $DB->get_record_sql("SELECT * FROM {course} WHERE id = (SELECT course FROM {course_modules} WHERE id = ?)", [$id]);
$module = $DB->get_record_sql("SELECT * FROM {course_modules} WHERE id = ?", [$id]);
$instance = $DB->get_record('alfacertificados', ['id' => $module->instance]);

$PAGE->set_url('/mod/alfacertificados/view.php', array('id' => $cm->id));
$PAGE->set_title( $instance->name );
$PAGE->set_cacheable(false);
$PAGE->requires->js_call_amd( 'mod_alfacertificados/iframe_viwer' );

echo $OUTPUT->header();
echo $OUTPUT->heading($instance->name);

// If you can generate for other users
if(has_capability('mod/alfacertificados:viewother', $context) ){
    echo alfacertificados_get_students_combo();
}

// If you can't but is trying. Reset the user id
if(!has_capability('mod/alfacertificados:viewother', $context) && $userid != 0 ){
    $userid = 0;
}

// If you can but didn't select one. You probably do not need to generate
if( has_capability('mod/alfacertificados:viewother', $context) && $userid == 0 ){
    echo $OUTPUT->footer();
    die;
}

$js = 'var element = document.getElementById("loader"); element.classList.add("hidden");';
echo '<div id="loader" style="text-align:center;"><img src="'.$CFG->wwwroot . '/mod/alfacertificados/pix/loader.gif'.'"/></div>';
echo "<div style='width:100%; text-align:center;'><iframe id='pdf_document' style='width:60%;' onload='".$js."' src='".$CFG->wwwroot."/mod/alfacertificados/view_document.php?id=".$cm->id."&userid=".$userid."' ></iframe>";
echo "<div id='download' class='hidden'> <a class='btn btn-success' download href='".$CFG->wwwroot."/mod/alfacertificados/view_document.php?id=".$cm->id."&userid=".$userid."'>Download</a> </div>  </div>";
echo $OUTPUT->footer();

