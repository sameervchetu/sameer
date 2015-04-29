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
 * @author Jon Sharp <jon.sharp@catalyst-eu.net>
 * @package totara
 * @subpackage certification
 */

// Certification db upgrades.

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/totara/core/db/utils.php');

/**
 * Certification database upgrade script
 *
 * @param   integer $oldversion Current (pre-upgrade)
 * @return  boolean $result
 */
function xmldb_totara_certification_upgrade($oldversion) {
    global $CFG, $DB, $OUTPUT;

    $dbman = $DB->get_manager();

    if ($oldversion < 2013111200) {
        // Define field unassigned to be added to certif_completion_history.
        $table = new xmldb_table('certif_completion_history');
        $field = new xmldb_field('unassigned', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'timemodified');

        // Conditionally launch add field unassigned.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Certification savepoint reached.
        totara_upgrade_mod_savepoint(true, 2013111200, 'totara_certification');
    }

    if ($oldversion < 2014110701) {
        // Find orphaned certif_completion records, archive them, delete them.
        $sql = "SELECT cc.* FROM {certif_completion} cc
             LEFT JOIN {prog} p ON cc.certifid = p.certifid
       LEFT OUTER JOIN {prog_user_assignment} pua on p.id = pua.programid AND cc.userid = pua.userid
                 WHERE pua.id IS NULL";
        $orphans = $DB->get_recordset_sql($sql, array());
        $deletecollection = array();
        $deleteids = array();
        $idcount = 0;
        foreach ($orphans as $orphan) {
            $deleteids[] = $orphan->id;
            $idcount++;
            $orphan->timemodified = time();
            $orphan->unassigned = true;
            // Move the record to history.
            $completionhistory = $DB->get_record('certif_completion_history',
                    array('certifid' => $orphan->certifid, 'userid' => $orphan->userid, 'timeexpires' => $orphan->timeexpires));
            if ($completionhistory) {
                $orphan->id = $completionhistory->id;
                $DB->update_record('certif_completion_history', $orphan);
            } else {
                $DB->insert_record('certif_completion_history', $orphan);
            }
            // In case there are many thousands of records chunk the ids for deletion later.
            if ($idcount >= BATCH_INSERT_MAX_ROW_COUNT) {
                $deletecollection[] = $deleteids;
                $deleteids = array();
                $idcount = 0;
            }
        }
        // Store any leftovers.
        if ($idcount > 0) {
            $deletecollection[] = $deleteids;
        }
        foreach ($deletecollection as $key => $idarray) {
            // Remove the original orphaned records.
            if (!empty($idarray)) {
                list($usql, $uparams) = $DB->get_in_or_equal($idarray, SQL_PARAMS_QM);
                $DB->delete_records_select('certif_completion', "id {$usql}", $uparams);
            }
        }
        // Certification savepoint reached.
        totara_upgrade_mod_savepoint(true, 2014110701, 'totara_certification');
    }
    return true;
}
