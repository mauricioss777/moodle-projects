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

require_once('../../config.php');
require_once('lib.php');

$id = optional_param('id', 0, PARAM_INT);    // Course Module ID.

if ($id) {
    $PAGE->set_url('/mod/presentation/index.php', ['id' => $id]);
    if (!$cm = get_coursemodule_from_id('presentation', $id, 0, true)) {
        throw new \moodle_exception('invalidcoursemodule');
    }

    if (!$course = $DB->get_record('course', ['id' => $cm->course])) {
        throw new \moodle_exception('coursemisconf');
    }

    if (!$presentation = $DB->get_record('presentation', ['id' => $cm->instance])) {
        throw new \moodle_exception('invalidcoursemodule');
    }
}

require_login($course, true, $cm);

if($presentation->embed){
    redirect(new \moodle_url('/course/view.php', array('id' => $course->id, 'section' => $cm->sectionnum), 'module-' . $id));
}

$PAGE->set_url('/mod/presentation/view.php', array('id' => $cm->id));
$renderer = $PAGE->get_renderer('mod_presentation');

$PAGE->add_body_class('limitedwidth');
$PAGE->set_title($course->shortname.': '.$presentation->name);
$PAGE->set_heading($course->fullname);
$PAGE->set_activity_record($presentation);
if (!$PAGE->activityheader->is_title_allowed()) {
    $activityheader['title'] = "";
}

$url = presentation_files_url($cm);
$download = presentation_file_url($cm, $presentation);

$PAGE->requires->js_call_amd('mod_presentation/presentation', 'init', [$id, 1, sizeof($url)]);

echo $OUTPUT->header();
echo $renderer->render_presentation(new \mod_presentation\output\mod_presentation_presentation($cm->id, $url, $download));
echo $OUTPUT->footer();