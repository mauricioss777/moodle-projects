<?php

defined('MOODLE_INTERNAL') || die();

class restore_format_bluegrid_plugin extends restore_format_plugin {
    
    /** @var int */
    protected $originalnumsections = 0;

    /**
     * Checks if backup file was made on Moodle before 3.3 and we should respect the 'numsections'
     * and potential "orphaned" sections in the end of the course.
     *
     * @return bool
     */
    public function define_section_plugin_structure() {

        return [ new restore_path_element('bluegrid', $this->get_pathfor('/bluegrid')) ];
    }

    protected function need_restore_numsections() {
        $backupinfo = $this->step->get_task()->get_info();
        $backuprelease = $backupinfo->backup_release; // The major version: 2.9, 3.0, 3.10...
        return version_compare($backuprelease, '3.3', '<');
    }

    /**
     * Creates a dummy path element in order to be able to execute code after restore.
     *
     * @return restore_path_element[]
     */
    public function define_course_plugin_structure() {
        global $DB;

        // Since this method is executed before the restore we can do some pre-checks here.
        // In case of merging backup into existing course find the current number of sections.
        $target = $this->step->get_task()->get_target();
        if (($target == backup::TARGET_CURRENT_ADDING || $target == backup::TARGET_EXISTING_ADDING) &&
                $this->need_restore_numsections()) {
            $maxsection = $DB->get_field_sql(
                'SELECT max(section) FROM {course_sections} WHERE course = ?',
                [$this->step->get_task()->get_courseid()]);
            $this->originalnumsections = (int)$maxsection;
        }

        // Dummy path element is needed in order for after_restore_course() to be called.
        return [new restore_path_element('dummy_course', $this->get_pathfor('/dummycourse'))];
    }

    /**
     * Dummy process method.
     *
     * @return void
     */
    public function process_dummy_course() {

    }

    /**
     * Executed after course restore is complete.
     *
     * This method is only executed if course configuration was overridden.
     *
     * @return void
     */
    public function after_restore_course() {
        //implementar (se necessário)
    }


    public function process_bluegrid(){
        //implementar (se necessário)
    }

    /**
     * Esta função está sendo utilizada para maperar os ids das seções
     * do curso antigo para o curso novo. Isso é necessário
     * para restautar corretamente os nomes das seções no bluegrid.
     */
    public function after_restore_section() {
        global $DB;
        
        $data = $this->connectionpoint->get_data();

        if (!isset($data['path'])
            || $data['path'] != "/section"
            || !isset($data['tags']['id'])) {
            return;
        }

        $oldsectionid = $data['tags']['id'];
        $oldsectionnum = $data['tags']['number'];

        $newcourseid = $this->step->get_task()->get_courseid();
        $newsectionid = $DB->get_field('course_sections', 'id', [
            'course' => $newcourseid,
            'section' => $oldsectionnum
        ]);

        if (!$newsectionid) {
            return;
        }

        self::json_blue_grid($newcourseid, $oldsectionid, $newsectionid);
    }

    /**
     * função auxiliar que apenas atualiza o json do bluegrid com os novos valores
     */
    private static function json_blue_grid(int $newcourseid, int $oldsectionid, int $newsectionid) {
        global $DB;

        $n = new stdClass();
        $n->courseid = $newcourseid;
        $n->oldsection = $oldsectionid;
        $n->newsection = $newsectionid;

        //carrega json restaurado com os IDs das sections antigas
        $newjsonnames = $DB->get_record_sql(
            'SELECT * FROM {course_format_options} WHERE courseid = :courseid AND name = :name', 
            [
                'courseid'   => $newcourseid,
                'name' => 'name_sections_json'
            ]
        );
        
        $newjsonnames->value = str_replace('"name_section_'.$oldsectionid.'"', '"name_section_'.$newsectionid.'"', $newjsonnames->value);
        $DB->update_record('course_format_options',$newjsonnames);

    }
}
