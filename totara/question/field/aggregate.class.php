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
 * @author David Curry david.curry@totaralms.com>
 * @package totara
 * @subpackage totara_question
 */

class question_aggregate extends question_base{

    public static function get_info() {
        return array('group' => question_manager::GROUP_OTHER,
                     'type' => get_string('questiontypeaggregate', 'totara_question'));
    }

    /**
     * Add database fields definition that represent current question
     *
     * @see question_base::get_xmldb()
     * @return array()
     */
    public function get_xmldb() {
        $fields = array();
        return $fields;
    }

    /**
     * Get the datatype of all the question types that can be used in an aggregated question.
     *
     * @return array()
     */
    public static function available_question_types() {
        $qtypes = array();
        $qtypes[] = 'Rating (numeric scale)';
        $qtypes[] = 'Rating (custom scale)';

        return $qtypes;
    }

    /**
     * Customfield specific settings elements
     *
     * @param MoodleQuickForm $form
     */
    protected function add_field_specific_settings_elements(MoodleQuickForm $form, $readonly, $moduleinfo) {
        $module = $this->prefix;
        $options = $module::get_aggregation_question_list($moduleinfo->pageid);

        if ($readonly) {
            $questions = html_writer::start_tag('ul');
            foreach (explode(',', $this->param1) as $qid) {
                $questions .= html_writer::tag('li', $options[$qid]);
            }
            $questions .= html_writer::end_tag('ul');

            $form->addElement('static', '', get_string('aggregate', 'totara_question'), $questions);

            $form->addElement('advcheckbox', 'aggregateaverage', get_string('aggregateaverage', 'totara_question'), null, array('disabled' => 'disabled'));
            $form->addElement('advcheckbox', 'aggregatemedian', get_string('aggregatemedian', 'totara_question'), null, array('disabled' => 'disabled'));
        } else {
            if (!empty($options)) {
                $select = &$form->addElement('select', 'multiselectfield', get_string('aggregate', 'totara_question'), $options, array('class' => 'aggregateselector'));
                $select->setMultiple(true);
            } else {
                $form->addElement('static', '', get_string('aggregate', 'totara_question'), get_string('aggregatenooptions', 'totara_question'));
            }

            $form->addElement('advcheckbox', 'aggregateaverage', get_string('aggregateaverage', 'totara_question'));
            $form->addElement('advcheckbox', 'aggregatemedian', get_string('aggregatemedian', 'totara_question'));
        }
    }

    /**
     * Add database fields definition that represent current question
     *
     * @see question_base::get_xmldb()
     * @return array()
     */
    public function define_get(stdClass $toform) {
        if (!isset($toform)) {
            $toform = new stdClass();
        }
        $toform->multiselectfield = explode(',', $this->param1);
        $toform->aggregateaverage = $this->param2;
        $toform->aggregatemedian = $this->param3;

        return $toform;
    }

    /**
     * Set values from configuration form
     *
     * @param stdClass $fromform
     * @return stdClass $fromform
     */
    public function define_set(stdClass $fromform) {
        $questions = implode(',', $fromform->multiselectfield);
        $this->param1 = $questions;
        $this->param2 = (int)$fromform->aggregateaverage;
        $this->param3 = (int)$fromform->aggregatemedian;

        return $fromform;
    }

    /**
     * Validate custom element configuration
     * @param stdClass $data
     * @param array $files
     */
    public function define_validate($data, $files) {
        $err = array();

        if (empty($data->aggregateaverage) && empty($data->aggregatemedian)) {
            $err[] = get_string('error:aggregatedisplayselect', 'totara_question');
        }

        return $err;
    }

    /**
     * If this element requires that a name be set up for its use.
     *
     * @see question_base::requires_name()
     * @return bool
     */
    public function requires_name() {
        return true;
    }

    /**
     * If this element requires that permissions be set up for its use.
     *
     * @see question_base::requires_permissions()
     * @return bool
     */
    public function requires_permissions() {
        return true;
    }

    /**
     * If this element has any answerable form fields, or it's a view only (informational or static) element.
     *
     * @see question_base::is_answerable()
     * @return bool
     */
    public function is_answerable() {
        return false;
    }

    /**
     * Retrieve the average result to display.
     *
     * @param  array $results A users answers as an array of integers.
     * @return float          Rounded to 2 decimal figures.
     */
    public function average_results($results) {
        $total = array_sum($results);
        $count = count($results);

        return round($total/$count, 2);
    }

    /**
     * Retrieve the median result to display.
     *
     * @param  array $results A users answers as an array of integers.
     * @return integer
     */
    public function median_results($results) {
        rsort($results);
        $count = count($results);
        $mid = ($count - 1) / 2;

        if ($count % 2) {
            // Odd number of results, just take the middle.
            return $results[$mid];
        } else {
            // Even number of results, average the middle 2.
            $lower = $results[floor($mid)];
            $higher = $results[ceil($mid)];

            return ($lower + $higher) / 2;

        }
    }

    public function add_field_form_elements(MoodleQuickForm $form) {

        $module = $this->prefix;
        $rolestringkeys = $module::get_roles();
        if ($this->preview) {
            return $this->display_preview($form);
        }

        $answers = $module::get_aggregate_question_answers($this->subjectid, $this->appraisalstagepageid, $this->id, $this->param1);

        // The don't have permission to view anything, just return.
        if (empty($answers)) {
            return $form;
        }

        // Set up the header for the question.
        $form->addElement('header', 'question', $this->name);

        foreach ($answers as $roletype => $answer) {
            $rolekey = get_string($rolestringkeys[$roletype], "totara_{$module}");

            $out = '';

            $answer = array_filter($answer);
            if (empty($answer)) {
                $out = get_string('notanswered', 'totara_question');
            } else {
                if (!empty($this->param2)) {
                    $avg = $this->average_results($answer);
                    $out .= get_string('aggregatedisplayavg', 'totara_question', $avg);
                }

                if (!empty($this->param2) && !empty($this->param3)) {
                    // Showing both avg & med so we'll need a seperator.
                    $out .= html_writer::empty_tag('br');
                }

                if (!empty($this->param3)) {
                    $med = $this->median_results($answer);
                    $out .= get_string('aggregatedisplaymed', 'totara_question', $med);
                }
            }

            // Add the aggregation as read only.
            $form->addElement('static', 'aggregate', $rolekey , $out);
        }

        return $form;
    }

    public function display_preview(MoodleQuickForm $form) {
        $module = $this->prefix;
        $roles = $module::get_roles();

        // Set up the header for the question.
        $form->addElement('header', 'question', $this->name);

        foreach ($roles as $roleid => $rolekey) {
            $rolestr = get_string($rolekey, "totara_{$module}") . ": ";

            $out = '';

            if (!empty($this->param2)) {
                $out .= get_string('aggregatedisplayavg', 'totara_question', 'X');
            }

            if (!empty($this->param2) && !empty($this->param3)) {
                // Showing both avg & med so we'll need a seperator.
                $out .= html_writer::empty_tag('br');
            }

            if (!empty($this->param3)) {
                $out .= get_string('aggregatedisplaymed', 'totara_question', 'Y');
            }

            // Add the aggregation as read only.
            $form->addElement('static', 'aggregate', $rolestr, $out);
        }
    }

    public function add_field_specific_view_elements(MoodleQuickForm $form) {
        return add_field_form_elements($form);
    }

    public function to_html($values) {
        throw new \coding_exception('coding error: question_aggregate->to_html() should never be called, see add_field_form_elements() instead.');
    }

    public function add_field_specific_edit_elements(MoodleQuickForm $form) {
        throw new \coding_exception('coding error: question_aggregate->add_field_specific_edit_elements() should never be called, see add_field_form_elements() instead.');
    }
}
