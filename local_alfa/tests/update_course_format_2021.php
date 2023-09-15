<?php

/**
 * This script was built to update the course formats, depreciating the
 * 'topicsunivates' and 'topicsead' formats, changing them to 'topics'
 * and 'bluegrid', respectively.
 *
 * @copyright 2020 onwards, Univates
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Christian Bayer (christian.bayer@universo.univates.br)
 */

define('CLI_SCRIPT', true);

ini_set("error_log", "/tmp/update_course_format_2021.log");

require_once('../../../config.php');

global $USER, $DB;

// Run
update_course_format('topicsead', 'bluegrid');
update_course_format('topicsunivates', 'topics');

function update_course_format($oldformat, $newformat)
{
    global $DB;

    echo "####################################################################################################\n";
    echo "# Updating courses from format \"{$oldformat}\" to \"$newformat\"\n";
    echo "####################################################################################################\n";
    echo "# Starting...\n";

    // Get the courses
    $courses = $DB->get_records('course', array('format' => $oldformat), 'id');

    // Update each course
    foreach ($courses as $course) {
        echo "# Updating course {$course->id} - {$course->fullname}\n";

        // Update the course with the new data
        $course->format = $newformat;
        $course->timemodified = time();
        $DB->update_record('course', $course);

        // Make sure the modinfo cache is reset
        rebuild_course_cache($course->id);

        // update course format options with full course data
        course_get_format($course->id)->update_course_format_options(array());
    }

    echo "####################################################################################################\n";
    echo "# Finish!\n";
    echo "####################################################################################################\n\n";
}

