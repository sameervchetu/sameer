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
 */

defined('MOODLE_INTERNAL') || die();

class rb_source_site_logstore extends rb_base_source {
    public $base, $joinlist, $columnoptions, $filteroptions;
    public $contentoptions, $paramoptions, $defaultcolumns;
    public $defaultfilters, $requiredcolumns, $sourcetitle;

    public function __construct() {
        $this->base = '{logstore_standard_log}';
        $this->joinlist = $this->define_joinlist();
        $this->columnoptions = $this->define_columnoptions();
        $this->filteroptions = $this->define_filteroptions();
        $this->contentoptions = $this->define_contentoptions();
        $this->paramoptions = $this->define_paramoptions();
        $this->defaultcolumns = $this->define_defaultcolumns();
        $this->defaultfilters = $this->define_defaultfilters();
        $this->requiredcolumns = $this->define_requiredcolumns();
        $this->sourcetitle = get_string('sourcetitle', 'rb_source_site_logstore');
        $this->sourcewhere = 'anonymous = 0';

        parent::__construct();
    }


    protected function define_joinlist() {

        $joinlist = array();

        // Include some standard joins.
        $this->add_user_table_to_joinlist($joinlist, 'base', 'userid');
        $this->add_course_table_to_joinlist($joinlist, 'base', 'courseid');
        // Requires the course join.
        $this->add_course_category_table_to_joinlist($joinlist,
            'course', 'category');
        $this->add_position_tables_to_joinlist($joinlist, 'base', 'userid');
        // Requires the position_assignment join.
        $this->add_manager_tables_to_joinlist($joinlist,
            'position_assignment', 'reportstoid');
        $this->add_tag_tables_to_joinlist('course', $joinlist, 'base', 'courseid');
        $this->add_cohort_user_tables_to_joinlist($joinlist, 'base', 'userid');
        $this->add_cohort_course_tables_to_joinlist($joinlist, 'base', 'courseid');

        // Add related user support.
        $this->add_relateduser_table_to_joinlist($joinlist, 'base', 'relateduserid');
        return $joinlist;
    }

    protected function define_columnoptions() {
        global $DB;

        $eventextrafields = array(
            'eventname' =>'base.eventname',
            'component' => 'base.component',
            'action' => 'base.action',
            'target' => 'base.target',
            'objecttable' => 'base.objecttable',
            'objectid' => 'base.objectid',
            'crud' => 'base.crud',
            'edulevel' => 'base.edulevel',
            'contextid' => 'base.contextid',
            'contextlevel' => 'base.contextlevel',
            'contextinstanceid' => 'base.contextinstanceid',
            'userid' => 'base.userid',
            'courseid' => 'base.courseid',
            'relateduserid' => 'base.relateduserid',
            'anonymous' => 'base.anonymous',
            'other' => 'base.other',
            'timecreated' => 'base.timecreated',
        );

        $columnoptions = array(
            new rb_column_option(
                'logstore_standard_log',
                'timecreated',
                get_string('time', 'rb_source_site_logstore'),
                'base.timecreated',
                array('displayfunc' => 'nice_datetime', 'dbdatatype' => 'timestamp')
            ),
            new rb_column_option(
                'logstore_standard_log',
                'ip',
                get_string('ip', 'rb_source_site_logstore'),
                'base.ip',
                array('displayfunc' => 'iplookup')
            ),
            new rb_column_option(
                'logstore_standard_log',
                'targetaction',
                get_string('targetaction', 'rb_source_site_logstore'),
                $DB->sql_fullname('base.target', 'base.action'),
                array('dbdatatype' => 'char',
                      'outputformat' => 'text')
            ),
            new rb_column_option(
                'logstore_standard_log',
                'other',
                get_string('other', 'rb_source_site_logstore'),
                'base.other',
                array('displayfunc' => 'serialized')
            ),
            new rb_column_option(
                'logstore_standard_log',
                'eventname',
                get_string('eventclass', 'rb_source_site_logstore'),
                'base.eventname',
                array('dbdatatype' => 'char',
                      'outputformat' => 'text')
            ),
            new rb_column_option(
                'logstore_standard_log',
                'component',
                get_string('component', 'rb_source_site_logstore'),
                'base.component',
                array('dbdatatype' => 'char',
                      'outputformat' => 'text')
            ),
            new rb_column_option(
                'logstore_standard_log',
                'action',
                get_string('action', 'rb_source_site_logstore'),
                'base.action',
                array('dbdatatype' => 'char',
                      'outputformat' => 'text')
            ),
            new rb_column_option(
                'logstore_standard_log',
                'target',
                get_string('target', 'rb_source_site_logstore'),
                'base.target',
                array('dbdatatype' => 'char',
                      'outputformat' => 'text')
            ),
            new rb_column_option(
                'logstore_standard_log',
                'objecttable',
                get_string('objecttable', 'rb_source_site_logstore'),
                'base.objecttable',
                array('dbdatatype' => 'char',
                      'outputformat' => 'text')
            ),
            new rb_column_option(
                'logstore_standard_log',
                'objectid',
                get_string('objectid', 'rb_source_site_logstore'),
                'base.objectid',
                array('dbdatatype' => 'integer')
            ),
            new rb_column_option(
                'logstore_standard_log',
                'origin',
                get_string('origin', 'rb_source_site_logstore'),
                'base.origin',
                array('dbdatatype' => 'char',
                      'outputformat' => 'text')
            ),
            new rb_column_option(
                'logstore_standard_log',
                'crud',
                get_string('crud', 'rb_source_site_logstore'),
                'base.crud',
                array('dbdatatype' => 'char',
                      'displayfunc' => 'crud')
            ),
            new rb_column_option(
                'logstore_standard_log',
                'edulevel',
                get_string('edulevel', 'moodle'),
                'base.edulevel',
                array('dbdatatype' => 'char',
                      'displayfunc' => 'edulevel')
            ),
            new rb_column_option(
                'logstore_standard_log',
                'name',
                get_string('name', 'rb_source_site_logstore'),
                'base.id',
                array('displayfunc' => 'name',
                      'extrafields' => $eventextrafields
                     )
            ),
            new rb_column_option(
                'logstore_standard_log',
                'description',
                get_string('description', 'moodle'),
                'base.id',
                array('displayfunc' => 'description',
                      'extrafields' => $eventextrafields
                )
            ),
        );

        // Include some standard columns.
        $this->add_user_fields_to_columns($columnoptions);
        $this->add_course_fields_to_columns($columnoptions);
        $this->add_course_category_fields_to_columns($columnoptions);
        $this->add_position_fields_to_columns($columnoptions);
        $this->add_manager_fields_to_columns($columnoptions);
        $this->add_tag_fields_to_columns('course', $columnoptions);
        $this->add_cohort_user_fields_to_columns($columnoptions);
        $this->add_cohort_course_fields_to_columns($columnoptions);
        // Add related user support.
        $this->add_user_fields_to_columns($columnoptions, 'ruser', 'relateduser');

        return $columnoptions;
    }

    protected function define_filteroptions() {
        $filteroptions = array(
            new rb_filter_option(
                'logstore_standard_log',
                'action',
                get_string('action', 'rb_source_site_logstore'),
                'text',
                array()
            ),
            new rb_filter_option(
                'logstore_standard_log',
                'component',
                get_string('component', 'rb_source_site_logstore'),
                'text',
                array()
            ),
            new rb_filter_option(
                'logstore_standard_log',
                'objecttable',
                get_string('objecttable', 'rb_source_site_logstore'),
                'text',
                array()
            ),
            new rb_filter_option(
                'logstore_standard_log',
                'objectid',
                get_string('objectid', 'rb_source_site_logstore'),
                'number',
                array()
            ),
            new rb_filter_option(
                'logstore_standard_log',
                'timecreated',
                get_string('time', 'rb_source_site_logstore'),
                'date',
                array()
            ),
        );

        // Include some standard filters.
        $this->add_user_fields_to_filters($filteroptions);
        $this->add_course_fields_to_filters($filteroptions);
        $this->add_course_category_fields_to_filters($filteroptions);
        $this->add_position_fields_to_filters($filteroptions);
        $this->add_manager_fields_to_filters($filteroptions);
        $this->add_tag_fields_to_filters('course', $filteroptions);
        $this->add_cohort_user_fields_to_filters($filteroptions);
        $this->add_cohort_course_fields_to_filters($filteroptions);

        return $filteroptions;
    }

    protected function define_contentoptions() {
        $contentoptions = array(
            new rb_content_option(
                'current_pos',
                get_string('currentpos', 'totara_reportbuilder'),
                'position.path',
                'position'
            ),
            new rb_content_option(
                'current_org',
                get_string('currentorg', 'totara_reportbuilder'),
                'organisation.path',
                'organisation'
            ),
            new rb_content_option(
                'user',
                get_string('user', 'rb_source_site_logs'),
                array(
                    'userid' => 'base.userid',
                    'managerid' => 'position_assignment.managerid',
                    'managerpath' => 'position_assignment.managerpath',
                    'postype' => 'position_assignment.type',
                ),
                'position_assignment'
            ),
            new rb_content_option(
                'date',
                get_string('date', 'rb_source_site_logstore'),
                'base.timecreated'
            ),
        );
        return $contentoptions;
    }

    protected function define_paramoptions() {
        $paramoptions = array(
            new rb_param_option(
                'userid',
                'base.userid',
                null
            ),
            new rb_param_option(
                'courseid',
                'base.courseid'
            ),
        );

        return $paramoptions;
    }

    protected function define_defaultcolumns() {
        $defaultcolumns = array(
            array(
                'type' => 'logstore_standard_log',
                'value' => 'timecreated',
            ),
            array(
                'type' => 'user',
                'value' => 'namelink',
            ),
            array(
                'type' => 'course',
                'value' => 'courselink',
            ),
            array(
                'type' => 'logstore_standard_log',
                'value' => 'ip',
            ),
            array(
                'type' => 'logstore_standard_log',
                'value' => 'other',
            ),
        );

        return $defaultcolumns;
    }

    protected function define_defaultfilters() {
        $defaultfilters = array(
            array(
                'type' => 'user',
                'value' => 'fullname',
            ),
            array(
                'type' => 'logstore_standard_log',
                'value' => 'targetaction',
                'advanced' => 1,
            ),
            array(
                'type' => 'course',
                'value' => 'fullname',
                'advanced' => 1,
            ),
            array(
                'type' => 'course_category',
                'value' => 'id',
                'advanced' => 1,
            ),
            array(
                'type' => 'user',
                'value' => 'positionpath',
                'advanced' => 1,
            ),
            array(
                'type' => 'user',
                'value' => 'organisationpath',
                'advanced' => 1,
            ),
        );

        return $defaultfilters;
    }


    protected function define_requiredcolumns() {
        $requiredcolumns = array(
        );
        return $requiredcolumns;
    }

    /**
     * Adds the user table to the $joinlist array with different join name
     *
     * @param array &$joinlist Array of current join options
     *                         Passed by reference and updated to
     *                         include new table joins
     * @param string $join Name of the join that provides the
     *                     'user id' field
     * @param string $field Name of user id field to join on
     * @return boolean True
     */
    protected function add_relateduser_table_to_joinlist(&$joinlist, $join, $field) {
        $joinlist[] = new rb_join(
            'ruser',
            'LEFT',
            '{user}',
            "ruser.id = $join.$field",
            REPORT_BUILDER_RELATION_ONE_TO_ONE,
            'base'
        );
    }

    //
    //
    // Source specific column display methods.
    //
    //

    /**
     * Display serialized info in preformated view.
     * @param string $other
     * @param stdClass $row
     * @return string
     */
    public function rb_display_serialized($other, $row) {
        return html_writer::tag('pre', print_r(unserialize($other), true));
    }

    /**
     * Convert IP address into a link to IP lookup page
     * @param string $ip
     * @param stdClass $row
     * @return string
     */
    public function rb_display_iplookup($ip, $row) {
        if (!isset($ip) || $ip == '') {
            return '';
        }
        $params = array('id' => $ip);
        if (isset($row->userid)) {
            $params['user'] = $row->user_id;
        }
        $url = new moodle_url('/iplookup/index.php', $params);
        return html_writer::link($url, $ip);
    }

    /**
     * Displays related educational level.
     * @param string $edulevel
     * @param stdClass $row
     * @return string
     */
    public function rb_display_edulevel($edulevel, $row) {
        switch ($edulevel) {
            case \core\event\base::LEVEL_PARTICIPATING:
                return get_string('edulevelparticipating', 'moodle');
                break;
            case \core\event\base::LEVEL_TEACHING:
                return get_string('edulevelteacher', 'moodle');
                break;
            case \core\event\base::LEVEL_OTHER:
                return get_string('edulevelother', 'moodle');
                break;
        }
        return get_string('unrecognized', 'rb_source_site_logstore', $edulevel);
    }

    /**
     * Displays CRUD verbs.
     * @param string $edulevel
     * @param stdClass $row
     * @return string
     */
    public function rb_display_crud($crud, $row) {
        switch ($crud) {
            case 'c':
                return get_string('crud_c', 'rb_source_site_logstore');
                break;
            case 'r':
                return get_string('crud_r', 'rb_source_site_logstore');
                break;
            case 'u':
                return get_string('crud_u', 'rb_source_site_logstore');
                break;
            case 'd':
                return get_string('crud_d', 'rb_source_site_logstore');
                break;
        }
        return get_string('unrecognized', 'rb_source_site_logstore', $crud);
    }
    /**
     * Displays CRUD verbs.
     * @param string $id
     * @param stdClass $row
     * @return string
     */
    public function rb_display_name($id, $row) {
        $eventdata = (array)$row;
        $event = \core\event\base::restore($eventdata, array());
        return $event->get_name();
    }
    /**
     * Displays event description.
     * @param string $id
     * @param stdClass $row
     * @return string
     */
    public function rb_display_description($id, $row) {
        $eventdata = (array)$row;
        $eventdata['other'] = unserialize($eventdata['other']);
        $event = \core\event\base::restore($eventdata, array());
        return $event->get_description();
    }
}

