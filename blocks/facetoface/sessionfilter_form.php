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
 * Block for displaying user-defined links
 *
 * @package   facetoface
 * @author    Brian Barnes <brian.barnes@totaralms.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');

class sessionfilter_form extends moodleform {
    public function definition() {
        global $DB;

        $coursenames = array('' => get_string('all'));
        $locations = array();
        $results = $DB->get_records_sql("SELECT s.id AS sessionid, c.fullname,
                                       f.id AS facetofaceid
                                    FROM {course} c
                                    JOIN {facetoface} f ON f.course = c.id
                                    JOIN {facetoface_sessions} s ON f.id = s.facetoface
                                    WHERE c.visible = 1
                                    GROUP BY c.id, c.idnumber, c.fullname, s.id, f.id
                                    ORDER BY c.fullname ASC");

        add_location_info($results);

        if (!empty($results)) {
            foreach ($results as $result) {
                // Create unique list of coursenames.
                if (!array_key_exists($result->fullname, $coursenames)) {
                    $coursenames[$result->fullname] = $result->fullname;
                }

                // Created unique list of locations.
                if (isset($result->location)) {
                    if (!array_key_exists($result->location, $locations)) {
                        $locations[$result->location] = $result->location;
                    }
                }
            }
        }

        $mform = $this->_form;

        $mform->addElement('hidden', 'userid', $this->_customdata['userid']);
        $mform->setType('userid', PARAM_INT);

        $mform->addElement('date_selector', 'from', get_string('daterange', 'block_facetoface'), array('optional' => true));
        $mform->setDefault('from', time());
        $mform->setType('from', PARAM_INT);

        $mform->addElement('date_selector', 'to', strtolower(get_string('to')), array('optional' => true));
        if (!$this->_customdata['allfuture']) {
            $mform->setDefault('to', strtotime('+1 month'));
        }
        $mform->setType('to', PARAM_INT);

        $mform->addElement('select', 'course', get_string('coursefullname', 'block_facetoface'), $coursenames);
        $mform->setType('course', PARAM_TEXT);

        if (!empty($locations)) {
            $mform->addElement('select', 'location', get_string('location', 'facetoface'), $locations);
            $mform->setType('location', PARAM_TEXT);
        }

        $this->add_action_buttons(false, get_string('apply', 'block_facetoface'));
    }

    public function validation($data, $files) {
        $errors = array();
        if (!empty($data['to']) && !empty($data['from']) && ($data['to'] < $data['from'])) {
            $errors['from'] = get_string('daterangeincorrect', 'block_facetoface');
        }

        return $errors;
    }
}