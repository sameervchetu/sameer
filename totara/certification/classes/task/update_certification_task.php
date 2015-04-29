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
 * @author Jon Sharp <jonathans@catalyst-eu.net>
 * @author Valerii Kuznetsov <valerii.kuznetsov@totaralms.com>
 * @package totara_certification
 */

namespace totara_certification\task;

/**
 * Update learner assignments for active appraisals.
 */
class update_certification_task extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('updatecertificationstask', 'totara_certification');
    }

    /**
     * Do the job.
     * Throw exceptions on errors (the job will be retried).
     */
    public function execute() {
        global $CFG;
        require_once($CFG->dirroot.'/totara/certification/lib.php');

        if (totara_feature_disabled('certifications')) {
            return;
        }

        mtrace("Doing recertify_window_opens_stage");
        $processed = recertify_window_opens_stage();
        mtrace("... ".$processed.' processed');

        mtrace("Doing recertify_window_abouttoclose_stage");
        $processed = recertify_window_abouttoclose_stage();
        mtrace("... ".$processed.' processed');

        mtrace("Doing recertify_expires_stage");
        $processed = recertify_expires_stage();
        mtrace("... ".$processed.' processed');
    }
}

