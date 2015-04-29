<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2010 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Valerii Kuznetsov <valerii.kuznetsov@totaralms.com>
 * @package totara_reportbuilder
 * @category test
 *
 * Reportbuilder generator.
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir  . '/testing/generator/data_generator.php');

require_once($CFG->dirroot . '/totara/customfield/definelib.php');
require_once($CFG->dirroot . '/totara/customfield/fieldlib.php');
require_once($CFG->dirroot . '/totara/customfield/field/multiselect/define.class.php');
require_once($CFG->dirroot . '/totara/customfield/field/multiselect/field.class.php');
require_once($CFG->dirroot . '/totara/customfield/field/text/field.class.php');
require_once($CFG->dirroot . '/totara/customfield/field/text/define.class.php');

/**
 * This class intended to generate different mock entities
 *
 * @package totara_reportbuilder
 * @category test
 */
class totara_customfield_generator extends testing_data_generator {
    /**
     * Add text custom field.
     *
     * @param string $tableprefix
     * @param array $cfdef Format: array('fieldname', ...)
     * @return array id's of custom fields. Format: array('fieldname' => id, ...)
     */
    public function create_text($tableprefix, $cfdef) {
        global $DB;

        $result = array();
        foreach ($cfdef as $name) {
            $data = new stdClass();
            $data->id = 0;
            $data->datatype = 'text';
            $data->fullname = $name;
            $data->description = '';
            $data->defaultdata = '';
            $data->forceunique = 0;
            $data->hidden = 0;
            $data->locked = 0;
            $data->required = 0;
            $data->description_editor = array('text' => '', 'format' => 0);
            $formfield = new customfield_define_text();
            $formfield->define_save($data, $tableprefix);
            $sql = "SELECT id FROM {{$tableprefix}_info_field} WHERE " .
                    $DB->sql_compare_text('fullname') . ' = ' . $DB->sql_compare_text(':fullname');
            $result[$name] = $DB->get_field_sql($sql, array('fullname' => $name));
        }
        return $result;
    }

    /**
     * Put text into text customfield
     *
     * @param stdClass $item Course/prog or other supported object
     * @param int $cfid Customfield id
     * @param string $value Field value
     * @param string $prefix
     * @param string $tableprefix
     */
    public function set_text($item, $cfid, $value, $prefix, $tableprefix) {
        $field = new customfield_text($cfid, $item, $prefix, $tableprefix);
        $field->inputname = 'cftest';

        $data = new stdClass();
        $data->id = $item->id;
        $data->cftest = $value;
        $field->edit_save_data($data, $prefix, $tableprefix);
    }

    /**
     * Add multi-select custom field. All fields have default icon and are not default
     *
     * @param string $tableprefix
     * @param array $cfdef Format: array('fieldname' => array('option1', 'option2', ...), ...)
     * @return array id's of custom fields. Format: array('fieldname' => id, ...)
     */
    public function create_multiselect($tableprefix, $cfdef) {
        global $DB;
        $result = array();
        foreach ($cfdef as $name => $options) {
            $data = new stdClass();
            $data->id = 0;
            $data->datatype = 'multiselect';
            $data->fullname = $name;
            $data->description = '';
            $data->defaultdata = '';
            $data->forceunique = 0;
            $data->hidden = 0;
            $data->locked = 0;
            $data->required = 0;
            $data->description_editor = array('text' => '', 'format' => 0);
            $data->multiselectitem = array();
            foreach ($options as $opt) {
                $data->multiselectitem[] = array('option' => $opt, 'icon' => 'default',
                        'default' => 0, 'delete' => 0);
            }
            $formfield = new customfield_define_multiselect();
            $formfield->define_save($data, $tableprefix);
            $sql = "SELECT id FROM {{$tableprefix}_info_field} WHERE ".
                    $DB->sql_compare_text('fullname') . ' = ' . $DB->sql_compare_text(':fullname');

            $result[$name] = $DB->get_field_sql($sql, array('fullname' => $name));
        }
        return $result;
    }

    /**
     * Enable one or more option for selected customfield
     *
     * @param stdClass $item - course/prog or other supported object
     * @param int $cfid - customfield id
     * @param array $options - option names to enable
     * @param string $prefix
     * @param string $tableprefix
     */
    public function set_multiselect($item, $cfid, array $options, $prefix, $tableprefix) {
        $field = new customfield_multiselect($cfid, $item, $prefix, $tableprefix);
        $field->inputname = 'cftest';

        $data = new stdClass();
        $data->id = $item->id;
        $cfdata = array();
        foreach ($field->options as $key => $option) {
            if (in_array($option['option'], $options)) {
                $cfdata[$key] = 1;
            } else {
                $cfdata[$key] = 0;
            }
        }
        $data->cftest = $cfdata;
        $field->edit_save_data($data, $prefix, $tableprefix);
    }
}
