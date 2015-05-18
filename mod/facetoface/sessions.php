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
 * @author Aaron Barnes <aaron.barnes@totaralms.com>
 * @author Francois Marier <francois@catalyst.net.nz>
 * @package modules
 * @subpackage facetoface
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once('lib.php');
require_once('session_form.php');
require_once($CFG->dirroot . '/totara/core/js/lib/setup.php');

$id = optional_param('id', 0, PARAM_INT); // Course Module ID
$f = optional_param('f', 0, PARAM_INT); // facetoface Module ID
$s = optional_param('s', 0, PARAM_INT); // facetoface session ID
$c = optional_param('c', 0, PARAM_INT); // copy session
$d = optional_param('d', 0, PARAM_INT); // delete session
$confirm = optional_param('confirm', false, PARAM_BOOL); // delete confirmation

$nbdays = 1; // default number to show

$session = null;
if ($id && !$s) {
    if (!$cm = get_coursemodule_from_id('facetoface', $id)) {
        print_error('error:incorrectcoursemoduleid', 'facetoface');
    }
    if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
        print_error('error:coursemisconfigured', 'facetoface');
    }
    if (!$facetoface =$DB->get_record('facetoface',array('id' => $cm->instance))) {
        print_error('error:incorrectcoursemodule', 'facetoface');
    }
} else if ($s) {
     if (!$session = facetoface_get_session($s)) {
         print_error('error:incorrectcoursemodulesession', 'facetoface');
     }
     if (!$facetoface = $DB->get_record('facetoface',array('id' => $session->facetoface))) {
         print_error('error:incorrectfacetofaceid', 'facetoface');
     }
     if (!$course = $DB->get_record('course', array('id'=> $facetoface->course))) {
         print_error('error:coursemisconfigured', 'facetoface');
     }
     if (!$cm = get_coursemodule_from_instance('facetoface', $facetoface->id, $course->id)) {
         print_error('error:incorrectcoursemoduleid', 'facetoface');
     }
     if (!$session->roomid == 0 && !$sroom = $DB->get_record('facetoface_room', array('id' => $session->roomid))) {
        print_error('error:incorrectroomid', 'facetoface');
     }

     $nbdays = count($session->sessiondates);
} else {
    if (!$facetoface = $DB->get_record('facetoface', array('id' => $f))) {
        print_error('error:incorrectfacetofaceid', 'facetoface');
    }
    if (!$course = $DB->get_record('course', array('id' => $facetoface->course))) {
        print_error('error:coursemisconfigured', 'facetoface');
    }
    if (!$cm = get_coursemodule_from_instance('facetoface', $facetoface->id, $course->id)) {
        print_error('error:incorrectcoursemoduleid', 'facetoface');
    }
}
$context = context_module::instance($cm->id);

require_login($course, false, $cm);
require_capability('mod/facetoface:editsessions', $context);

$errorstr = '';


local_js(array(
    TOTARA_JS_DIALOG,
    TOTARA_JS_TREEVIEW
));
$PAGE->set_url('/mod/facetoface/sessions.php', array('f' => $f));
$PAGE->requires->string_for_js('save', 'totara_core');
$PAGE->requires->string_for_js('error:addpdroom-dialognotselected', 'totara_core');
$PAGE->requires->strings_for_js(array('cancel', 'ok'), 'moodle');
$PAGE->requires->strings_for_js(array('chooseroom', 'pdroomcapacityexceeded'), 'facetoface');

$display_selected = dialog_display_currently_selected(get_string('selected', 'facetoface'), 'addpdroom-dialog');
$jsconfig = array('sessionid' => $s, 'display_selected_item' => $display_selected, 'facetofaceid' => $facetoface->id);
$jsmodule = array(
    'name' => 'totara_f2f_room',
    'fullpath' => '/mod/facetoface/sessions.js',
    'requires' => array('json', 'totara_core'));
$PAGE->requires->js_init_call('M.totara_f2f_room.init', array($jsconfig), false, $jsmodule);

$returnurl = "view.php?f=$facetoface->id";

$editoroptions = array(
    'noclean'  => false,
    'maxfiles' => EDITOR_UNLIMITED_FILES,
    'maxbytes' => $course->maxbytes,
    'context'  => $context,
);

// Handle deletions
if ($d and $confirm) {
    if (!confirm_sesskey()) {
        print_error('confirmsesskeybad', 'error');
    }

    if (facetoface_delete_session($session)) {
        \mod_facetoface\event\session_deleted::create_from_session($session, $context)->trigger();
    } else {
        print_error('error:couldnotdeletesession', 'facetoface', $returnurl);
    }
    redirect($returnurl);
}

$sessionid = isset($session->id) ? $session->id : 0;

$canconfigurecancellation = has_capability('mod/facetoface:configurecancellation', $context);

if (isset($session)) {
    $defaulttimezone = empty($session->sessiondates[0]->sessiontimezone) ? get_string('sessiontimezoneunknown', 'facetoface') : $session->sessiondates[0]->sessiontimezone;
    customfield_load_data($session, 'facetofacesession', 'facetoface_session');
} else {
    $defaulttimezone = totara_get_clean_timezone();
}

$mform = new mod_facetoface_session_form(null, compact('id', 'f', 's', 'c', 'session', 'nbdays', 'course', 'editoroptions', 'defaulttimezone', 'facetoface', 'cm'));
if ($mform->is_cancelled()) {
    redirect($returnurl);
}

if ($fromform = $mform->get_data()) { // Form submitted

    if (empty($fromform->submitbutton)) {
        print_error('error:unknownbuttonclicked', 'facetoface', $returnurl);
    }

    // Pre-process fields
    if (empty($fromform->allowoverbook)) {
        $fromform->allowoverbook = 0;
    }
    if (empty($fromform->waitlisteveryone)) {
        $fromform->waitlisteveryone = 0;
    }
    if (empty($fromform->normalcost)) {
        $fromform->normalcost = 0;
    }
    if (empty($fromform->discountcost)) {
        $fromform->discountcost = 0;
    }
    if (empty($fromform->selfapproval)) {
        $fromform->selfapproval = 0;
    }
    if (empty($fromform->availablesignupnote)) {
        $fromform->availablesignupnote = 0;
    }

    //check dates and calculate total duration
    $sessiondates = array();
    if ($fromform->datetimeknown === '1') {
        $fromform->duration = 0;
    }
    for ($i = 0; $i < $fromform->date_repeats; $i++) {
        if (!empty($fromform->datedelete[$i])) {
            continue; // skip this date
        }
        $timezonefield = $fromform->sessiontimezone;
        $timestartfield = "timestart[$i]_raw";
        $timefinishfield = "timefinish[$i]_raw";
        if (!empty($fromform->$timestartfield) && !empty($fromform->$timefinishfield) && !empty($timezonefield[$i])) {
            $date = new stdClass();
            //Use the raw ISO date string to get an accurate Unix timestamp
            $date->sessiontimezone = $timezonefield[$i];
            $startdt = new DateTime($fromform->$timestartfield, new DateTimeZone($date->sessiontimezone));
            $finishdt = new DateTime($fromform->$timefinishfield, new DateTimeZone($date->sessiontimezone));
            $date->timestart = $startdt->getTimestamp();
            $date->timefinish = $finishdt->getTimestamp();
            if ($fromform->datetimeknown === '1') {
                $fromform->duration += ($date->timefinish - $date->timestart);
            }
            $sessiondates[] = $date;
        }
    }

    $todb = new stdClass();
    $todb->facetoface = $facetoface->id;
    $todb->datetimeknown = $fromform->datetimeknown;
    $todb->capacity = $fromform->capacity;
    $todb->allowoverbook = $fromform->allowoverbook;
    $todb->waitlisteveryone = $fromform->waitlisteveryone;
    $todb->duration = $fromform->duration;
    $todb->normalcost = $fromform->normalcost;
    $todb->discountcost = $fromform->discountcost;
    $todb->usermodified = $USER->id;
    $todb->roomid = (isset($session->roomid)) ? $session->roomid : 0;
    $todb->selfapproval = $facetoface->approvalreqd ? $fromform->selfapproval : 0;
    $todb->availablesignupnote = $fromform->availablesignupnote;

    // If min capacity is not provided or unset default to 0.
    if (empty($fromform->enablemincapacity) || $fromform->mincapacity < 0) {
        $fromform->mincapacity = 0;
    }

    $todb->mincapacity = $fromform->mincapacity;
    $todb->cutoff = $fromform->cutoff;

    if ($canconfigurecancellation) {
        $todb->allowcancellations = $fromform->allowcancellations;
        $todb->cancellationcutoff = $fromform->cancellationcutoff;
    }

    $transaction = $DB->start_delegated_transaction();

    $update = false;
    if (!$c and $session != null) {
        $update = true;
        $todb->id = $session->id;
        $sessionid = $session->id;
        $olddates = $DB->get_records('facetoface_sessions_dates', array('sessionid' => $session->id), 'timestart');
        if (!facetoface_update_session($todb, $sessiondates)) {
            print_error('error:couldnotupdatesession', 'facetoface', $returnurl);
        }
    } else {
        if (!$sessionid = facetoface_add_session($todb, $sessiondates)) {
            print_error('error:couldnotaddsession', 'facetoface', $returnurl);
        }
    }

    // Save session room info.
    if (!facetoface_save_session_room($sessionid, $fromform)) {
        print_error('error:couldnotsaveroom', 'facetoface');
    }
    $fromform->id = $sessionid;
    customfield_save_data($fromform, 'facetofacesession', 'facetoface_session');

    $transaction->allow_commit();

    // Retrieve record that was just inserted/updated.
    if (!$session = facetoface_get_session($sessionid)) {
        print_error('error:couldnotfindsession', 'facetoface', $returnurl);
    }

    if ($update) {
        // Now that we have updated the session record fetch the rest of the data we need.
        facetoface_update_attendees($session);

        // Get datetimeknown value from form.
        $datetimeknown = $fromform->datetimeknown == 1;

        // Send any necessary datetime change notifications but only if date/time is known.
        if ($datetimeknown && facetoface_session_dates_check($olddates, $sessiondates)) {
            $attendees = facetoface_get_attendees($session->id);
            foreach ($attendees as $user) {
                facetoface_send_datetime_change_notice($facetoface, $session, $user->id);
            }
        }
    }

    // Save trainer roles.
    if (isset($fromform->trainerrole)) {
        facetoface_update_trainers($facetoface, $session, $fromform->trainerrole);
    }

    // Save any calendar entries.
    $session->sessiondates = $sessiondates;
    facetoface_update_calendar_entries($session, $facetoface);

    if ($update) {
        \mod_facetoface\event\session_updated::create_from_session($session, $context)->trigger();
    } else {
        \mod_facetoface\event\session_created::create_from_session($session, $context)->trigger();
    }

    $data = file_postupdate_standard_editor($fromform, 'details', $editoroptions, $context, 'mod_facetoface', 'session', $session->id);
    $DB->set_field('facetoface_sessions', 'details', $data->details, array('id' => $session->id));

    redirect($returnurl);
} else if ($session != null) { // Edit mode
    // Set values for the form and unset some values that will be evaluated later.
    $sessioncopy = clone($session);
    if (isset($sessioncopy->sessiondates)) {
        unset($sessioncopy->sessiondates);
    }

    if (isset($sessioncopy->roomid)) {
        unset($sessioncopy->roomid);
    }

    if (isset($sessioncopy->allowcancellations)) {
        unset($sessioncopy->allowcancellations);
    }

    $sessioncopy->detailsformat = FORMAT_HTML;
    $editoroptions = $TEXTAREA_OPTIONS;
    $editoroptions['context'] = $context;
    $sessioncopy = file_prepare_standard_editor($sessioncopy, 'details', $editoroptions, $editoroptions['context'],
        'mod_facetoface', 'session', $session->id);

    $sessioncopy->datetimeknown = (1 == $session->datetimeknown);

    if ($canconfigurecancellation) {
        $sessioncopy->allowcancellations = $session->allowcancellations;
        $sessioncopy->cancellationcutoff = $session->cancellationcutoff;
    }

    if ($session->sessiondates) {
        $i = 0;
        foreach ($session->sessiondates as $date) {
            $idfield = "sessiondateid[$i]";
            $timestartfield = "timestart[$i]";
            $timefinishfield = "timefinish[$i]";
            $timezonefield = "sessiontimezone[$i]";

            $sessioncopy->$idfield = $date->id;
            $sessioncopy->$timestartfield = $date->timestart;
            $sessioncopy->$timefinishfield = $date->timefinish;
            $sessioncopy->$timezonefield = $date->sessiontimezone;
            $i++;
        }
    }

    if (!empty($sroom->id)) {
        if (!$sroom->custom) {
            // Pre-defined room
            $sessioncopy->pdroomid = $session->roomid;
            $sessioncopy->pdroomcapacity = $sroom->capacity;
        } else {
            // Custom room
            $sessioncopy->customroom = 1;
            $sessioncopy->croomname = $sroom->name;
            $sessioncopy->croombuilding = $sroom->building;
            $sessioncopy->croomaddress = $sroom->address;
            $sessioncopy->croomcapacity = $sroom->capacity;
        }
    }

    if ($session->mincapacity) {
        $sessioncopy->enablemincapacity = true;
    }

    $mform->set_data($sessioncopy);
}

if ($c) {
    $heading = get_string('copyingsession', 'facetoface', $facetoface->name);
}
else if ($d) {
    $heading = get_string('deletingsession', 'facetoface', $facetoface->name);
}
else if ($id or $f) {
    $heading = get_string('addingsession', 'facetoface', $facetoface->name);
}
else {
    $heading = get_string('editingsession', 'facetoface', $facetoface->name);
}

$pagetitle = format_string($facetoface->name);

$PAGE->set_cm($cm);
$PAGE->set_title($pagetitle);
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();

echo $OUTPUT->box_start();
echo $OUTPUT->heading($heading);

if (!empty($errorstr)) {
    echo $OUTPUT->container(html_writer::tag('span', $errorstr, array('class' => 'errorstring')), array('class' => 'notifyproblem'));
}

if ($d) {
    $viewattendees = has_capability('mod/facetoface:viewattendees', $context);
    facetoface_print_session($session, $viewattendees);
    $optionsyes = array('sesskey' => sesskey(), 's' => $session->id, 'd' => 1, 'confirm' => 1);
    echo $OUTPUT->confirm(get_string('deletesessionconfirm', 'facetoface', format_string($facetoface->name)),
        new moodle_url('sessions.php', $optionsyes),
        new moodle_url($returnurl));
}
else {
    $mform->display();
}

echo $OUTPUT->box_end();
echo $OUTPUT->footer($course);
