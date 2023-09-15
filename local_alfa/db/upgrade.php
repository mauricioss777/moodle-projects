<?php

/*
* @package   local_alfa
*/

function xmldb_local_alfa_upgrade($oldversion)
{
    global $CFG, $DB, $OUTPUT;

    $dbman = $DB->get_manager(); // Loads ddl manager and xmldb classes.

    if($oldversion < 2014091520.01) {

        // Define table forum_digests to be created.
        $table = new xmldb_table('local_alfa_curriculum');

        // Adding fields to table local_alfa_curriculum.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('curriculum', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_alfa_curriculum.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('courseid', XMLDB_KEY_FOREIGN, array('courseid'), 'course', array('id'));

        // Conditionally launch create table for local_alfa_curriculum.
        if( ! $dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Alfa savepoint reached.
        upgrade_plugin_savepoint(true, 2014091520.01, 'local', 'alfa');
    }

    if($oldversion < 2018011504) {

        // Define table forum_digests to be created.
        $table = new xmldb_table('local_alfa_tcc');

        // Adding fields to table local_alfa_curriculum.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('idnumber', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_alfa_curriculum.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('courseid', XMLDB_KEY_FOREIGN, array('courseid'), 'course', array('id'));

        // Conditionally launch create table for local_alfa_curriculum.
        if( ! $dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Alfa savepoint reached.
        upgrade_plugin_savepoint(true, 2018011504, 'local', 'alfa');
    }

    if($oldversion < 2018080101) {

        $category = new stdClass();
        $category->name = 'Alfa';

        $category_id = $DB->insert_record('user_info_category', $category, true);

        $field = new stdClass();
        $field->shortname = 'Polo';
        $field->name = 'Polo';
        $field->locked = 1;
        $field->visible = 2;
        $field->sortorder = 1;
        $field->param1 = 30;
        $field->param2 = 2048;
        $field->param3 = 0;
        $field->datatype = 'text';
        $field->categoryid = $category_id;

        $DB->insert_record('user_info_field', $field);

    }

    if($oldversion < 2019012908) {

        $category = $DB->get_record('user_info_category', array('name' => 'Alfa'));

        $field = new stdClass();
        $field->shortname = 'Curso';
        $field->name = 'Curso';
        $field->locked = 1;
        $field->param1 = 30;
        $field->param2 = 2048;
        $field->param3 = 0;
        $field->visible = 2;
        $field->sortorder = 2;
        $field->datatype = 'text';
        $field->categoryid = $category->id;

        $DB->insert_record('user_info_field', $field);
    }

    if($oldversion < 2019022700) {

        $table = new xmldb_table('block_email');

        $index = new xmldb_index('parent', XMLDB_INDEX_NOTUNIQUE, array('parent'));

        if( ! $dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        upgrade_plugin_savepoint(true, 2019022700, 'local', 'alfa');
    }

    return true;
}
