<?php

require_once('../../config.php');

$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('mydashboard');
$PAGE->set_title("Minha Biblioteca");
$PAGE->requires->css('/local/pages/style/custom.css');

$url = $CFG->wwwroot . '/local/pages/minhabiblioteca.php';
$pix = $CFG->wwwroot . '/local/pages/pix/';

$PAGE->set_url($url);

if( ! isloggedin()) {
    $SESSION->wantsurl = $url;
    redirect(get_login_url());
}
echo $OUTPUT->header();

$url = 'https://digitallibrary.zbra.com.br/DigitalLibraryIntegrationService/AuthenticatedUrl';
$key = '62f191e1-c9b9-443e-aec8-524d6b219ea0';
$xml = '<?xml version="1.0"?>
<CreateAuthenticatedUrlRequest 
xmlns:xsd="http://www.w3.org/2001/XMLSchema" 
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xmlns="http://dli.zbra.com.br">
<FirstName>' . $USER->firstname . '</FirstName>
<LastName>' . $USER->lastname . '</LastName>
<Email>' . $USER->email . '</Email>
</CreateAuthenticatedUrlRequest>';

//setting the curl parameters.
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_VERBOSE, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/xml; charset=utf-8',
    'X-DigitalLibraryIntegration-API-Key: ' . $key,
));
curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);

$response = curl_exec($ch);
curl_close($ch);

$oXML = new SimpleXMLElement($response);
$data = [
    'iframeurl' => $oXML->AuthenticatedUrl,
];

echo $OUTPUT->render_from_template('local_pages/minhabiblioteca', $data);

echo $OUTPUT->footer();