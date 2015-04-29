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
 * @author David Curry <david.curry@totaralms.com>
 * @package totara_hierarchy
 */

namespace totara_hierarchy\event;

defined('MOODLE_INTERNAL') || die();

/**
 * Abstract Event used as the base by each hierarchy,
 * triggered when a hierarchy item is viewed.
 *
 * @property-read array $other {
 *      Extra information about the event.
 * }
 *
 * @author David Curry <david.curry@totaralms.com>
 * @package totara_hierarchy
 */
abstract class hierarchy_viewed extends \core\event\base {

    /**
     * Flag for prevention of direct create() call.
     * @var bool
     */
    protected static $preventcreatecall = true;

    /**
     * Returns hierarchy prefix.
     * @return string
     */
    abstract public function get_prefix();

    /**
     * Create instance of event.
     *
     * @param   \stdClass $instance A hierarchy item record.
     * @return  hierarchy_viewed
     */
    public static function create_from_instance(\stdClass $instance) {
        $data = array(
            'objectid' => $instance->id,
            'context' => \context_system::instance(),
            'other' => array(),
        );

        self::$preventcreatecall = false;
        $event = self::create($data);
        $event->add_record_snapshot($event->objecttable, $instance);
        self::$preventcreatecall = true;

        return $event;
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        $prefix = $this->get_prefix();
        return "The {$prefix}: {$this->objectid} was viewed";
    }

    public function get_url() {
        $urlparams = array('prefix' => $this->get_prefix(), 'id' => $this->objectid);
        return new \moodle_url('/totara/hierarchy/item/view.php', $urlparams);
    }

    public function get_legacy_logdata() {
        $prefix = $this->get_prefix();

        $logdata = array();
        $logdata[] = SITEID;
        $logdata[] = $prefix;
        $logdata[] = 'view item';
        $logdata[] = $this->get_url()->out_as_local_url(false);
        $logdata[] = "{$prefix}: {$this->objectid}";

        return $logdata;
    }
}
