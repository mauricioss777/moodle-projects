<?php

require_once 'classes/pdf.php';

/**
 * Summary of presentation_add_instance
 * @param mixed $presentation
 * @return bool|int
 */
function presentation_add_instance($presentation)
{
    global $DB;

    $presentation->timecreated = time();
    $presentation->timemodified = time();

    $cmid = $presentation->coursemodule;
    $draftitemid = $presentation->files;
    $context = context_module::instance($cmid);
    $options = array('subdirs' => true, 'embed' => false);
    file_save_draft_area_files($draftitemid, $context->id, 'mod_presentation', 'content', 0, $options);

    $test = PresentationPdf::test_gs_path();
    if($test->status != PresentationPdf::GSPATH_OK){
        throw new Exception(get_string('error:enablegs','mod_presentation'));
    }
	
	presentation_process_file($cmid);

    return $DB->insert_record("presentation", $presentation);
}

/**
 * Summary of presentation_update_instance
 * @param mixed $presentation
 * @param mixed $a
 * @return bool
 */
function presentation_update_instance($presentation, $a)
{
    global $DB, $CFG;

    //include_once $CFG->dirroot . '/mod/presentation/classes/task/convert_presentation.php';

    $presentation->id = $presentation->instance;
    $presentation->timemodified = time();

    $cmid = $presentation->coursemodule;

    //presentation_process_file($cmid);

    //$task = new \mod_presentation\task\convert_presentation();
    //$task->set_cmid($cmid);
    //\core\task\manager::queue_adhoc_task($task, true);

    /*$draftitemid = $presentation->files;
    $context = context_module::instance($cmid);
    $options = array('subdirs' => true, 'embed' => false);
    file_save_draft_area_files($draftitemid, $context->id, 'mod_presentation', 'content', 0, $options);*/

    return $DB->update_record("presentation", $presentation);
}

/**
 * Summary of presentation_delete_instance
 * @param mixed $id
 * @return bool
 */
function presentation_delete_instance($id)
{
    global $DB;

    if (!$presentation = $DB->get_record('presentation', array('id'=>$id))) {
        return false;
    }

    $cm = get_coursemodule_from_instance('presentation', $id);
    \core_completion\api::update_completion_date_event($cm->id, 'presentation', $id, null);

    // note: all context files are deleted automatically
    $DB->delete_records('presentation', array('id'=>$presentation->id));

    return true;
}

/**
 * Summary of presentation_process_file
 * @param mixed $id
 * @param mixed $cm
 * @return void
 */
function presentation_process_file($cmid)
{
	global $DB;

    $context = context_module::instance($cmid);

    $fs = get_file_storage();

    $files = $fs->get_area_files($context->id, 'mod_presentation', 'content', 0);
    $presentation_file = null;

    foreach ($files as $file) {
        if ($file->get_filename() == '.') {
            continue;
        }
        $presentation_file = $file;
    }

    $tmpdir = \make_temp_directory('presentation/pageimages/');
    $presentation_file->copy_content_to($tmpdir . 'source');

    $record = new \stdClass();
    $record->contextid = $context->id;
    $record->component = 'mod_presentation';
    $record->filearea = 'presentation';
    $record->filepath = '/';
    $record->itemid = 0;
    $fs = get_file_storage();

    $pdf = new PresentationPdf();

    $pdf->set_image_folder($tmpdir);
    $pagecount = $pdf->set_pdf($tmpdir . 'source');

    $pages = $pdf->get_images();

    for ($i = 0; $i < $pagecount; $i++) {
        try {
            $image = $pages[$i];
        } catch (\Throwable $th) {
            //throw $th;
        }
        $record->sortorder = $i;
        $record->filename = basename($image);
        $files[$i] = $fs->create_file_from_pathname($record, $tmpdir . '/' . $image);
        @unlink($tmpdir . '/' . $image);
    }

    $pdf->Close(); // PDF loaded and never saved/outputted needs to be closed.
    @rmdir($tmpdir);

}

/**
 * Summary of presentation_pluginfile
 * @param mixed $course
 * @param mixed $cm
 * @param mixed $context
 * @param mixed $filearea
 * @param mixed $args
 * @param mixed $forcedownload
 * @param mixed $options
 * @return bool
 */
function presentation_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array())
{

    require_login($course, true, $cm);

    $itemid = array_shift($args);

    $filename = array_pop($args);
    if (!$args) {
        $filepath = '/';
    } else {
        $filepath = '/' . implode('/', $args) . '/';
    }

    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'mod_presentation', $filearea, $itemid, $filepath, $filename);

    if (!$file) {
        return false;
    }

    send_stored_file($file, 86400, 0, $forcedownload, $options);
}


/**
 * Summary of presentation_file_url
 * @param mixed $cm
 * @param mixed $presentation
 * @return bool|moodle_url|null
 */
function presentation_file_url($cm, $presentation)
{
    global $CFG;

    if(!$presentation->download){
        return false;
    }

    $fs = get_file_storage();
    $context = context_module::instance($cm->id);

    $presentation_file = null;
    $files = $fs->get_area_files($context->id, 'mod_presentation', 'content');

    foreach ($files as $file) {

        if ($file->get_filename() == '.') {
            continue;
        }

        $presentation_file = moodle_url::make_pluginfile_url(
            $context->id,
            'mod_presentation',
            'content',
            0,
            '/',
            $file->get_filename()
        );
    }

    return $presentation_file;

}

/**
 * Summary of presentation_files_url
 * @param mixed $cm
 * @return array<moodle_url>
 */
function presentation_files_url($cm)
{
    global $CFG;

    $fs = get_file_storage();
    $context = context_module::instance($cm->id);

    $presentation_files = [];
    $files = $fs->get_area_files($context->id, 'mod_presentation', 'presentation');

    foreach ($files as $file) {
        if ($file->get_filename() == '.') {
            continue;
        }
        $presentation_files[] = moodle_url::make_pluginfile_url(
            $context->id,
            'mod_presentation',
            'presentation',
            0,
            '/',
            $file->get_filename()
        );
    }

    return $presentation_files;

}


/**
 * Summary of presentation_cm_info_dynamic
 * @param cm_info $cm
 * @return void
 */
function presentation_cm_info_dynamic(cm_info $cm)
{
    global $DB;

    $presentation = $DB->get_record('presentation', ['id' => $cm->instance]);

    //if ($presentation->embed) {
        //$cm->set_no_view_link();
        //$cm->set_custom_cmlist_item(false);
    //}
}

/**
 * Summary of presentation_cm_info_view
 * @param cm_info $cm
 * @return void
 */
function presentation_cm_info_view(cm_info $cm)
{
    global $PAGE, $CFG, $DB;

    $presentation = $DB->get_record('presentation', ['id' => $cm->instance]);
    $context = context_module::instance($cm->id);

    if (!$presentation->embed) {
        $cm->set_custom_cmlist_item(false);
        return;
    }

    $url = presentation_files_url($cm);
    $download = presentation_file_url($cm, $presentation);

    $renderer = $PAGE->get_renderer('mod_presentation');

    $PAGE->requires->js_call_amd('mod_presentation/presentation', 'init', [$cm->id, 1, sizeof($url)]);

    $cm->set_content(
        $renderer->render_presentation(new \mod_presentation\output\mod_presentation_presentation($cm->id, $url, $download)),
        true
    );

}

/**
 * Summary of presentation_supports
 * @param mixed $feature
 * @return bool|int|string|null
 */
function presentation_supports($feature)
{
    switch ($feature) {
        case FEATURE_MOD_ARCHETYPE:
            return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_MOD_PURPOSE:
            return MOD_PURPOSE_CONTENT;
        case FEATURE_GROUPS:
            return false;
        case FEATURE_GROUPINGS:
            return false;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return false;
        case FEATURE_GRADE_HAS_GRADE:
            return false;
        case FEATURE_GRADE_OUTCOMES:
            return false;
        case FEATURE_NO_VIEW_LINK:
            return false;
        case FEATURE_BACKUP_MOODLE2:          
            return true;
        default:
            return null;
    }
}
