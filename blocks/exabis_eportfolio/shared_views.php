<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 exabis internet solutions <info@exabis.at>
*  All rights reserved
*
*  You can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  This module is based on the Collaborative Moodle Modules from
*  NCSA Education Division (http://www.ncsa.uiuc.edu)
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

require_once dirname(__FILE__).'/inc.php';
require_once dirname(__FILE__).'/lib/sharelib.php';

$courseid = required_param('courseid', PARAM_INT);
$sort = optional_param('sort', '', PARAM_RAW);
$access = optional_param('access', 0, PARAM_TEXT);

require_login($courseid);

$context = get_context_instance(CONTEXT_SYSTEM);
require_capability('block/exabis_eportfolio:use', $context);

if (! $course = get_record("course", "id", $courseid) ) {
	error("That's an invalid course id");
}

$parsedsort = block_exabis_eportfolio_parse_sort($sort, array('course', 'user', 'view', 'timemodified'));
if ($parsedsort[0] == 'timemodified') {
	$sql_sort = " ORDER BY v.timemodified DESC, v.name, u.lastname, u.firstname";
	$parsedsort[1] = 'desc';
} elseif ($parsedsort[0] == 'view') {
	$sql_sort = " ORDER BY v.name, u.lastname, u.firstname";
} else {
	$sql_sort = " ORDER BY u.lastname, u.firstname, v.name";
}



block_exabis_eportfolio_print_header("sharedbookmarks");

$strheader = get_string("sharedbookmarks", "block_exabis_eportfolio");

echo "<div class='block_eportfolio_center'>\n";

$views = get_records_sql(
"SELECT v.*, u.firstname, u.lastname, u.picture".
" FROM {$CFG->prefix}user AS u".
" JOIN {$CFG->prefix}block_exabeporview v ON u.id=v.userid".
" LEFT JOIN {$CFG->prefix}block_exabeporviewshar vshar ON v.id=vshar.viewid AND vshar.userid={$USER->id}".
" WHERE (v.shareall=1 OR vshar.userid IS NOT NULL)".
" $sql_sort");


function exabis_eportfolio_search_views($views, $column, $value) {
	$viewsFound = array();
	foreach ($views as $view) {
		if ($view->{$column} == $value) {
			$view->found = true;
			$viewsFound[] = $view;
		}
	}
	return $viewsFound;
}

function exabis_eportfolio_print_views($views, $parsedsort) {
	global $CFG, $courseid;
	
	$sort = $parsedsort[0];
	
	echo get_string('sortby').': ';
	echo "<a href=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/shared_views.php?courseid=$courseid&amp;sort=course\"".
		($sort=='course'?' style="font-weight: bold;"':'').">".get_string('course')."</a> | ";
	echo "<a href=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/shared_views.php?courseid=$courseid&amp;sort=user\"".
		($sort=='user'?' style="font-weight: bold;"':'').">".get_string('user')."</a> | ";
	echo "<a href=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/shared_views.php?courseid=$courseid&amp;sort=view\"".
		($sort=='view'?' style="font-weight: bold;"':'').">".get_string('view', 'block_exabis_eportfolio')."</a> | ";
	echo "<a href=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/shared_views.php?courseid=$courseid&amp;sort=timemodified\"".
		($sort=='timemodified'?' style="font-weight: bold;"':'').">".get_string('date', 'block_exabis_eportfolio')."</a> ";
	echo '</div>';

	$table = new stdClass();
	$table->width = "100%";
	$table->head = array();
	$table->size = array();
	$table->head = array('userpic'=>get_string('userpic'), 'user'=>get_string('user'), 'view'=>get_string('view', 'block_exabis_eportfolio'), 'timemodified'=>get_string("date", "block_exabis_eportfolio"));
	$table->data = array();

	if ($sort == 'course') {
		$table->head = array_merge(array('course'=>get_string('course')), $table->head);

		$courses = exabis_eportfolio_get_shareable_courses_with_users('shared_views');
		foreach ($courses as $course) {
			foreach ($course['users'] as $user) {
				$viewsFound = exabis_eportfolio_search_views($views, 'userid', $user['id']);
				if ($viewsFound) {
					foreach ($viewsFound as $view) {
						$table->data[] = array(
							$course['fullname'], 
							print_user_picture($view->userid, 0, $view->picture, 0, true, false),
							fullname($view),
							"<a href=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/shared_view.php?courseid=$courseid&amp;access=id/{$view->userid}-{$view->id}\">".
								format_string($view->name) . "</a>",
							userdate($view->timemodified)
						);
					}
				}
			}
		}
		
		// get views, which weren't printed yet
		foreach ($views as $view) {
			if (!empty($view->found)) continue;
			$table->data[] = array(get_string('nocoursetogether', 'block_exabis_eportfolio'),
				print_user_picture($view->userid, 0, $view->picture, 0, true, false),
				fullname($view),
				"<a href=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/shared_view.php?courseid=$courseid&amp;access=id/{$view->userid}-{$view->id}\">".
					format_string($view->name) . "</a>",
				userdate($view->timemodified)
			);
		}
	} else {
		foreach ($views as $view) {
			$table->data[] = array(
				print_user_picture($view->userid, 0, $view->picture, 0, true, false),
				fullname($view),
				"<a href=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/shared_view.php?courseid=$courseid&amp;access=id/{$view->userid}-{$view->id}\">".
					format_string($view->name) . "</a>",
				userdate($view->timemodified)
			);
		}
	}
	
	$sorticon = $parsedsort[1].'.gif';
	$table->head[$parsedsort[0]] .= " <img src=\"pix/$sorticon\" alt='".get_string("updownarrow", "block_exabis_eportfolio")."' />";

	print_table($table);
}




echo '<div style="padding-bottom: 20px;">';

if (!$views) {
	echo get_string("nothingshared", "block_exabis_eportfolio");
} else {
	exabis_eportfolio_print_views($views, $parsedsort);
}

echo "<br /><br />";

echo "</div>";

print_footer($course);
