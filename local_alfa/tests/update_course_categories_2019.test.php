<?php
require_once('../../../config.php');
require_once('../../../lib/coursecatlib.php');
require_once('../../../course/lib.php');

if(!is_siteadmin()){
    die('Você não tem permissão de executar este script.');
}

global $CFG, $DB;

// Make the hard work
change_parent('GRADUAÇÃO');
change_parent('TÉCNICO');
change_parent('TECNÓLOGO');
change_parent('SEQUENCIAL');
change_parent('SEQÜENCIAL', 'SEQUENCIAL');

// Fix categories that have the period with a space and move to correct parent
for ($i = 2005; $i < 2018; $i ++) {
    change_parent_and_trim_period("{$i} ");
}

// Join some categories
move_content_and_delete('MESTRADO/DOUTORADO', 'MESTRADO E DOUTORADO');
move_content_and_delete('EXTENSÃO MAIS DE 50 HORAS', 'EXTENSÃO');
move_content_and_delete('ESPECIALIZAÇÃO', 'PÓS-GRADUAÇÃO');
move_content_and_delete('ESPECIALIZAÇÃO S/C', 'PÓS-GRADUAÇÃO');
move_content_and_delete('ESPECIALIZAÇÃO EAD', 'PÓS-GRADUAÇÃO');
move_content_and_delete('MBA S/C', 'PÓS-GRADUAÇÃO');
move_content_and_delete('EAD', 'NEAD');
move_content_and_delete('Graduação', 'NEAD');

// Make the soft work
move_category_to_new_parent('2010 - TÉCNICO', 'TÉCNICO', '2010');
move_category_to_new_parent('PRÉ-VESTIBULAR', 'EXTENSÃO');
move_category_to_new_parent('CURSOS LIVRES UNIAPREN', 'APOIO');
move_category_to_new_parent('APOIO DISCENTE', 'APOIO');
move_category_to_new_parent('DCE', 'APOIO');
move_category_to_new_parent('AMBIENTES EM DESENVOLVIMENTO', 'NEAD');
move_category_to_new_parent('CURSOS CTTI', 'ENCERRADOS');
move_category_to_new_parent('Formação Pedagógica Comung', 'ENCERRADOS', 'FORMAÇÃO PEDAGÓGICA COMUNG');
move_category_to_new_parent('INTERINSTITUCIONAL', 'APOIO');
move_category_to_new_parent('PROJETOS ESPECIAIS', 'APOIO');


// Delete unused courses
if(delete_course(5705, false)){
    echo "Deleting course \"BIOQUÍMICA MOLECULAR APLICADA À TECNOLOGIA DE ALIMENTOS\"<br/>";
}
if(delete_course(28718, false)){
    echo "Deleting course \"FUNDAMENTOS DE ADM - REF87672\"<br/>";
}

// Move courses
foreach([78323,78322, 78321,78324,78320,76917,76919,76920] as $idoffer){
    move_course_by_idoffer($idoffer, '2014B', 'TÉCNICO');
}
foreach([92345, 92354,92353,92341,92350,92352,92343,92349] as $idoffer){
    move_course_by_idoffer($idoffer, '2016B', 'TÉCNICO');
}

move_content_and_delete('TÉCNICO - UNIVATES', 'TÉCNICO');

rename_category('EDUCAÇÃO CONTINUADA MENOS DE 50 HORAS', 'EDUCAÇÃO CONTINUADA');
move_content_and_delete('EDUCAÇÃO CONTINUADA MAIS DE 50 HORAS', 'EDUCAÇÃO CONTINUADA');

// Resort
order_subcategories_by_name('MESTRADO E DOUTORADO');
order_subcategories_by_name('TECNÓLOGO');
order_subcategories_by_name('TÉCNICO');
order_subcategories_by_name('GRADUAÇÃO');
order_subcategories_by_name('SEQUENCIAL');
order_subcategories_by_name('EXTENSÃO');
order_subcategories_by_name('PÓS-GRADUAÇÃO');
order_subcategories_by_name('APOIO');

function order_subcategories_by_name($categoryname){
    global $DB;
    $category = $DB->get_record('course_categories', array('name' => $categoryname));
    if($category) {
        echo "Resorting {$categoryname}<br/>";
        $coursecat = coursecat::get($category->id);
        $coursecat->resort_subcategories('name', false);
        $coursecat->resort_courses('fullname', false);
    }
}

function move_course_by_idoffer($idoffer, $categoryname, $parentcategoryname){
    global $DB;
    $course = $DB->get_record('course', array('idnumber' => $idoffer));
    if(!$course){
        $course = $DB->get_record_sql('SELECT * FROM {course} WHERE shortname like ?', array("%REF{$idoffer}"));
    }

    if($parentcategoryname){
        $parentcategory = $DB->get_record('course_categories', array('name' => $parentcategoryname));
        $category = $DB->get_record('course_categories', array('name' => $categoryname, 'parent' => $parentcategory->id));
    } else {
        $category = $DB->get_record('course_categories', array('name' => $categoryname));
    }

    echo "Moving course {$course->shortname} from category {$course->category} to category {$category->name}<br/>";

    move_courses([$course->id], $category->id);
}

function rename_category($categoryname, $newname)
{
    global $DB;
    $category = $DB->get_record('course_categories', array('name' => $categoryname));
    if($category) {
        echo "Renaming {$categoryname} to {$newname}<br/>";
        $coursecat = coursecat::get($category->id);
        $coursecat->update([
            'name' => $newname,
        ]);
    }

}

function move_category_to_new_parent($categoryname, $tocategoryname, $rename = null)
{
    global $DB;
    $category = $DB->get_record('course_categories', array('name' => $categoryname));
    $tocategory = $DB->get_record('course_categories', array('name' => $tocategoryname));
    if($category && $tocategory) {
        echo "Moving category {$category->name} to {$tocategory->name}<br/>";
        // Update this category
        $coursecat = coursecat::get($category->id);
        $coursecat->update([
            'name'   => $rename ?: $category->name,
            'parent' => $tocategory->id,
        ]);
    }
}

function move_content_and_delete($origintobedeletedname, $destinationname)
{
    global $DB;

    $origin = $DB->get_record('course_categories', array('name' => $origintobedeletedname));
    $destination = $DB->get_record('course_categories', array('name' => $destinationname));
    if($origin && $destination) {
        echo "Moving all content from {$origin->name} to {$destination->name} and excluding {$origin->name}<br/>";
        $origincoursecat = coursecat::get($origin->id);
        $origincoursecat->delete_move($destination->id);
    }

    echo "------------------------------------------------<br/>";
}

function change_parent_and_trim_period($searchperiod)
{
    global $DB;
    // Search for categories with the period
    $coursecategories = $DB->get_records_sql('SELECT * FROM {course_categories} WHERE name ILIKE ?', array("%{$searchperiod}%"));
    foreach ($coursecategories as $coursecategory) {

        // Get the period, eg: "2009 A " with trim, "2009 A" (6 chars)
        $period = trim(substr(trim($coursecategory->name), 0, 7));
        // If the first 4 chars (eg: "2009", "GRAD") are not numbers or has more or less than 6 chars or has a '-', go to next
        if( ! is_numeric(substr($period, 0, 4)) || strlen($period) != 6 || substr($period, 5, 1) == '-') {
            continue;
        }
        // Fix the period (joining the "2009" + "A", without space in it
        $period = substr($period, 0, 4) . substr($period, 5, 1);

        // Get the category name
        $parentcategoryname = trim(substr($coursecategory->name, strpos($coursecategory->name, '-') + 1));

        // Get the parent
        $parentcategory = $DB->get_record('course_categories', array('name' => $parentcategoryname));

        if($parentcategory) {

            echo "Renaming and moving {$coursecategory->name} to ";

            // Update this category
            $coursecat = coursecat::get($coursecategory->id);
            $coursecat->update([
                'name'        => $period,
                'description' => $period,
                'parent'      => $parentcategory->id,
            ]);

            echo "category {$parentcategory->name} with name {$period}<br/>";
        }
    }
    echo "------------------------------------------------<br/>";
}

function change_parent($searchcategory, $moveto = null)
{
    global $DB;

    // If don't need to move the category to another
    $moveto = $moveto ?: $searchcategory;

    // Get the parent
    $parentcategory = $DB->get_record('course_categories', array('name' => $moveto));
    if( ! $parentcategory) {
        // Or create it
        $newcategory = new stdClass();
        $newcategory->name = $moveto;
        $newcategory->description = $moveto;
        $parentcategory = coursecat::create($newcategory);
    }

    // Search for categories with the same name
    $coursecategories = $DB->get_records_sql("SELECT * FROM {course_categories} WHERE name LIKE ?", array("%{$searchcategory}%"));
    foreach ($coursecategories as $coursecategory) {

        // Get the period, eg: "2009A" with trim
        $period = trim(substr(trim($coursecategory->name), 0, 5));

        if( ! is_numeric(substr($period, 0, 4)) || strlen($period) < 5) {
            continue;
        }

        echo "Renaming and moving {$coursecategory->name} to ";

        // Get the coursecat object and update it
        $coursecat = coursecat::get($coursecategory->id);
        $coursecat->update([
            'name'        => $period,
            'description' => $period,
            'parent'      => $parentcategory->id,
        ]);

        echo "category {$parentcategory->name} with name {$period}<br/>";
    }
    echo "------------------------------------------------<br/>";
}

