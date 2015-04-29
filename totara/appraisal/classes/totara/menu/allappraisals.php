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
 * Totara navigation edit page.
 *
 * @package    totara
 * @subpackage navigation
 * @author     Oleg Demeshev <oleg.demeshev@totaralms.com>
 */

namespace totara_appraisal\totara\menu;

use \totara_core\totara\menu\menu as menu;

class allappraisals extends \totara_core\totara\menu\item {

    protected function get_default_title() {
        return get_string('allappraisals', 'totara_appraisal');
    }

    protected function get_default_url() {
        return '/totara/appraisal/index.php';
    }

    public function get_default_sortorder() {
        return 32000;
    }

    public function get_default_visibility() {
        return menu::SHOW_WHEN_REQUIRED;
    }

    protected function check_visibility() {
        global $CFG, $USER;

        static $cache = null;
        if (isset($cache)) {
            return $cache;
        }

        require_once($CFG->dirroot . '/totara/appraisal/lib.php');

        $isappraisalenabled = totara_feature_visible('appraisals');
        $viewappraisals = $isappraisalenabled &&
                (\appraisal::can_view_own_appraisals($USER->id) || \appraisal::can_view_staff_appraisals($USER->id));

        if ($viewappraisals) {
            $cache = menu::SHOW_ALWAYS;
        } else {
            $cache = menu::HIDE_ALWAYS;
        }
        return $cache;
    }

    protected function get_default_parent() {
        return '\totara_appraisal\totara\menu\appraisal';
    }
}
