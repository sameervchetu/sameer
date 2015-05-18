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
 * @package totara_reportbuilder
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot . '/totara/core/dialogs/dialog_content.class.php');

require_login();
require_sesskey();
$context = context_system::instance();
require_capability('moodle/user:viewdetails', $context);

// Legacy Totara HTML ajax, this should be converted to json + AJAX_SCRIPT.
send_headers('text/html; charset=utf-8', false);

$PAGE->set_context($context);

// Get all users
$guest = guest_user();
$fullname = $DB->sql_fullname();
$items = $DB->get_records_select('user', 'deleted = 0 AND suspended = 0 AND id != ?', array($guest->id), '',
    'id, ' . $fullname . ' AS fullname, email');

///
/// Setup dialog.
///

// Load dialog content generator; skip access, since it's checked above.
$dialog = new totara_dialog_content();
$dialog->type = totara_dialog_content::TYPE_CHOICE_MULTI;
$dialog->items = $items;

// Set title.
$dialog->selected_title = 'itemstoadd';

// Setup search.
$dialog->searchtype = 'user';

$selected = optional_param('selected', null, PARAM_SEQUENCE);
if (!empty($selected)) {
    $selectedids = explode(',', $selected);
    $disable = array();
    foreach ($selectedids as $selectedid) {
        $disable[$selectedid] = $DB->get_record('user', array('id' => $selectedid), 'id, '. $fullname . 'AS fullname');
    }

    // Disable items.
    $dialog->disabled_items = $disable;

    // Selected items.
    $dialog->selected_items = $disable;
}

// Display.
echo $dialog->generate_markup();