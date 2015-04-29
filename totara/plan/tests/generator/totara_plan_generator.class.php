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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Rob Tyler <rob.tyler@totaralms.com>
 * @package totara_plan
 * @subpackage test
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Plan generator.
 *
 * @package totara_plan
 * @subpackage test
 */
class totara_plan_generator extends component_generator_base {

    // Default name when created a learning plan.
    const DEFAULT_NAME = 'Test Learning Plan';
    const DEFAULT_NAME_OBJECTIVE = 'Test Objective';

    /*
     * @var integer Keep track of how many learning plans have been created.
     */
    private $learningplancount = 0;

    /*
     * @var integer Keep track of how many learning plan objectives have been created.
     */
    private $learningplanobjectivecount = 0;

    /*
     * Create a learning plan.
     *
     * @param  array    $record Optional record data.
     * @return stdClass Created learning plan instance.
     *
     * @todo Define an array of default values then use
     *       array_merge($default_values,$record) to
     *       merge in the optional record data and reduce
     *       / remove the need for multiple statements
     *       beginning with: if (!isset($record['...
     */
    public function create_learning_plan($record=null) {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/totara/plan/lib.php');

        $record = (array) $record;
        // Increment the count of learning plans.
        $i = ++$this->learningplancount;

        if (!isset($record['templateid'])) {
            $record['templateid'] = 1;
        }

        if (!isset($record['userid'])) {
            $record['userid'] = 2;
        }

        if (!isset($record['name'])) {
            $record['name'] = trim(self::DEFAULT_NAME) . ' ' .$i;
        }

        if (!isset($record['description'])) {
            $record['description'] = '<p>' . $record['name'] . ' description</p>';
        }

        if (!isset($record['startdate'])) {
            $record['startdate'] = strtotime(date('Y') . '-01-01');
        }

        if (!isset($record['enddate'])) {
            $record['enddate'] = strtotime(date('Y') . '-12-31');
        }

        if (!isset($record['status'])) {
            $record['status'] = (mt_rand(0, 1)) ? DP_PLAN_STATUS_APPROVED : DP_PLAN_STATUS_UNAPPROVED;
        }

        if (!isset($record['createdby'])) {
            $record['createdby'] = 2;
        }

        // Create a record for the given id or one
        // with an id that's next in the sequence.
        if (isset($record['id'])) {
            $DB->import_record('dp_plan', $record);
            $DB->get_manager()->reset_sequence('dp_plan');
            $id = $record['id'];
        } else {
            $id = $DB->insert_record('dp_plan', $record);
        }

        return $DB->get_record('dp_plan', array('id' => $id));
    }

    /*
     * Create an objective for a learning plan.
     *
     * @param  array    $record Optional record data.
     * @return stdClass Created learning plan instance.
     */
    public function create_learning_plan_objective($plan_id,$userid,$record=null) {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/totara/plan/lib.php');

        $record = (array) $record;
        // Increment the count of learning plans.
        $i = ++$this->learningplanobjectivecount;

        if (!isset($record['fullname'])) {
            $record['fullname'] = self::DEFAULT_NAME_OBJECTIVE . ' ' .$i;
        }

        if (!isset($record['description'])) {
            $record['description'] = '<p>' . $record['fullname']. ' description</p>';
        }

        if (!isset($record['priority'])) {
            // Get the default priority value from the basic priority scale created on installation.
            $defaultvalue = $DB->get_field('dp_priority_scale', 'defaultid', array('id' => 1));
            $record['priority'] = ( $defaultvalue ? $defaultvalue : 0 ); // 0 = None
        }

        if (!isset($record['scalevalueid'])) {
            // Get the default priority value from the basic priority scale created on installation.
            $defaultvalue = $DB->get_field('dp_objective_scale', 'defaultid', array('id' => 1));
            $record['scalevalueid'] = ( $defaultvalue ? $defaultvalue : 0 ); // 3 = Not Started
        }

        $plan = new development_plan($plan_id,$userid);
        $component = $plan->get_component('objective');
        $id = $component->create_objective(
                $record['fullname'],
                $record['description'],
                $record['priority'],
                NULL, // duedate not currently part of objective form.
                $record['scalevalueid']
        );

        return $DB->get_record('dp_plan_objective', array('id' => $id));
    }

}
