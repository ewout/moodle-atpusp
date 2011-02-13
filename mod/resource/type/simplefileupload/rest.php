<?php
// This file is part of SimpleFileUpload
//
// SimpleFileUpload provides a simpler mechanism for adding file resources to
// a Moodle Course and link them on the course page than the standard Moodle mechanism.
//
// SimpleFileUpload is (C) Copyright 2010 by John Ennew and Steve Coppin
// of the University of Kent, Canterbury, UK http://www.kent.ac.uk/
// Contact info: John Ennew; J.Ennew@kent.ac.uk
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * This is a restful data transport interface for the client side 
 * javascript to request a list of what files are part of a specified
 * course_section
 *
 * @package simplefileupload
 * @copyright 2010 John Ennew, Steve Coppin
 * @copyrigth 2010 University of Kent, Canterbury, UK http://www.kent.ac.uk
 * @author John Ennew
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once('../../../../config.php');
require_once("SfuController.class.php");

try {

    // check the moodleness of this page request
    $site = get_site();
    if(is_null($site) || !$site) {
        throw new Exception("Failed to load Moodle");
    }

    global $USER;

    $course_id = optional_param('courseid', null, PARAM_INT);
    $section_number = optional_param('sectionnumber',null,PARAM_INT);
    $action = optional_param('action', null, PARAM_TEXT);

    // filter inputs
    if (!is_int($course_id) || $course_id<1) throw new Exception("Invalid course id $course_id");
    if (!is_int($section_number) || $section_number<0) throw new Exception("Invalid section id $section_number");

    // check access rights - course update required to get a list of files in a section in this way
    if ($USER->id < 1) throw new Exception("User id invalid - are you logged into Moodle?");
    $context = get_context_instance(CONTEXT_COURSE, $course_id);
    if (!has_capability('moodle/course:update', $context)) {
        throw new Exception("User {$USER->id} does not have course update on this module");
    }

    $sfu = new SfuController($course_id, $section_number);

    // white list filter the RPC call
    if (!method_exists($sfu, $action)) {
        throw new Exception("{$action} is not a supported action");
    }

    $result = $sfu->$action();

    header("HTTP/1.0 200 OK");
    echo json_encode(array('Response' => $result));
    
} catch (Exception $e) {
    header("HTTP/1.0 400 Bad Request");
    if (debugging('', DEBUG_MINIMAL)) {
        echo json_encode(array('Error' => $e->getMessage()));
    } else {
        echo json_encode(array('Error' => 'Server fault'));
    }
} 
