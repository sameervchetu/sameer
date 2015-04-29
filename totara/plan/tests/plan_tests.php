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
 * @package totara_learningplans
 * @subpackage tests_generator
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot.'/totara/plan/lib.php');
require_once($CFG->dirroot.'/totara/plan/tests/generator/lib.php');
require_once($CFG->dirroot.'/totara/hierarchy/lib.php');
require_once($CFG->dirroot.'/totara/hierarchy/tests/generator/lib.php');
require_once($CFG->dirroot.'/totara/program/tests/generator/lib.php');


class core_plan_testcase extends advanced_testcase {

    /*
     * A user id is required to add components to a learning plan.
     * Without it, the component will not be added.
     */

    const DUMMY_USER_ID = 2;

    /*
     * Test creating a learning plan.
     */
    public function test_create_learning_plan() {
        $this->resetAfterTest(true);
        $this->create_learning_plan();
    }

    /*
     * Test creating a learning plan and adding a course.
     */
    public function test_add_course_to_learning_plan() {
        global $DB, $USER;
        $this->resetAfterTest(true);

        // Create a learning plan.
        $plan_id = $this->create_learning_plan();

        // Create a new course and check it exists.
        $course = $this->getDataGenerator()->create_course();
        $exists = $DB->record_exists('course', array('id' => $course->id));
        // Assert the existence of the course.
        $this->assertTrue($exists);

        // Add the course to the learning plan.
        $this->add_component_to_learning_plan($plan_id,'course',$course->id);
    }

    /*
     * Test creating a learning plan and adding a competency.
     */
    public function test_add_competency_to_learning_plan() {
        global $DB;
        $this->resetAfterTest(true);

        // Create a learning plan.
        $plan_id = $this->create_learning_plan();

        // Get a hierarchy generator.
        $hierarchy_gen = new totara_hierarchy_generator($this->getDataGenerator());
        // Create a new competency framework and check it exists.
        $comp_fw = $hierarchy_gen->create_framework('competency');
        $exists = $DB->record_exists('comp_framework', array('id' => $comp_fw->id));
        // Assert the existence of the course.
        $this->assertTrue($exists);

        // Create a new competency framework and check it exists.
        $comp = $hierarchy_gen->create_hierarchy($comp_fw->id, 'competency', array('fullname' => 'Test Competency'));
        $exists = $DB->record_exists('comp_framework', array('id' => $comp->id));
        // Assert the existence of the course.
        $this->assertTrue($exists);

        $this->add_component_to_learning_plan($plan_id,'competency',$comp->id);
    }

    /*
     * Test creating a learning plan and adding an objective.
     */
    public function test_add_objective_to_learning_plan() {
        global $DB;
        $this->resetAfterTest(true);

        // Create a learning plan.
        $plan_id = $this->create_learning_plan();
        // Create an objective.
        $objective_id = $this->create_learning_plan_objective($plan_id);
    }


    /*
     * Test creating a learning plan and adding a program.
     */
    public function test_add_program_to_learning_plan() {
        global $DB;
        $this->resetAfterTest(true);

        // Create a learning plan.
        $plan_id = $this->create_learning_plan();

        // Create a new program and check it exists
        $program_gen = new totara_program_generator($this->getDataGenerator());
        $program = $program_gen->create_program();
        $exists = $DB->record_exists('prog', array('id' => $program->id));
        $this->assertTrue($exists);
        // Add program to learning plan.
        $this->add_component_to_learning_plan($plan_id,'program',$program->id);
    }


    /*
     * Create a learning plan.
     *
     * @returns integer Record id of created learning plan.
     */
    private function create_learning_plan() {
        global $DB;

        // Create a new learning plan and check it exists.
        $plan_gen = new totara_plan_generator($this->getDataGenerator());
        $record = $plan_gen->create_learning_plan();
        $exists = $DB->record_exists('dp_plan', array('id' => $record->id));
        // Assert the existence of the record.
        $this->assertTrue($exists);

        return $record->id;
    }

    /*
     * Create a learning plan objective.
     *
     * @returns integer Record id of created objective.
     */
    private function create_learning_plan_objective($plan_id) {
        global $DB;

        // Create a new learning plan objective and check it exists.
        $plan_gen = new totara_plan_generator($this->getDataGenerator());
        $record = $plan_gen->create_learning_plan_objective($plan_id,self::DUMMY_USER_ID);
        $exists = $DB->record_exists('dp_plan_objective', array('id' => $record->id));
        // Assert the existence of the record.
        $this->assertDebuggingCalled(null, null, 'message_send() must print debug message that messaging is disabled in phpunit tests.');
        $this->assertTrue($exists);

        return $record->id;
    }

    /*
     * Add a component to a competency framework.
     */
    private function add_component_to_learning_plan($plan_id,$component,$component_id) {
        global $DB;
        // Add the competency to the learning plan.
        $plan = new development_plan($plan_id,self::DUMMY_USER_ID);
        $componentobj = $plan->get_component($component);
        $componentobj->update_assigned_items(array($component_id));
        // Check the course has been assigned to the learning plan.
        $exists = $DB->record_exists('dp_plan_' . $component . '_assign', array('planid' => $plan_id,$component . 'id' => $component_id));
        // Assert the existence of the record.
        $this->assertTrue($exists);
    }

}
