<?php
require_once('../../../config.php');
require_once('../../../lib/coursecatlib.php');
require_once('../../../course/lib.php');

if( ! is_siteadmin()) {
    die('Você não tem permissão de executar este script.');
}

die;

global $CFG, $DB;

// Search for templates with the script
$records = $DB->get_records_sql('SELECT * from {data} WHERE ' . $DB->sql_like('singletemplate', ':singletemplate'), array('singletemplate' => '%var username%'));

$newsingletemplatescript = "<script>
window.addEventListener('load', function() {
    // Get the user name from toolbar
    var username = jQuery('#site-navbar-collapse').find('.username').html();
    if(username.indexOf('viewingas') > -1) {
        username = jQuery('#site-navbar-collapse').find('.username .value').html();
    }
    // Check if is a student
    if (jQuery('#site-navbar-collapse').html().indexOf('switchrole=-1') < 0) {
        if (jQuery('#actualitem').find('.student').html().indexOf(username) < 0) {
            jQuery('#actualitem').addClass('hidden');
            jQuery('#other').removeClass('hidden');
        }
    }
});
</script>";

$newlisttemplatescript = "<script>
window.addEventListener('load', function () {
    // Get the user name from toolbar
    var username = jQuery('#site-navbar-collapse').find('.username').html();
    if(username.indexOf('viewingas') > -1) {
        username = jQuery('#site-navbar-collapse').find('.username .value').html();
    }
    // Default role
    var role = 'student';
    // Check if is teacher
    if (jQuery('#site-navbar-collapse').html().indexOf('switchrole=-1') > -1) {
        role = 'teacher';
    } else {
        // Hide controls
        jQuery('#options').addClass('hidden');
    }
    // Run through items
    jQuery('.dbitem').each(function () {
        if (role == 'teacher' || jQuery(this).find('.student').html().indexOf(username) > -1) {
            jQuery(this).removeClass('hidden');
        }
    });
    // Change approval link
    jQuery('.approve').each(function () {
        jQuery(this).find('a').attr('href', jQuery(this).find('a').attr('href').replace('advanced=0&paging&page=0', 'mode=single'));
    });
});
</script>";

foreach ($records as $record) {

    echo "Updating {$record->id}<br>";

    // Get the templates
    $singletemplate = $record->singletemplate;
    $listtemplatefooter = $record->listtemplatefooter;

    // Get script position in string
    $singletemplatescriptpos = strpos($singletemplate, '<script>');
    $listtemplatefooterscriptpos = strpos($listtemplatefooter, '<script>');

    // Cut off the old script and add the new one
    if($singletemplatescriptpos !== false) {
        $singletemplate = substr($singletemplate, 0, $singletemplatescriptpos);
        $singletemplate .= $newsingletemplatescript;
    }
    if($listtemplatefooterscriptpos !== false) {
        $listtemplatefooter = substr($listtemplatefooter, 0, $listtemplatefooterscriptpos);
        $listtemplatefooter .= $newlisttemplatescript;
    }

    // Update the property
    $record->singletemplate = $singletemplate;
    $record->listtemplatefooter = $listtemplatefooter;

    // Update DB
    $DB->update_record('data', $record);
}

echo "Completed.";
die;
