<?php

class alfa_helper{

    /**
     * Função que cria as três categorias de notas da Univates
     * @param $course object
     */
    public static function course_create_grade_item($course, $category_grades = 3, $c)
    {
        global $CFG, $DB;
        require_once $CFG->dirroot . '/grade/lib.php';
        require_once $CFG->dirroot . '/grade/report/lib.php';
        require_once $CFG->libdir . '/mathslib.php';

        // Caso for EAD e já tenha sido ofertado, provavelmente será importado
        if(self::has_ead_course($c['disciplineid'], $c['idoffer']) && $c['ead']){
            return;
        }

        if(!$c['ead']){
            return;
        }

        try {

            $calculation = "=";
            if( $c['ead'] ){
                $calculation .= "SUM(";
            }else{
                $calculation .= "AVERAGE(";
            }
            $grade_items_holder = [];

            if( ! isset($course) || ! is_object($course)) {
                throw new moodle_exception("Erro");
            }

            for ($i = 1; $i <= $category_grades; $i++) {
                $grade_category = new grade_category(array('courseid' => $course->id), false);
                $grade_category->apply_default_settings();
                $grade_category->apply_forced_settings();
                $category = $grade_category->get_record_data();
                $grade_item = new grade_item(array(
                    'courseid' => $course->id,
                    'itemtype' => 'manual',
                ), false);
                foreach ($grade_item->get_record_data() as $key => $value) {
                    $category->{"grade_item_$key"} = $value;
                }
                $data = new stdclass();
                $data->aggregateonlygraded = 0;
                $data->aggregateoutcomes = 0;
                $data->fullname = get_string('note', 'local_alfa', $i);
                grade_category::set_properties($grade_category, $data);
                $category_id = $grade_category->insert();
                $grade_items_holder[$category_id] = 'N'.$i;
                unset($grade_category);
            }

            if($c['eadworkload'] != 0 && !$c['ead'] ){
                $grade_category = new grade_category(array('courseid' => $course->id), false);
                $grade_category->apply_default_settings();
                $grade_category->apply_forced_settings();
                $category = $grade_category->get_record_data();
                $grade_item = new grade_item(array(
                    'courseid' => $course->id,
                    'itemtype' => 'manual',
                ), false);
                foreach ($grade_item->get_record_data() as $key => $value) {
                    $category->{"grade_item_$key"} = $value;
                }
                $data = new stdclass();
                $data->aggregateonlygraded = 0;
                $data->aggregateoutcomes = 0;
                $data->fullname = get_string('special_regime_idependent', 'local_alfa');
                grade_category::set_properties($grade_category, $data);
                $category_id = $grade_category->insert();
                unset($grade_category);
            }

            //Add idnumbers to N1 - N2 - N3
            foreach($grade_items_holder as $key => $grade_item){
                $item = $DB->get_record('grade_items', ['iteminstance' => $key]);
                $item->idnumber = $grade_item;
                $DB->update_record('grade_items', $item);
                $calculation .= "[[".$grade_item."]];";
            }

            if(empty($grade_items_holder)){ return; }

            $calculation = rtrim($calculation, ';') . ")";
            $calculation = calc_formula::unlocalize($calculation);

            $id = $DB->get_record('grade_items', ['courseid' => $course->id, 'itemtype' => 'course'])->id;
            $grade_item = grade_item::fetch(array('id'=>$id, 'courseid'=>$course->id));
            $grade_item->set_calculation($calculation);

        } catch (Exception $ex) {
            error_log("LOCAL_ALFA_ERROR::_course_create_grade_item:: " . $ex->getMessage());
            throw new moodle_exception($ex->getMessage());
        }
    }

    /**
     * Verifica se a oferta já teve alguma EAD
     *
     * @param $disciplineid
     * @param $idnumber
     * @return boolean
     *
     **/
    public static function has_ead_course($disciplineid, $idnumber)
    {
        global $DB;

        $sql = "SELECT
                    mco.id
                    FROM
                        mdl_course mco,
                        mdl_local_alfa mlaf,
                        mdl_course_categories mcc
                    WHERE
                        mlaf.disciplineid = ? AND
                        mlaf.idnumber != ? AND
                        mco.id = mlaf.courseid AND
                        mcc.id = mco.category AND
                        mcc.name like ('%EAD%')";

        if( $DB->get_record_sql($sql, [$disciplineid, $idnumber]) ){
            return true;
        }

        return false;

    }

    /**
     * Processa quantos tópicos o curso precisa ter
     * caso já tenha defindo, retorna a quantidade predefinida
     *
     * @param $c
     * @return int
     *
     **/
    public static function resolve_topics_ammount($c)
    {

        // Caso o alfa informe a quantidade
        if(isset($c['numsections'])){
            return $c['numsections'];
        }

        // Cursos presenciais
        if( !strpos($c['period'], 'EAD') ){
            if($c['workload'] == 40){
                return 9;
            }

            if($c['workload'] == 80){
                return 18;
            }

            return 18;
        }

        if( strpos($c['fullname'], 'ESTÁGIO SUPERVISIONADO') > -1 ){
            return 2;
        }

        if( strpos($c['fullname'], 'TRABALHO DE CONCLUSÃO DE CURSO') > -1 ){
            return 1;
        }

        if( strpos($c['fullname'], 'SEMINÁRIO INTEGRADOR') > -1 ){
            return 1;
        }

        if( strpos($c['fullname'], 'PROJETO INTEGRADOR') > -1 ){
            return 1;
        }

        if($c['workload'] == 40){
            return 6;
        }

        if($c['workload'] == 80){
            return 10;
        }
    }

    /**
     * Retorna o formato e algumas informações sobre o curso
     *
     *
     **/
    public static function resolve_course_format($c)
    {

        // 0 -> Format | 1 -> Visible | 2 -> Completion | 3 -> Aula+ format
        $return = ['topics', 1, 0, ''];

        if( strpos($c['period'], 'EAD') > 0 && $c['format'] == '' ){
            $return[0] = 'bluegrid';
            $return[1] = 0;
            $return[2] = 1;
        }

        if( strpos( $c['dayofweek'], 'Regime Especial' ) !== false ){
            $return[2] = 1;
        }

        if($c['format'] == 'Atelier' || $c['format'] == 'Seminário'){
            if($c['format'] == 'Seminário'){
                $return[3] = 1;
            }else if($c['format'] == 'Atelier'){
                $return[3]  = 2;
            }

            $return[0] = 'aulamais';
        }

        return $return;

    }

    /**
     * Retorna a descrição do curso
     *
     *
     **/
    public static function resolve_course_syllabus($c)
    {
        global $DB;

        $summary = "";

        if($c['syllabus'] != '' && strpos($c['period'], 'EAD') > 0 ){
            $summary = str_replace(PHP_EOL, '<br />', $c['syllabus']) . "<br /> <br />";
        } else if ($c['format'] == 'aulamais' && $c['dayshift'] == 'EAD'){
            $summary =  $DB->get_record_sql("SELECT id, summary FROM {course} WHERE shortname = 'Template Aula+'")->summary;
        }

        if(!$c['ead']){
            $summary .= $c['dayofweek'] . " - " . $c['dayshift'];
        }
        return $summary;
    }

    /**
     * Prepara cursos do tipo Bluegrid
     *
     **/
    public static function course_create_bluegrid($course, $c, $context)
    {
        global $DB;

        if($c['format'] != 'bluegrid'){ return; }

        $topics = $DB->get_records( 'course_sections', ['course'=>$course->id] );
        $i = 1;
        $lenght = sizeof($topics);
        $value = [];

        if($lenght > 3){
            foreach($topics as $topic){
                if($i == $lenght){
                    $value['name_section_'.$topic->id] = 'Prova';
                }else if($i == ($lenght-1) ){
                    $value['name_section_'.$topic->id] = 'BÔNUS';
                }else{
                    $value['name_section_'.$topic->id] = '';
                }
                $i++;
            }

            $value = [
                'courseid' => $course->id,
                'format' => 'bluegrid',
                'name' => 'name_sections_json',
                'value' => json_encode($value)
            ];

            $DB->insert_record('course_format_options', $value);
        }

        if( strpos($c['fullname'], 'ESTÁGIO SUPERVISIONADO') > -1 ){
            foreach($topics as $topic){
                if($i == 2){
                    $value['name_section_'.$topic->id] = ' ';
                }else if($i == 3){
                    $value['name_section_'.$topic->id] = 'Conteúdo e Atividades';
                }else{
                    $value['name_section_'.$topic->id] = '';
                }
                $i++;
            }

            $value = [
                'courseid' => $course->id,
                'format' => 'bluegrid',
                'name' => 'name_sections_json',
                'value' => json_encode($value)
            ];

            $DB->insert_record('course_format_options', $value);
        }

        //Renomear o papel para os polos
        if($c['dayofweek'] == 'Distância' || $c['ead']){
            $rolename = (object)['roleid' => 4, 'contextid' => $context->id, 'name' => 'Polo Univates'];
            $DB->insert_record('role_names', $rolename);
        }
    }

    public static function last_friday_night($timestamp)
    {

        $formaDate = date("Y:m:d", $timestamp);
        $dayOfWeek = date("w", $timestamp);
        $returnDays = $dayOfWeek + 3;
        return ($timestamp -  ($returnDays * 86400)  + (19 * 3600)) ;
    }

}
