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
 * @author Maria Torres <maria.torres@totaralms.com>
 * @package totara_customfield
 */

namespace totara_customfield\prefix;
defined('MOODLE_INTERNAL') || die();

abstract class hierarchy_type extends type_base {

    /**
     * Create a new hierarchy type.
     *
     * @param string $prefix
     * @param string $context
     * @param array $extrainfo
     */
    public function __construct($prefix, $context, $extrainfo = array()) {
        global $CFG;
        require_once($CFG->dirroot . '/totara/hierarchy/lib.php');

        $shortprefix = \hierarchy::get_short_prefix($prefix);
        $tableprefix = $shortprefix.'_type';
        parent::__construct($prefix, $tableprefix, $shortprefix, $context, $extrainfo);
    }

    /**
     * Get an array of conditions to look for fields
     *
     * @param $neworder
     * @param $field
     * @return array
     */
    public static function get_conditions_swapfields($neworder, $field) {
        return array('sortorder' => $neworder, 'typeid' => $field->typeid);
    }

    /**
     * Get the field record to move.
     *
     * @param $tableprefix
     * @param $id
     * @return mixed
     */
    public static function get_field_to_move($tableprefix, $id) {
        global $DB;
        return $DB->get_record($tableprefix.'_info_field', array('id' => $id), 'id, typeid, sortorder');
    }

    /**
     * Reordering fields in database
     *
     * @return bool Result of the action executed.
     */
    public function reorder_fields() {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/totara/core/utils.php');
        $tableprefix = $this->tableprefix;
        $rs = $DB->get_recordset($tableprefix . '_info_field', array(), 'sortorder ASC');
        if ($types = totara_group_records($rs, 'typeid')) {
            foreach ($types as $unused => $fields) {
                $i = 1;
                foreach ($fields as $field) {
                    $f = new \stdClass();
                    $f->id = $field->id;
                    $f->sortorder = $i++;
                    $DB->update_record($tableprefix.'_info_field', $f);
                }
            }
        }
        $rs->close();
        return true;
    }

    public function get_fields_sql_where() {
        return array('typeid' => $this->other['typeid']);
    }
}
