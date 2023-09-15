<?php
defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) { // needs this condition or there is error on login page
    $settings = new admin_settingpage('local_alfa', get_string('pluginname','local_alfa'));
    $ADMIN->add('localplugins', $settings);
    $settings->add(new admin_setting_heading('alfa_settings', '', get_string('pluginnamedesc', 'local_alfa')));
    $settings->add(new admin_setting_heading('alfa_webservice_settings', get_string('webservicesettings', 'local_alfa'), ''));

    $settings->add(new admin_setting_configtext('local_alfa/webservicelocation',
        get_string('webservicelocation','local_alfa'), get_string('webservicelocationdescription','local_alfa'), 'http://<your_site>/alfa/EngineSoap.class.php', PARAM_TEXT));

    $settings->add(new admin_setting_configtext('local_alfa/webserviceuri',
        get_string('webserviceuri','local_alfa'), get_string('webserviceuridescription','local_alfa'), 'http://<your_site>/alfa', PARAM_TEXT));

    $settings->add(new admin_setting_configtext('local_alfa/webservicetrace',
        get_string('webservicetrace','local_alfa'), get_string('webservicetracedescription','local_alfa'), 1, PARAM_INT));

    $settings->add(new admin_setting_configtext('local_alfa/webserviceencoding',
        get_string('webserviceencoding','local_alfa'), get_string('webserviceencodingdescription','local_alfa'), 'UTF 8', PARAM_TEXT));

    $settings->add(new admin_setting_configpasswordunmask('local_alfa/webservicekey',
        get_string('webservicekey','local_alfa'), get_string('webservicekeydescription','local_alfa'), null, PARAM_TEXT));
}

