<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2014 onwards Totara Learning Solutions LTD
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
 * @package totara_facetoface
 */

require_once(dirname(__FILE__).'/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/totara/customfield/lib.php');

$page = optional_param('page', 0, PARAM_INT);
$prefix = required_param('prefix', PARAM_ALPHA);
$action = optional_param('action', 'showlist', PARAM_ALPHA);
$id = optional_param('id', 0, PARAM_INT);

$contextsystem = context_system::instance();
$PAGE->set_context($contextsystem);

// Add params to extrainfo in case the customfield need them.
$extrainfo = array('prefix' => $prefix, 'id' => $id, 'action' => $action);
$customfieldtype = get_customfield_type_instace($prefix, $contextsystem, $extrainfo);

// Set redirect options.
$redirectoptions = array('prefix' => $prefix, 'id' => $id);
$redirectpage = '/mod/facetoface/customfields.php';
$redirect = new moodle_url($redirectpage, $redirectoptions);

$PAGE->set_url($redirect);
admin_externalpage_setup('modfacetofacecustomfields', '', array('prefix' => $prefix));

/** @var totara_customfield_renderer $renderer*/
$renderer = $PAGE->get_renderer('totara_customfield');

/** @var mod_facetoface_renderer $renderer*/
$facetofacerenderer = $PAGE->get_renderer('mod_facetoface');

// Check if any actions need to be performed.
switch ($action) {
    case 'showlist':
        echo $OUTPUT->header();
        echo $facetofacerenderer->customfield_management_tabs($prefix);
        echo $OUTPUT->heading(get_string('customfieldsheading', 'facetoface'));

        $options = customfield_list_datatypes();
        $cancreate = has_capability($customfieldtype->get_capability_createfield(), $contextsystem);
        $canedit = has_capability($customfieldtype->get_capability_editfield(), $contextsystem);
        $candelete = has_capability($customfieldtype->get_capability_deletefield(), $contextsystem);
        $fields = $customfieldtype->get_defined_fields($customfieldtype->get_fields_sql_where());

        echo $renderer->totara_customfield_print_list($fields, $canedit, $candelete, $cancreate, $options, $redirectpage, $redirectoptions);
        break;
    case 'movefield':
        require_capability($customfieldtype->get_capability_movefield(), $contextsystem);
        $id  = required_param('id', PARAM_INT);
        $dir = required_param('dir', PARAM_ALPHA);

        if (confirm_sesskey()) {
            $customfieldtype->move($id, $dir);
            redirect($redirect);
        }
        break;
    case 'deletefield':
        require_capability($customfieldtype->get_capability_deletefield(), $contextsystem);
        $id      = required_param('id', PARAM_INT);
        $confirm = optional_param('confirm', 0, PARAM_BOOL);

        if (data_submitted() and $confirm and confirm_sesskey()) {
            $customfieldtype->delete($id);
            redirect($redirect);
        }

        echo $OUTPUT->header();
        echo $facetofacerenderer->customfield_management_tabs($prefix);
        echo $OUTPUT->heading(get_string('customfieldsheadingaction', 'facetoface', get_string('delete')));

        // Ask for confirmation.
        $datacount = $DB->count_records($customfieldtype->get_table_prefix().'_info_data', array('fieldid' => $id));
        $optionsyes = array ('prefix' => $prefix, 'id' => $id, 'confirm' => 1, 'action' => 'deletefield', 'sesskey' => sesskey(), 'typeid' => 0);
        echo $renderer->totara_customfield_delete_confirmation($datacount, $redirectpage, $optionsyes, $redirectoptions);
        break;
    case 'editfield':
        $id       = optional_param('id', 0, PARAM_INT);
        $datatype = optional_param('datatype', '', PARAM_ALPHA);

        $heading = $datatype;
        $capability = $customfieldtype->get_capability_editfield();
        $tableprefix = $customfieldtype->get_table_prefix();
        if ($id === 0) {
            $datatypes = customfield_list_datatypes();
            $capability = $customfieldtype->get_capability_createfield();
            $heading = $datatypes[$datatype];
        }

        $tabs = $facetofacerenderer->customfield_management_tabs($prefix);
        $heading = $OUTPUT->heading(get_string('customfieldsheadingaction', 'facetoface', $heading));
        require_capability($capability, $contextsystem);

        $field = customfield_get_record_by_id($tableprefix, $id, $datatype);

        $appendedfields = array();
        if ($prefix == 'facetofacesession') {
            // Pass additional fields to be displayed in the customfield form.
            $showinsummary = array(
                'element' => 'advcheckbox',
                'name' => 'showinsummary',
                'label' => get_string('setting:showinsummary', 'facetoface'),
                'type' => PARAM_BOOL,
                'defaultvalue' => true,
            );
            $appendedfields = array($showinsummary);
        }

        $renderer->customfield_manage_edit_form($prefix, 0, $tableprefix, $field, $redirect, $heading, $tabs, $appendedfields);
        break;
    default:
        echo $OUTPUT->header();
        echo $facetofacerenderer->customfield_management_tabs($prefix);
        print_error('actiondoesnotexist', 'totara_customfield');
        break;
}

echo $OUTPUT->footer();
