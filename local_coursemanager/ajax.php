<?php

require_once('../../config.php');
require_once('lib.php');

if( ! local_coursemanager_user_can_access() ){
    die();
}

$action    = required_param('action', PARAM_ALPHANUMEXT);

call_user_func( $action );

function load_report(){

}

function search_courses(){
    global $DB;
    $course = required_param('data', PARAM_RAW);

    $courses = $DB->get_records_sql("SELECT id, fullname FROM {course} WHERE category IN ($course) ORDER BY fullname", Array($course));

    $return = "<option value='-1'>Selecione</option>\n";
    foreach ($courses as $course){
        $return .= "<option value='$course->id'>$course->fullname</option>\n";
    }
    echo $return;
}

function add_course(){
    global $DB, $USER;

    $course = required_param('data', PARAM_ALPHANUMEXT);
    $user = $USER->id;

    $old = $DB->get_record('local_coursemanager_watch', Array('userid'=>$user, 'courseid'=>$course));

    if(!$old){
        $item = new stdClass();
        $item->courseid = $course;
        $item->userid = $user;
        $DB->insert_record('local_coursemanager_watch', $item);
    }

    $courses = $DB->get_records_sql("SELECT id, fullname
                                          FROM {course}
                                        WHERE id IN (SELECT courseid
                                                              FROM {local_coursemanager_watch}
                                                              WHERE userid = $user) ORDER BY fullname" );
    $return = '';
    foreach ($courses as $course){
        $return .= "<li> <a data-id='$course->id'> $course->fullname </a></li>";
    }
    echo $return;
}

function load_mods(){
    global $DB, $USER, $CFG;

    $course = required_param('data', PARAM_ALPHANUMEXT);

    $mods = $DB->get_records_sql("SELECT mcm.id, mmod.name, mcm.instance, mcs.section, mcm.visible
                                      FROM {course_modules} mcm,
                                           {course_sections} mcs,
                                           {modules} mmod
                                     WHERE mcs.id = mcm.section
                                      AND mcm.module = mmod.id
                                      AND mcs.course = ?
                                      AND mmod.name IN ('assign', 'bigbluebuttonbn', 'quiz')
                                        ORDER BY mcs.section;", Array($course) );

    $sections = $DB->get_records_sql('SELECT *
                                          FROM {course_sections}
                                        WHERE course = ? AND
                                              section > 0', Array($course) );
    $section_items = Array();
    $last_section = 0;
    $section_items[] = reset($sections);
    array_shift($sections);
    $last_section++;

    foreach ($mods as $mod){
        $actual_mod = $DB->get_record($mod->name, Array('id'=>$mod->instance));
        if($mod->section != $last_section){
            $section_items[] = reset($sections);
            array_shift($sections);
            $last_section++;
        }
        $actual_mod->module = $mod->name;
        $actual_mod->coursemod_id = $mod->id;
        $actual_mod->visible = ($mod->visible) ? 'checked' : '';
        $section_items[] = $actual_mod;
    }

    $return =  "<div id='item_list' style='padding:20px; text-align: left;'> <table style='width: 100%;'>";

    $time_diff = new DateTime();
    $time_diff = $time_diff->getOffset();

    foreach ($section_items as $section_item){
        if( !isset($section_item->module) ){
            if($section_item->availability != ''){
                $restriction = json_decode($section_item->availability);
                $restriction_date = $restriction->c[0]->t + ($time_diff+3600);
                $restriction_date = new DateTime("@$restriction_date");
                $restriction_date = $restriction_date->format('d/m/Y H:i');
                $return .= "<tr><td colspan='4'>&nbsp;</td></tr>";
                $return .= "<tr><td colspan='4'><span class='topic'>$section_item->name</span></td></tr>";
                $return .= "<tr data-type='topic' data-id='$section_item->id'>";
                $return .= "<td> <input type='checkbox' class='visible' $section_item->visible  /> </td> <td colspan='3'> <input class='avaiability' type='text' value='".$restriction_date ."' placeholder='dd/mm/YYYY HH:mm'> </td>";
                $return .= "<td> <button style='width: 100%;'>Save</button></td></tr>";
            }else{
                $return .= "<tr><td colspan='4'>&nbsp;</td></tr>";
                $return .= "<tr><td colspan='4'><span class='topic'>$section_item->name</span></td></tr>";
                $return .= "<tr data-type='topic' data-id='$section_item->id'>";
                $return .= "<td> <input type='checkbox' class='visible' $section_item->visible  /> </td> <td colspan='3'>  <input class='avaiability' type='text' placeholder='dd/mm/YYYY HH:mm' /> </td>";
                $return .= "<td > <button style='width: 100%;'>Save</button> </td></tr>";
            }
        }else{
            switch ($section_item->module){
                case 'quiz':
                    if(!$section_item->name){ break; }
                    $ln = "<a target='_blank' href='$CFG->wwwroot/course/modedit.php?update=$section_item->coursemod_id&return=1'>$section_item->name</a>";
                    $section_item->name = "<strong style='color:#bf3c18'>Questinário: </strong> <a href=''>$ln";
                    if($section_item->timeopen != 0){
                        $timeopen = ($section_item->timeopen + $time_diff);
                        $timeopen = new DateTime( "@$timeopen" );
                        $timeopen = $timeopen->format('d/m/Y H:i');
                    }
                    if($section_item->timeclose != 0){
                        $timeclose = ($section_item->timeclose + $time_diff);
                        $timeclose = new DateTime( "@$timeclose" );
                        $timeclose = $timeclose->format('d/m/Y H:i');
                    }
                    $return .= "<tr><td colspan='4'>$section_item->name</td></tr>";
                    $return .= "<tr data-type='quiz' data-id='$section_item->id' ><td> <input type='checkbox' class='visible' $section_item->visible  /> </td><td><input class='timeopen' type='text' value='$timeopen' placeholder='dd/mm/YYYY HH:mm'/></td> <td><input class='timeclose' type='text' value='$timeclose' placeholder='dd/mm/YYYY HH:mm'/></td><td colspan='2'> <button style='width: 100%;'>Save</button></td> </tr>";
                    break;
                case 'bigbluebuttonbn':
                    if(!$section_item->name){ break; }
                    $ln = "<a target='_blank' href='$CFG->wwwroot/course/modedit.php?update=$section_item->coursemod_id&return=1'>$section_item->name</a>";
                    $section_item->name = "<strong style='color:#0b96e5'>Video: </strong>$ln";
                    if($section_item->openingtime != 0){
                        $openingtime = $section_item->openingtime + $time_diff;
                        $openingtime = new DateTime( "@$openingtime" );
                        $openingtime = $openingtime->format('d/m/Y H:i');
                    }
                    if($section_item->closingtime != 0){
                        $closingtime = $section_item->closingtime + $time_diff;
                        $closingtime = new DateTime( "@$closingtime" );
                        $closingtime = $closingtime->format('d/m/Y H:i');
                    }
                    $return .= "<tr><td colspan='4'>$section_item->name</td></tr>";
                    $return .= "<tr data-type='bigbluebuttonbn' data-id='$section_item->id'><td> <input type='checkbox' class='visible' $section_item->visible  /> </td><td><input class='openingtime' type='text' value='$openingtime' placeholder='dd/mm/YYYY HH:mm' /></td> <td><input class='closingtime' type='text' value='$closingtime' placeholder='dd/mm/YYYY HH:mm'/></td> <td colspan='2'><button style='width:100%'>Save</button></td> </tr>";
                    break;
                case 'assignments' :
                    break;
                case 'assign' :
                    if(!$section_item->name){ break; }
                    $ln = "<a target='_blank' href='$CFG->wwwroot/course/modedit.php?update=$section_item->coursemod_id&return=1'>$section_item->name</a>";
                    $section_item->name = "<strong style='color:#00aa00'>Tarefa: </strong>$ln";
                    if($section_item->allowsubmissionsfromdate != 0){
                        $allowsubmissionsfromdate = $section_item->allowsubmissionsfromdate - $time_diff;
                        $allowsubmissionsfromdate = new DateTime( "@$allowsubmissionsfromdate" );
                        $allowsubmissionsfromdate = $allowsubmissionsfromdate->format('d/m/Y H:i');
                    }
                    if($section_item->duedate != 0){
                        $duedate = $section_item->duedate - $time_diff;
                        $duedate = new DateTime( "@$duedate" );
                        $duedate = $duedate->format('d/m/Y H:i');
                    }
                    if($section_item->duedate != 0){
                        $duedate = $section_item->duedate - $time_diff;
                        $duedate = new DateTime( "@$duedate" );
                        $duedate = $duedate->format('d/m/Y H:i');
                    }
                    if($section_item->cutoffdate != 0){
                        $cutoffdate = $section_item->cutoffdate - $time_diff;
                        $cutoffdate = new DateTime( "@$cutoffdate" );
                        $cutoffdate = $cutoffdate->format('d/m/Y H:i');
                    }

                    $return .= "<tr><td colspan='4'>$section_item->name</td></tr>";
                    $return .= "<tr data-type='assign' data-id='$section_item->id' ><td> <input type='checkbox' class='visible' $section_item->visible  /> </td><td><input class='allowsubmissionsfromdate' type='text' value='$allowsubmissionsfromdate' placeholder='dd/mm/YYYY HH:mm'/></td> <td><input class='duedate' type='text' value='$duedate' placeholder='dd/mm/YYYY HH:mm'/></td> <td><input class='cutoffdate' type='text' value='$cutoffdate' placeholder='dd/mm/YYYY HH:mm'/></td> <td colspan='2'><button style='width:100%'>Save</button></td> </tr>";
                    break;
                default:
                    $return .= "<tr><td colspan='4'>$section_item->name</td></tr>";
                    break;
            }
        }
    }
    $return .= "</table></div>";
    echo $return;
}

function save_date(){
    global $DB;
    $course = required_param('data', PARAM_RAW);
    switch ($course['activity']){
        case 'topic':
            if($course['avaiability'] == '' || $course['avaiability'] == '0'){
                $record = $DB->get_record('course_sections', Array('id'=>$course['id']));
                $record->availability = '{"op":"&","c":[],"showc":[]}';
                $record->timemodified = time();
                $DB->update_record('course_sections', $record);
            }else{
                $template = '{"op":"&","c":[{"type":"date","d":">=","t":{{time}}}}],"showc":[false]}';
                $template = str_replace('{{time}}}', DateTime::createFromFormat('d/m/Y H:i', $course['avaiability'])->format('U'), $template);
                $record = $DB->get_record('course_sections', Array('id'=>$course['id']));
                $record->availability = $template;
                $record->timemodified = time();
                $DB->update_record('course_sections', $record);
            }
            break;
        case 'quiz':
            $record = $DB->get_record('quiz', Array('id'=>$course['id']));
            if($course['timeopen'] != ''){
                $timeopen = $course['timeopen'];
                $timeopen = DateTime::createFromFormat('d/m/Y H:i', $timeopen)->format('U');
            }else{
                $timeopen = 0;
            }
            if($course['timeclose'] != '') {
                $timeclose = $course['timeclose'];
                $timeclose = DateTime::createFromFormat('d/m/Y H:i', $timeclose)->format('U');
            }else{
                $timeclose = 0;
            }
            $record->timeopen = $timeopen;
            $record->timeclose = $timeclose;
            $record->timemodified = time();
            $DB->update_record('quiz', $record);
            break;
        case 'assign':
            $record = $DB->get_record('assign', Array('id'=>$course['id']));
            if($course['allowsubmissionsfromdate'] != ''){
                $allowsubmissionsfromdate = $course['allowsubmissionsfromdate'];
                $allowsubmissionsfromdate = DateTime::createFromFormat('d/m/Y H:i', $allowsubmissionsfromdate)->format('U');
                $record->allowsubmissionsfromdate = $allowsubmissionsfromdate;
            }else{
                $record->allowsubmissionsfromdate = 0;
            }
            if($course['duedate'] != ''){
                $duedate = $course['duedate'];
                $duedate = DateTime::createFromFormat('d/m/Y H:i', $duedate)->format('U');
                $record->duedate = $duedate;
            }else{
                $record->duedate = 0;
            }
            if($course['cutoffdata'] != ''){
                $cutoffdate = $course['cutoffdata'];
                $cutoffdate = DateTime::createFromFormat('d/m/Y H:i', $cutoffdate)->format('U');
                $record->cutoffdate = $cutoffdate;
            }else{
                $record->cutoffdate = 0;
            }
            $record->timemodified = time();
            $DB->update_record('assign', $record);
            break;
        case 'bigbluebuttonbn':
            $record = $DB->get_record('bigbluebuttonbn', Array('id'=>$course['id']));
            if($course['openingtime']!= ''){
                $openingtime = $course['openingtime'];
                $openingtime = DateTime::createFromFormat('d/m/Y H:i', $openingtime)->format('U');
            }else{
                $openingtime = 0;
            }
            if($course['closingtime'] != ''){
                $closingtime = $course['closingtime'];
                $closingtime = DateTime::createFromFormat('d/m/Y H:i', $closingtime)->format('U');
            }else{
                $closingtime = 0;
            }
            $record->openingtime = $openingtime;
            $record->closingtime = $closingtime;
            $record->timemodified = time();
            $DB->update_record('bigbluebuttonbn', $record);
            break;
    }
}
