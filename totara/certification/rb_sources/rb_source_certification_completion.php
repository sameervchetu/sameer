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
require_once($CFG->dirroot . '/totara/program/rb_sources/rb_source_program_completion.php');

class rb_source_certification_completion extends rb_source_program_completion {

    /**
     * Overwrite instance type value of totara_visibility_where() in rb_source_program->post_config().
     */
    protected $instancetype = 'certification';

    public function __construct() {
        parent::__construct();

        $this->sourcetitle = get_string('sourcetitle', 'rb_source_certification_completion');
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

        $this->add_certification_table_to_joinlist($joinlist, 'program', 'certifid');

        $joinlist[] = new rb_join(
            'certif_completion',
            'LEFT',
            '{certif_completion}',
            "certif_completion.userid = base.userid AND certif_completion.certifid = program.certifid",
            REPORT_BUILDER_RELATION_ONE_TO_ONE,
            'base'
        );

        $joinlist[] = new rb_join(
            'prog_courseset',
            'LEFT',
            '{certif_completion}',
            "certif_completion.userid = base.userid AND certif_completion.certifid = program.certifid",
            REPORT_BUILDER_RELATION_ONE_TO_MANY,
            'base'
        );

        return $joinlist;
    }

    protected function define_columnoptions() {
        $columnoptions = parent::define_columnoptions();

        $this->add_certification_fields_to_columns($columnoptions, 'certif', 'totara_certification');

        return $columnoptions;
    }

    protected function define_filteroptions() {
        $filteroptions = parent::define_filteroptions();

        $this->add_certification_fields_to_filters($filteroptions, 'totara_certification');

        return $filteroptions;
    }
}
