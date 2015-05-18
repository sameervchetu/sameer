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
 * @author Nathan Lewis <nathan.lewis@totaralms.com>
 * @package totara_certification
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/totara/reportbuilder/tests/reportcache_advanced_testcase.php');
require_once($CFG->dirroot . '/totara/certification/lib.php');

define('CERTIFICATION_USERS', 10);
define('CERTIFICATION_PART_2_USERS', 8);
define('CERTIFICATION_PART_4_USERS', 6);

/**
 * Certification module PHPUnit archive test class.
 *
 * To test, run this from the command line from the $CFG->dirroot.
 * vendor/bin/phpunit --verbose totara_certification_certification_testcase totara/certification/tests/certification_test.php
 */
class totara_certification_certification_testcase extends reportcache_advanced_testcase {

    /**
     * The expiry date is calculated depending on the recertifydatetype property as follows:
     * CERTIFRECERT_EXPIRY
     *      To calculate the next expiry date based on previous expiry date, add the period to the base, where base is the
     *      the completion date when first completed, otherwise the previous expiry date.
     * CERTIFRECERT_COMPLETION
     *      Just add the period to the completion time.
     */
    public function test_certification_recertification() {
        $this->resetAfterTest(true);
        $this->setAdminUser();

        $this->actions_stage_1(); // Initial setup.
        $this->check_stage_1();
        $this->actions_stage_2(); // First completion / primary certification.
        $this->check_stage_2();
        $this->actions_stage_3(); // Recertification window opens.
        $this->check_stage_3();
        $this->actions_stage_4(); // Second completion / recertification.
        $this->check_stage_4();
        $this->actions_stage_5(); // Recertification window opens again.
        $this->check_stage_5();
        $this->actions_stage_6(); // Third completion / recertification.
        $this->check_stage_6();
    }

    private $setuptimeminimum, $setuptimemaximum, $certprograms, $userswithcompletiontime, $users, $completiontime,
            $certprogram1, $certprogram2, $certprogram3, $certprogram4, $courses;

    /**
     * Testing part 1 - Initial setup.
     *
     * Create some users.
     * Create some certifications.
     * Add the users to the certifications.
     * Create some courses and add them to the certifications.
     */
    private function actions_stage_1() {
        global $DB;

        $setuptime = time();
        $this->setuptimeminimum = $setuptime;
        // By waiting a little bit, we ensure that our time asserts are correct, and not just happening
        // within a single second, which could cause intermittent testing failures.
        sleep(1);
        $startoftoday = totara_date_parse_from_format(get_string('datepickerlongyearparseformat', 'totara_core'),
            date('d/m/Y', $setuptime));

        // Create users.
        $this->assertEquals(2, $DB->count_records('user')); // Guest + Admin.
        $this->users = array();
        for ($i = 1; $i <= CERTIFICATION_USERS; $i++) {
            $this->users[$i] = $this->getDataGenerator()->create_user();
        }
        $this->assertEquals(CERTIFICATION_USERS + 2, $DB->count_records('user'),
            'Record count mismatch for users'); // Guest + Admin + generated users.

        // Create four certifications. We will experiment with 1 and 2 and leave the other two alone.
        // Certs 1 and 3 use CERTIFRECERT_EXPIRY, 2 and 4 use CERTIFRECERT_COMPLETION.
        $this->assertEquals(0, $DB->count_records('prog'), "Programs table isn't empty");
        $this->assertEquals(0, $DB->count_records('certif'), "Certif table isn't empty");
        $cert1data = array(
            'cert_learningcomptype' => CERTIFTYPE_PROGRAM,
            'cert_activeperiod' => '365 day',
            'cert_windowperiod' => '90 day',
            'cert_recertifydatetype' => CERTIFRECERT_EXPIRY,
        );
        $this->certprogram1 = $this->getDataGenerator()->create_certification($cert1data);
        $cert2data = array(
            'cert_learningcomptype' => CERTIFTYPE_PROGRAM,
            'cert_activeperiod' => '365 day',
            'cert_windowperiod' => '90 day',
            'cert_recertifydatetype' => CERTIFRECERT_COMPLETION,
        );
        $this->certprogram2 = $this->getDataGenerator()->create_certification($cert2data);
        $cert3data = array(
            'cert_learningcomptype' => CERTIFTYPE_PROGRAM,
            'cert_activeperiod' => '365 day',
            'cert_windowperiod' => '90 day',
            'cert_recertifydatetype' => CERTIFRECERT_EXPIRY,
        );
        $this->certprogram3 = $this->getDataGenerator()->create_certification($cert3data);
        $cert4data = array(
            'cert_learningcomptype' => CERTIFTYPE_PROGRAM,
            'cert_activeperiod' => '365 day',
            'cert_windowperiod' => '90 day',
            'cert_recertifydatetype' => CERTIFRECERT_COMPLETION,
        );
        $this->certprogram4 = $this->getDataGenerator()->create_certification($cert4data);
        $this->certprograms = array($this->certprogram1, $this->certprogram2, $this->certprogram3, $this->certprogram4);
        $this->assertEquals(4, $DB->count_records('prog'), 'Record count mismatch in program table');
        $this->assertEquals(4, $DB->count_records('certif'), 'Record count mismatch for certif');

        // Assign users to the certification as individuals.
        // Completion time is at the start of the day, 14 days from $now.
        $this->completiontime = strtotime("14 day", $startoftoday);
        $this->userswithcompletiontime = array();
        foreach ($this->certprograms as $certprogram) {
            for ($i = 1; $i <= CERTIFICATION_USERS; $i++) {
                if ($i % 2) { // Half of the users (with odd $i) have a completion time.
                    $record = array('completiontime' => date('d/m/Y', $this->completiontime),
                        'completionevent' => COMPLETION_EVENT_NONE);
                    $this->getDataGenerator()->assign_to_program($certprogram->id,
                        ASSIGNTYPE_INDIVIDUAL, $this->users[$i]->id, $record);
                    $this->userswithcompletiontime[] = $this->users[$i];
                } else {
                    $this->getDataGenerator()->assign_to_program($certprogram->id,
                        ASSIGNTYPE_INDIVIDUAL, $this->users[$i]->id);
                }
            }
        }

        // Create some courses and add them to the certification and recertification paths.
        $this->courses = array();
        foreach ($this->certprograms as $certprogram) {
            $course = $this->getDataGenerator()->create_course();
            $this->courses[$certprogram->id] = $course;
            $this->getDataGenerator()->add_courseset_program($certprogram->id, array($course->id), CERTIFPATH_CERT);
            $this->getDataGenerator()->add_courseset_program($certprogram->id, array($course->id), CERTIFPATH_RECERT);
        }

        sleep(1);
        $this->setuptimemaximum = time();
    }

    private $firstcompletiontimeminimum, $firstcompletiontimemaximum, $firstcompletiontime;

    /**
     * Testing part 2 - First completion / primary certification.
     *
     * Mark some users as completion in some courses.
     */
    private function actions_stage_2() {
        // Complete some users in the first two certifications.
        $this->firstcompletiontime = time();
        $this->firstcompletiontimeminimum = $this->firstcompletiontime;
        sleep(1);
        foreach (array($this->courses[$this->certprogram1->id], $this->courses[$this->certprogram2->id]) as $course) {
            for ($i = 1; $i <= CERTIFICATION_PART_2_USERS; $i++) {
                $completion = new completion_completion(array('userid' => $this->users[$i]->id, 'course' => $course->id));
                $completion->mark_complete($this->firstcompletiontime);
            }
        }
        sleep(1);
        $this->firstcompletiontimemaximum = time();
    }

    /**
     * Testing part 3 - Recertification window opens.
     *
     * To do this, we're going to move everything back in time. How far? We want to choose a time where the
     * recertification window is open for all users who completed the initial certification stage. Those with
     * CERTIFRECERT_COMPLETION will be open from 9 months to 12 months from $firstcompletiontime (a few seconds
     * ago). Those with CERTIFRECERT_EXPIRY and no completion time will be the same as above, otherwise they
     * will be open from 9 months to 12 months from $completiontime (= 9m, 2w from now). So they should all be
     * open 11 months (365 - 30 days) from today.
     * Run cron to open the recertification windows.
     */
    private function actions_stage_3() {
        global $DB;

        // Move everything back in time.
        $records = $DB->get_records('certif_completion');
        foreach ($records as $record) {
            if ($record->timewindowopens > 0) {
                $record->timewindowopens = strtotime("-335 day", $record->timewindowopens);
            }
            if ($record->timeexpires > 0) {
                $record->timeexpires = strtotime("-335 day", $record->timeexpires);
            }
            if ($record->timecompleted > 0) {
                $record->timecompleted = strtotime("-335 day", $record->timecompleted);
            }
            if ($record->timemodified > 0) {
                $record->timemodified = strtotime("-335 day", $record->timemodified);
            }
            $DB->update_record('certif_completion', $record);
        }

        $records = $DB->get_records('prog_assignment');
        foreach ($records as $record) {
            if ($record->completiontime > 0) {
                $record->completiontime = strtotime("-335 day", $record->completiontime);
            }
            $DB->update_record('prog_assignment', $record);
        }

        $records = $DB->get_records('prog_completion');
        foreach ($records as $record) {
            if ($record->timestarted > 0) {
                $record->timestarted = strtotime("-335 day", $record->timestarted);
            }
            if ($record->timedue > 0) {
                $record->timedue = strtotime("-335 day", $record->timedue);
            }
            if ($record->timecompleted > 0) {
                $record->timecompleted = strtotime("-335 day", $record->timecompleted);
            }
            $DB->update_record('prog_completion', $record);
        }

        $records = $DB->get_records('prog_user_assignment');
        foreach ($records as $record) {
            if ($record->timeassigned > 0) {
                $record->timeassigned = strtotime("-335 day", $record->timeassigned);
            }
            $DB->update_record('prog_user_assignment', $record);
        }

        $this->completiontime = strtotime("-335 day", $this->completiontime);
        $this->setuptimeminimum = strtotime("-335 day", $this->setuptimeminimum);
        $this->setuptimemaximum = strtotime("-335 day", $this->setuptimemaximum);
        $this->firstcompletiontime = strtotime("-335 day", $this->firstcompletiontime);
        $this->firstcompletiontimeminimum = strtotime("-335 day", $this->firstcompletiontimeminimum);
        $this->firstcompletiontimemaximum = strtotime("-335 day", $this->firstcompletiontimemaximum);

        // Run cron.
        ob_start();
        $certcron = new \totara_certification\task\update_certification_task();
        $certcron->execute();
        $assignmentscron = new \totara_program\task\user_assignments_task();
        $assignmentscron->execute();
        ob_end_clean();
    }

    private $secondcompletiontime, $secondcompletiontimeminimum, $secondcompletiontimemaximum;

    /**
     * Testing part 4 - Second completion / recertification.
     *
     * Mark some users as complete in some courses.
     */
    private function actions_stage_4() {
        global $DB;

        // Complete some users in the first two certifications.
        $this->secondcompletiontime = time();
        $this->secondcompletiontimeminimum = $this->secondcompletiontime;
        sleep(1);
        foreach (array($this->courses[$this->certprogram1->id], $this->courses[$this->certprogram2->id]) as $course) {
            for ($i = 1; $i <= CERTIFICATION_PART_4_USERS; $i++) {
                $completion = new completion_completion(array('userid' => $this->users[$i]->id, 'course' => $course->id));
                $completion->mark_complete($this->secondcompletiontime);
            }
        }
        sleep(1);
        $this->secondcompletiontimemaximum = time();
    }

    private $secondcrontime, $secondcrontimeminimum, $secondcrontimemaximum;

    /**
     * Testing part 5 - Recertification window opens again.
     *
     * To do this, we're going to move everything back in time again. How far? We want to choose a time where the
     * recertification window is open for all users who completed the recertification. 11 months from the previous
     * recertification is ideal for all users, as it is 1 year and 10 months from the initial certification date.
     * For CERTIFRECERT_COMPLETION it is 11 months from the previous completion. For CERTIFRECERT_EXPIRY it is
     * 1 year and 10 months from the first completion. Both of these are inside the three month recertification window.
     * Run cron to open recertification windows. This also clears certification for those who didn't complete
     * recertification - these users will be back on the primary certification path.
     */
    private function actions_stage_5() {
        global $DB;

        // Move everything back in time.
        $records = $DB->get_records('certif_completion');
        foreach ($records as $record) {
            if ($record->timewindowopens > 0) {
                $record->timewindowopens = strtotime("-335 day", $record->timewindowopens);
            }
            if ($record->timeexpires > 0) {
                $record->timeexpires = strtotime("-335 day", $record->timeexpires);
            }
            if ($record->timecompleted > 0) {
                $record->timecompleted = strtotime("-335 day", $record->timecompleted);
            }
            if ($record->timemodified > 0) {
                $record->timemodified = strtotime("-335 day", $record->timemodified);
            }
            $DB->update_record('certif_completion', $record);
        }

        $records = $DB->get_records('prog_assignment');
        foreach ($records as $record) {
            if ($record->completiontime > 0) {
                $record->completiontime = strtotime("-335 day", $record->completiontime);
            }
            $DB->update_record('prog_assignment', $record);
        }

        $records = $DB->get_records('prog_completion');
        foreach ($records as $record) {
            if ($record->timestarted > 0) {
                $record->timestarted = strtotime("-335 day", $record->timestarted);
            }
            if ($record->timedue > 0) {
                $record->timedue = strtotime("-335 day", $record->timedue);
            }
            if ($record->timecompleted > 0) {
                $record->timecompleted = strtotime("-335 day", $record->timecompleted);
            }
            $DB->update_record('prog_completion', $record);
        }

        $records = $DB->get_records('prog_user_assignment');
        foreach ($records as $record) {
            if ($record->timeassigned > 0) {
                $record->timeassigned = strtotime("-335 day", $record->timeassigned);
            }
            $DB->update_record('prog_user_assignment', $record);
        }

        $this->completiontime = strtotime("-335 day", $this->completiontime);
        $this->setuptimeminimum = strtotime("-335 day", $this->setuptimeminimum);
        $this->setuptimemaximum = strtotime("-335 day", $this->setuptimemaximum);
        $this->firstcompletiontime = strtotime("-335 day", $this->firstcompletiontime);
        $this->firstcompletiontimeminimum = strtotime("-335 day", $this->firstcompletiontimeminimum);
        $this->firstcompletiontimemaximum = strtotime("-335 day", $this->firstcompletiontimemaximum);
        $this->secondcompletiontime = strtotime("-335 day", $this->secondcompletiontime);
        $this->secondcompletiontimeminimum = strtotime("-335 day", $this->secondcompletiontimeminimum);
        $this->secondcompletiontimemaximum = strtotime("-335 day", $this->secondcompletiontimemaximum);

        // Run cron.
        $this->secondcrontime = time();
        $this->secondcrontimeminimum = $this->secondcrontime;
        sleep(1);
        ob_start();
        $certcron = new \totara_certification\task\update_certification_task();
        $certcron->execute();
        $assignmentscron = new \totara_program\task\user_assignments_task();
        $assignmentscron->execute();
        ob_end_clean();
        sleep(1);
        $this->secondcrontimemaximum = time();
    }

    private $thirdcompletiontime, $thirdcompletiontimeminimum, $thirdcompletiontimemaximum;

    private function actions_stage_6() {

        // Testing part 6 - Third completion / recertification.
        // Mark all users as completion in some courses. It should not matter if they are recertifying for the second time,
        // primary certifying for the first time, or certified, failed to recertify and now did primary certification again.
        // Check the user data.

        // Complete all users in the first two certifications.
        $this->thirdcompletiontime = time();
        $this->thirdcompletiontimeminimum = $this->thirdcompletiontime;
        sleep(1);
        foreach (array($this->courses[$this->certprogram1->id], $this->courses[$this->certprogram2->id]) as $course) {
            for ($i = 1; $i <= CERTIFICATION_USERS; $i++) {
                $completion = new completion_completion(array('userid' => $this->users[$i]->id, 'course' => $course->id));
                $completion->mark_complete($this->thirdcompletiontime);
            }
        }
        sleep(1);
        $this->thirdcompletiontimemaximum = time();

    }

    public function check_stage_1() {
        global $DB;

        // Check the status of all users.
        foreach ($this->certprograms as $certprogram) {
            for ($i = 1; $i <= CERTIFICATION_USERS; $i++) {
                $user = $this->users[$i];
                $hascompletiontime = in_array($user, $this->userswithcompletiontime);

                // Program assignment and program user assignment records should exist for all users.
                // We get all prog_user_assignment records and their matching prog_assignment records.
                $sql = "SELECT pa.*, pua.programid, pua.userid, pua.timeassigned, exceptionstatus
                          FROM {prog_assignment} pa
                          JOIN {prog_user_assignment} pua ON pua.assignmentid = pa.id
                         WHERE pua.programid = :programid AND pua.userid = :userid";
                $progassignments = $DB->get_records_sql($sql,
                    array('programid' => $certprogram->id,
                          'userid' => $user->id));
                $this->assertCount(1, $progassignments); // Just one.
                $progassignment = reset($progassignments);
                $this->assertEquals(ASSIGNTYPE_INDIVIDUAL, $progassignment->assignmenttype);
                $this->assertEquals($user->id, $progassignment->assignmenttypeid);
                $this->assertEquals(0, $progassignment->includechildren);
                if ($hascompletiontime) {
                    $this->assertEquals($this->completiontime, $progassignment->completiontime); // Has completion time.
                } else {
                    $this->assertEquals(-1, $progassignment->completiontime); // No completion time.
                }
                $this->assertEquals(0, $progassignment->completionevent); // No completion event.
                $this->assertEquals(0, $progassignment->completioninstance); // No completion instance.
                // Time assigned.
                $this->assertGreaterThan($this->setuptimeminimum, $progassignment->timeassigned);
                $this->assertLessThan($this->setuptimemaximum, $progassignment->timeassigned);
                $this->assertEquals(PROGRAM_EXCEPTION_NONE, $progassignment->exceptionstatus); // No exceptions.

                // Program completion records should exist for all users.
                $progcompletions = $DB->get_records('prog_completion',
                    array('programid' => $certprogram->id,
                        'userid' => $user->id,
                        'coursesetid' => 0));
                $this->assertCount(1, $progcompletions); // Just one.
                $progcompletion = reset($progcompletions);
                $this->assertEquals(STATUS_PROGRAM_INCOMPLETE, $progcompletion->status); // Status is incomplete.
                // When it was started.
                $this->assertGreaterThan($this->setuptimeminimum, $progcompletion->timestarted);
                $this->assertLessThan($this->setuptimemaximum, $progcompletion->timestarted);
                if ($hascompletiontime) {
                    $this->assertEquals($this->completiontime, $progcompletion->timedue); // Has timedue.
                } else {
                    $this->assertEquals(-1, $progcompletion->timedue); // No timedue.
                }

                // Certification completion records should exist for all users.
                $certifcompletions = $DB->get_records('certif_completion',
                    array('certifid' => $certprogram->certifid,
                          'userid' => $user->id));
                $this->assertCount(1, $certifcompletions); // Just one.
                $certifcompletion = reset($certifcompletions);
                $this->assertEquals(CERTIFPATH_CERT, $certifcompletion->certifpath); // Primary certification path.
                $this->assertEquals(CERTIFSTATUS_ASSIGNED, $certifcompletion->status); // Status assigned.
                $this->assertEquals(CERTIFRENEWALSTATUS_NOTDUE, $certifcompletion->renewalstatus); // Not due for renewal.
                $this->assertEquals(0, $certifcompletion->timeexpires); // No expiry.
                $this->assertEquals(0, $certifcompletion->timewindowopens); // No window.
                $this->assertEquals(0, $certifcompletion->timecompleted); // Not completed.
                // When record was created.
                $this->assertGreaterThan($this->setuptimeminimum, $certifcompletion->timemodified);
                $this->assertLessThan($this->setuptimemaximum, $certifcompletion->timemodified);
            }
        }
    }

    public function check_stage_2() {
        global $DB;

        // Check the status of all users.
        foreach ($this->certprograms as $certprogram) {
            for ($i = 1; $i <= CERTIFICATION_USERS; $i++) {
                $user = $this->users[$i];
                $hascompletiontime = in_array($user, $this->userswithcompletiontime);
                $didfirstcompletion = ($certprogram == $this->certprogram1 || $certprogram == $this->certprogram2) &&
                    ($i <= CERTIFICATION_PART_2_USERS);
                if ($didfirstcompletion) {
                    if ($certprogram == $this->certprogram1) {
                        /*
                         * CERTIFRECERT_EXPIRY.
                         * At this point, no users have previous expiry date.
                         */
                        $basetime = $this->firstcompletiontime;
                    } else {
                        // CERTIFRECERT_COMPLETION.
                        $basetime = $this->firstcompletiontime;
                    }
                    $timeexpires = get_timeexpires($basetime, '365 day');
                    $timewindowopens = get_timewindowopens($timeexpires, '90 day');
                } else {
                    // Not applicable if they haven't certified.
                    $timeexpires = 'invalid';
                    $timewindowopens = 'invalid';
                }

                // Program assignment and program user assignment records should exist for all users.
                // We get all prog_user_assignment records and their matching prog_assignment records.
                $sql = "SELECT pa.*, pua.programid, pua.userid, pua.timeassigned, exceptionstatus
                          FROM {prog_assignment} pa
                          JOIN {prog_user_assignment} pua ON pua.assignmentid = pa.id
                         WHERE pua.programid = :programid AND pua.userid = :userid";
                $progassignments = $DB->get_records_sql($sql,
                    array('programid' => $certprogram->id,
                        'userid' => $user->id));
                $this->assertCount(1, $progassignments); // Just one.
                $progassignment = reset($progassignments);
                $this->assertEquals(ASSIGNTYPE_INDIVIDUAL, $progassignment->assignmenttype);
                $this->assertEquals($user->id, $progassignment->assignmenttypeid);
                $this->assertEquals(0, $progassignment->includechildren);
                if ($hascompletiontime) {
                    $this->assertEquals($this->completiontime, $progassignment->completiontime); // Has completion time.
                } else {
                    $this->assertEquals(-1, $progassignment->completiontime); // No completion time.
                }
                $this->assertEquals(0, $progassignment->completionevent); // No completion event.
                $this->assertEquals(0, $progassignment->completioninstance); // No completion instance.
                // Time assigned.
                $this->assertGreaterThan($this->setuptimeminimum, $progassignment->timeassigned);
                $this->assertLessThan($this->setuptimemaximum, $progassignment->timeassigned);
                $this->assertEquals(PROGRAM_EXCEPTION_NONE, $progassignment->exceptionstatus); // No exceptions.

                // Program completion records should exist for all users.
                $progcompletions = $DB->get_records('prog_completion',
                    array('programid' => $certprogram->id,
                        'userid' => $user->id,
                        'coursesetid' => 0));
                $this->assertCount(1, $progcompletions); // Just one.
                $progcompletion = reset($progcompletions);
                if ($didfirstcompletion) {
                    $this->assertEquals(STATUS_PROGRAM_COMPLETE, $progcompletion->status); // Status complete!
                } else {
                    $this->assertEquals(STATUS_PROGRAM_INCOMPLETE, $progcompletion->status); // Status is incomplete.
                }
                // When it was started.
                $this->assertGreaterThan($this->setuptimeminimum, $progcompletion->timestarted);
                $this->assertLessThan($this->setuptimemaximum, $progcompletion->timestarted);
                if ($didfirstcompletion) {
                    $this->assertEquals($timeexpires, $progcompletion->timedue); // Set when certified.
                } else {
                    if ($hascompletiontime) {
                        $this->assertEquals($this->completiontime, $progcompletion->timedue); // Has timedue.
                    } else {
                        $this->assertEquals(-1, $progcompletion->timedue); // No timedue.
                    }
                }

                // Certification completion records should exist for all users.
                $certifcompletions = $DB->get_records('certif_completion',
                    array('certifid' => $certprogram->certifid,
                        'userid' => $user->id));
                $this->assertCount(1, $certifcompletions); // Just one.
                $certifcompletion = reset($certifcompletions);
                $this->assertEquals(CERTIFRENEWALSTATUS_NOTDUE, $certifcompletion->renewalstatus); // Not due for renewal.
                if ($didfirstcompletion) {
                    $this->assertEquals(CERTIFPATH_RECERT, $certifcompletion->certifpath); // Recertification path.
                    $this->assertEquals(CERTIFSTATUS_COMPLETED, $certifcompletion->status); // Status completed.
                    $this->assertEquals($timeexpires, $certifcompletion->timeexpires);
                    $this->assertEquals($timewindowopens, $certifcompletion->timewindowopens);
                    $this->assertEquals($this->firstcompletiontime, $certifcompletion->timecompleted); // Completed.
                    // When record was modified.
                    $this->assertGreaterThan($this->firstcompletiontimeminimum, $certifcompletion->timemodified);
                    $this->assertLessThan($this->firstcompletiontimemaximum, $certifcompletion->timemodified);
                } else {
                    $this->assertEquals(CERTIFPATH_CERT, $certifcompletion->certifpath); // Primary certification path.
                    $this->assertEquals(CERTIFSTATUS_ASSIGNED, $certifcompletion->status); // Status assigned.
                    $this->assertEquals(0, $certifcompletion->timeexpires); // No expiry.
                    $this->assertEquals(0, $certifcompletion->timewindowopens); // No window.
                    $this->assertEquals(0, $certifcompletion->timecompleted); // Not completed.
                    // When record was created.
                    $this->assertGreaterThan($this->setuptimeminimum, $certifcompletion->timemodified);
                    $this->assertLessThan($this->setuptimemaximum, $certifcompletion->timemodified);
                }
            }
        }
    }

    public function check_stage_3() {
        global $DB;

        // Check the status of all users.
        foreach ($this->certprograms as $certprogram) {
            for ($i = 1; $i <= CERTIFICATION_USERS; $i++) {
                $user = $this->users[$i];
                $hascompletiontime = in_array($user, $this->userswithcompletiontime);
                $didfirstcompletion = ($certprogram == $this->certprogram1 || $certprogram == $this->certprogram2) &&
                    ($i <= CERTIFICATION_PART_2_USERS);
                if ($didfirstcompletion) {
                    if ($certprogram == $this->certprogram1) {
                        /*
                         * CERTIFRECERT_EXPIRY.
                         * At this point, no users have previous expiry date.
                         */
                        $basetime = $this->firstcompletiontime;
                    } else {
                        // CERTIFRECERT_COMPLETION.
                        $basetime = $this->firstcompletiontime;
                    }
                    $timeexpires = get_timeexpires($basetime, '365 day');
                    $timewindowopens = get_timewindowopens($timeexpires, '90 day');
                } else {
                    // Not applicable if they haven't certified.
                    $timeexpires = 'invalid';
                    $timewindowopens = 'invalid';
                }

                // Program assignment and program user assignment records should exist for all users.
                // We get all prog_user_assignment records and their matching prog_assignment records.
                $sql = "SELECT pa.*, pua.programid, pua.userid, pua.timeassigned, exceptionstatus
                          FROM {prog_assignment} pa
                          JOIN {prog_user_assignment} pua ON pua.assignmentid = pa.id
                         WHERE pua.programid = :programid AND pua.userid = :userid";
                $progassignments = $DB->get_records_sql($sql,
                    array('programid' => $certprogram->id,
                        'userid' => $user->id));
                $this->assertCount(1, $progassignments); // Just one.
                $progassignment = reset($progassignments);
                $this->assertEquals(ASSIGNTYPE_INDIVIDUAL, $progassignment->assignmenttype);
                $this->assertEquals($user->id, $progassignment->assignmenttypeid);
                $this->assertEquals(0, $progassignment->includechildren);
                if ($hascompletiontime) {
                    $this->assertEquals($this->completiontime, $progassignment->completiontime); // Has completion time.
                } else {
                    $this->assertEquals(-1, $progassignment->completiontime); // No completion time.
                }
                $this->assertEquals(0, $progassignment->completionevent); // No completion event.
                $this->assertEquals(0, $progassignment->completioninstance); // No completion instance.
                // Time assigned.
                $this->assertGreaterThan($this->setuptimeminimum, $progassignment->timeassigned);
                $this->assertLessThan($this->setuptimemaximum, $progassignment->timeassigned);
                $this->assertEquals(PROGRAM_EXCEPTION_NONE, $progassignment->exceptionstatus); // No exceptions.

                // Program completion records should exist for all users.
                $progcompletions = $DB->get_records('prog_completion',
                    array('programid' => $certprogram->id,
                        'userid' => $user->id,
                        'coursesetid' => 0));
                $this->assertCount(1, $progcompletions); // Just one.
                $progcompletion = reset($progcompletions);
                if ($didfirstcompletion) {
                    $this->assertEquals(STATUS_PROGRAM_INCOMPLETE, $progcompletion->status); // Set during window opening.
                    $this->assertEquals(0, $progcompletion->timestarted); // Reset to 0 during window opening.
                    $this->assertEquals($timeexpires, $progcompletion->timedue); // Set when certified.
                } else {
                    $this->assertEquals(STATUS_PROGRAM_INCOMPLETE, $progcompletion->status); // Status is incomplete.
                    // When it was started.
                    $this->assertGreaterThan($this->setuptimeminimum, $progcompletion->timestarted);
                    $this->assertLessThan($this->setuptimemaximum, $progcompletion->timestarted);
                    if ($hascompletiontime) {
                        $this->assertEquals($this->completiontime, $progcompletion->timedue); // Has timedue.
                    } else {
                        $this->assertEquals(-1, $progcompletion->timedue); // No timedue.
                    }
                }

                // Certification completion records should exist for all users.
                $certifcompletions = $DB->get_records('certif_completion',
                    array('certifid' => $certprogram->certifid,
                        'userid' => $user->id));
                $this->assertCount(1, $certifcompletions); // Just one.
                $certifcompletion = reset($certifcompletions);
                if ($didfirstcompletion) {
                    $this->assertEquals(CERTIFRENEWALSTATUS_DUE, $certifcompletion->renewalstatus); // Due for renewal.
                    $this->assertEquals(CERTIFPATH_RECERT, $certifcompletion->certifpath); // Recertification path.
                    $this->assertEquals(CERTIFSTATUS_COMPLETED, $certifcompletion->status); // Status completed.
                    $this->assertEquals($timeexpires, $certifcompletion->timeexpires);
                    $this->assertEquals($timewindowopens, $certifcompletion->timewindowopens);
                    $this->assertEquals($this->firstcompletiontime, $certifcompletion->timecompleted); // Completed.
                    // When record was modified.
                    $this->assertGreaterThan($this->firstcompletiontimeminimum, $certifcompletion->timemodified);
                    $this->assertLessThan($this->firstcompletiontimemaximum, $certifcompletion->timemodified);
                } else {
                    $this->assertEquals(CERTIFRENEWALSTATUS_NOTDUE, $certifcompletion->renewalstatus); // Not due for renewal.
                    $this->assertEquals(CERTIFPATH_CERT, $certifcompletion->certifpath); // Primary certification path.
                    $this->assertEquals(CERTIFSTATUS_ASSIGNED, $certifcompletion->status); // Status assigned.
                    $this->assertEquals(0, $certifcompletion->timeexpires); // No expiry.
                    $this->assertEquals(0, $certifcompletion->timewindowopens); // No window.
                    $this->assertEquals(0, $certifcompletion->timecompleted); // Not completed.
                    // When record was created.
                    $this->assertGreaterThan($this->setuptimeminimum, $certifcompletion->timemodified);
                    $this->assertLessThan($this->setuptimemaximum, $certifcompletion->timemodified);
                }
            }
        }
    }

    public function check_stage_4() {
        global $DB;

        // Check the status of all users.
        foreach ($this->certprograms as $certprogram) {
            for ($i = 1; $i <= CERTIFICATION_USERS; $i++) {
                $user = $this->users[$i];
                $hascompletiontime = in_array($user, $this->userswithcompletiontime);
                $didfirstcompletion = ($certprogram == $this->certprogram1 || $certprogram == $this->certprogram2) &&
                    ($i <= CERTIFICATION_PART_2_USERS);
                $didsecondcompletion = ($certprogram == $this->certprogram1 || $certprogram == $this->certprogram2) &&
                    ($i <= CERTIFICATION_PART_4_USERS);
                if ($didsecondcompletion) {
                    if ($certprogram == $this->certprogram1) {
                        /*
                         * CERTIFRECERT_EXPIRY.
                         * At this point, these users do have previous expiry date.
                         */
                        $basetime = strtotime("365 day", $this->firstcompletiontime);
                    } else {
                        // CERTIFRECERT_COMPLETION.
                        $basetime = $this->secondcompletiontime;
                    }
                    $timeexpires = get_timeexpires($basetime, '365 day');
                    $timewindowopens = get_timewindowopens($timeexpires, '90 day');
                } else if ($didfirstcompletion) { // But hasn't recertified.
                    if ($certprogram == $this->certprogram1) {
                        /*
                         * CERTIFRECERT_EXPIRY.
                         * At this point, these users do have previous expiry date.
                         */
                        $basetime = $this->firstcompletiontime;
                    } else {
                        // CERTIFRECERT_COMPLETION.
                        $basetime = $this->firstcompletiontime;
                    }
                    $timeexpires = get_timeexpires($basetime, '365 day');
                    $timewindowopens = get_timewindowopens($timeexpires, '90 day');
                } else {
                    // Not applicable if they haven't certified.
                    $timeexpires = 'invalid';
                    $timewindowopens = 'invalid';
                }

                // Program assignment and program user assignment records should exist for all users.
                // We get all prog_user_assignment records and their matching prog_assignment records.
                $sql = "SELECT pa.*, pua.programid, pua.userid, pua.timeassigned, exceptionstatus
                          FROM {prog_assignment} pa
                          JOIN {prog_user_assignment} pua ON pua.assignmentid = pa.id
                         WHERE pua.programid = :programid AND pua.userid = :userid";
                $progassignments = $DB->get_records_sql($sql,
                    array('programid' => $certprogram->id,
                        'userid' => $user->id));
                $this->assertCount(1, $progassignments); // Just one.
                $progassignment = reset($progassignments);
                $this->assertEquals(ASSIGNTYPE_INDIVIDUAL, $progassignment->assignmenttype);
                $this->assertEquals($user->id, $progassignment->assignmenttypeid);
                $this->assertEquals(0, $progassignment->includechildren);
                if ($hascompletiontime) {
                    $this->assertEquals($this->completiontime, $progassignment->completiontime); // Has completion time.
                } else {
                    $this->assertEquals(-1, $progassignment->completiontime); // No completion time.
                }
                $this->assertEquals(0, $progassignment->completionevent); // No completion event.
                $this->assertEquals(0, $progassignment->completioninstance); // No completion instance.
                // Time assigned.
                $this->assertGreaterThan($this->setuptimeminimum, $progassignment->timeassigned);
                $this->assertLessThan($this->setuptimemaximum, $progassignment->timeassigned);
                $this->assertEquals(PROGRAM_EXCEPTION_NONE, $progassignment->exceptionstatus); // No exceptions.

                // Program completion records should exist for all users.
                $progcompletions = $DB->get_records('prog_completion',
                    array('programid' => $certprogram->id,
                        'userid' => $user->id,
                        'coursesetid' => 0));
                $this->assertCount(1, $progcompletions); // Just one.
                $progcompletion = reset($progcompletions);
                if ($didsecondcompletion) {
                    $this->assertEquals(STATUS_PROGRAM_COMPLETE, $progcompletion->status); // Set when completed.
                    $this->assertEquals(0, $progcompletion->timestarted); // Reset to 0 during window opening.
                    $this->assertEquals($timeexpires, $progcompletion->timedue); // Set when certified.
                } else if ($didfirstcompletion) {
                    $this->assertEquals(STATUS_PROGRAM_INCOMPLETE, $progcompletion->status); // Set during window opening.
                    $this->assertEquals(0, $progcompletion->timestarted); // Reset to 0 during window opening.
                    $this->assertEquals($timeexpires, $progcompletion->timedue); // Set when certified.
                } else {
                    $this->assertEquals(STATUS_PROGRAM_INCOMPLETE, $progcompletion->status); // Status is incomplete.
                    // When it was started.
                    $this->assertGreaterThan($this->setuptimeminimum, $progcompletion->timestarted);
                    $this->assertLessThan($this->setuptimemaximum, $progcompletion->timestarted);
                    if ($i % 2) {
                        $this->assertEquals($this->completiontime, $progcompletion->timedue); // Has timedue.
                    } else {
                        $this->assertEquals(-1, $progcompletion->timedue); // No timedue.
                    }
                }

                // Certification completion records should exist for all users.
                $certifcompletions = $DB->get_records('certif_completion',
                    array('certifid' => $certprogram->certifid,
                        'userid' => $user->id));
                $this->assertCount(1, $certifcompletions); // Just one.
                $certifcompletion = reset($certifcompletions);
                if ($didsecondcompletion) {
                    $this->assertEquals(CERTIFRENEWALSTATUS_NOTDUE, $certifcompletion->renewalstatus); // Not due for renewal.
                    $this->assertEquals(CERTIFPATH_RECERT, $certifcompletion->certifpath); // Recertification path.
                    $this->assertEquals(CERTIFSTATUS_COMPLETED, $certifcompletion->status); // Status completed.
                    $this->assertEquals($timeexpires, $certifcompletion->timeexpires);
                    $this->assertEquals($timewindowopens, $certifcompletion->timewindowopens);
                    $this->assertEquals($this->secondcompletiontime, $certifcompletion->timecompleted); // Completed.
                    // When record was modified.
                    $this->assertGreaterThan($this->secondcompletiontimeminimum, $certifcompletion->timemodified);
                    $this->assertLessThan($this->secondcompletiontimemaximum, $certifcompletion->timemodified);
                } else if ($didfirstcompletion) { // But haven't recertified.
                    $this->assertEquals(CERTIFRENEWALSTATUS_DUE, $certifcompletion->renewalstatus); // Due for renewal.
                    $this->assertEquals(CERTIFPATH_RECERT, $certifcompletion->certifpath); // Recertification path.
                    $this->assertEquals(CERTIFSTATUS_COMPLETED, $certifcompletion->status); // Status completed.
                    $this->assertEquals($timeexpires, $certifcompletion->timeexpires);
                    $this->assertEquals($timewindowopens, $certifcompletion->timewindowopens);
                    $this->assertEquals($this->firstcompletiontime, $certifcompletion->timecompleted); // Completed.
                    // When record was modified.
                    $this->assertGreaterThan($this->firstcompletiontimeminimum, $certifcompletion->timemodified);
                    $this->assertLessThan($this->firstcompletiontimemaximum, $certifcompletion->timemodified);
                } else {
                    $this->assertEquals(CERTIFRENEWALSTATUS_NOTDUE, $certifcompletion->renewalstatus); // Not due for renewal.
                    $this->assertEquals(CERTIFPATH_CERT, $certifcompletion->certifpath); // Primary certification path.
                    $this->assertEquals(CERTIFSTATUS_ASSIGNED, $certifcompletion->status); // Status assigned.
                    $this->assertEquals(0, $certifcompletion->timeexpires); // No expiry.
                    $this->assertEquals(0, $certifcompletion->timewindowopens); // No window.
                    $this->assertEquals(0, $certifcompletion->timecompleted); // Not completed.
                    // When record was created.
                    $this->assertGreaterThan($this->setuptimeminimum, $certifcompletion->timemodified);
                    $this->assertLessThan($this->setuptimemaximum, $certifcompletion->timemodified);
                }
            }
        }
    }

    public function check_stage_5() {
        global $DB;

        // Check the status of all users.
        foreach ($this->certprograms as $certprogram) {
            for ($i = 1; $i <= CERTIFICATION_USERS; $i++) {
                $user = $this->users[$i];
                $hascompletiontime = in_array($user, $this->userswithcompletiontime);
                $didfirstcompletion = ($certprogram == $this->certprogram1 || $certprogram == $this->certprogram2) &&
                    ($i <= CERTIFICATION_PART_2_USERS);
                $didsecondcompletion = ($certprogram == $this->certprogram1 || $certprogram == $this->certprogram2) &&
                    ($i <= CERTIFICATION_PART_4_USERS);
                if ($didsecondcompletion) {
                    if ($certprogram == $this->certprogram1) {
                        /*
                         * CERTIFRECERT_EXPIRY.
                         * At this point, these users do have previous expiry date.
                         */
                        $basetime = strtotime("365 day", $this->firstcompletiontime);
                    } else {
                        // CERTIFRECERT_COMPLETION.
                        $basetime = $this->secondcompletiontime;
                    }
                    $timeexpires = get_timeexpires($basetime, '365 day');
                    $timewindowopens = get_timewindowopens($timeexpires, '90 day');
                } else if ($didfirstcompletion) { // But didn't recertify, so is now expired and back on primary cert path, overdue.
                    if ($certprogram == $this->certprogram1) {
                        /*
                         * CERTIFRECERT_EXPIRY.
                         * At this point, these users do have previous expiry date.
                         */
                        $basetime = $this->firstcompletiontime;
                    } else {
                        // CERTIFRECERT_COMPLETION.
                        $basetime = $this->firstcompletiontime;
                    }
                    $timeexpires = get_timeexpires($basetime, '365 day');
                    $timewindowopens = get_timewindowopens($timeexpires, '90 day');
                } else {
                    // Not applicable if they haven't certified.
                    $timeexpires = 'invalid';
                    $timewindowopens = 'invalid';
                }

                // Program assignment and program user assignment records should exist for all users.
                // We get all prog_user_assignment records and their matching prog_assignment records.
                $sql = "SELECT pa.*, pua.programid, pua.userid, pua.timeassigned, exceptionstatus
                          FROM {prog_assignment} pa
                          JOIN {prog_user_assignment} pua ON pua.assignmentid = pa.id
                         WHERE pua.programid = :programid AND pua.userid = :userid";
                $progassignments = $DB->get_records_sql($sql,
                    array('programid' => $certprogram->id,
                        'userid' => $user->id));
                $this->assertCount(1, $progassignments); // Just one.
                $progassignment = reset($progassignments);
                $this->assertEquals(ASSIGNTYPE_INDIVIDUAL, $progassignment->assignmenttype);
                $this->assertEquals($user->id, $progassignment->assignmenttypeid);
                $this->assertEquals(0, $progassignment->includechildren);
                if ($hascompletiontime) {
                    $this->assertEquals($this->completiontime, $progassignment->completiontime); // Has completion time.
                } else {
                    $this->assertEquals(-1, $progassignment->completiontime); // No completion time.
                }
                $this->assertEquals(0, $progassignment->completionevent); // No completion event.
                $this->assertEquals(0, $progassignment->completioninstance); // No completion instance.
                // Time assigned.
                $this->assertGreaterThan($this->setuptimeminimum, $progassignment->timeassigned);
                $this->assertLessThan($this->setuptimemaximum, $progassignment->timeassigned);
                $this->assertEquals(PROGRAM_EXCEPTION_NONE, $progassignment->exceptionstatus); // No exceptions.

                // Program completion records should exist for all users.
                $progcompletions = $DB->get_records('prog_completion',
                    array('programid' => $certprogram->id,
                        'userid' => $user->id,
                        'coursesetid' => 0));
                $this->assertCount(1, $progcompletions); // Just one.
                $progcompletion = reset($progcompletions);
                if ($didsecondcompletion) {
                    $this->assertEquals(STATUS_PROGRAM_INCOMPLETE, $progcompletion->status); // Set during window opening.
                    $this->assertEquals(0, $progcompletion->timestarted); // Reset to 0 during window opening.
                    $this->assertEquals($timeexpires, $progcompletion->timedue); // Set when certified.
                } else if ($didfirstcompletion) {
                    $this->assertEquals(STATUS_PROGRAM_INCOMPLETE, $progcompletion->status); // Set during window opening.
                    $this->assertEquals(0, $progcompletion->timestarted); // Reset to 0 during window opening.
                    $this->assertEquals($timeexpires, $progcompletion->timedue); // Set when certified.
                } else {
                    $this->assertEquals(STATUS_PROGRAM_INCOMPLETE, $progcompletion->status); // Status is incomplete.
                    // When it was started.
                    $this->assertGreaterThan($this->setuptimeminimum, $progcompletion->timestarted);
                    $this->assertLessThan($this->setuptimemaximum, $progcompletion->timestarted);
                    if ($hascompletiontime) {
                        $this->assertEquals($this->completiontime, $progcompletion->timedue); // Has timedue.
                    } else {
                        $this->assertEquals(-1, $progcompletion->timedue); // No timedue.
                    }
                }

                // Certification completion records should exist for all users.
                $certifcompletions = $DB->get_records('certif_completion',
                    array('certifid' => $certprogram->certifid,
                        'userid' => $user->id));
                $this->assertCount(1, $certifcompletions); // Just one.
                $certifcompletion = reset($certifcompletions);
                if ($didsecondcompletion) {
                    $this->assertEquals(CERTIFRENEWALSTATUS_DUE, $certifcompletion->renewalstatus); // Due for renewal.
                    $this->assertEquals(CERTIFPATH_RECERT, $certifcompletion->certifpath); // Recertification path.
                    $this->assertEquals(CERTIFSTATUS_COMPLETED, $certifcompletion->status); // Status completed.
                    $this->assertEquals($timeexpires, $certifcompletion->timeexpires);
                    $this->assertEquals($timewindowopens, $certifcompletion->timewindowopens);
                    $this->assertEquals($this->secondcompletiontime, $certifcompletion->timecompleted); // Completed.
                    // When record was modified.
                    $this->assertGreaterThan($this->secondcompletiontimeminimum, $certifcompletion->timemodified);
                    $this->assertLessThan($this->secondcompletiontimemaximum, $certifcompletion->timemodified);
                } else {
                    // Those who failed to recertify will be back on the primary certification path.
                    if ($didfirstcompletion) {
                        $this->assertEquals(CERTIFRENEWALSTATUS_EXPIRED, $certifcompletion->renewalstatus); // Expired.
                    } else {
                        $this->assertEquals(CERTIFRENEWALSTATUS_NOTDUE, $certifcompletion->renewalstatus); // Not due for renewal.
                    }
                    $this->assertEquals(CERTIFPATH_CERT, $certifcompletion->certifpath); // Back to primary certification path.
                    $this->assertEquals(CERTIFSTATUS_ASSIGNED, $certifcompletion->status); // Status assigned, as if newly assigned.
                    $this->assertEquals(0, $certifcompletion->timeexpires); // No expiry.
                    $this->assertEquals(0, $certifcompletion->timewindowopens); // No window.
                    $this->assertEquals(0, $certifcompletion->timecompleted); // Not completed.
                    // When record was created.
                    if ($didfirstcompletion) {
                        // Those who did the first but not the second are now expired, which caused refresh of timemodified.
                        $this->assertGreaterThan($this->secondcrontimeminimum, $certifcompletion->timemodified);
                        $this->assertLessThan($this->secondcrontimemaximum, $certifcompletion->timemodified);
                    } else {
                        $this->assertGreaterThan($this->setuptimeminimum, $certifcompletion->timemodified);
                        $this->assertLessThan($this->setuptimemaximum, $certifcompletion->timemodified);
                    }
                }
            }
        }
    }

    public function check_stage_6() {
        global $DB;

        // Check the status of all users.
        foreach ($this->certprograms as $certprogram) {
            for ($i = 1; $i <= CERTIFICATION_USERS; $i++) {
                $user = $this->users[$i];
                $hascompletiontime = in_array($user, $this->userswithcompletiontime);
                $didfirstcompletion = ($certprogram == $this->certprogram1 || $certprogram == $this->certprogram2) &&
                    ($i <= CERTIFICATION_PART_2_USERS);
                $didsecondcompletion = ($certprogram == $this->certprogram1 || $certprogram == $this->certprogram2) &&
                    ($i <= CERTIFICATION_PART_4_USERS);
                $didthirdcompletion = ($certprogram == $this->certprogram1 || $certprogram == $this->certprogram2);
                if ($didthirdcompletion) { // All users in programs 1 and 2.
                    if ($didsecondcompletion) { // They are recertifying for the second time.
                        if ($certprogram == $this->certprogram1) {
                            /*
                             * CERTIFRECERT_EXPIRY.
                             * At this point, these users do have previous expiry date.
                             */
                            $basetime = strtotime("730 day", $this->firstcompletiontime);
                        } else {
                            // CERTIFRECERT_COMPLETION.
                            $basetime = $this->thirdcompletiontime;
                        }
                    } else { // Expired first certification or certifying overdue for the first time.
                        if ($certprogram == $this->certprogram1) {
                            /*
                             * CERTIFRECERT_EXPIRY.
                             * Since they are overdue, just use the completion date.
                             */
                            $basetime = $this->thirdcompletiontime;
                        } else {
                            // CERTIFRECERT_COMPLETION.
                            $basetime = $this->thirdcompletiontime;
                        }
                    }
                    $timeexpires = get_timeexpires($basetime, '365 day');
                    $timewindowopens = get_timewindowopens($timeexpires, '90 day');
                } else {
                    // Not applicable if they haven't certified.
                    $timeexpires = 'invalid';
                    $timewindowopens = 'invalid';
                }

                // Program assignment and program user assignment records should exist for all users.
                // We get all prog_user_assignment records and their matching prog_assignment records.
                $sql = "SELECT pa.*, pua.programid, pua.userid, pua.timeassigned, exceptionstatus
                          FROM {prog_assignment} pa
                          JOIN {prog_user_assignment} pua ON pua.assignmentid = pa.id
                         WHERE pua.programid = :programid AND pua.userid = :userid";
                $progassignments = $DB->get_records_sql($sql,
                    array('programid' => $certprogram->id,
                        'userid' => $user->id));
                $this->assertCount(1, $progassignments); // Just one.
                $progassignment = reset($progassignments);
                $this->assertEquals(ASSIGNTYPE_INDIVIDUAL, $progassignment->assignmenttype);
                $this->assertEquals($user->id, $progassignment->assignmenttypeid);
                $this->assertEquals(0, $progassignment->includechildren);
                if ($hascompletiontime) {
                    $this->assertEquals($this->completiontime, $progassignment->completiontime); // Has completion time.
                } else {
                    $this->assertEquals(-1, $progassignment->completiontime); // No completion time.
                }
                $this->assertEquals(0, $progassignment->completionevent); // No completion event.
                $this->assertEquals(0, $progassignment->completioninstance); // No completion instance.
                // Time assigned.
                $this->assertGreaterThan($this->setuptimeminimum, $progassignment->timeassigned);
                $this->assertLessThan($this->setuptimemaximum, $progassignment->timeassigned);
                $this->assertEquals(PROGRAM_EXCEPTION_NONE, $progassignment->exceptionstatus); // No exceptions.

                // Program completion records should exist for all users.
                $progcompletions = $DB->get_records('prog_completion',
                    array('programid' => $certprogram->id,
                        'userid' => $user->id,
                        'coursesetid' => 0));
                $this->assertCount(1, $progcompletions); // Just one.
                $progcompletion = reset($progcompletions);
                if ($didthirdcompletion) {
                    $this->assertEquals(STATUS_PROGRAM_COMPLETE, $progcompletion->status); // Set when completed.
                    if ($didfirstcompletion) {
                        $this->assertEquals(0, $progcompletion->timestarted); // Reset to 0 during window opening.
                    } else {
                        // When it was started (these users have never had a window open).
                        $this->assertGreaterThan($this->setuptimeminimum, $progcompletion->timestarted);
                        $this->assertLessThan($this->setuptimemaximum, $progcompletion->timestarted);
                    }
                    $this->assertEquals($timeexpires, $progcompletion->timedue); // Set during window opening.
                } else {
                    $this->assertEquals(STATUS_PROGRAM_INCOMPLETE, $progcompletion->status); // Set during setup.
                    // When it was started.
                    $this->assertGreaterThan($this->setuptimeminimum, $progcompletion->timestarted);
                    $this->assertLessThan($this->setuptimemaximum, $progcompletion->timestarted);
                    if ($hascompletiontime) {
                        $this->assertEquals($this->completiontime, $progcompletion->timedue); // Has timedue.
                    } else {
                        $this->assertEquals(-1, $progcompletion->timedue); // No timedue.
                    }
                }

                // Certification completion records should exist for all users.
                $certifcompletions = $DB->get_records('certif_completion',
                    array('certifid' => $certprogram->certifid,
                        'userid' => $user->id));
                $this->assertCount(1, $certifcompletions); // Just one.
                $certifcompletion = reset($certifcompletions);
                if ($didthirdcompletion) {
                    $this->assertEquals(CERTIFRENEWALSTATUS_NOTDUE, $certifcompletion->renewalstatus); // Not due for renewal.
                    $this->assertEquals(CERTIFPATH_RECERT, $certifcompletion->certifpath); // Recertification path.
                    $this->assertEquals(CERTIFSTATUS_COMPLETED, $certifcompletion->status); // Status completed.
                    $this->assertEquals($timeexpires, $certifcompletion->timeexpires);
                    $this->assertEquals($timewindowopens, $certifcompletion->timewindowopens);
                    $this->assertEquals($this->thirdcompletiontime, $certifcompletion->timecompleted); // Completed.
                    // When record was modified.
                    $this->assertGreaterThan($this->thirdcompletiontimeminimum, $certifcompletion->timemodified);
                    $this->assertLessThan($this->thirdcompletiontimemaximum, $certifcompletion->timemodified);
                } else {
                    // Just those who are in the certifications that were left alone.
                    $this->assertEquals(CERTIFRENEWALSTATUS_NOTDUE, $certifcompletion->renewalstatus); // Not due for renewal.
                    $this->assertEquals(CERTIFPATH_CERT, $certifcompletion->certifpath); // Back to primary certification path.
                    $this->assertEquals(CERTIFSTATUS_ASSIGNED, $certifcompletion->status); // Status assigned, as if newly assigned.
                    $this->assertEquals(0, $certifcompletion->timeexpires); // No expiry.
                    $this->assertEquals(0, $certifcompletion->timewindowopens); // No window.
                    $this->assertEquals(0, $certifcompletion->timecompleted); // Not completed.
                    // When record was created.
                    $this->assertGreaterThan($this->setuptimeminimum, $certifcompletion->timemodified);
                    $this->assertLessThan($this->setuptimemaximum, $certifcompletion->timemodified);
                }
            }
        }
    }
}
