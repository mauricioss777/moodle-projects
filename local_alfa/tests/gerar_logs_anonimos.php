
<?php
/**
 * Testa as funções de busca do plano de Ensino.
 *
 */

//define('CLI_SCRIPT', true);

require_once('../../../config.php');
require_once('../classes/alfa.class.php');

if(!is_siteadmin()){
    die('Você não tem permissão de executar este script.');
}

$sql = "SELECT userid FROM mdl_role_assignments WHERE contextid IN (SELECT id FROM mdl_context WHERE contextlevel = 50 AND instanceid IN (SELECT id FROM mdl_course WHERE category IN ( SELECT id FROM mdl_course_categories WHERE name like ('%-EAD%') ))) AND roleid = 5";
$users = $DB->get_records_sql($sql);

$userids = '';

foreach($users as $user){
   $userids .= $user->userid . ', ';
}
$userids = rtrim($userids, ', ');

$sql = "SELECT mlsl.id, 
               mlsl.userid, 
               mlsl.courseid, 
                mlsl.eventname, 
                mus.firstname, 
                mus.lastname, 
                mcu.fullname, 
                mcu.id 
            FROM 
                mdl_logstore_standard_log mlsl, 
                mdl_user mus, 
                mdl_course mcu 
            WHERE 
                mus.id = mlsl.userid AND 
                mlsl.courseid = mcu.id AND 
            ( courseid IN ( 
                SELECT id 
                    FROM 
                        mdl_course 
                    WHERE category IN ( 
                            SELECT id 
                                FROM mdl_course_categories 
                            WHERE name like ('%-EAD%') ) ) 
                OR courseid = 0 ) AND userid IN ($userids)";

echo preg_replace( "/\r|\n/", "", $sql ); die;

$logs = $DB->get_records_sql($sql); 
echo '<pre>';
var_dump($logs);die;


//var_dump($users);
