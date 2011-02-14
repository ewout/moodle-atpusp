<?php
// This file is part of SimpleFileUpload
//
// SimpleFileUpload provides a simpler mechanism for adding file resources to
// a Moodle Course and link them on the course page than the standard Moodle mechanism.
//
// SimpleFileUpload is (C) Copyright 2010 by John Ennew and Steve Coppin
// of the University of Kent, Canterbury, UK
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
 * This configuration file allows you to specify default resource
 * variable values for uploaded files
 *
 * @package simplefileupload
 * @copyright 2010 John Ennew, Steve Coppin
 * @copyrigth 2010 University of Kent, Canterbury, UK http://www.kent.ac.uk
 * @author John Ennew
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$simplefileuploadoptions = array();

/*
 * Put the default window behaviour in here for different files types.
 * Each array element index is an extension and each value is the options.
 *
 * options can be ...
 *  'forcedownload' - file is delivered to the user, no popup window
 *  'frame'         - same window with navigation in a frame
 *  'objectframe'   - same window with navigation not in a frame
 *  ''              - same window without navigation or a popup window
 *
 * if popup is specified, options must be blank ('').
 * Popup is a string that looks like this:
 * 'resizable=1,scrollbars=1,directories=1,location=1,menubar=1,toolbar=1,status=1,width=620,height=450'
 *
 * all items except width and height are 1 for on and 0 for off
 * width and height are numbers indicating the width and height of the window
 *
 * e.g. default options if an extension is not specified is to force the file
 *      to be downloaded ...
 *  $simplefileuploadoptions['default']['popup'] = '';
 *  $simplefileuploadoptions['default']['options'] = 'forcedownload';
 *
 * e.g. mp3 files open in the same browser window...
 *  $simplefileuploadoptions['mp3']['popup'] = '';
 *  $simplefileuploadoptions['mp3']['options'] = '';
 *
 */
$simplefileuploadoptions['default']['popup'] = '';
$simplefileuploadoptions['default']['options'] = 'forcedownload';

$simplefileuploadoptions['mp3']['options'] = '';
$simplefileuploadoptions['png']['options'] = '';
$simplefileuploadoptions['jpg']['options'] = '';
$simplefileuploadoptions['gif']['options'] = '';
