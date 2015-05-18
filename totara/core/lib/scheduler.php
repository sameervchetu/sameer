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
 * @package totara
 * @subpackage core
 */

/**
 * This class incapsulates operation with scheduling
 *
 * It operates on DB row objects by changing it's fields. After applying changes on object, this
 * object should be saved in DB by $DB->insert_record or $DB->update_record
 *
 * To avoid overwriting other fields use scheduler::to_object().
 * This method will return object with only scheduler specific fields and 'id' field
 * Scheduler changes original object fields aswell, so no need to use scheduler::to_object() if you
 * save original object after applying scheduler changes.
 *
 * To support scheduling db table represented by operated db row object must have next fields:
 * frequency (int), schedule(int), nextevent (bigint)
 * If field(s) have dfferent names it can be configured via set_field method
 * Also, it has tight integration with Scheduler form element, and as result it's easily to integrate
 * them.
 */
global $CFG;
require_once($CFG->dirroot . '/calendar/lib.php');

class scheduler {
    /**
     *  Schedule constants
     *
     */
    const DAILY = 1;
    const WEEKLY = 2;
    const MONTHLY = 3;
    const HOURLY = 4;
    const MINUTELY = 5;

    /**
     * DB row decorated object
     *
     * @var stdClass
     */
    protected $subject = null;

    /**
     * status changes
     *
     * @var bool
     */
    protected $changed = false;

    /**
     * Mapping of field names used by scheduler
     *
     * @var array
     */
    protected $map = array('frequency' => 'frequency',
                           'schedule' => 'schedule',
                           'nextevent' => 'nextevent',
                           'timezone' => 'timezone');

    protected $time = 0;
    /**
     * Constructor
     *
     * @param object DB row object
     * @param array $alias_map Optional field renaming
     */
    public function __construct(stdClass $row = null, array $alias_map = array()) {
        if (is_null($row)) {
            $row = new stdClass();
        }
        $this->subject = $row;
        // Remap and add fields.
        foreach ($this->map as $k => $v) {
            $v = (isset($alias_map[$k])) ? $alias_map[$k] : $v;
            $this->set_field($k, $v);
            $this->subject->{$v} = isset($this->subject->{$v}) ? $this->subject->{$v} : null;
        }
        $this->set_time();
    }

    /**
     * Set operational time
     *
     * @param int $time
     */
    public function set_time($time = null) {
        if (is_null($time)) {
            $this->time = time();
        } else {
            $this->time = $time;
        }
    }

    /**
     * Change field name used by scheduler to filed represented in db row object
     *
     * @param string $name Name used in scheduler
     * @param string $alias Field used in DB
     */
    public function set_field($name, $alias) {
        if (isset($this->map[$name])) {
            $this->map[$name] = $alias;
        }
    }

    public function do_asap() {
        $this->changed = true;
        $this->subject->{$this->map['nextevent']} = $this->time - 1;
    }

    /**
     * Calculate next time of execution
     *
     * @param int $timestamp Current date
     * @param bool $is_cron True if the next report is calculated via cron, false otherwise
     * @return scheduler $this
     */
    public function next($timestamp = null, $is_cron = true) {
        if (!isset($this->subject->{$this->map['frequency']})) {
            return $this;
        }

        $this->changed = true;
        $frequency = $this->subject->{$this->map['frequency']};
        $schedule = $this->subject->{$this->map['schedule']};
        $usertz = totara_get_clean_timezone($this->subject->{$this->map['timezone']});

        if (is_null($timestamp)) {
            $datetime = new DateTime('now', new DateTimeZone($usertz));
            $timestamp = strtotime($datetime->format('Y-m-d H:i:s'));
        }
        $this->set_time($timestamp);
        $timeminute = date('i', $this->time);
        $timehour = date('H', $this->time);
        $timeday = date('j', $this->time);
        $timemonth = date('n', $this->time);
        $timeyear = date('Y', $this->time);

        switch ($frequency) {
            case self::MINUTELY:
                $nexttimeminute = (floor($timeminute / $schedule) + 1) * $schedule;
                $nextevent = mktime($timehour, $nexttimeminute, 0, $timemonth, $timeday, $timeyear);
                break;
            case self::HOURLY:
                $nexttimehour = (floor($timehour / $schedule) + 1) * $schedule;
                $nextevent = mktime($nexttimehour, 0, 0, $timemonth, $timeday, $timeyear);
                break;
            case self::DAILY:
                // We need to account for DST boundary changes.
                // A particular hour the next day across the boundary may be 23-25 hours from the last run.
                $userzoneobject = new DateTimeZone($usertz);
                // Pad the date components with a leading zero if needed
                $schedule =  sprintf("%02s", $schedule);
                $timeday =  sprintf("%02s", $timeday);
                $timemonth =  sprintf("%02s", $timemonth);
                // Calculate time strings to build DateTime objects using the actual scheduled time.
                $datestring = "{$timeyear}-{$timemonth}-{$timeday} ";
                $nowdatestring = $datestring . date('H', $this->time) . ':00:00';
                $targetdatestring = $datestring . $schedule . ':00:00';
                // Create the DateTime objects to compare properly the current time and the scheduled time.
                $ts1 = DateTime::createFromFormat('Y-m-d H:i:s', $nowdatestring, $userzoneobject);
                $ts2 = DateTime::createFromFormat('Y-m-d H:i:s', $targetdatestring, $userzoneobject);
                // If the report is already overdue for today, bump the day for the next run.
                if ($ts1->getTimestamp() >= $ts2->getTimestamp()) {
                    $timeday = (int)$timeday + 1;
                    $timeday =  sprintf("%02s", $timeday);
                }
                // Calculate the next run using the correct day/timezone/DST.
                $newdatestring = "{$timeyear}-{$timemonth}-{$timeday} {$schedule}:00:00";
                $newts = DateTime::createFromFormat('Y-m-d H:i:s', $newdatestring, $userzoneobject);
                $nextevent = $newts->getTimestamp();
                break;
            case self::WEEKLY:
                // Calculate the day of the week index.
                $calendartype = \core_calendar\type_factory::get_calendar_instance();
                $timeweekday = $calendartype->get_weekday($timeyear, $timemonth, $timeday);
                if (($schedule == $timeweekday) && (!$is_cron)) {
                    // The scheduled day of the week is the same as the given day of the week, so schedule for this day.
                    $nextevent = mktime(0, 0, 0, $timemonth, $timeday, $timeyear);
                } else {
                    // The scheduled day of the week is different (or one week future on cron), so schedule for the future.
                    if ($schedule <= $timeweekday) {
                        // An earlier or same weekday. Add one week to the schedule index and then find the difference.
                        $daysinweek = count($calendartype->get_weekdays());
                        $days = $schedule + $daysinweek - $timeweekday;
                    } else {
                        // Just find the difference.
                        $days = $schedule - $timeweekday;
                    }
                    $nextevent = mktime(0, 0, 0, $timemonth, $timeday, $timeyear) + DAYSECS * $days;
                }
                break;
            case self::MONTHLY:
                if (($timeday == $schedule) && (!$is_cron)) {
                    $nextevent = mktime(0, 0, 0, $timemonth, $timeday, $timeyear);
                } else {
                    $offset = ($timeday >= $schedule) ? 1 : 0;
                    $newmonth = $timemonth + $offset;
                    if ($newmonth < 13) {
                        $newyear = $timeyear;
                    } else {
                        $newyear = $timeyear + 1;
                        $newmonth = 1;
                    }

                    $daysinmonth = date('t', mktime(0, 0, 0, $newmonth, 3, $newyear));
                    $newday = ($schedule > $daysinmonth) ? $daysinmonth : $schedule;
                    $nextevent = mktime(0, 0, 0, $newmonth, $newday, $newyear);
                }
                break;
        }

        $this->subject->{$this->map['nextevent']} = $nextevent;
        return $this;
    }

    /**
     * Check if it's time to run event
     *
     * @return bool
     */
    public function is_time() {
        return $this->subject->{$this->map['nextevent']} < $this->time;
    }

    /**
     * Is there any changes to object made by scheduler
     *
     * @return bool
     */
    public function is_changed() {
        return $this->changed;
    }

    /**
     * Get available scheduler options
     *
     * @return array
     */
    public static function get_options() {
        return array('daily' => self::DAILY,
                     'weekly' => self::WEEKLY,
                     'monthly' => self::MONTHLY,
                     'hourly' => self::HOURLY,
                     'minutely' => self::MINUTELY,
        );
    }

    /**
     * Given scheduled report frequency and schedule data, output a human readable string.
     *
     * @param integer Code representing the frequency of reports (one of Schedule::get_options)
     * @param integer The scheduled date/time (either hour of day, day or week or day of month)
     * @param object User object belonging to the recipient (optional). Defaults to current user
     * @return string Human readable string describing the schedule
     */
    public function get_formatted($user = null) {
        // Use current user if not set.
        if ($user === null) {
            global $USER;
            $user = $USER;
        }
        $calendardays = calendar_get_days();
        $dateformat = ($user->lang == 'en') ? 'jS' : 'j';
        $out = '';
        $schedule = $this->subject->{$this->map['schedule']};

        $timemonth = date('n', $this->time);
        $timeday = date('j', $this->time);
        $timeyear = date('Y', $this->time);

        switch($this->subject->{$this->map['frequency']}) {
            case self::MINUTELY:
                $out .= get_string('scheduledminutely', 'totara_core', $schedule);
                break;
            case self::HOURLY:
                $out .= get_string('scheduledhourly', 'totara_core', $schedule);
                break;
            case self::DAILY:
                $out .= get_string('scheduleddaily', 'totara_core',
                    strftime(get_string('strftimetime', 'langconfig'), mktime($schedule, 0, 0, $timemonth, $timeday, $timeyear)));
                break;
            case self::WEEKLY:
                $out .= get_string('scheduledweekly', 'totara_core',
                    $calendardays[$schedule]['fullname']);
                break;
            case self::MONTHLY:
                $out .= get_string('scheduledmonthly', 'totara_core',
                    date($dateformat , mktime(0, 0, 0, 0, $schedule, $timeyear)));
                break;
        }

        return $out;
    }

    /**
     * Return timestamp when scheduled event is going to run
     * @return int timestamp
     */
    public function get_scheduled_time() {
        return $this->subject->{$this->map['nextevent']};
    }

    /**
     * Populate data based on initial array
     *
     * Compatible with scheduler form element data @see MoodleQuickForm_scheduler::exportValue()
     *
     * @param array $data - array with schedule parameters. If not set, default schedule will be applied
     */
    public function from_array(array $data = array()) {
        global $CFG;

        $this->changed = true;

        $data['frequency'] = isset($data['frequency']) ? $data['frequency'] : self::DAILY;
        $data['schedule'] = isset($data['schedule']) ? $data['schedule'] : 0;
        $data['initschedule'] = isset($data['initschedule']) ? $data['initschedule'] : false;
        $data['timezone'] = isset($data['timezone']) ? $data['timezone'] : $CFG->timezone;
        $this->subject->{$this->map['frequency']} = $data['frequency'];
        $this->subject->{$this->map['schedule']} = $data['schedule'];
        $this->subject->{$this->map['timezone']} = $data['timezone'];
        // If no need in reinitialize, don't change nextreport value.
        if ($data['initschedule']) {
            $this->subject->{$this->map['nextevent']} = $this->time - 1;
        } else {
            $this->next();
        }
    }

    /**
     * Export scheduler parameters as an array
     * @return array
     */
    public function to_array() {
        $result = array(
                        'frequency' => $this->subject->{$this->map['frequency']},
                        'schedule' => $this->subject->{$this->map['schedule']},
                        'timezone' => $this->subject->{$this->map['timezone']},
                        'nextevent' => $this->subject->{$this->map['nextevent']},
                        'initschedule' => ($this->subject->{$this->map['nextevent']} <= $this->time)
        );
        return $result;
    }

    /**
     * Export scheduler parameters as an object
     *
     * Useful for saving in DB
     * @param mixed array|string $extrafields primary key name and other fields to export
     * @return stdClass
     */
    public function to_object($extrafields = 'id') {
        if (!is_array($extrafields)) {
            $extrafields = array($extrafields);
        }

        $obj = new stdClass();
        $obj->{$this->map['nextevent']} = $this->subject->{$this->map['nextevent']};
        $obj->{$this->map['frequency']} = $this->subject->{$this->map['frequency']};
        $obj->{$this->map['schedule']} = $this->subject->{$this->map['schedule']};
        $obj->{$this->map['timezone']} = $this->subject->{$this->map['timezone']};
        foreach ($extrafields as $field) {
            if (isset($this->subject->$field)) {
                $obj->$field = $this->subject->$field;
            }
        }
        return $obj;
    }
}
