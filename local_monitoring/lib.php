<?php

/**
 * Library of functions for the Multiple Evaluations form.
 *
 * @package   mod_multievaluations
 * @copyright 2020 onwards, Univates
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Artur Henrique Welp      (ahwelp@universo.univates.br)
 */

defined('MOODLE_INTERNAL') || die();

function get_user_activities_on_course($user, $course){
    global $DB;

    $modules = '5, 14, 33';

    $courses = [];
    $order = $DB->get_records_sql("SELECT id, sequence FROM {course_sections} WHERE course = ? ORDER BY section", [$course]);
    $modules_raw = $DB->get_records_sql("SELECT mcm.*, mmo.name FROM {course_modules} mcm, {modules} mmo WHERE mmo.id = mcm.module AND course =  ? AND mcm.module IN ($modules) ", [$course] );

    //Sort elements
    $modules = [];
    foreach($order as $item){
        foreach( explode(',', $item->sequence) as $i){ @$modules[] = $modules_raw[$i]; }
    } 
    
    foreach($modules as $module){
        if( !isset($module->name) ){ continue; }
        switch ($module->name) {
            case 'forum':
                $item = get_forum($module, $user);
                if($item['type'] == 'news'){ break; }
                $courses[] = $item; 
                break;
            case 'assign':
                $courses[] = get_assign($module, $user);
                break;
            case 'quiz':
                $courses[] = get_quiz($module, $user);
                break;
        }
    }

    return $courses;
}

function get_user_grades($user, $course){
    global $DB;

    $sql = "SELECT id, rawgrade, rawgrademax FROM mdl_grade_grades WHERE itemid IN ( SELECT id FROM mdl_grade_items WHERE categoryid IN (select id from mdl_grade_categories Where courseid = $course AND depth > 1) ) AND userid = $user";
    $grades = $DB->get_records_sql($sql);

    $total = 0;
    $possible = 0;
    $acchived = 0;

    foreach($grades as $grade){
        $total += $grade->rawgrademax;
        if($grade->rawgrade != '' ){
            $possible += $grade->rawgrademax;
        }
        $acchived += $grade->rawgrade;
    }

    $posssible_porcent = ($possible * $total) / 100;
    $acchived_porcent  = ($acchived * $total) / 100;
    $rest = 100 - $posssible_porcent - $acchived_porcent;

    $return = "";
    $return .= "<div class='progress-bar bg-success' role='progressbar' style='width: $acchived_porcent%' >$acchived</div>";
    $return .= "<div class='progress-bar bg-warning' role='progressbar' style='width: $posssible_porcent%' >$possible</div>";
    $return .= "<div class='progress-bar bg-danger'  role='progressbar' style='width: $rest%' >$total</div>";

    return $return;

}

function get_last_access_corse($user, $course){
    global $DB;

    $time = $DB->get_record_sql( 'SELECT to_timestamp(timeaccess) as date, now() FROM {user_lastaccess} WHERE userid = ? AND courseid = ? ORDER BY id DESC LIMIT 1', [$user, $course] ); 
    if(!$time){ return ''; }
    $diff = date_diff( date_create(substr( $time->date, 0, 19) ), date_create( substr( $time->now, 0, 19) ) )->format("%a dias %h horas %i minutos");
    return substr( $time->date, 0, 19) . " - ". $diff ; 

}


function get_forum($resource, $user){
    global $DB, $CFG;

    $forum = $DB->get_record('forum', ['id' => $resource->instance]);
    $posts = $DB->get_record_sql('select count(mfp.id) as posts from mdl_forum_discussions mfd, mdl_forum_posts mfp WHERE mfp.discussion = mfd.id AND mfd.forum = ? AND mfp.userid = ?', [$forum->id, $user])->posts;

    return [
        'instance' => $forum->id,
        'module'   => $resource->name,
        'mod'      => $resource->module,
        'name'     => $forum->name,
        'link'     => $CFG->wwwroot . '/mod/forum/view.php?id='.$resource->id.'&headless=1',
        'userdata' => $posts . ' postagens',
        'type'     => $forum->type,
        'grade'    => get_grade($forum->id, $resource->name, $user)
    ];
}

function get_assign($resource, $user){
    global $DB, $CFG;

    $assign = $DB->get_record('assign', ['id' => $resource->instance]);
    $attempts = $DB->get_record_sql("SELECT COUNT(id) entregas FROM {assign_submission} WHERE status = 'submitted' AND assignment = ? AND userid = ?", [$resource->instance, $user]);

    $status = 'alert-primary';

    if($attempts->entregas == 0){
        if( time() > $assign->duedate ){
            $status = 'alert-danger';
        }
    }else{
        $status = 'alert-success';
    }

    return [
        'instance' => $assign->id,
        'module'   => $resource->name,
        'mod'      => $resource->module,
        'status'   => $status,
        'name'     => $assign->name,
        'link'     => $CFG->wwwroot . "/mod/assign/view.php?id=$resource->id&rownum=0&action=grader&userid=$user?headless=1",
        'userdata' => $attempts->entregas . ' tentativas',
        'grade'    => get_grade($assign->id, $resource->name, $user)
    ];

}

function get_quiz($resource, $user){
    global $DB, $CFG;

    $quiz = $DB->get_record('quiz', ['id' => $resource->instance]);
    $attempts = $DB->get_record_sql("SELECT * FROM {quiz_attempts} WHERE quiz = ? AND userid = ? ORDER BY attempt DESC LIMIT 1", [$resource->instance, $user]);

    if(!$attempts){  @$attempts->attempt = '0'; $attempts->id = '0'; }

    $status = 'alert-primary';

    if($attempts->attempt == 0){
        if( time() > $quiz->timeclose ){
            $status = 'alert-danger';
        }
    }else{
        $status = 'alert-success';
    }

    return [
        'instance' => $quiz->id,
        'module'   => $resource->name,
        'mod'      => $resource->module,
        'name'     => $quiz->name,
        'status'   => $status, 
        'userdata' => $attempts->attempt . ' tentativas',
        'grade'    => get_grade($quiz->id, $resource->name, $user),
        'link'     => $CFG->wwwroot . "/mod/quiz/review.php?attempt=$attempts->id&headless=1",
    ];

}


function get_grade($resource, $module, $user){
    global $DB;

    $item = $DB->get_record_sql("SELECT * FROM {grade_items} WHERE itemmodule = '$module' AND iteminstance = $resource" ); 

    if( !$item ){ return 'N/A'; }
    $grade = $DB->get_record('grade_grades', ['itemid' => $item->id, 'userid' => $user ]);

    if( !$grade ){ return '-- / ' . $item->grademax; }
    return $grade->rawgrade . ' / ' . $grade->rawgrademax;

}

// Add the menu item
function local_monitoring_extend_navigation(global_navigation $nav) {
    global $CFG, $PAGE, $DB, $USER;

    if(!local_monitoring_user_can_access()){ return; }

    $url = new moodle_url('/local/monitoring/index.php');

    $node = navigation_node::create(get_string('pluginname', 'local_monitoring'),
        $url,
        navigation_node::NODETYPE_LEAF
    );
    $node->remuiicon = 'fa-eye';
    $node->showinflatnavigation = true;
    $nav->add_node($node);

}

function local_monitoring_get_fontawesome_icon_map() {
    return array(
        'local_monitoring:google'   => 'fa-google',
        'local_monitoring:eye'      => 'fa-eye',
        'local_monitoring:triangle' => 'fa-exclamation-triangle',
        'local_monitoring:universo' => 'icon-universo-univates',
    );
}

function local_monitoring_user_can_access(){
    global $DB, $USER;

    if( is_siteadmin() ) { return true; }

    $u = $DB->get_record('local_monitoring_user', ['userid' => $USER->id] );

    if($u){
        return true;
    }else{
        return false;
    }
}

function local_monitoring_build_table($content){
    $table  = "<table border='1' style='width: 100%'>";
    $table .= "<tr> <td colspan='2'> " . $content->obs . "</td> </tr>";
    $table .= "<tr> <td colspan='2'> " . $content->linha_digitavel . "</td> </tr>";
    $table .= "<tr> <td> Data-emissão </td> <td>" . $content->dt_emissao . "</td> </tr>";
    $table .= "<tr> <td> Data-vencimento </td> <td>" . $content->dt_vencimento . "</td> </tr>";
    $table .= "</table>";
    return $table;
}
