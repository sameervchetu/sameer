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
 * @author Valerii Kuznetsov <valerii.kuznetsov@totaralms.com>
 * @package totara_customfield
 */

/**
 * Local database upgrade script
 *
 * @param   integer $oldversion Current (pre-upgrade) local db version timestamp
 * @return  boolean $result
 */
function xmldb_totara_customfield_upgrade($oldversion) {
    if ($oldversion < 2015021300) {
        xmldb_totara_customfield_upgrade_clean_removed();

        // Main savepoint reached.
        totara_upgrade_mod_savepoint(true, 2015021300, 'totara_customfield');
    }

    return true;
}

/**
 * Clean customfields data from removed courses and programs.
 * Made as additional function for testability.
 */
function xmldb_totara_customfield_upgrade_clean_removed() {
    global $DB;
    $dbman = $DB->get_manager();
    if ($dbman->table_exists('course_info_data')) {
        // Remove customfields data for removed courses.
        $sql = "DELETE FROM {course_info_data} WHERE courseid NOT IN (SELECT id FROM {course})";
        $DB->execute($sql);

        $sqlparam = "DELETE FROM {course_info_data_param} WHERE dataid NOT IN (SELECT id FROM {course_info_data})";
        $DB->execute($sqlparam);
    }

    if ($dbman->table_exists('prog_info_data')) {
        // Remove customfields data for removed programs and certs.
        $sql = "DELETE FROM {prog_info_data} WHERE programid NOT IN (SELECT id FROM {prog})";
        $DB->execute($sql);

        $sqlparam = "DELETE FROM {prog_info_data_param} WHERE dataid NOT IN (SELECT id FROM {prog_info_data})";
        $DB->execute($sqlparam);
    }
}