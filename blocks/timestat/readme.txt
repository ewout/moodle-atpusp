/*
 **************************************************************************
 * NOTICE OF COPYRIGHT													  *
 *																		  *
 * Copyright (C)														  *
 *																		  *
 * This program is free software; you can redistribute it and/or modify   *
 * it under the terms of the GNU General Public License as published by   *
 * the Free Software Foundation; either version 2 of the License, or      *
 * (at your option) any later version.					  *
 *                                                                        *
 * This program is distributed in the hope that it will be useful,        *
 * but WITHOUT ANY WARRANTY; without even the implied warranty of         *
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          *
 * GNU General Public License for more details:                           *
 *                                                                        *
 *          http://www.gnu.org/copyleft/gpl.html                          *
 * 																		  *
 * 																		  *
 *                                                                        *
 **************************************************************************
 */

Timestat block for Moodle

This application was developed in cooperation of Higher State Vocational
School in Krosno (Poland) and Rzeszow University of Technology (Poland)

The application allows you to measure active time Moodle users. Measured 
activity time is incremented only when user perform operations using the
mouse or keyboard.


Installation guide:

1) Add one line of code in file weblib.php:

    require_once($CFG->dirroot.'/blocks/timestat/timestatlib.php');

so it should look like:

function print_footer($course=NULL, $usercourse=NULL, $return=false) {
    global $USER, $CFG, $THEME, $COURSE;
    require_once($CFG->dirroot.'/blocks/timestat/timestatlib.php');


2) Folder 'timestat' contains Moodle block which is installed in standard way.

Contact: l.sanokowski@pwsz.krosno.pl
