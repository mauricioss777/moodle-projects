<?php

require_once('../../config.php');

$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('mydashboard');
$PAGE->set_title(get_string('googleforeducation', 'local_pages'));
$PAGE->set_heading(get_string('googleforeducation', 'local_pages'));
$PAGE->requires->css('/local/pages/style/custom.css');

$url = $CFG->wwwroot . '/local/pages/googleforeducation.php';
$pix = $CFG->wwwroot . '/local/pages/pix/l/';

$PAGE->set_url($url);

if( ! isloggedin()) {
    $SESSION->wantsurl = $url;
    redirect(get_login_url());
}
echo $OUTPUT->header();

$data = [
    'products' => [
        [
            'logo'        => $pix . 'gmail.png',
            'name'        => get_string('appgmailname', 'local_pages'),
            'description' => get_string('appgmaildescription', 'local_pages'),
            'link'        => 'https://www.google.com/gmail',
        ],
        [
            'logo'        => $pix . 'drive.png',
            'name'        => get_string('appdrivename', 'local_pages'),
            'description' => get_string('appdrivedescription', 'local_pages'),
            'link'        => 'https://www.google.com/drive',
        ],
        [
            'logo'        => $pix . 'calendar.png',
            'name'        => get_string('appcalendarname', 'local_pages'),
            'description' => get_string('appcalendardescription', 'local_pages'),
            'link'        => 'https://www.google.com/calendar',
        ],
        [
            'logo'        => $pix . 'chat.png',
            'name'        => get_string('appchatname', 'local_pages'),
            'description' => get_string('appchatdescription', 'local_pages'),
            'link'        => 'https://mail.google.com/chat',
        ],
        [
            'logo'        => $pix . 'docs.png',
            'name'        => get_string('appdocsname', 'local_pages'),
            'description' => get_string('appdocsdescription', 'local_pages'),
            'link'        => 'https://docs.google.com/document',
        ],
        [
            'logo'        => $pix . 'sheets.png',
            'name'        => get_string('appsheetsname', 'local_pages'),
            'description' => get_string('appsheetsdescription', 'local_pages'),
            'link'        => 'https://docs.google.com/spreadsheets',
        ],
        [
            'logo'        => $pix . 'slides.png',
            'name'        => get_string('appslidesname', 'local_pages'),
            'description' => get_string('appslidesdescription', 'local_pages'),
            'link'        => 'https://docs.google.com/presentation',
        ],
        [
            'logo'        => $pix . 'forms.png',
            'name'        => get_string('appformsname', 'local_pages'),
            'description' => get_string('appformsdescription', 'local_pages'),
            'link'        => 'https://www.google.com/forms',
        ],
        [
            'logo'        => $pix . 'photos.png',
            'name'        => get_string('appphotosname', 'local_pages'),
            'description' => get_string('appphotosdescription', 'local_pages'),
            'link'        => 'https://www.google.com/photos',
        ],
        [
            'logo'        => $pix . 'keep.png',
            'name'        => get_string('appkeepname', 'local_pages'),
            'description' => get_string('appkeepdescription', 'local_pages'),
            'link'        => 'https://hangouts.google.com',
        ],
        [
            'logo'        => $pix . 'cloud.png',
            'name'        => get_string('appcloudname', 'local_pages'),
            'description' => get_string('appclouddescription', 'local_pages'),
            'link'        => 'https://cloud.google.com',
        ],
        [
            'logo'        => $pix . 'sites.png',
            'name'        => get_string('appsitesname', 'local_pages'),
            'description' => get_string('appsitesdescription', 'local_pages'),
            'link'        => 'https://sites.google.com',
        ],
    ],
    'logo'     => [
        'googleforeducation' => $pix . 'google_for_education.png',
    ],
];

echo $OUTPUT->render_from_template('local_pages/googleforeducation', $data);

echo $OUTPUT->footer();

?>
