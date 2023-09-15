<?php

    define('CLI_SCRIPT', true);
    require_once('../../../config.php');
    require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
    ini_set("memory_limit",'8192M');
    /**
     * local onde está o arquivo que será restaurado
     */
    $unzipfile          = $CFG->dirroot .'/local/alfa/tests/restaurar.mbz';

    $sharedfolder       = get_config('local_restorecourse','sharedfolder');
    $folder             = '/restore-course-tcc-estagio-'.time().'/'; // $CFG->dataroot .'/temp/backup/restore-<courseid>/' 

    $tmpfolder          = $CFG->dataroot .'/temp/backup'.$folder;
    
    
    if(file_exists($tmpfolder)){
        __remove_tmp_course_dir($tmpfolder);//apaga o direório se ele já existe.
    }
    if(!file_exists($unzipfile)){
        error_log('The file that you are trying restore does not exist.'); die();
    }
    mkdir($tmpfolder);
    
    try{
        $p = new PharData($unzipfile);
        $p->extractTo($tmpfolder);
    } catch(Exception $e){
        error_log('Unexpected error while trying to extract the backup file.'); die();
    }

    //$transaction        = $DB->start_delegated_transaction();// Transaction.
    $userdoingrestore   = '2'; // e.g. 2 == admin
    $category           = $DB->get_record('course_categories',array('name'=>'AMBIENTES EM DESENVOLVIMENTO'),'id',MUST_EXIST);
    $courseid           = restore_dbops::create_new_course('', '',$category->id);

    // Restore backup into course.
    $controller = new restore_controller($folder, $courseid,
            backup::INTERACTIVE_NO, backup::MODE_SAMESITE, $userdoingrestore,
            backup::TARGET_NEW_COURSE);
    if(!$controller->execute_precheck()){//verifica se não deu erro na preparação do curso
        error_log('AjaxCreateCourseTCC:: controller->execute_precheck = ERROR');
    }
    
    @$controller->execute_plan();
    $newid = $controller->get_courseid();
    __remove_tmp_course_dir($tmpfolder);
    

    //Adiciona as informações no Alfa.
    // Por padrão os cursos de TCC tem duração de 8 meses
//    $alfa = new stdClass();
//    $alfa->courseid = $newid;
//    $alfa->idnumber = '999';
//    $alfa->createlabels = 0;
//    $alfa->closedcourseid = 0;
//    $alfa->timeclosed = time() + ((86400 * 31) * 6);//hoje + ((1 dia * 31) * 6 meses)
//    $DB->insert_record('local_alfa',$alfa);

//    $course = new stdClass();
//    $course->id = $newid;
//    $course->enddate= time() + ((86400 * 31) * 6);//hoje + ((1 dia * 31) * 6 meses)
//    $DB->update_record('course', $course);

    $return = new stdClass();
    $return->id = $newid;
    $return->linkead = $CFG->wwwroot.'/course/view.php?id='.$newid;
    $return->enddate = $course->enddate;

    echo print_r($return,true);
    error_log(print_r($return,true));


/**
 * Função utilizada para remover diretórios.
 * Extraido de http://php.net/manual/pt_BR/function.rmdir.php
 */
function __remove_tmp_course_dir($directory, $empty = false) { 
    if(substr($directory,-1) == "/") { 
        $directory = substr($directory,0,-1); 
    } 

    if(!file_exists($directory) || !is_dir($directory)) { 
        return false; 
    } elseif(!is_readable($directory)) { 
        return false; 
    } else { 
        $directoryHandle = opendir($directory); 
        
        while ($contents = readdir($directoryHandle)) { 
            if($contents != '.' && $contents != '..') { 
                $path = $directory . "/" . $contents; 
                
                if(is_dir($path)) { 
                    __remove_tmp_course_dir($path); 
                } else { 
                    unlink($path); 
                } 
            } 
        } 
        
        closedir($directoryHandle); 

        if($empty == false) { 
            if(!rmdir($directory)) { 
                return false; 
            } 
        } 
        
        return true; 
    } 
}


?>
