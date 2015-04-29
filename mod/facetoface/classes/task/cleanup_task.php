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
 * @author Petr Skoda <petr.skoda@totaralms.com>
 * @package mod_facetoface
 */

namespace mod_facetoface\task;

/**
 * Send facetoface notifications
 */
class cleanup_task extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('cleanuptask', 'mod_facetoface');
    }

    /**
     * Periodic cron cleanup.
     */
    public function execute() {
        global $DB, $CFG;
        require_once($CFG->dirroot.'/mod/facetoface/lib.php');

        // Cancel sessions of all suspended or deleted users,
        // this solves skipped events, direct db edits and upgrades.

        $sql = "SELECT u.id, u.suspended, u.deleted, fs.sessionid
                  FROM {user} u
                  JOIN {facetoface_signups} fs ON fs.userid = u.id
                 WHERE u.deleted <> 0 OR u.suspended <> 0";

        $rs = $DB->get_recordset_sql($sql);

        foreach ($rs as $user) {
            if ($user->deleted) {
                $reason = get_string('userdeletedcancel', 'facetoface');
            } else {
                $reason = get_string('usersuspendedcancel', 'facetoface');
            }
            $session = facetoface_get_session($user->sessionid);
            $error = null; // Passed by reference.
            facetoface_user_cancel($session, $user->id, false, $error, $reason);
        }
        $rs->close();
    }
}
