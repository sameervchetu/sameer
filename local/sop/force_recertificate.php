<?php

require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/'.$CFG->admin.'/user/lib.php');
require_once($CFG->dirroot.'/local/sop/force_recertification_form.php');


admin_externalpage_setup('userbulk');
$id = required_param('id', PARAM_INT); // program id

$program = new program($id);
$iscertif = $program->certifid ? true : false;
$programcontext = $program->get_context();

// Check if programs or certifications are enabled.
if ($iscertif) {
    check_certification_enabled();
} else {
    check_program_enabled();
}

if (!isset($SESSION->recertify_users)) {
    $SESSION->recertify_users = array();
}

$returnurl = new moodle_url('/local/sop/force_recertificate.php', array('id' => $id));
$PAGE->set_url($returnurl);
$currenturl = qualified_me();

// create the user filter form
$ufiltering = new user_filtering(null, $currenturl);
// array of bulk operations
// create the bulk operations form
$action_form = new force_recertification_action_form($currenturl);
if ($data = $action_form->get_data()) {
    // check if an action should be performed and do so
    
    sop_recertify_window_opens_stage($id, $SESSION->recertify_users);
    $SESSION->recertify_users = array();
}
$user_recertify_form = new force_recertif_form($currenturl, get_recertif_selection_data($ufiltering, $id));

if ($data = $user_recertify_form->get_data()) {
    
    if (!empty($data->addall)) {
        add_recertif_selection_all($ufiltering, $id);

    } else if (!empty($data->addsel)) {
        if (!empty($data->ausers)) {
            if (in_array(0, $data->ausers)) {
                add_recertif_selection_all($ufiltering, $id);
            } else {
                foreach($data->ausers as $userid) {
                    if ($userid == -1) {
                        continue;
                    }
                    if (!isset($SESSION->recertify_users[$userid])) {
                        $SESSION->recertify_users[$userid] = $userid;
                    }
                }
            }
        }

    } else if (!empty($data->removeall)) {
        $SESSION->recertify_users= array();

    } else if (!empty($data->removesel)) {
        if (!empty($data->susers)) {
            if (in_array(0, $data->susers)) {
                $SESSION->recertify_users= array();
            } else {
                foreach($data->susers as $userid) {
                    if ($userid == -1) {
                        continue;
                    }
                    unset($SESSION->recertify_users[$userid]);
                }
            }
        }
    }

    // reset the form selections
    unset($_POST);
    $user_recertify_form = new force_recertif_form($currenturl, get_recertif_selection_data($ufiltering, $id));
}
// do output
echo $OUTPUT->header();
echo $OUTPUT->container_start('program content', 'edit-program-content');


// Display.
$heading = format_string($program->fullname);

if ($iscertif) {
    $heading .= ' ('.get_string('certification', 'totara_certification').')';
}
echo $OUTPUT->heading($heading);
$renderer = $PAGE->get_renderer('totara_program');
// Display the current status
echo $program->display_current_status();

$exceptions = $program->get_exception_count();
$currenttab = 'forcerecert';
require($CFG->dirroot.'/totara/program/tabs.php');
$ufiltering->display_add();
$ufiltering->display_active();

$user_recertify_form->display();

$action_form->display();

echo $OUTPUT->footer();