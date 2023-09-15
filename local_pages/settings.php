<?php
defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) { // needs this condition or there is error on login page
    $settings = new admin_settingpage('local_pages', get_string('pluginname','local_pages'));
    $ADMIN->add('localplugins', $settings);
    $settings->add(new admin_setting_heading('pages_settings', '', get_string('pluginnamedesc', 'local_pages')));
    $settings->add(new admin_setting_heading('pages_webservice_settings', get_string('webservicesettings', 'local_pages'), ''));

    $settings->add(new admin_setting_configtext('local_pages/webservicelocation',
        get_string('webservicelocation','local_pages'), get_string('webservicelocationdescription','local_pages'), 'https://servicos.<your_site>/avaliacao/EngineSoap.class.php', PARAM_TEXT));

    $settings->add(new admin_setting_configtext('local_pages/webserviceuri',
        get_string('webserviceuri','local_pages'), get_string('webserviceuridescription','local_pages'), 'https://servicos.<your_site>/avaliacao', PARAM_TEXT));

    $settings->add(new admin_setting_configtext('local_pages/webservicetrace',
        get_string('webservicetrace','local_pages'), get_string('webservicetracedescription','local_pages'), 1, PARAM_INT));

    $settings->add(new admin_setting_configtext('local_pages/webserviceencoding',
        get_string('webserviceencoding','local_pages'), get_string('webserviceencodingdescription','local_pages'), 'UTF 8', PARAM_TEXT));

    $settings->add(new admin_setting_configpasswordunmask('local_pages/webservicekey',
        get_string('webservicekey','local_pages'), get_string('webservicekeydescription','local_pages'), null, PARAM_TEXT));
}

