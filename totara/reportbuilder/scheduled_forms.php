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
 * @author Alastair Munro <alastair.munro@totaralms.com>
 * @package totara
 * @subpackage reportbuilder
 */

/**
 * Moodle Formslib templates for scheduled reports settings forms
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->dirroot . '/lib/formslib.php');
require_once($CFG->dirroot . '/calendar/lib.php');

/**
 * Formslib template for the new report form
 */
class scheduled_reports_new_form extends moodleform {
    function definition() {

        $mform =& $this->_form;
        $id = $this->_customdata['id'];
        $frequency = $this->_customdata['frequency'];
        $schedule = $this->_customdata['schedule'];
        $report = $this->_customdata['report'];
        $savedsearches = $this->_customdata['savedsearches'];
        $exporttofilesystem = $this->_customdata['exporttofilesystem'];

        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'reportid', $report->_id);
        $mform->setType('reportid', PARAM_INT);

        // Export type options.
        $exportformatselect = reportbuilder_get_export_options();

        $exporttofilesystemenabled = false;
        if (get_config('reportbuilder', 'exporttofilesystem') == 1) {
            $exporttofilesystemenabled = true;
        }

        $mform->addElement('header', 'general', get_string('scheduledreportsettings', 'totara_reportbuilder'));

        $mform->addElement('static', 'report', get_string('report', 'totara_reportbuilder'), $report->fullname);
        if (empty($savedsearches)) {
            $mform->addElement('static', '', get_string('data', 'totara_reportbuilder'),
                    html_writer::div(get_string('scheduleneedssavedfilters', 'totara_reportbuilder', $report->report_url()),
                            'notifyproblem'));
        } else {
            $mform->addElement('select', 'savedsearchid', get_string('data', 'totara_reportbuilder'), $savedsearches);
        }
        $mform->addElement('select', 'format', get_string('export', 'totara_reportbuilder'), $exportformatselect);

        if ($exporttofilesystemenabled) {
            $exporttosystemarray = array();
            $exporttosystemarray[] =& $mform->createElement('radio', 'emailsaveorboth', '',
                    get_string('exporttoemail', 'totara_reportbuilder'), REPORT_BUILDER_EXPORT_EMAIL);
            $exporttosystemarray[] =& $mform->createElement('radio', 'emailsaveorboth', '',
                    get_string('exporttoemailandsave', 'totara_reportbuilder'), REPORT_BUILDER_EXPORT_EMAIL_AND_SAVE);
            $exporttosystemarray[] =& $mform->createElement('radio', 'emailsaveorboth', '',
                    get_string('exporttosave', 'totara_reportbuilder'), REPORT_BUILDER_EXPORT_SAVE);
            $mform->setDefault('emailsaveorboth', $exporttofilesystem);
            $mform->addGroup($exporttosystemarray, 'exporttosystemarray',
                    get_string('exportfilesystemoptions', 'totara_reportbuilder'), array('<br />'), false);
        } else {
            $mform->addElement('hidden', 'emailsaveorboth', REPORT_BUILDER_EXPORT_EMAIL);
            $mform->setType('emailsaveorboth', PARAM_TEXT);
        }

        $mform->addElement('scheduler', 'schedulegroup', get_string('schedule', 'totara_reportbuilder'),
                           array('frequency' => $frequency, 'schedule' => $schedule));

        // Email to, setting for the schedule reports.
        $mform->addElement('header', 'emailto', get_string('scheduledemailtosettings', 'totara_reportbuilder'));

        // Input hidden fields for system_users and audiences.
        $mform->addElement('hidden', 'systemusers');
        $mform->setType('systemusers', PARAM_SEQUENCE);

        $mform->addElement('hidden', 'audiences');
        $mform->setType('audiences', PARAM_SEQUENCE);

        $mform->addElement('hidden', 'externalemails');
        $mform->setType('externalemails', PARAM_TEXT);

        // Create Audience list option.
        $audiencestr = html_writer::tag('strong', get_string('cohorts', 'totara_cohort'));
        $mform->addElement('static', 'audienceslabel', $audiencestr);

        // Create a place to show existing audiences.
        $mform->addElement('static', 'audiences_list', '', '<div class="list-audiences"></div>');

        $mform->addElement('button', 'addaudiences', get_string('addcohorts', 'totara_reportbuilder'),
            array('id' => 'show-audiences-dialog'));

        $systemuserstr = html_writer::tag('strong', get_string('systemusers', 'totara_reportbuilder'));
        $mform->addElement('static', 'systemuserslabel', $systemuserstr);

        // Create a place to show existing system users.
        $mform->addElement('static', 'systemusers_list', '', '<div class="list-systemusers"></div>');

        $mform->addElement('button', 'addsystemusers', get_string('addsystemusers', 'totara_reportbuilder'),
            array('id' => 'show-systemusers-dialog'));

        $externalemailstr = html_writer::tag('strong', get_string('emailexternalusers', 'totara_reportbuilder'));
        $mform->addElement('static', 'externalemaillabel', $externalemailstr);
        $mform->addHelpButton('externalemaillabel', 'emailexternalusers', 'totara_reportbuilder');

        // Create a place to show existing external emails.
        $mform->addElement('static', 'externalemails_list', '', '<div class="list-externalemails"></div>');

        // Text input to add new emails for external users.
        $objs = array();
        $objs[] =& $mform->createElement('text', 'emailexternals', '', 'maxlength="150" size="30"');
        $objs[] =& $mform->createElement('button', 'addemail', get_string('addexternalemail', 'totara_reportbuilder'),
            array('id' => 'addexternalemail'));

        // Create a group for the elements.
        $grp =& $mform->addElement('group', 'externalemailsgrp', '', $objs, '', false);

        $mform->setType('emailexternals', PARAM_EMAIL);

        if (!empty($savedsearches)) {
            $this->add_action_buttons();
        }
    }

    public function set_data($data) {
        global $PAGE;

        $mform =& $this->_form;
        $renderer = $PAGE->get_renderer('totara_reportbuilder');

        $audiences = $data->audiences;
        $sysusers = $data->systemusers;
        $extusers = $data->externalusers;

        unset($data->audiences);
        unset($data->systemusers);
        unset($data->externalusers);

        parent::set_data($data);

        if (!empty($audiences)) {
            // Render all audiences.
            $audiencesrecords = array();
            $audienceids = array();
            foreach ($audiences as $audience) {
                $audiencesrecords[] = $renderer->schedule_email_setting($audience, 'audiences');
                $audienceids[] = $audience->id;
            }
            $divcontainer = html_writer::div(implode($audiencesrecords, ''), 'list-audiences');
            $mform->getElement('audiences_list')->setValue($divcontainer);
            $mform->getElement('audiences')->setValue(implode(',', $audienceids));
        }

        if (!empty($sysusers)) {
            // Render system users.
            $systemusers = array();
            $userids = array();
            foreach ($sysusers as $user) {
                $systemusers[] = $renderer->schedule_email_setting($user, 'systemusers');
                $userids[] = $user->id;
            }
            $divcontainer = html_writer::div(implode($systemusers, ''), 'list-systemusers');
            $mform->getElement('systemusers_list')->setValue($divcontainer);
            $mform->getElement('systemusers')->setValue(implode(',', $userids));
        }

        if (!empty($extusers)) {
            // Render external emails.
            $externalemails = array();
            foreach ($extusers as $extuser) {
                $external = new stdClass();
                $external->id = $extuser;
                $external->name = $extuser;
                $externalemails[] = $renderer->schedule_email_setting($external, 'externalemails');
            }
            $divcontainer = html_writer::div(implode($externalemails, ''), 'list-externalemails');
            $mform->getElement('externalemails_list')->setValue($divcontainer);
            $mform->getElement('externalemails')->setValue(implode(',', $extusers));
        }
    }
}


class scheduled_reports_add_form extends moodleform {
    function definition() {

        $mform =& $this->_form;

        $sources = array();

        //Report type options
        $reports = reportbuilder_get_reports();
        $reportselect = array();
        foreach ($reports as $report) {
            if (!isset($sources[$report->source])) {
                $sources[$report->source] = reportbuilder::get_source_object($report->source);
            }

            if ($sources[$report->source]->scheduleable) {
                try {
                    if ($report->embedded) {
                        $reportobject = new reportbuilder($report->id);
                    }
                    $reportselect[$report->id] = $report->fullname;
                } catch (moodle_exception $e) {
                    if ($e->errorcode != "nopermission") {
                        // The embedded report creation failed, almost certainly due to a failed is_capable check.
                        // In this case, we just don't add it to $reportselect.
                    } else {
                        throw ($e);
                    }
                }
            }
        }

        if (!empty($reportselect)) {
            $mform->addElement('select', 'reportid', get_string('addnewscheduled', 'totara_reportbuilder'), $reportselect);
            $mform->addElement('submit', 'submitbutton', get_string('addscheduledreport', 'totara_reportbuilder'));

            $renderer =& $mform->defaultRenderer();
            $elementtemplate = '<span>{element}</span>';
            $renderer->setElementTemplate($elementtemplate, 'submitbutton');
            $renderer->setElementTemplate('<label for="{id}" class="accesshide">{label}</label><span>{element}</span>', 'reportid');
        }
    }
}
