<?php


$ip = isset($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['HTTP_X_REAL_IP'] : $_SERVER["REMOTE_ADDR"];


require_once('../../../config.php');
/*if ( substr($ip, 0, 7) != '192.168' &&  !in_array( $USER->username, ['596064', '576218', '631976', 'ahwelp']) ){
    die();
}*/

$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);

echo $OUTPUT->header();

$user = $_GET['username'];

?>

    <form action="#" method="get">
        Codigo de usuário(separar com ;): <br/>
        <textarea name="username"><?= $user ?></textarea>
        <input type="hidden" name="send" value="true"/>
        <input type="submit" />
    </form>
    <hr/>

<?php

if (!$_GET['send']) {
    echo "Empty";
    echo $OUTPUT->footer();
    die;
}

$categories = $DB->get_records_sql("SELECT mcs.id, mcss.name || ' - '|| mcs.name as name, '0' as count, 'Não' as acessou
                                        FROM                                          
                                          {course_categories} mcs,
                                          {course_categories} mcss
                                        WHERE                                                                                    
                                          mcs.parent IN ( SELECT id from {course_categories} WHERE name IN ('GRADUAÇÃO', 'TECNÓLOGO') ) AND
                                          mcs.parent = mcss.id AND 
                                          mcs.name LIKE ('%-EAD%')
                                        ORDER BY mcs.name");

$courses = $DB->get_records_sql("SELECT mcu.id, mcu.category, mcs.name
                                        FROM
                                          mdl_course mcu,
                                          mdl_course_categories mcs
                                        WHERE
                                          mcu.category = mcs.id AND
                                          mcs.parent IN ( SELECT id from {course_categories} WHERE name IN ('GRADUAÇÃO', 'TECNÓLOGO') ) AND
                                          mcs.name LIKE ('%-EAD%')
                                        ORDER BY mcs.name");

$_course = "";
foreach ($courses as $course) {
    $_course .= "$course->id, ";
}
$_course = rtrim($_course, ', ');

if ($user == '') {
    $user = $DB->get_records_sql("SELECT DISTINCT(userid) 
                                              FROM {role_assignments} 
                                           WHERE contextid IN ( 
                                                      SELECT id FROM {context} 
                                                       WHERE contextlevel = 50 AND 
                                                       instanceid IN ($_course) ) AND roleid = 5");
}else{
    $user = explode(';', $user);
}



foreach ($user as $usr) {
    $pessoa;
    $in_cat = $categories;
    if( isset($usr->userid) ){
        $usr = $usr->userid;
        $pessoa = $DB->get_record('user', Array('id' => trim($usr)));
        $usr = $pessoa->username;
    }else{
        $pessoa = $DB->get_record('user', Array('username' => trim($usr)));
    }
    $values = $DB->get_records_sql("SELECT mca.id, COUNT(mcu.category) 
                                       FROM 
                                       {logstore_standard_log} mlsl, 
                                       {user} mus, 
                                       {course} mcu, 
                                       {course_categories} mca 
                                    WHERE 
                                      mus.username = ? AND 
                                      mcu.category IN (SELECT id from {course_categories} WHERE name like ('%-EAD%') ) AND  
                                      mcu.category = mca.id AND 
                                      mcu.id = mlsl.courseid AND 
                                      mlsl.userid = mus.id AND 
                                      mlsl.origin = 'web' 
                                    GROUP BY mcu.category, mca.id;", Array(trim($usr)));

    echo "<b>$usr ---> $pessoa->firstname $pessoa->lastname</b><br />";

    foreach ($values as $value) {
        $in_cat[$value->id]->count = $value->count;
        $in_cat[$value->id]->acessou = ( $value->count > 0 ) ? 'Sim' : 'Não';
    }

    foreach ($in_cat as $cat){
        $cat->name = str_replace('GRADUAÇÃO - ', '', $cat->name);
        $cat->name = str_replace('TECNÓLOGO - ', '', $cat->name);
    }

    $new_cat = [];

    foreach ($in_cat as $cat){
        $new_cat[$cat->name] = (isset($new_cat[$cat->name])) ? $new_cat[$cat->name] : new stdClass();
        $new_cat[$cat->name]->count = (isset($new_cat[$cat->name]->count) || $new_cat[$cat->name]->count == 0) ? $new_cat[$cat->name]->count + $cat->count : 0;
        $new_cat[$cat->name]->acessou = ($new_cat[$cat->name]->count > 0) ? 'Sim' : 'Não';
        echo $new_cat[$cat->name]->count;
    }

    echo '<table style="text-align: center; width: 50%;" border="1">';
    echo "<tr style='font-weight: bold'><td colspan='4'>$usr ---> $pessoa->firstname $pessoa->lastname</td></tr>";
    echo "<tr style='font-weight: bold'>";
    echo "    <td>Nomes</td> <td>Acessou?</td> <td>Registros</td>";
    echo "</tr>";
    foreach ($new_cat as $key => $cat){
        echo "<tr>";
        echo "<td>$key</td> <td>$cat->acessou</td> <td>$cat->count</td> ";
        echo "</tr>";
    }
}
echo $OUTPUT->footer();

?>
