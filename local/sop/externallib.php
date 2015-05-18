<?php

/**
 * Externallib file for Alfresco SOP
 * 
 * @copyright Copyright 2015 eLearningExperts
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU Public License 3.0
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once($CFG->libdir . "/externallib.php");

class local_sop_external extends external_api {

/**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.2
     */
    public static function create_sop_parameters() {
        $courseconfig = get_config('moodlecourse'); //needed for many default values
        return new external_function_parameters(
            array(
                'courses' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'fullname' => new external_value(PARAM_TEXT, 'full name'),
                            'shortname' => new external_value(PARAM_TEXT, 'course short name'),
                            'categoryid' => new external_value(PARAM_INT, 'category id'),
                            'idnumber' => new external_value(PARAM_RAW, 'id number', VALUE_OPTIONAL),
                            'summary' => new external_value(PARAM_RAW, 'summary', VALUE_OPTIONAL),
                            'summaryformat' => new external_format_value('summary', VALUE_DEFAULT),
                            'format' => new external_value(PARAM_PLUGIN,
                                    'course format: weeks, topics, social, site,..',
                                    VALUE_DEFAULT, $courseconfig->format),
                            'showgrades' => new external_value(PARAM_INT,
                                    '1 if grades are shown, otherwise 0', VALUE_DEFAULT,
                                    $courseconfig->showgrades),
                            'newsitems' => new external_value(PARAM_INT,
                                    'number of recent items appearing on the course page',
                                    VALUE_DEFAULT, $courseconfig->newsitems),
                            'startdate' => new external_value(PARAM_RAW,
                                    'timestamp when the course start', VALUE_OPTIONAL),
                            'numsections' => new external_value(PARAM_INT,
                                    '(deprecated, use courseformatoptions) number of weeks/topics',
                                    VALUE_OPTIONAL),
                            'maxbytes' => new external_value(PARAM_INT,
                                    'largest size of file that can be uploaded into the course',
                                    VALUE_DEFAULT, $courseconfig->maxbytes),
                            'showreports' => new external_value(PARAM_INT,
                                    'are activity report shown (yes = 1, no =0)', VALUE_DEFAULT,
                                    $courseconfig->showreports),
                            'visible' => new external_value(PARAM_INT,
                                    '1: available to student, 0:not available', VALUE_OPTIONAL),
                            'hiddensections' => new external_value(PARAM_INT,
                                    '(deprecated, use courseformatoptions) How the hidden sections in the course are displayed to students',
                                    VALUE_OPTIONAL),
                            'groupmode' => new external_value(PARAM_INT, 'no group, separate, visible',
                                    VALUE_DEFAULT, $courseconfig->groupmode),
                            'groupmodeforce' => new external_value(PARAM_INT, '1: yes, 0: no',
                                    VALUE_DEFAULT, $courseconfig->groupmodeforce),
                            'defaultgroupingid' => new external_value(PARAM_INT, 'default grouping id',
                                    VALUE_DEFAULT, 0),
                            'enablecompletion' => new external_value(PARAM_INT,
                                    'Enabled, control via completion and activity settings. Disabled,
                                        not shown in activity settings.',
                                    VALUE_OPTIONAL),
                            'completionstartonenrol' => new external_value(PARAM_INT,
                                    '1: begin tracking a student\'s progress in course completion
                                        after course enrolment. 0: does not',
                                    VALUE_OPTIONAL),
                            'completionnotify' => new external_value(PARAM_INT,
                                    '1: yes 0: no', VALUE_OPTIONAL),
                            'lang' => new external_value(PARAM_SAFEDIR,
                                    'forced course language', VALUE_OPTIONAL),
                            'forcetheme' => new external_value(PARAM_PLUGIN,
                                    'name of the force theme', VALUE_OPTIONAL),
                            'courseformatoptions' => new external_multiple_structure(
                                new external_single_structure(
                                    array('name' => new external_value(PARAM_ALPHANUMEXT, 'course format option name'),
                                        'value' => new external_value(PARAM_RAW, 'course format option value')
                                )),
                                    'additional options for particular course format', VALUE_OPTIONAL),
                            'customfield_sopversion' => new external_value(PARAM_TEXT, 'sopversion'),
                            'customfield_issop' => new external_value(PARAM_TEXT, 'is sop course'),
                            'customfield_certificationurl' => new external_value(PARAM_TEXT, 'sop certification url course'),
                        )
                    ), 'SOP to create'
                )
            )
        );
    }

    /**
     * Create  courses
     *
     * @param array $courses
     * @return array courses (id and shortname only)
     * @since Moodle 2.2
     */
    public static function create_sop($courses) {
        global $CFG, $DB;
        require_once($CFG->dirroot . "/course/lib.php");
        require_once($CFG->libdir . '/completionlib.php');

        $params = self::validate_parameters(self::create_sop_parameters(),
                        array('courses' => $courses));

        $availablethemes = core_component::get_plugin_list('theme');
        $availablelangs = get_string_manager()->get_list_of_translations();

        $transaction = $DB->start_delegated_transaction();

        foreach ($params['courses'] as $course) {

            // Ensure the current user is allowed to run this function
            $context = context_coursecat::instance($course['categoryid'], IGNORE_MISSING);
            try {
                self::validate_context($context);
            } catch (Exception $e) {
                $exceptionparam = new stdClass();
                $exceptionparam->message = $e->getMessage();
                $exceptionparam->catid = $course['categoryid'];
                throw new moodle_exception('errorcatcontextnotvalid', 'webservice', '', $exceptionparam);
            }
            require_capability('moodle/course:create', $context);

            // Make sure lang is valid
            if (array_key_exists('lang', $course) and empty($availablelangs[$course['lang']])) {
                throw new moodle_exception('errorinvalidparam', 'webservice', '', 'lang');
            }

            // Make sure theme is valid
            if (array_key_exists('forcetheme', $course)) {
                if (!empty($CFG->allowcoursethemes)) {
                    if (empty($availablethemes[$course['forcetheme']])) {
                        throw new moodle_exception('errorinvalidparam', 'webservice', '', 'forcetheme');
                    } else {
                        $course['theme'] = $course['forcetheme'];
                    }
                }
            }

            //force visibility if ws user doesn't have the permission to set it
            $category = $DB->get_record('course_categories', array('id' => $course['categoryid']));
            if (!has_capability('moodle/course:visibility', $context)) {
                $course['visible'] = $category->visible;
            }

            //set default value for completion
            $courseconfig = get_config('moodlecourse');
            if (completion_info::is_enabled_for_site()) {
                if (!array_key_exists('enablecompletion', $course)) {
                    $course['enablecompletion'] = $courseconfig->enablecompletion;
                }
                if (!array_key_exists('completionstartonenrol', $course)) {
                    $course['completionstartonenrol'] = $courseconfig->completionstartonenrol;
                }
            } else {
                $course['enablecompletion'] = 0;
                $course['completionstartonenrol'] = 0;
            }

            $course['category'] = $course['categoryid'];

            // Summary format.
            $course['summaryformat'] = external_validate_format($course['summaryformat']);

            if (!empty($course['courseformatoptions'])) {
                foreach ($course['courseformatoptions'] as $option) {
                    $course[$option['name']] = $option['value'];
                }
            }

            require_once($CFG->dirroot.'/local/sop/lib.php');
            $course['format'] = 'topics';
            $course['numsections'] = SOP_CONST_TRUE;
            $course['newsitems'] = SOP_CONST_FALSE;
            $CFG->defaultblocks_override = ' ';
            
            //Note: create_course() core function check shortname, idnumber, category
            $newdata = (object) $course;
            $course['id'] = create_course((object) $course)->id;
            
            // Save the values of customfields for the course.
            require_once($CFG->dirroot.'/totara/customfield/fieldlib.php');
            $newdata->id = $course['id'];
            
            customfield_save_data($newdata, 'course', 'course');
            
            //create new URL activity in course
            $data = clone($newdata);
            $cm = create_mod($data);
            
            // create course completion criteria
            $comp_data = new stdClass();
            $comp_data->criteria_activity_value[$cm['cm_url']] = SOP_CONST_TRUE;
            $comp_data->criteria_activity_value[$cm['cm_label']] = SOP_CONST_TRUE;
            $comp_data->id = $course['id'];
            $comp_data->overall_aggregation = SOP_CONST_TRUE;
            $comp_data->activity_aggregation = SOP_CONST_TRUE;
            require_once($CFG->dirroot.'/completion/criteria/completion_criteria_activity.php');
            $class = 'completion_criteria_activity';
            $criterion = new $class();
            $criterion->update_config($comp_data);
            
            $aggdata = array(
                'course'        => $comp_data->id,
                'criteriatype'  => null
            );
            $aggregation = new completion_aggregation($aggdata);
            $aggregation->setMethod($comp_data->overall_aggregation);
            $aggregation->save();

            // Handle activity aggregation.
            if (empty($comp_data->activity_aggregation)) {
            $comp_data->activity_aggregation = SOP_CONST_FALSE;
            }

            $aggdata['criteriatype'] = COMPLETION_CRITERIA_TYPE_ACTIVITY;
            $aggregation = new completion_aggregation($aggdata);
            $aggregation->setMethod($comp_data->activity_aggregation);
            $aggregation->save();
            sop_edit_section_name($newdata);
            
            // Create new certificate, courseset and assign the created course in the courseset
            $programid = create_certificate($newdata);
            save_set($programid, $course['id']);
            
            // Prepare the response data
            $resultcourses[] = array('id' => $course['id'], 'shortname' => $course['shortname'], 'status' => 'SOP created successfully');
        }

        $transaction->allow_commit();

        return $resultcourses;
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 2.2
     */
    public static function create_sop_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id'       => new external_value(PARAM_INT, 'course id'),
                    'shortname' => new external_value(PARAM_TEXT, 'short name'),
                    'status' => new external_value(PARAM_TEXT, 'status'),
                )
            )
        );
    }
    
    
    public static function update_sop_parameters() {
        return new external_function_parameters(
            array(
                'courses' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'idnumber' => new external_value(PARAM_ALPHANUM, 'course id number'),
                            'customfield_certificationurl' => new external_value(PARAM_TEXT, 'sop certification url course'),
                            'fullname' => new external_value(PARAM_TEXT, 'full name'),
                            'shortname' => new external_value(PARAM_TEXT, 'course short name'),
                            'categoryid' => new external_value(PARAM_INT, 'category id'),
                            'customfield_sopversion' => new external_value(PARAM_TEXT, 'sopversion'),
                            'customfield_issop' => new external_value(PARAM_TEXT, 'is sop course'),
                        )
                    ), 'SOP to update'
                )
            )
        );
    }

    public static function update_sop($courses) {
        global $CFG, $DB;
         
        require_once($CFG->dirroot . "/course/lib.php");
        require_once($CFG->libdir . '/completionlib.php');

        $params = self::validate_parameters(self::update_sop_parameters(),
                        array('courses' => $courses));
        foreach ($params['courses'] as $course) {
            if(!$coursedata = $DB->get_record('course', array('idnumber' => $course['idnumber']))) {
                
            }
            $newcourse = new stdClass();
            $newcourse = (object) $course;
            $newcourse->id = $coursedata->id;
            $newcourse->customfield_wslog_editor['text'] = 'SOP version and URL resource updated';
            $transaction = $DB->start_delegated_transaction();
            update_course($newcourse);
            require_once($CFG->dirroot.'/local/sop/lib.php');
            update_mod($coursedata, $newcourse);
            require_once($CFG->dirroot.'/totara/customfield/fieldlib.php');
            customfield_save_data($newcourse, 'course', 'course');
            sop_edit_section_name($newcourse);
            $transaction->allow_commit();
            $resultcourses[] = array('id' => $coursedata->id, 'status' => 'SOP updated successfully');
        }
        return $resultcourses;
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 2.2
     */
    public static function update_sop_returns() {
        return new external_multiple_structure(
                new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'course id'),
                    'status' => new external_value(PARAM_TEXT, 'status'),
                )
            )
        );
    }
}