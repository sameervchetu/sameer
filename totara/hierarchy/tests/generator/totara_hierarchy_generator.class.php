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
 * @author Rob Tyler <rob.tyler@totaralms.com>
 * @package totara_hierarchy
 * @subpackage test
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Hierarchy generator
 *
 * @package totara_hierarchy
 * @subpackage test
 */
class totara_hierarchy_generator extends component_generator_base {

    // Default names when created a framework.
    const DEFAULT_NAME_FRAMEWORK_COMPETENCY = 'Test Competency Framework';
    const DEFAULT_NAME_FRAMEWORK_GOAL = 'Test Goal Framework';
    const DEFAULT_NAME_FRAMEWORK_ORGANISATION = 'Test Organisation Framework';
    const DEFAULT_NAME_FRAMEWORK_POSITION = 'Test Position Framework';

    // Default names when created a hierarchy.
    const DEFAULT_NAME_HIERARCHY_COMPETENCY = 'Test Competency';
    const DEFAULT_NAME_HIERARCHY_GOAL = 'Test Goal';
    const DEFAULT_NAME_HIERARCHY_ORGANISATION = 'Test Organisation';
    const DEFAULT_NAME_HIERARCHY_POSITION = 'Test Position';

    /**
     * @var array Map of hierarchy type and prefix
     */
    private $hierarchy_type_prefix = array('competency' => 'comp',
                                           'goal'=> 'goal',
                                           'organisation' => 'org',
                                           'position' => 'pos');
    /**
     * @var array integer Number of items to be assigned.
     */
    private $hierarchy_assign_quantities = array(2, 4, 8, 16, 32, 64);

    /**
     * @var integer Keep track of how many frameworks have been created.
     */
    private $frameworkcount = array ('competency' => 0,
                                     'goal' => 0,
                                     'organisation' => 0,
                                     'position' => 0);
    /**
     * @var integer Keep track of how many hierarchies have been created.
     */
    private $hierarchycount = array ('competency' => 0,
                                     'goal' => 0,
                                     'organisation' => 0,
                                     'position' => 0);

    /**
     * Create a framework for the given prefix.
     *
     * @param string $prefix Prefix that identifies the type of hierarchy (position, organisation, etc)
     * @param array $record
     * @return int hierarchy framework id
     *
     * @todo Define an array of default values then use
     *       array_merge($default_values,$record) to
     *       merge in the optional record data and reduce
     *       / remove the need for multiple statements
     *       beginning with: if (!isset($record['...
     */
    public function create_framework($prefix, $record = array()) {
        global $USER, $DB, $CFG;
        require_once($CFG->dirroot . '/totara/hierarchy/lib.php');

        $record = (array) $record;
        $shortprefix = hierarchy::get_short_prefix($prefix);
        // Increment the count for the given framework.
        $i = ++$this->frameworkcount[$prefix];

        if (!isset($record['visible'])) {
            $record['visible'] = 1;
        }

        if (!isset($record['fullname'])) {
            $defaultnameconst = 'self::DEFAULT_NAME_FRAMEWORK_' . strtoupper($prefix);
            $record['fullname'] = trim(constant($defaultnameconst)) . ' ' .$i;
        }

        if (!isset($record['idnumber'])) {
            $record['idnumber'] = totara_generator_util::create_short_name($record['fullname']);
        }

        if (!isset($record['description'])) {
            $record['description'] = '<p>' . $record['fullname'] . ' description</p>';
        }

        // Get the sort order from the database.
        if (!isset($record['sortorder'])) {
            $record['sortorder'] = $DB->get_field($shortprefix.'_framework', 'MAX(sortorder) + 1', array());
        }
        // A sort order may not have been found in the database or may have an invalid 0 or NULL value.
        if (!$record['sortorder']) {
            $record['sortorder'] = 1;
        }

        if (!isset($record['hidecustomfields'])) {
            $record['hidecustomfields'] = '0';
        }

        $record['timecreated'] = time();
        $record['timemodified'] = $record['timecreated'];
        $record['usermodified'] = $USER->id;

        $framework_id = $DB->insert_record($shortprefix.'_framework', $record);
        $framework = $DB->get_record($shortprefix.'_framework', array('id' => $framework_id));

        if (!isset($record['scale'])) {
            $record['scale'] = 1;
        }

        // If this is an competency or organisation
        // framework we need to assign a scale to it.
        if ($prefix == 'competency' || $prefix == 'goal') {
            // We need to assign a scale to the
            $scale_assign = new stdClass();
            $scale_assign->scaleid = $record['scale'];
            $scale_assign->frameworkid = $framework_id;
            $scale_assign->timemodified = time();
            $scale_assign->usermodified = $USER->id;

            $scale_assign_id = $DB->insert_record($shortprefix . '_scale_assignments', $scale_assign);
        }

        return $framework;
    }

    /**
     * Create a hierarchy based on the shortprefix and assign it to a framework.
     *
     * @param $frameworkid
     * @param $prefix
     * @param null $record
     * @return bool|int hierarchy item id
     *
     * @todo Define an array of default values then use
     *       array_merge($default_values,$record) to
     *       merge in the optional record data and reduce
     *       / remove the need for multiple statements
     *       beginning with: if (!isset($record['...
     */
    public function create_hierarchy($frameworkid, $prefix, $record = null) {
        global $DB, $USER, $CFG;
        require_once($CFG->dirroot . '/totara/hierarchy/lib.php');

        if (is_string($record)) {
            throw new coding_exception('$record parameter must be array or object');
        }

        $record = (array) $record;
        // Increment the count for the given hierarchy.
        $i = ++$this->hierarchycount[$prefix];

        if (!isset($record['fullname'])) {
            $defaultnameconst = 'self::DEFAULT_NAME_HIERARCHY_' . strtoupper($prefix);
            $record['fullname'] = trim(constant($defaultnameconst)) . ' ' .$i;
        }

        if (!isset($record['idnumber'])) {
            $record['idnumber'] = totara_generator_util::create_short_name($record['fullname']);
        }

        if (!isset($record['description'])) {
            $record['description'] = '<p>' . $record['fullname'] . ' description</p>';
        }

        if (!isset($record['visible'])) {
            $record['visible'] = 1;
        }

        if (!isset($record['hidecustomfields'])) {
            $record['hidecustomfields'] = 0;
        }

        if (!isset($record['parentid'])) {
            $record['parentid'] = 0;
        }

        if (!isset($record['aggregationmethod'])) {
            // Get a default value for the agreggation method.
            // This variable is used to build the select menu
            // in the hierarchy form.
            global $COMP_AGGREGATION;
            $record['aggregationmethod'] = $COMP_AGGREGATION['ALL'];
        }

        if (!isset($record['proficiencyexpected'])) {
            // The default value for proficiencyexpected
            // is hard coded in the hierarchy form.
            $record['proficiencyexpected'] = 1;
        }

        $record['frameworkid'] = $frameworkid;
        $record['timecreated'] = time();
        $record['timemodified'] = $record['timecreated'];
        $record['usermodified'] = $USER->id;

        $record = (object) $record;
        $hierarchy = hierarchy::load_hierarchy($prefix);
        $itemnew = $hierarchy->process_additional_item_form_fields($record);
        $item = $hierarchy->add_hierarchy_item($itemnew, $itemnew->parentid, $itemnew->frameworkid, false);

        return $item;
    }

    /**
     * Create some hierarchies.
     *
     * @param int $frameworkid The framework to assign the hierachies to.
     * @param string $prefix The type of hierarchy to create.
     * @param int $quantity The number of hierarchies to create.
     * @param string $name The base name of the hierarchy.
     * @param int $randomise_percent Randomly determine (by percentage) if the hierarchy is created.
     * @return array of hierarchies
     */
    public function create_hierarchies($frameworkid, $prefix, $quantity, $name = '', $randomise_percent = 0, $hierarchy_extra_data = array() ) {
        global $CFG;
        require_once($CFG->dirroot . '/totara/hierarchy/lib.php');

        // Create the objective name we want to use with by ge3tting
        // the number off any previous matching records we created.
        if (!$name) {
            $name = 'self::DEFAULT_NAME_HIERARCHY_' . strtoupper($prefix);
        }
        $shortprefix = hierarchy::get_short_prefix($prefix);
        $number = totara_generator_util::get_next_record_number($shortprefix, 'fullname', $name);

        $hierarchy_data = array ();
        $hierarchy_ids = array ();
        // Create the quantity of hierarchies we need.
        for ($i = 1; $i <= $quantity; $i++) {
            // Create a hierarchy, or apply randomisation and create if required.
            if ($randomise_percent == 0 || ($randomise_percent && get_random_act($randomise_percent))) {
                $hierarchy_data['fullname'] = $name . ' ' . $number++;
                $create_data = array_merge ($hierarchy_data, $hierarchy_extra_data);
                $hierarchy = $this->create_hierarchy($frameworkid, $prefix, $create_data);
                $hierarchy_ids[$i] = $hierarchy->id;
            }
        }

        return $hierarchy_ids;
    }


    /**
     * Assign primary positions to a user.
     *
     * @param $userid
     * @param $managerid
     * @param $organisationid
     * @param $positionid
     * @param null $record
     * @return void
     */
    public function assign_primary_position($userid, $managerid, $organisationid, $positionid, $record = null) {
        global $CFG;
        require_once($CFG->dirroot . '/totara/hierarchy/lib.php');

        $data = new stdClass();
        $data->type = (isset($record['type'])) ? $record['type'] : POSITION_TYPE_PRIMARY;
        $data->userid = (isset($record['userid'])) ? $record['userid'] : $userid;
        $data->managerid = (isset($record['managerid'])) ? $record['managerid'] : $managerid; // Assign manager to user position.
        $data->organisationid = (isset($record['organisationid'])) ? $record['organisationid'] : $organisationid; // Assign org.
        $data->positionid = (isset($record['positionid'])) ? $record['positionid'] : $positionid; // Assign pos.

        // Other fields.
        if (isset($record['timevalidfrom'])) {
            $data->timevalidfrom = $record['timevalidfrom'];
        }

        if (isset($record['timevalidto'])) {
            $data->timevalidto = $record['timevalidto'];
        }

        // Attempt to load the assignment.
        $position_assignment = new position_assignment(
            array(
                'userid'    => $data->userid,
                'type'      => $data->type
            )
        );
        $position_assignment::set_properties($position_assignment, $data); // Setup data.
        assign_user_position($position_assignment);
    }


    /**
     * Assigns random courses to competencies.
     *
     * @param $size int number of items to process.
     */
    public function assign_competency($size) {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/totara/hierarchy/lib.php');
        require_once($CFG->dirroot . '/totara/hierarchy/prefix/competency/lib.php');

        // Do not assign the site course!
        $site = get_site();
        // Track the ones we have already assigned.
        $assignedhierarchies = array();
        // Get $size competencies.
        for ($x=0; $x < $size; $x++) {
            // Find one we have not already used.
            $uniquehierarchy = false;
            while (!$uniquehierarchy) {
                $hierarchyid = totara_generator_util::get_random_record_id($this->hierarchy_type_prefix['competency']);
                if (!in_array($hierarchyid, $assignedhierarchies)) {
                    $assignedhierarchies[] = $hierarchyid;
                    $uniquehierarchy = true;
                }
            }
            // Load competency
            if ($competency = $DB->get_record('comp', array('id' => $hierarchyid))) {
                // Assign random number of courses up to $size.
                $coursesassigned = 0;
                $coursestoassign = mt_rand(0, $size);
                while ($coursesassigned < $coursestoassign) {
                    // Set up the completion evidence type.
                    $evidence = competency_evidence_type::factory(array('itemtype' => 'coursecompletion'));
                    $evidence->iteminstance = totara_generator_util::get_random_record_id('course');
                    if ($evidence->iteminstance != $site->id && !$DB->record_exists('comp_criteria', array('competencyid' => $hierarchyid, 'itemtype' => 'coursecompletion', 'iteminstance' => $evidence->iteminstance))) {
                        // Randomise mandatory or optional.
                        $evidence->linktype = mt_rand(0,1);
                        // Assign courses to competency.
                        $newevidenceid = $evidence->add($competency);
                        $coursesassigned++;
                    }
                }
            }
        }
        echo "\n" . get_string('progress_assigncoursecompetencies', 'totara_generator');
    }

    /**
     * Assigns random competencies and goals to organisations.
     *
     * @param $size int number of items to process.
     */
    public function assign_organisation($size) {
        $this->assign_competency_to_hierarchy('organisation', $size);
    }

    /**
     * Assigns random competencies and goals to positions
     *
     * @param $size int number of items to process.
     */
    public function assign_position($size) {
        $this->assign_competency_to_hierarchy('position', $size);
    }

    /**
     * Assigns random user groups to goals.
     *
     * @param $size int number of items to process.
     */
    public function assign_goal($size) {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/totara/hierarchy/lib.php');
        require_once($CFG->dirroot . '/totara/hierarchy/prefix/goal/assign/lib.php');
        require_once($CFG->dirroot . '/totara/hierarchy/prefix/goal/lib.php');

        // Track the ones we have already assigned.
        $assignedhierarchies = array();
        // Get all goals to assign at least one group.
        for ($x=0; $x < $size; $x++) {
            // Find one we have not already used.
            $uniquehierarchy = false;
            while (!$uniquehierarchy) {
                $hierarchyid = totara_generator_util::get_random_record_id($this->hierarchy_type_prefix['goal']);
                if (!in_array($hierarchyid, $assignedhierarchies)) {
                    $assignedhierarchies[] = $hierarchyid;
                    $uniquehierarchy = true;
                }
            }
            // Get the base goal item.
            $item = $DB->get_record('goal', array('id' => $hierarchyid));
            $baseclassname = "totara_assign_goal";
            $baseclass = new $baseclassname('goal', $item);
            // Assign random pos, org or cohort groups to this goal.
            $grouptypes = array('pos', 'org', 'cohort');
            $groupstoassign = mt_rand(1,3);
            for ($i=0; $i < $groupstoassign; $i++) {
                $grouptype = $grouptypes[mt_rand(0,2)];
                $grouptypeobj = $baseclass->load_grouptype($grouptype);
                // Get a random record from the groups.
                $groupid = totara_generator_util::get_random_record_id($grouptype);
                $grouptypeobj->validate_item_selector($groupid);
                $urlparams = array('module' => 'group',
                        'grouptype' => $grouptype,
                        'itemid' => $hierarchyid,
                        'add' => 1,
                        'listofvalues' => array($groupid),
                        'includechildren' => 0
                );
                $grouptypeobj->handle_item_selector($urlparams);
            }
        }
        echo "\n" . get_string('progress_assigngoalusergroups', 'totara_generator');
    }

    /**
     * Assigns a random number of competencies from 1 to $size to each item in the hierarchy type
     *
     * @param $hierarchytype string usually 'position' or 'organisation'
     * @param $size int number of items to process.
     */
    private function assign_competency_to_hierarchy($hierarchytype, $size) {
        global $CFG, $DB, $USER;
        require_once($CFG->dirroot . '/totara/hierarchy/lib.php');
        require_once($CFG->dirroot . '/totara/hierarchy/prefix/competency/lib.php');
        require_once($CFG->dirroot . '/totara/hierarchy/prefix/' . $hierarchytype . '/lib.php');

        $time = time();
        // Track the ones we have already assigned.
        $assignedhierarchies = array();
        // Assign something to every hierarchy item.
        for ($x=0; $x < $size; $x++) {
            // Find one we have not already used.
            $uniquehierarchy = false;
            while (!$uniquehierarchy) {
                $hierarchyid = totara_generator_util::get_random_record_id($this->hierarchy_type_prefix[$hierarchytype]);
                if (!in_array($hierarchyid, $assignedhierarchies)) {
                    $assignedhierarchies[] = $hierarchyid;
                    $uniquehierarchy = true;
                }
            }
            // Setup hierarchy objects
            $competencies = new competency();
            $hierarchies = new $hierarchytype();
            // Load position
            if (!$hierarchy = $hierarchies->get_item($hierarchyid)) {
                print_error("{$hierarchytype}notfound", 'totara_hierarchy');
            }
            // Currently assigned competencies
            if (!$currentlyassigned = $hierarchies->get_assigned_competencies($hierarchyid)) {
                $currentlyassigned = array();
            }
            $addcompetencies = 0;
            $add = array();
            $competencytoassign = mt_rand(1, $size);
            while ($addcompetencies < $competencytoassign) {
                $newcomp = totara_generator_util::get_random_record_id ($this->hierarchy_type_prefix['competency']);
                if (!in_array($newcomp, $currentlyassigned)) {
                    $add[] = $newcomp;
                    // Add it to currently assigned too - on small sites it may try to add the same competency twice.
                    $currentlyassigned[] = $newcomp;
                    $addcompetencies++;
                }
            }
            foreach ($add as $addition) {
                // Add relationship
                $related = $competencies->get_item($addition);
                $relationship = new stdClass();
                $field = "{$hierarchytype}id";
                $relationship->$field = $hierarchy->id;
                $relationship->competencyid = $related->id;
                $relationship->timecreated = $time;
                $relationship->usermodified = $USER->id;
                $relationship->linktype = mt_rand(0,1);
                $relationship->id = $DB->insert_record($this->hierarchy_type_prefix[$hierarchytype] . '_competencies', $relationship);
            }
        }
        echo "\n" . get_string('progress_assigncompetenciestohierarchy', 'totara_generator', get_string($hierarchytype, 'totara_hierarchy'));
    }

    /**
     * Get immediate reports for a manager.
     * @param $managerid id of the manager.
     * @return array of userids.
     */
    function get_subordinates($managerid){
        global $DB;

        return $DB->get_fieldset_select('pos_assignment', 'userid', 'managerid = :manager', array('manager' => $managerid));
    }

    /**
     * Get entire list of subordinates at all levels below a given manager.
     * @param $managerid id of the manager.
     * @return array of userids.
     */
    function get_manager_hierarchy($parentid) {
        $tree = array();
        if (!empty($parentid)) {
            $tree = $this->get_subordinates($parentid);
            if (!empty($tree)) {
                foreach ($tree as $key => $value) {
                    $ids = $this->get_manager_hierarchy($value);
                    $tree = array_merge($tree, $ids);
                }
            }
        }
        return $tree;
    }
}
