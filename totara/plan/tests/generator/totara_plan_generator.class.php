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
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Plan generator.
 *
 * @package totara_plan
 */
class totara_plan_generator extends component_generator_base {

    // Default name when created a learning plan.
    const DEFAULT_NAME = 'Test Learning Plan';
    const DEFAULT_NAME_OBJECTIVE = 'Test Objective';

    /**
     * @var integer Keep track of how many learning plans have been created.
     */
    private $learningplancount = 0;

    /**
     * @var integer Keep track of how many learning plan objectives have been created.
     */
    private $learningplanobjectivecount = 0;

    /** @var int  */
    private $evidencetypecount = 0;

    /** @var int  */
    private $evidencecount = 0;
    /**
     * To be called from data reset code only,
     * do not use in tests.
     * @return void
     */
    public function reset() {
        $this->learningplancount = 0;
        $this->learningplanobjectivecount = 0;
        $this->evidencetypecount = 0;
        $this->evidencecount = 0;
    }

    /**
     * Create a learning plan.
     *
     * @param  array    $record Optional record data.
     * @return stdClass Created learning plan instance.
     *
     * @todo Define an array of default values then use
     *       array_merge($default_values, $record) to
     *       merge in the optional record data and reduce
     *       / remove the need for multiple statements
     *       beginning with: if (!isset($record['...
     */
    public function create_learning_plan($record=null) {
        global $DB, $CFG, $USER;
        require_once($CFG->dirroot . '/totara/plan/lib.php');

        $record = (array) $record;
        // Increment the count of learning plans.
        $i = ++$this->learningplancount;

        if (!isset($record['templateid'])) {
            $record['templateid'] = 1;
        }

        if (!isset($record['userid'])) {
            $record['userid'] = $USER->id;
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
            $record['status'] = DP_PLAN_STATUS_APPROVED;
        }

        if (!isset($record['createdby'])) {
            $record['createdby'] = $USER->id;
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

    /**
     * Add a competency to a learning plan.
     *
     * @param int $planid identifying id of a plan object
     * @param int $competencyid identifying id of a competency object
     * @return bool success
     */
    public function add_learning_plan_competency($planid, $competencyid) {
        global $DB, $CFG, $USER;
        require_once($CFG->dirroot . '/totara/plan/lib.php');

        if (!has_capability('totara/plan:manageanyplan', context_system::instance())) {
            debugging('add_learning_plan_competency generator needs totara/plan:manageanyplan capability!');
            return false;
        }

        $plan = new development_plan($planid);
        $plan->viewas = $USER->id;
        $plan->load_roles();
        $plan->load_components();
        $plan->initialize_settings();
        $plan->role = $plan->get_user_role($plan->viewas);
        $componentname = 'competency';
        $component = $plan->get_component($componentname);
        $comps_added = array($competencyid);
        // Get linked courses for newly added competencies.
        $evidence = $component->get_course_evidence_items($comps_added);
        // Add them all.
        $comp_mandatory = array();
        foreach ($evidence as $compid => $linkedcourses) {
            foreach ($linkedcourses as $linkedcourse) {
                if (!isset($comp_mandatory[$competencyid])) {
                    $comp_mandatory[$competencyid] = array();
                }
                $comp_mandatory[$competencyid][] = $linkedcourse->courseid;
            }
        }
        $component->update_assigned_items($comps_added);
        foreach ($comp_mandatory as $compid => $courses) {
            foreach ($courses as $key => $course) {
                if (!$plan->get_component('course')->is_item_assigned($course)) {
                    $plan->get_component('course')->assign_new_item($course, true, false);
                }
                // Now we need to grab the assignment ID.
                $assignmentid = $DB->get_field('dp_plan_course_assign', 'id', array('planid' => $plan->id, 'courseid' => $course), MUST_EXIST);
                // Get the competency assignment ID from the competency.
                $compassignid = $DB->get_field('dp_plan_competency_assign', 'id', array('competencyid' => $competencyid, 'planid' => $plan->id), MUST_EXIST);
                $mandatory = 'course';
                // Create relation.
                $plan->add_component_relation('competency', $compassignid, 'course', $assignmentid, $mandatory);
            }
        }

        return true;
    }

    /**
     * Create an objective for a learning plan.
     *
     * @param  int $planid
     * @param  int $viewas
     * @param  array    $record Optional record data.
     * @return stdClass Created learning plan instance.
     */
    public function create_learning_plan_objective($planid, $viewas, $record=null) {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/totara/plan/lib.php');

        $record = (array) $record;
        // Increment the count of learning plans.
        $i = ++$this->learningplanobjectivecount;

        if (!isset($record['fullname'])) {
            $record['fullname'] = self::DEFAULT_NAME_OBJECTIVE . ' ' . $i;
        }

        if (!isset($record['description'])) {
            $record['description'] = '<p>' . $record['fullname']. ' description</p>';
        }

        if (!isset($record['priority'])) {
            // Get the default priority value from the basic priority scale created on installation.
            $record['priority'] = $DB->get_field('dp_priority_scale', 'defaultid', array('id' => 1));
        }

        if (!isset($record['scalevalueid'])) {
            // Get the default priority value from the basic priority scale created on installation.
            $record['scalevalueid'] = $DB->get_field('dp_objective_scale', 'defaultid', array('id' => 1));
        }

        $plan = new development_plan($planid, $viewas);
        $component = $plan->get_component('objective');
        $id = $component->create_objective(
                $record['fullname'],
                $record['description'],
                $record['priority'],
                NULL, // Field duedate not currently part of objective form.
                $record['scalevalueid']
        );

        return $DB->get_record('dp_plan_objective', array('id' => $id));
    }

    /**
     * Create evidence type
     * @param array $record
     * @return stdClass
     */
    public function create_evidence_type($record = null) {
        global $DB, $USER;

        $record = (array)$record;

        $i = ++$this->evidencetypecount;

        if (!isset($record['name'])) {
            $record['name'] = 'Evidence type ' . $i;
        }

        if (!isset($record['description'])) {
            $record['description'] = 'Evidence description ' . $i;
        }

        if (!isset($record['timemodified'])) {
            $record['timemodified'] = time();
        }

        if (!isset($record['usermodified'])) {
            $record['usermodified'] = $USER->id;
        }

        if (!isset($record['sortorder'])) {
            $record['sortorder'] = $i;
        }

        $id = $DB->insert_record('dp_evidence_type', $record);

        return $DB->get_record('dp_evidence_type', array('id' => $id));
    }

    /**
     * Create evidence
     * @param array $record
     * @return stdClass
     */
    public function create_evidence($record = null) {
        global $DB, $USER;

        $record = (array)$record;

        $i = ++$this->evidencecount;

        if (empty($record['evidencetypeid'])) {
            throw new coding_exception('missing evidencetypeid');
        }

        if (empty($record['userid'])) {
            throw new coding_exception('missing userid');
        }

        if (!isset($record['name'])) {
            $record['name'] = 'Evidence ' . $i;
        }

        if (!isset($record['timecreated'])) {
            $record['timecreated'] = time();
        }

        if (!isset($record['timemodified'])) {
            $record['timemodified'] = time();
        }

        if (!isset($record['usermodified'])) {
            $record['usermodified'] = $USER->id;
        }

        if (!isset($record['readonly'])) {
            $record['readonly'] = 0;
        }

        $id = $DB->insert_record('dp_plan_evidence', $record);

        return $DB->get_record('dp_plan_evidence', array('id' => $id));
    }
}
