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
 * @author David Curry <david.curry@totaralms.com>
 * @package totara
 * @subpackage reportbuilder
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/totara/certification/lib.php');
require_once($CFG->dirroot . '/totara/program/rb_sources/rb_source_program_overview.php');

class rb_source_certification_overview extends rb_source_program_overview {

    /**
     * Overwrite instance type value of totara_visibility_where() in rb_source_program->post_config().
     */
    protected $instancetype = 'certification';

    public function __construct() {
        parent::__construct();

        $this->sourcetitle = get_string('sourcetitle', 'rb_source_certification_overview');
        $this->sourcewhere = $this->define_sourcewhere();
    }

    protected function define_sourcewhere() {
        // Only consider whole certifications - not courseset completion.
        $sourcewhere = 'base.coursesetid = 0';

        // Exclude programs (they have their own source).
        $sourcewhere .= ' AND (program.certifid IS NOT NULL)';

        return $sourcewhere;
    }

    protected function define_joinlist() {
        $joinlist = parent::define_joinlist();

        /* Psuedo Explaination:
         *
         * if (window is open) {
         *      Use current record
         * } else {
         *      if (history record exists) {
         *          use history certifpath
         *      } else {
         *          default to certif
         *      }
         * }
         */
        $now = time();
        $path = CERTIFPATH_CERT;
        $joinlist[] = new rb_join(
            'prog_courseset',
            'INNER',
            '{prog_courseset}',
            "prog_courseset.programid = base.programid
            AND base.coursesetid = 0
            AND (
                   (certif_completion.timewindowopens < {$now} AND prog_courseset.certifpath = certif_completion.certifpath)
                OR (certif_completion.timewindowopens > {$now} AND history.certifpath IS NOT NULL AND prog_courseset.certifpath = history.certifpath)
                OR (certif_completion.timewindowopens > {$now} AND history.certifpath IS NULL AND prog_courseset.certifpath = {$path})
            )
            ",
            REPORT_BUILDER_RELATION_ONE_TO_MANY,
            array('base', 'certif_completion', 'history')
        );

        $joinlist[] = new rb_join(
            'certif_completion',
            'INNER',
            '{certif_completion}',
            "certif_completion.userid = base.userid AND certif_completion.certifid = program.certifid",
            REPORT_BUILDER_RELATION_ONE_TO_ONE,
            'program'
        );

        $joinlist[] = new rb_join(
            'history',
            'LEFT',
            '{certif_completion_history}',
            "certif_completion.userid = history.userid
             AND certif_completion.certifid = history.certifid
             AND history.timecompleted = (SELECT MAX(timecompleted)
                                            FROM {certif_completion_history} cch
                                           WHERE cch.userid = history.userid
                                             AND cch.certifid = history.certifid)",
            REPORT_BUILDER_RELATION_ONE_TO_ONE,
            'certif_completion'
        );

        $this->add_certification_table_to_joinlist($joinlist, 'program', 'certifid');

        return $joinlist;
    }

    protected function define_columnoptions() {
        global $DB;

        $columnoptions = parent::define_columnoptions();

        $this->add_certification_fields_to_columns($columnoptions, 'certif', 'totara_certification');

        // Certification path col.
        $columnoptions[] = new rb_column_option(
            'certif_completion',
            'certifpath',
            get_string('certifpath', 'rb_source_certification_overview'),
            'certif_completion.certifpath',
            array(
                'joins' => 'certif_completion',
                'displayfunc' => 'certif_certifpath'
            )
        );

        $columnoptions[] = new rb_column_option(
            'certif_completion',
            'status',
            get_string('status', 'rb_source_dp_certification'),
            'certif_completion.status',
            array(
                'joins' => 'certif_completion',
                'displayfunc' => 'certif_status',
                'extrafields' => array(
                )
            )
        );

        $columnoptions[] = new rb_column_option(
            'certif_completion',
            'renewalstatus',
            get_string('renewalstatus', 'rb_source_dp_certification'),
            'certif_completion.renewalstatus',
            array(
                'joins' => 'certif_completion',
                'displayfunc' => 'certif_renewalstatus',
                'extrafields' => array(
                )
            )
        );

        $columnoptions[] = new rb_column_option(
            'certif_completion',
            'timewindowopens',
            get_string('timewindowopens', 'rb_source_dp_certification'),
            'certif_completion.timewindowopens',
            array(
                'joins' => 'certif_completion',
                'displayfunc' => 'nice_date',
                'extrafields' => array(
                    'status' => 'certif_completion.status'
                )
            )
        );

        $columnoptions[] = new rb_column_option(
            'certif_completion',
            'timeexpires',
            get_string('timeexpires', 'rb_source_dp_certification'),
            'certif_completion.timeexpires',
            array(
                'joins' => 'certif_completion',
                'displayfunc' => 'nice_date',
                'extrafields' => array(
                    'status' => 'certif_completion.status'
                )
            )
        );

        $columnoptions[] = new rb_column_option(
            'certif_completion',
            'timecompleted',
            get_string('timecompleted', 'rb_source_dp_certification'),
            'certif_completion.timecompleted',
            array(
                'joins' => 'certif_completion',
                'displayfunc' => 'nice_date'
            )
        );

        $columnoptions[] = new rb_column_option(
            'certif_completion',
            'progress',
            get_string('programcompletionprogress', 'rb_source_program_overview'),
            $DB->sql_concat_join("'|'", array(sql_cast2char('prog_courseset.id'), sql_cast2char("prog_completion.status"))),
            array(
                'extrafields' => array(
                    'completion' => "certif_completion.timecompleted",
                    'window' => "certif_completion.timewindowopens",
                    'histpath' => "history.certifpath",
                    'histcomp' => "history.timecompleted",
                ),
                'displayfunc' => 'certif_completion_progress',
                'grouping' => 'comma_list',
                'joins' => array('prog_completion', 'certif_completion', 'history'),
            )
        );

        $columnoptions[] = new rb_column_option(
            'course',
            'shortname',
            get_string('courseshortname', 'rb_source_program_overview'),
            'COALESCE('.$DB->sql_concat_join("'|'", array('course.shortname', sql_cast2char('course.id'))).', \'-\')',
            array(
                'joins' => 'course',
                'grouping' => 'comma_list',
                'displayfunc' => 'list_to_newline_coursename',
                'style' => array('white-space' => 'pre'),
            )

        );

        $columnoptions[] = new rb_column_option(
            'course',
            'status',
            get_string('coursecompletionstatus', 'rb_source_program_overview'),
            sql_cast2char('COALESCE(course_completions.status, '.COMPLETION_STATUS_NOTYETSTARTED.')'),
            array(
                'joins' => 'course_completions',
                'grouping' => 'comma_list',
                'displayfunc' => 'course_completion_status',
                'style' => array('white-space' => 'pre'),
            )

        );

        return $columnoptions;
    }

    protected function define_defaultcolumns() {
        $defaultcolumns = parent::define_defaultcolumns();

        $defaultcolumns[] = array('type' => 'certif_completion', 'value' => 'certifpath');

        return $defaultcolumns;
    }

    protected function define_filteroptions() {
        $filteroptions = parent::define_filteroptions();

        $this->add_certification_fields_to_filters($filteroptions, 'totara_certification');

        return $filteroptions;
    }

    function rb_display_certif_completion_progress($status, $row, $isexport = false) {
        global $PAGE;

        $now = time();
        $totara_renderer = $PAGE->get_renderer('totara_core');

        if ($row->window < $now) {
            // The window is open, use the current record.
            $completions = array();
            $tempcompletions = explode(', ', $status);

            foreach ($tempcompletions as $completion) {
                $coursesetstatus = explode("|", $completion);
                if (isset($coursesetstatus[1])) {
                    $completions[$coursesetstatus[0]] = $coursesetstatus[1];
                } else {
                    $completions[$coursesetstatus[0]] =  STATUS_COURSESET_INCOMPLETE;
                }
            }

            $cnt = count($completions);
            if ($cnt == 0) {
                return '-';
            }
            $complete = 0;

            foreach ($completions as $comp) {
                if ($comp == STATUS_COURSESET_COMPLETE) {
                    $complete++;
                }
            }

            $percentage = round(($complete / $cnt) * 100, 2);
        } else {
            // The window is not open
            if (!empty($row->histcompletion) || !empty($row->completion)) {
                // But they have previously completed or currently completed.
                $percentage = 100;
            } else {
                // They havent had a chance to do anything yet, or did not previously complete.
                $percentage = 0;
            }
        }

        // Get relevant progress bar and return for display.
        return $totara_renderer->print_totara_progressbar($percentage, 'medium', $isexport, $percentage . '%');
    }

    /**
     * Certification display the certification status as string.
     *
     * @param string $status
     * @param array $row
     * @return string
     */
    function rb_display_certif_status($status, $row) {
        global $CERTIFSTATUS;

        if ($status && isset($CERTIFSTATUS[$status])) {
            return get_string($CERTIFSTATUS[$status], 'totara_certification');
        }

        return '';
    }

}
