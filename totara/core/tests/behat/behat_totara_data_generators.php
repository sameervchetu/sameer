<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2015 onwards Totara Learning Solutions LTD
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
 * @author Petr Skoda <petr.skoda@totaralms.com>
 * @package totara_core
 * @category  test
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');
require_once(__DIR__ . '/helper_generator.php');

use Behat\Gherkin\Node\TableNode as TableNode;
use Behat\Behat\Exception\PendingException as PendingException;

/**
 * Class to set up quickly a Given environment.
 *
 * Acceptance tests are block-boxed, so this steps definitions should only
 * be used to set up the test environment as we are not replicating user steps.
 *
 * All data generators should be in lib/testing/generator/*, shared between phpunit
 * and behat and they should be called from here, if possible using the standard
 * 'create_$elementname($options)' and if it's not possible (data generators arguments will not be
 * always the same) or the element is not suitable to be a data generator, create a
 * 'process_$elementname($options)' method and use the data generator from there if possible.
 */
class behat_totara_data_generators extends behat_base {
    /**
     * Each component element specifies:
     * - The data generator sufix used.
     * - The required fields.
     * - The mapping between other elements references and database field names.
     * @var array
     */
    protected static $componentelements = array(
        // NOTE: this could be dynamic, but it is not a problem for Totara.
        'totara_program' => array(
            'programs' => array(
                'datagenerator' => 'program',
                'required' => array('shortname'),
            ),
            'program assignments' => array(
                'datagenerator' => 'prog_assign',
                'required' => array('user', 'program'),
                'switchids' => array(
                    'user' => 'userid',
                    'program' => 'programid',
                ),
            ),
            'certifications' => array(
                'datagenerator' => 'certification',
                'required' => array('shortname'),
            ),
        ),
        'totara_hierarchy' => array(
            'position frameworks' => array(
                'datagenerator' => 'pos_frame',
                'required' => array('idnumber'),
            ),
            'positions' => array(
                'datagenerator' => 'pos',
                'required' => array('fullname', 'idnumber', 'pos_framework'),
                'switchids' => array(
                    'pos_framework' => 'frameworkid',
                ),
            ),
            'position assignments' => array(
                'datagenerator' => 'pos_assign',
                'required' => array('user', 'position'),
                'switchids' => array(
                    'user' => 'userid',
                    'position' => 'positionid',
                ),
            ),
            'organisation frameworks' => array(
                'datagenerator' => 'org_frame',
                'required' => array('idnumber'),
            ),
            'organisations' => array(
                'datagenerator' => 'org',
                'required' => array('fullname', 'idnumber', 'org_framework'),
                'switchids' => array(
                    'org_framework' => 'frameworkid',
                ),
            ),
            'organisation assignments' => array(
                'datagenerator' => 'org_assign',
                'required' => array('user', 'organisation'),
                'switchids' => array(
                    'user' => 'userid',
                    'organisation' => 'organisationid',
                ),
            ),
            'manager assignments' => array(
                'datagenerator' => 'man_assign',
                'required' => array('user', 'manager'),
                'switchids' => array(
                    'user' => 'userid',
                    'manager' => 'managerid',
                ),
            ),
        ),
    );

    /**
     * Creates the specified element. More info about available elements in http://docs.moodle.org/dev/Acceptance_testing#Fixtures.
     *
     * @Given /^the following "(?P<element_string>(?:[^"]|\\")*)" exist in "([a-z0-9_]*)" plugin:$/
     *
     * @throws Exception
     * @throws PendingException
     * @param string    $elementname The name of the entity to add
     * @param string    $component The Frankenstyle name of the plugin
     * @param TableNode $data
     */
    public function the_following_exist_in_plugin($elementname, $component, TableNode $data) {

        // Now that we need them require the data generators.
        require_once(__DIR__ . '/../../../../lib/testing/generator/lib.php');

        if (empty(self::$componentelements[$component][$elementname])) {
            throw new PendingException($elementname . ' data generator is not implemented');
        }

        $helper = new totara_core_behat_helper_generator();
        $componentgenerator = testing_util::get_data_generator()->get_plugin_generator($component);

        $elementdatagenerator = self::$componentelements[$component][$elementname]['datagenerator'];
        $requiredfields = self::$componentelements[$component][$elementname]['required'];
        if (!empty(self::$componentelements[$component][$elementname]['switchids'])) {
            $switchids = self::$componentelements[$component][$elementname]['switchids'];
        }

        foreach ($data->getHash() as $elementdata) {

            // Check if all the required fields are there.
            foreach ($requiredfields as $requiredfield) {
                if (!isset($elementdata[$requiredfield])) {
                    throw new Exception($elementname . ' requires the field ' . $requiredfield . ' to be specified');
                }
            }

            // Switch from human-friendly references to ids.
            if (isset($switchids)) {
                foreach ($switchids as $element => $field) {
                    // Not all the switch fields are required, default vars will be assigned by data generators.
                    if (isset($elementdata[$element])) {
                        // Temp $id var to avoid problems when $element == $field.
                        if (method_exists($this, 'get_' . $element . '_id')) {
                            $id = $this->{'get_' . $element . '_id'}($elementdata[$element]);
                            unset($elementdata[$element]);
                            $elementdata[$field] = $id;
                        } else if ($helper->get_exists($elementdatagenerator)) {
                            $id = $helper->protected_get($elementdatagenerator, $elementdata[$element]);
                            unset($elementdata[$element]);
                            $elementdata[$field] = $id;
                        } else {
                            // Nothing to change.
                        }
                    }
                }
            }

            // Preprocess the entities that requires a special treatment.
            if (method_exists($this, 'preprocess_' . $elementdatagenerator)) {
                $elementdata = $this->{'preprocess_' . $elementdatagenerator}($elementdata);
            } else if ($helper->preprocess_exists($elementdatagenerator)) {
                $elementdata = $helper->protected_preprocess($elementdatagenerator, $elementdata);
            }

            // Creates element.
            $methodname = 'create_' . $elementdatagenerator;
            if (method_exists($componentgenerator, $methodname)) {
                // Using data generators directly.
                $componentgenerator->{$methodname}($elementdata);

            } else if (method_exists($this, 'process_' . $elementdatagenerator)) {
                // Using an alternative to the direct data generator call.
                $this->{'process_' . $elementdatagenerator}($elementdata);

            } else if ($helper->preprocess_exists($elementdatagenerator)) {
                $helper->protected_process($elementdatagenerator, $elementdata);

            } else {
                throw new PendingException($elementname . ' data generator is not implemented');
            }
        }
    }

    public function get_manager_id($username) {
        return $this->get_user_id($username);
    }

    public function get_user_id($username) {
        global $DB;
        return $DB->get_field('user', 'id', array('username' => $username), MUST_EXIST);
    }

    public function get_program_id($shortname) {
        global $DB;
        return $DB->get_field('prog', 'id', array('shortname' => $shortname), MUST_EXIST);
    }

    public function get_org_framework_id($idnumber) {
        global $DB;
        return $DB->get_field('org_framework', 'id', array('idnumber' => $idnumber));
    }

    public function get_organisation_id($idnumber) {
        global $DB;
        return $DB->get_field('org', 'id', array('idnumber' => $idnumber));
    }

    public function get_pos_framework_id($idnumber) {
        global $DB;
        return $DB->get_field('pos_framework', 'id', array('idnumber' => $idnumber));
    }

    public function get_position_id($idnumber) {
        global $DB;
        return $DB->get_field('pos', 'id', array('idnumber' => $idnumber));
    }
}
