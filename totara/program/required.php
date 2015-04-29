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
 * @author Ben Lobo <ben@benlobo.co.uk>
 * @author Alastair Munro <alastair.munro@totaralms.com>
 * @package totara
 * @subpackage plan
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot . '/totara/program/lib.php');
require_once($CFG->dirroot . '/totara/core/js/lib/setup.php');

require_login();

$userid = optional_param('userid', $USER->id, PARAM_INT); // show required learning for this user
$programid = optional_param('id', 0, PARAM_INT);
$filter = optional_param('filter', 'all', PARAM_TEXT);

$PAGE->set_context(context_system::instance());
// Check if programs or certifications are enabled.
if ($filter == 'program') {
    check_program_enabled();
} else if ($filter == 'certification') {
    check_certification_enabled();
} else if (totara_feature_disabled('programs') &&
    totara_feature_disabled('certifications')) {
    print_error('programsandcertificationsdisabled', 'totara_program');
}

$PAGE->set_url('/totara/program/required.php');
$PAGE->set_pagelayout('report');

//
/// Permission checks
//
if (!prog_can_view_users_required_learning($userid)) {
    print_error('error:nopermissions', 'totara_program');
}

// Check if we are viewing the required learning as a manager or a learner
if ($userid != $USER->id) {
    $role = 'manager';
} else {
    $role = 'learner';
}

if ($programid) {
    $program = new program($programid);
    if (!prog_required_for_user($programid, $userid)) {
        print_error('error:notrequiredlearning', 'totara_program');
    }

    if (prog_is_accessible($program)) {

        //Javascript include
        local_js(array(
            TOTARA_JS_DIALOG,
            TOTARA_JS_TREEVIEW,
            TOTARA_JS_PLACEHOLDER,
            TOTARA_JS_DATEPICKER
        ));

        $PAGE->requires->strings_for_js(array('pleaseentervaliddate', 'pleaseentervalidreason', 'extensionrequest', 'cancel', 'ok'), 'totara_program');
        $notify_html = trim($OUTPUT->notification(get_string("extensionrequestsent", "totara_program"), "notifysuccess"));
        $notify_html_fail = trim($OUTPUT->notification(get_string("extensionrequestnotsent", "totara_program"), null));
        $args = array('args'=>'{"id":'.$program->id.', "userid":'.$USER->id.', "user_fullname":'.json_encode(fullname($USER)).', "notify_html_fail":'.json_encode($notify_html_fail).', "notify_html":'.json_encode($notify_html).'}');
        $jsmodule = array(
             'name' => 'totara_programview',
             'fullpath' => '/totara/program/view/program_view.js',
             'requires' => array('json', 'totara_core')
             );
        $PAGE->requires->js_init_call('M.totara_programview.init',$args, false, $jsmodule);

        ///
        /// Display
        ///

        $heading = $program->fullname;
        $pagetitle = format_string(get_string('program', 'totara_program').': '.$heading);

        prog_add_required_learning_base_navlinks($userid);

        $PAGE->navbar->add($heading);

        $PAGE->set_title($pagetitle);
        $PAGE->set_heading(format_string($SITE->fullname));
        dp_display_plans_menu($userid, 0 , $role, 'courses', 'none', true, $program->id, true);
        echo $OUTPUT->header();

        // Trigger event.
        $dataevent = array('id' => $programid, 'userid' => $userid, 'other' => array('section' => 'required'));
        $event = \totara_program\event\program_viewed::create_from_data($dataevent)->trigger();

        // Program page content
        echo $OUTPUT->container_start('', 'program-content');

        echo $OUTPUT->heading($heading);

        echo $program->display($userid);

        echo $OUTPUT->container_end();

        echo $OUTPUT->footer();
    } else {
        // If the program is not accessible then print heading
        // and unavailiable message

        $heading = $program->fullname;
        $pagetitle = format_string(get_string('program', 'totara_program').': '.$heading);

        prog_add_required_learning_base_navlinks($userid);

        $PAGE->navbar->add($heading);

        $PAGE->set_title($pagetitle);
        $PAGE->set_heading(format_string($SITE->fullname));
        echo $OUTPUT->header();

        echo $OUTPUT->heading($heading);

        echo html_writer::start_tag('p') . get_string('programnotcurrentlyavailable', 'totara_program') . html_writer::end_tag('p');

        echo $OUTPUT->footer();
    }
} else {
    //
    // Display program list
    //

    $heading = get_string('requiredlearning', 'totara_program');
    $pagetitle = format_string(get_string('requiredlearning', 'totara_program'));

    prog_add_required_learning_base_navlinks($userid);

    $PAGE->set_title($heading);
    $PAGE->set_heading($pagetitle);
    dp_display_plans_menu($userid, 0, $role, 'courses', 'none');
    echo $OUTPUT->header();

    // Required learning page content
    echo $OUTPUT->container_start('', 'required-learning');

    if ($userid != $USER->id) {
        echo prog_display_user_message_box($userid);
    }

    echo $OUTPUT->heading($heading);

    echo $OUTPUT->container_start('', 'required-learning-description');

    if ($userid == $USER->id) {
        $requiredlearninginstructions = html_writer::start_tag('div', array('class' => 'instructional_text')) . get_string('requiredlearninginstructions', 'totara_program') . html_writer::end_tag('div');
    } else {
        $user = $DB->get_record('user', array('id' => $userid));
        $userfullname = fullname($user);
        $requiredlearninginstructions = html_writer::start_tag('div', array('class' => 'instructional_text')) . get_string('requiredlearninginstructionsuser', 'totara_program', $userfullname) . html_writer::end_tag('div');
    }

    echo $requiredlearninginstructions;

    echo html_writer::start_tag('div', array('style' => 'clear: both;')) . html_writer::end_tag('div');
    echo $OUTPUT->container_end();

    if (($filter == 'all' || $filter == 'program') && totara_feature_visible('programs')) {
        echo $OUTPUT->container_start('', 'required-learning-list');
        echo $OUTPUT->heading(get_string('programs', 'totara_program'), 3);

        $requiredlearninghtml = prog_display_required_programs($userid);

        if (empty($requiredlearninghtml)) {
            echo get_string('norequiredlearning', 'totara_program');
        } else {
            echo $requiredlearninghtml;
        }

        echo $OUTPUT->container_end();
    }

    if (($filter == 'all' || $filter == 'certification') && totara_feature_visible('certifications')) {
        echo $OUTPUT->container_start('', 'certification-learning-list');
        echo $OUTPUT->heading(get_string('certifications', 'totara_program'), 3);

        $certificationhtml = prog_display_certification_programs($userid);

        if (empty($certificationhtml)) {
            echo get_string('nocertificationlearning', 'totara_program');
        } else {
            echo $certificationhtml;
        }

        echo $OUTPUT->container_end();
    }

    echo $OUTPUT->container_end();
    echo $OUTPUT->footer();
}
