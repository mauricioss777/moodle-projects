<?php

require_once('../../config.php');

$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('mydashboard');
$PAGE->set_title("Biblioteca Virtual");
$PAGE->requires->css('/local/pages/style/custom.css');

$url = $CFG->wwwroot . '/local/pages/bibliotecavirtual.php';
$pix = $CFG->wwwroot . '/local/pages/pix/';

$PAGE->set_url($url);

if( ! isloggedin()) {
    $SESSION->wantsurl = $url;
    redirect(get_login_url());
}

$token = 'z8fcRunvtpVMosDCbvoVAZUTuIi5oaS7';
$data = [
    'login' => $USER->username,
    'token' => md5($USER->username . $token),
];

echo $OUTPUT->header();

echo $OUTPUT->render_from_template('local_pages/bibliotecavirtual', $data);
//echo print_r($data,true);

echo $OUTPUT->footer();

?>
