<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2015 onwards Totara Learning Solutions LTD
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
 * @package totara_code
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Check that database user has enough permission for database upgrade
 * @param environment_results $result
 * @return environment_results
 */
function totara_core_mysql_environment_check(environment_results $result) {
    global $DB;
    $result->info = 'mysql_configuration';

    if ($DB->get_dbfamily() === 'mysql') {
        // No matter what anybody says InnoDB and XtraDB are the only supported and tested engines.
        $engine = $DB->get_dbengine();
        if (!in_array($engine, array('InnoDB', 'XtraDB'))) {
            $result->setRestrictStr(array('mysqlneedsinnodb', 'totara_core', $engine));
            $result->setStatus(false);
            return $result;
        }

        $fileformat = $DB->get_record_sql("SHOW VARIABLES LIKE 'innodb_file_format'");
        if (!$fileformat or $fileformat->value !== 'Barracuda') {
            $result->setRestrictStr(array('mysqlneedsbarracuda', 'totara_core', $engine));
            $result->setStatus(false);
            return $result;
        }

        $filepertable = $DB->get_record_sql("SHOW VARIABLES LIKE 'innodb_file_per_table'");
        if (!$filepertable or $filepertable->value !== 'ON') {
            $result->setRestrictStr(array('mysqlneedsfilepertable', 'totara_core', $engine));
            $result->setStatus(false);
            return $result;
        }

        $result->setStatus(true);
        return $result;
    }

    // Do not show anything for other databases.
    return null;
}
