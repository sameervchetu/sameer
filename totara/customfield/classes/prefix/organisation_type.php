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
 * @package totara_customfield
 */

namespace totara_customfield\prefix;
defined('MOODLE_INTERNAL') || die();

class organisation_type extends hierarchy_type {

    public function get_capability_movefield() {
        return 'totara/hierarchy:updateorganisationcustomfield';
    }

    public function get_capability_editfield() {
        return 'totara/hierarchy:updateorganisationcustomfield';
    }

    public function get_capability_createfield() {
        return 'totara/hierarchy:createorganisationcustomfield';
    }

    public function get_capability_deletefield() {
        return 'totara/hierarchy:deleteorganisationcustomfield';
    }
}
