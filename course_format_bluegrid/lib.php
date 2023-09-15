<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * BlueGrid Course Format - A topics based format that uses card layout to display the content.
 *
 * @package   format_bluegrid
 * @copyright 2020 onwards, Univates
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Christian Bayer (christian.bayer@universo.univates.br)
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/format/lib.php');
require_once($CFG->dirroot . '/course/format/bluegrid/renderer.php');
require_once($CFG->dirroot . '/course/format/bluegrid/classes/output/header_renderable.php');

use format_bluegrid\output\format_bluegrid_header;

/**
 * Main class for the BlueGrid Course Format
 *
 * @package   format_bluegrid
 * @copyright 2020 onwards, Univates
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Christian Bayer (christian.bayer@universo.univates.br)
 */
class format_bluegrid extends format_base
{
    private $settings;

    /**
     * Creates a new instance of class
     * Please use {@link course_get_format($courseorid)} to get an instance of the format class
     * @param string $format
     * @param int    $courseid
     */
    protected function __construct($format, $courseid) {
        if($courseid === 0) {
            global $COURSE;
            // Save lots of global $COURSE as we will never be the site course.
            $courseid = $COURSE->id;
        }
        parent::__construct($format, $courseid);
    }

    /**
     * Course-specific information to be output on any course page (usually above navigation bar)
     *
     * Example of usage:
     * define
     * class format_FORMATNAME_XXX implements renderable {}
     *
     * create format renderer in course/format/FORMATNAME/renderer.php, define rendering function:
     * class format_FORMATNAME_renderer extends plugin_renderer_base {
     *     protected function render_format_FORMATNAME_XXX(format_FORMATNAME_XXX $xxx) {
     *         return html_writer::tag('div', 'This is my header/footer');
     *     }
     * }
     *
     * Return instance of format_FORMATNAME_XXX in this function, the appropriate method from
     * plugin renderer will be called
     *
     * @return null|renderable null for no output or object with data for plugin renderer
     */
    public function course_header() {
        return new format_bluegrid_header();
    }

    /**
     * Returns the information about the ajax support in the given source format
     *
     * The returned object's property (boolean)capable indicates that
     * the course format supports Moodle course ajax features.
     * The property (array)testedbrowsers can be used as a parameter for {@link ajaxenabled()}.
     *
     * @return stdClass
     */
    public function supports_ajax() {
        $ajaxsupport = new stdClass();
        $ajaxsupport->capable = true;

        return $ajaxsupport;
    }

    /**
     * Returns the format's settings and gets them if they do not exist.
     * @return array The settings as an array.
     */
    public function get_settings() {
        if(empty($this->settings) == true) {
            $this->settings = $this->get_format_options();
        }

        return $this->settings;
    }

    /**
     * Indicates this format uses sections.
     * @return bool Returns true
     */
    public function uses_sections() {
        return true;
    }

    /**
     * Returns the display name of the given section that the course prefers.
     * Use section name is specified by user. Otherwise use default ("Topic #")
     * @param int|stdClass $section Section object from database or just field section.section
     * @return string Display name that the course format prefers, e.g. "Topic 2"
     */
    public function get_section_name($section) {
        if((string) $section->name !== '') {
            return format_string($section->name, true,
                array('context' => context_course::instance($this->courseid)));
        } else {
            return $this->get_default_section_name($section);
        }
    }

    /**
     * Returns the default section name for the topics course format.
     * If the section number is 0, it will use the string with key = section0name from the course format's lang file.
     * If the section number is not 0, the base implementation of format_base::get_default_section_name which uses
     * the string with the key = 'sectionname' from the course format's lang file + the section number will be used.
     *
     * @param stdClass $section Section object from database or just field course_sections section
     * @return string The default value for the section name.
     */
    public function get_default_section_name($section) {
        if($section->section == 0) {
            return get_string('section0name', 'format_bluegrid');
        } else {
            return get_string('sectionname', 'format_bluegrid') . ' ' . $section->section;
        }
    }

    /**
     * The URL to use for the specified course (with section)
     * @param int|stdClass $section Section object from database or just field course_sections.section
     *                              if omitted the course view page is returned
     * @param array        $options options for view URL. At the moment core uses:
     *                              'navigation' (bool) if true and section has no separate page, the function returns
     *                              null
     *                              'sr' (int) used by multipage formats to specify to which section to return
     * @return moodle_url
     * @throws moodle_exception
     */
    public function get_view_url($section, $options = array()) {
        $course = $this->get_course();
        $url = new moodle_url('/course/view.php', array('id' => $course->id));

        if(array_key_exists('sr', $options)) {
            $sectionno = $options['sr'];
        } else if(is_object($section)) {
            $sectionno = $section->section;
        } else {
            $sectionno = $section;
        }
        if($sectionno !== null) {
            $url->set_anchor('section-' . $sectionno);
        }

        return $url;
    }

    /**
     * Definitions of the additional options that this course format uses for the course.
     * @param bool $foreditform
     * @return array of options
     */
    public function course_format_options($foreditform = false) {
        $course = $this->get_course();
        $modinfo = get_fast_modinfo($course);

        $total_of_sections_showing_on_course = count($modinfo->get_section_info_all()) - 1;

        $sections_of_course = [];

        foreach ($modinfo->get_section_info_all() as $k => $section_info) {
            if($k > 0) {
                $sections_of_course[$k] = $section_info->id;
            }
        }

        $sections_showing_on_course = [];
        $course_format_options_of_showing_sections = [];

        for ($i = 1; $i <= $total_of_sections_showing_on_course; $i ++) {
            $sections_showing_on_course["name_section_" . $sections_of_course[$i]] = [
                'label'          => new lang_string("name_section", 'format_bluegrid', $i),
                'element_type'   => 'text',
                'help'           => "name_section",
                'help_component' => 'format_bluegrid',
            ];

            $course_format_options_of_showing_sections["name_section_" . $sections_of_course[$i]] = [
                'default' => (strlen(get_config('format_bluegrid', "name_section_" . $sections_of_course[$i])) <= 1) ? '' :
                    get_config('format_bluegrid', "name_section_" . $sections_of_course[$i]),
                'type'    => PARAM_RAW,
            ];
        }

        $courseformatoptions = array(
            'sectionname'               => array(
                'default' => get_config('format_bluegrid', 'defaultsectionname'),
                'type'    => PARAM_INT,
            ),
            'coursepresentationvideo'   => array(
                'default' => get_config('format_bluegrid', 'defaultcoursepresentationvideo'),
                'type'    => PARAM_RAW,
            ),
            'show_teachers'             => array(
                'default' => get_config('format_bluegrid', 'show_teachers'),
                'type'    => PARAM_INT,
            ),
            'show_course_name'          => array(
                'default' => get_config('format_bluegrid', 'show_course_name') === false ? 1 :
                    get_config('format_bluegrid', 'show_course_name'),
                'type'    => PARAM_INT,
            ),
            'show_overview_on_menu'     => array(
                'default' => get_config('format_bluegrid', 'show_overview_on_menu') === false ? 1 :
                    get_config('format_bluegrid', 'show_overview_on_menu'),
                'type'    => PARAM_INT,
            ),
            'show_participants_on_menu' => array(
                'default' => get_config('format_bluegrid', 'show_participants_on_menu') === false ? 1 :
                    get_config('format_bluegrid', 'show_participants_on_menu'),
                'type'    => PARAM_INT,
            ),
            'show_grades_on_menu'       => array(
                'default' => get_config('format_bluegrid', 'show_grades_on_menu') === false ? 1 :
                    get_config('format_bluegrid', 'show_grades_on_menu'),
                'type'    => PARAM_INT,
            ),
            'show_calendar_on_menu'     => array(
                'default' => get_config('format_bluegrid', 'show_calendar_on_menu') === false ? 1 :
                    get_config('format_bluegrid', 'show_calendar_on_menu'),
                'type'    => PARAM_INT,
            ),
            'brandcolor'     => array(
                'default' => get_config('format_bluegrid', 'brandcolor') === false ? 1 :
                    get_config('format_bluegrid', 'brandcolor'),
                'type'    => PARAM_RAW,
            ),
        );

        $courseformatoptions = array_merge($courseformatoptions, $course_format_options_of_showing_sections);

        if($foreditform) {
            $courseformatoptionsedit = array(
                'sectionname'               => array(
                    'label'              => new lang_string('defaultsectionname', 'format_bluegrid'),
                    'element_type'       => 'select',
                    'element_attributes' => array(
                        array(
                            0 => new lang_string('sectionnamenone', 'format_bluegrid'),
                            1 => new lang_string('sectionnameunit', 'format_bluegrid'),
                            2 => new lang_string('sectionnameclass', 'format_bluegrid'),
                            3 => new lang_string('sectionnamenumber', 'format_bluegrid'),
                        ),
                    ),
                    'help'               => 'defaultsectionname',
                    'help_component'     => 'format_bluegrid',
                ),
                'coursepresentationvideo'   => array(
                    'label'          => new lang_string('coursepresentationvideo', 'format_bluegrid'),
                    'element_type'   => 'text',
                    'help'           => 'coursepresentationvideo',
                    'help_component' => 'format_bluegrid',
                ),
                'show_teachers'             => array(
                    'label'              => new lang_string('showteachers', 'format_bluegrid'),
                    'element_type'       => 'select',
                    'element_attributes' => array(
                        array(
                            0 => new lang_string('no'),
                            1 => new lang_string('yes'),
                        ),
                    ),
                    'help'               => 'showteachers',
                    'help_component'     => 'format_bluegrid',
                ),
                'show_course_name'          => array(
                    'label'              => new lang_string('showcoursename', 'format_bluegrid'),
                    'element_type'       => 'select',
                    'element_attributes' => array(
                        array(
                            0 => new lang_string('no'),
                            1 => new lang_string('yes'),
                        ),
                    ),
                    'element_default'    => 1,
                    'help'               => 'showcoursename',
                    'help_component'     => 'format_bluegrid',
                ),
                'show_overview_on_menu'     => array(
                    'label'              => new lang_string('show_overview_on_menu', 'format_bluegrid'),
                    'element_type'       => 'select',
                    'element_attributes' => array(
                        array(
                            0 => new lang_string('no'),
                            1 => new lang_string('yes'),
                        ),
                    ),
                    'element_default'    => 1,
                    'help'               => 'show_overview_on_menu',
                    'help_component'     => 'format_bluegrid',
                ),
                'show_participants_on_menu' => array(
                    'label'              => new lang_string('show_participants_on_menu', 'format_bluegrid'),
                    'element_type'       => 'select',
                    'element_attributes' => array(
                        array(
                            0 => new lang_string('no'),
                            1 => new lang_string('yes'),
                        ),
                    ),
                    'element_default'    => 1,
                    'help'               => 'show_participants_on_menu',
                    'help_component'     => 'format_bluegrid',
                ),
                'show_grades_on_menu'       => array(
                    'label'              => new lang_string('show_grades_on_menu', 'format_bluegrid'),
                    'element_type'       => 'select',
                    'element_attributes' => array(
                        array(
                            0 => new lang_string('no'),
                            1 => new lang_string('yes'),
                        ),
                    ),
                    'element_default'    => 1,
                    'help'               => 'show_grades_on_menu',
                    'help_component'     => 'format_bluegrid',
                ),
                'show_calendar_on_menu'     => array(
                    'label'              => new lang_string('show_calendar_on_menu', 'format_bluegrid'),
                    'element_type'       => 'select',
                    'element_attributes' => array(
                        array(
                            0 => new lang_string('no'),
                            1 => new lang_string('yes'),
                        ),
                    ),
                    'element_default'    => 1,
                    'help'               => 'show_calendar_on_menu',
                    'help_component'     => 'format_bluegrid',
                ),
                'brandcolor'     => array(
                    'label'              => new lang_string('brandcolor', 'format_bluegrid'),
                    'element_type'       => 'select',
                    'element_attributes' => array(
                        array(
                            '#1670CC' => get_string('colourblue', 'format_bluegrid'),
                            '#00A9CE' => get_string('colourlightblue', 'format_bluegrid'),
                            '#7A9A01' => get_string('colourgreen', 'format_bluegrid'),
                            '#009681' => get_string('colourdarkgreen', 'format_bluegrid'),
                            '#D13C3C' => get_string('colourred', 'format_bluegrid'),
                            '#772583' => get_string('colourpurple', 'format_bluegrid'),
                            '#E5AC30' => get_string('colourorange', 'format_bluegrid'),
                        ),
                    ),
                    'element_default'    => 1,
                    'help'               => 'brandcolor',
                    'help_component'     => 'format_bluegrid',
                ),
            );

            $courseformatoptionsedit = array_merge($courseformatoptionsedit, $sections_showing_on_course);
            $courseformatoptions = array_merge_recursive($courseformatoptions, $courseformatoptionsedit);
        }

        return $courseformatoptions;
    }

    /**
     * Updates format options for a course or section
     *
     * If $data does not contain property with the option name, the option will not be updated
     *
     * @param stdClass|array $data return value from {@link moodleform::get_data()} or array with data
     * @param null|int null if these are options for course or section id (course_sections.id)
     *                             if these are options for section
     * @return bool whether there were any changes to the options values
     *
     */
    protected function update_format_options($data, $sectionid = null) {
        global $DB;

        if( ! $sectionid) {
            $allformatoptions = $this->course_format_options();
            $sectionid = 0;
        } else {
            $allformatoptions = $this->section_format_options();
        }

        if(empty($allformatoptions)) {
            // nothing to update anyway
            return false;
        }

        $defaultoptions = array();
        $cached = array();
        foreach ($allformatoptions as $key => $option) {
            $defaultoptions[$key] = null;
            if(array_key_exists('default', $option)) {
                $defaultoptions[$key] = $option['default'];
            }
            $cached[$key] = ($sectionid === 0 || ! empty($option['cache']));
        }

        $records = $DB->get_records('course_format_options',
            array(
                'courseid'  => $this->courseid,
                'format'    => $this->format,
                'sectionid' => $sectionid,
            ), '', 'name,id,value');

        $changed = $needrebuild = false;

        error_log( print_r($defaultoptions, true) );

        $data = (array) $data;

        $sectionsnames = isset($records['name_sections_json']) ? (array) json_decode($records['name_sections_json']->value) : [];

        foreach ($defaultoptions as $key => $value) {
            if(preg_match('/name_section_[0-9]?/', $key)) {
                if(array_key_exists($key, $data) && isset($sectionsnames[$key])) {
                    if($data[$key] !== $sectionsnames[$key]) {
                        $sectionsnames[$key] = $data[$key];
                        $DB->set_field('course_format_options', 'value',
                            json_encode($sectionsnames), array('id' => $records['name_sections_json']->id));
                        $changed = true;
                        $needrebuild = $needrebuild || $cached[$key];
                    }
                } else {
                    if(array_key_exists($key, $data) && $data[$key] !== $value) {
                        $newvalue = $data[$key];
                        $changed = true;
                        $needrebuild = $needrebuild || $cached[$key];

                    } else {
                        if(isset($sectionsnames[$key])){
                            $newvalue = $sectionsnames[$key];
                        }else{
                            $newvalue = $value;
                            // we still insert entry in DB but there are no changes from user point of
                            // view and no need to call rebuild_course_cache()
                        }

                    }

                    $name_section_json_insert = $DB->get_records('course_format_options',
                        array(
                            'courseid'  => $this->courseid,
                            'format'    => $this->format,
                            'sectionid' => $sectionid,
                        ), '', 'name,id,value')['name_sections_json'];

                    if($name_section_json_insert == null) {
                        $name_section_json_insert_array[$key] = $newvalue;
                        $DB->insert_record('course_format_options', array(
                            'courseid'  => $this->courseid,
                            'format'    => $this->format,
                            'sectionid' => $sectionid,
                            'name'      => 'name_sections_json',
                            'value'     => json_encode($name_section_json_insert_array),
                        ));
                    } else {
                        $name_section_json_insert_array = (array) json_decode($name_section_json_insert->value);
                        $name_section_json_insert_array[$key] = $newvalue;
                        $DB->set_field('course_format_options', 'value',
                            json_encode($name_section_json_insert_array), array('id' => $name_section_json_insert->id));
                    }
                }
            } else {
                if(isset($records[$key])) {
                    if(array_key_exists($key, $data) && $records[$key]->value !== $data[$key]) {
                        $DB->set_field('course_format_options', 'value',
                            $data[$key], array('id' => $records[$key]->id));
                        $changed = true;
                        $needrebuild = $needrebuild || $cached[$key];
                    }
                } else {
                    if(array_key_exists($key, $data) && $data[$key] !== $value) {
                        $newvalue = $data[$key];
                        $changed = true;
                        $needrebuild = $needrebuild || $cached[$key];

                    } else {
                        $newvalue = $value;
                        // we still insert entry in DB but there are no changes from user point of
                        // view and no need to call rebuild_course_cache()
                    }
                    $DB->insert_record('course_format_options', array(
                        'courseid'  => $this->courseid,
                        'format'    => $this->format,
                        'sectionid' => $sectionid,
                        'name'      => $key,
                        'value'     => $newvalue,
                    ));
                }
            }
        }

        if($needrebuild) {
            rebuild_course_cache($this->courseid, true);
        }
        if($changed) {
            // reset internal caches
            if( ! $sectionid) {
                $this->course = false;
            }
            unset($this->formatoptions[$sectionid]);
        }

        return $changed;
    }

    /**
     * Adds format options elements to the course/section edit form.
     * This function is called from {@link course_edit_form::definition_after_data()}.
     * @param MoodleQuickForm $mform      form the elements are added to.
     * @param bool            $forsection 'true' if this is a section edit form, 'false' if this is course edit form.
     * @return array array of references to the added form elements.
     * @throws coding_exception
     * @throws dml_exception
     */
    public function create_edit_form_elements(&$mform, $forsection = false) {
        global $COURSE;
        global $DB;

        $elements = parent::create_edit_form_elements($mform, $forsection);

        $sectionsform = [];

        foreach ($mform as $form) {
            foreach ($form as $campus => $value) {
                if(preg_match('/name_section_[0-9]?/', $campus) and ! in_array($campus, $sectionsform)) {
                    $sectionsform[] = $campus;
                }
            }
        }

        $records = $DB->get_records('course_format_options',
            array(
                'courseid'  => $this->courseid,
                'format'    => $this->format,
                'sectionid' => '0',
            ), '', 'name,id,value')['name_sections_json'];

        $name_sections_array = (array) json_decode($records->value);

        //Setting default values to sections_name
        foreach ($sectionsform as $section_form) {
            $mform->setDefault($section_form, $name_sections_array[$section_form]);
        }

        if( ! $forsection && (empty($COURSE->id) || $COURSE->id == SITEID)) {
            // Add "numsections" element to the create course form - it will force new course to be prepopulated
            // with empty sections.
            // The "Number of sections" option is no longer available when editing course, instead teachers should
            // delete and add sections when needed.
            $courseconfig = get_config('moodlecourse');
            $max = (int) $courseconfig->maxsections;
            $element = $mform->addElement('select', 'numsections', get_string('numberweeks'), range(0, $max ?: 52));
            $mform->setType('numsections', PARAM_INT);
            if(is_null($mform->getElementValue('numsections'))) {
                $mform->setDefault('numsections', $courseconfig->numsections);
            }
            array_unshift($elements, $element);
        }

        return $elements;
    }

    /**
     * Prepares the templateable object to display section name
     *
     * @param \section_info|\stdClass $section
     * @param bool                    $linkifneeded
     * @param bool                    $editable
     * @param null|lang_string|string $edithint
     * @param null|lang_string|string $editlabel
     * @return \core\output\inplace_editable
     */
    public function inplace_editable_render_section_name(
        $section,
        $linkifneeded = true,
        $editable = null,
        $edithint = null,
        $editlabel = null
    ) {
        if(empty($edithint)) {
            $edithint = new lang_string('editsectionname', 'format_bluegrid');
        }
        if(empty($editlabel)) {
            $title = get_section_name($section->course, $section);
            $editlabel = new lang_string('newsectionname', 'format_bluegrid', $title);
        }

        return parent::inplace_editable_render_section_name($section, $linkifneeded, $editable, $edithint, $editlabel);
    }

    /**
     * Loads all of the course sections into the navigation
     *
     * @param global_navigation $navigation
     * @param navigation_node   $node The course node within the navigation
     */
    public function extend_course_navigation($navigation, navigation_node $node) {
        parent::extend_course_navigation($navigation, $node);
        // Remove some menu items
        if($participants = $node->get('participants')) {
            $participants->remove();
        }
        if($competencies = $node->get('competencies')) {
            $competencies->remove();
        }
        if($grades = $node->get('grades')) {
            $grades->remove();
        }
        if($badgesview = $node->get('badgesview')) {
            $badgesview->remove();
        }
        if($badgesview = $node->get('badgesview')) {
            $badgesview->remove();
        }
    }

    /**
     * Indicates whether the course format supports the creation of a news forum.
     *
     * @return bool
     */
    public function supports_news() {
        return true;
    }

    /**
     * Returns the list of blocks to be automatically added for the newly created course
     *
     * @return array of default blocks, must contain two keys BLOCK_POS_LEFT and BLOCK_POS_RIGHT
     *     each of values is an array of block names (for left and right side columns)
     */
    public function get_default_blocks() {
        return array(
            BLOCK_POS_LEFT  => array(),
            BLOCK_POS_RIGHT => array(),
        );
    }

    /**
     * Returns whether this course format allows the activity to
     * have "triple visibility state" - visible always, hidden on course page but available, hidden.
     *
     * @param stdClass|cm_info      $cm      course module (may be null if we are displaying a form for adding a module)
     * @param stdClass|section_info $section section where this module is located or will be added to
     * @return bool
     */
    public function allow_stealth_module_visibility($cm, $section) {
        // Allow the third visibility state inside visible sections or in section 0.
        return ! $section->section || $section->visible;
    }

    /**
     * Callback used in WS core_course_edit_section when teacher performs an AJAX action on a section (show/hide)
     *
     * Access to the course is already validated in the WS but the callback has to make sure
     * that particular action is allowed by checking capabilities
     *
     * Course formats should register
     *
     * @param stdClass|section_info $section
     * @param string $action
     * @param int $sr
     * @return null|array|stdClass any data for the Javascript post-processor (must be json-encodeable)
     */
    public function section_action($section, $action, $sr) {
        global $PAGE;

        if($section->section && ($action === 'setmarker' || $action === 'removemarker')) {
            // Format 'topics' allows to set and remove markers in addition to common section actions.
            require_capability('moodle/course:setcurrentsection', context_course::instance($this->courseid));
            course_set_marker($this->courseid, ($action === 'setmarker') ? $section->section : 0);

            return null;
        }

        // For show/hide actions call the parent method and return the new content for .section_availability element.
        $rv = parent::section_action($section, $action, $sr);
        $renderer = $PAGE->get_renderer('format_topics');
        $rv['section_availability'] = $renderer->section_availability($this->get_section($section));

        return $rv;
    }

    /**
     * Return the plugin configs for external functions.
     *
     * @return array the list of configuration settings
     * @since Moodle 3.5
     */
    public function get_config_for_external() {
        return $this->get_format_options();
    }

    /**
     * Custom action after section has been moved in AJAX mode
     *
     * Used in course/rest.php
     *
     * @return array This will be passed in ajax respose
     * @throws moodle_exception
     */
    public function ajax_section_move() {
        global $PAGE;
        $titles = array();
        $course = $this->get_course();
        $modinfo = get_fast_modinfo($course);
        $renderer = $this->get_renderer($PAGE);
        if($renderer && ($sections = $modinfo->get_section_info_all())) {
            foreach ($sections as $number => $section) {
                $titles[$number] = $renderer->section_title($section, $course);
            }
        }

        return array(
            'sectiontitles' => $titles,
            'action'        => 'move',
        );
    }

    /**
     * Whether this format allows to delete sections
     *
     * Do not call this function directly, instead use {@link course_can_delete_section()}
     *
     * @param int|stdClass|section_info $section
     * @return bool
     */
    public function can_delete_section($section) {
        return true;
    }
}

/**
 * Implements callback inplace_editable() allowing to edit values in-place
 *
 * @param string $itemtype
 * @param int    $itemid
 * @param mixed  $newvalue
 * @return \core\output\inplace_editable
 * @throws dml_exception
 */
function format_bluegrid_inplace_editable($itemtype, $itemid, $newvalue) {
    global $DB, $CFG;
    require_once($CFG->dirroot . '/course/lib.php');
    if($itemtype === 'sectionname' || $itemtype === 'sectionnamenl') {
        $section = $DB->get_record_sql(
            'SELECT s.* FROM {course_sections} s JOIN {course} c ON s.course = c.id WHERE s.id = ? AND c.format = ?',
            array(
                $itemid,
                'bluegrid',
            ), MUST_EXIST);

        return course_get_format($section->course)->inplace_editable_update_section_name($section, $itemtype, $newvalue);
    }

    return null;
}
