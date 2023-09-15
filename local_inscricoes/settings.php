<?php
defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) { // needs this condition or there is error on login page
    $settings = new admin_settingpage('local_inscricoes', get_string('pluginname','local_inscricoes'));
    $ADMIN->add('localplugins', $settings);
    $settings->add(new admin_setting_heading('inscricoes_settings', '', get_string('pluginnamedesc', 'local_inscricoes')));
    $settings->add(new admin_setting_heading('inscricoes_webservice_settings', get_string('webservicesettings', 'local_inscricoes'), ''));

    $settings->add(new admin_setting_configtext('local_inscricoes/webservicelocation',
        get_string('webservicelocation','local_inscricoes'), get_string('webservicelocationdescription','local_inscricoes'), 'http://<your_site>/inscricoes/EngineSoap.class.php', PARAM_TEXT));

    $settings->add(new admin_setting_configtext('local_inscricoes/webserviceuri',
        get_string('webserviceuri','local_inscricoes'), get_string('webserviceuridescription','local_inscricoes'), 'http://<your_site>/inscricoes', PARAM_TEXT));

    $settings->add(new admin_setting_configtext('local_inscricoes/webservicetrace',
        get_string('webservicetrace','local_inscricoes'), get_string('webservicetracedescription','local_inscricoes'), 1, PARAM_INT));

    $settings->add(new admin_setting_configtext('local_inscricoes/webserviceencoding',
        get_string('webserviceencoding','local_inscricoes'), get_string('webserviceencodingdescription','local_inscricoes'), 'UTF 8', PARAM_TEXT));

    $settings->add(new admin_setting_configpasswordunmask('local_inscricoes/webservicekey',
        get_string('webservicekey','local_inscricoes'), get_string('webservicekeydescription','local_inscricoes'), null, PARAM_TEXT));
}

