<?php

// This file is NOT a part of Moodle - http://moodle.org/
//
// This client for Moodle 2 is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
/**
 * REST client for Moodle 2
 * Return JSON or XML format
 *
 * @authorr Jerome Mouneyrac
 */

require_once ('..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config.php');
global $CFG, $DB;

$token = required_param('token', PARAM_USERNAME);

$course = new stdClass();
$course->idnumber = required_param('idnumber', PARAM_ALPHANUM);
$course->customfield_certificationurl = required_param('certificateurl', PARAM_URL);

$fullname = required_param('fullname', PARAM_TEXT);
    $shortname = required_param('shortname', PARAM_TEXT);

    $course->fullname = $fullname;
    $course->shortname = $shortname;
    $course->customfield_sopversion = required_param('sopversion', PARAM_TEXT);
    $course->customfield_issop = 1;
    $categoryid = required_param('categoryid', PARAM_TEXT);
    if ($catidno = $DB->get_record('course_categories', array('idnumber' => $categoryid), 'id')) {
        $course->categoryid = intval($catidno->id);
    } else {
        $course->categoryid = 1;
    }
if (!$exist = $DB->get_record('course', array('idnumber' => $course->idnumber), 'id, fullname')) {
    $functionname = 'local_sop_create_sop';
    $course->lang = 'en';
} else {
    $functionname = 'local_sop_update_sop';
}
$restformat = required_param('format', PARAM_TEXT);

$domainname = $CFG->wwwroot;

$courses = array($course);
$params = array('courses' => $courses);
/// REST CALL
header('Content-Type: application/xml');
$serverurl = $domainname . '/webservice/rest/server.php' . '?wstoken=' . $token . '&wsfunction=' . $functionname;
require_once('./curl.php');
$curl = new curl;
//if rest format == 'xml', then we do not add the param for backward compatibility with Moodle < 2.2
$restformat = ($restformat == 'json') ? '&moodlewsrestformat=' . $restformat : '';
$resp = $curl->post($serverurl . $restformat, $params);
print_r($resp);
