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
$action = optional_param("action", "", PARAM_ALPHA);
$itemid = optional_param('itemid', 0, PARAM_INT);
$sharecourseid = optional_param('sharecourseid', '', PARAM_INT); // array of integer

$backtype = optional_param('backtype', 'all', PARAM_ALPHA);
$backtype = block_exabis_eportfolio_check_item_type($backtype, true);

$context = get_context_instance(CONTEXT_SYSTEM);

require_login($courseid);
require_capability('block/exabis_eportfolio:use', $context);
require_capability('block/exabis_eportfolio:shareintern', $context);

if (! $course = get_record("course", "id", $courseid) ) {
	print_error("invalidcourseid", "block_exabis_eportfolio");
}

if (!block_exabis_eportfolio_feature_enabled('copy_to_course')) {
	print_error("Copy to course not enabled");
}

// get the bookmark if it is mine.
$item = get_record_sql("select *".
							 " from {$CFG->prefix}block_exabeporitem i".
							 " where i.userid = '{$USER->id}' and i.id='".$itemid."' AND i.type='file'");

if(!$item) {
	print_error("bookmarknotfound","block_exabis_eportfolio", 'view.php?courseid=' . $courseid);	 
}

$courses = exabis_eportfolio_get_shareable_courses();



if ($sharecourseid) {
	if (!isset($courses[$sharecourseid])) {
		print_error('not allowed to share to this course');
	}
	
	$dir = make_upload_directory($sharecourseid.'/students');

	$filenameNew = clean_filename($USER->lastname.'_'.$USER->firstname).'_'.$item->attachment;
	copy($CFG->dataroot . "/" . block_exabis_eportfolio_file_area_name($item) . "/" . $item->attachment,
		$dir.'/'.$filenameNew);
	
	block_exabis_eportfolio_print_header("bookmarks".block_exabis_eportfolio_get_plural_item_type($backtype), "share");
	
	echo '<div style="text-align: center; font-weight: bold; padding: 20px;">';
	echo get_string('filecopiedtocourse', 'block_exabis_eportfolio', (object)array('coursename'=>$courses[$sharecourseid]['fullname'], 'filename'=>$filenameNew));
	echo '</div>';
	
	redirect($CFG->wwwroot.'/blocks/exabis_eportfolio/view_items.php?courseid='.$courseid."&type=".$backtype);

	print_footer();
	exit;
}






block_exabis_eportfolio_print_header("bookmarks".block_exabis_eportfolio_get_plural_item_type($backtype), "share");

echo "<div class='block_eportfolio_center'>";
echo '<div style="padding: 20px; font-weight: bold;">'.get_string("copy").' '.$item->name.'</div>';

if (!$courses) {
	echo 'error, no courses';
} else {
	foreach ($courses as $course) {
		echo "<a href=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/copy_item_to_course.php?courseid={$courseid}&amp;itemid={$itemid}&amp;backtype={$backtype}&amp;sharecourseid={$course['id']}\">".
		$course['fullname'].'</a><br />';
	}
}

echo "<br /><a href=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/view_items.php?courseid={$courseid}&amp;backtype={$backtype}\">".get_string("back","block_exabis_eportfolio")."</a><br /><br />";
echo '</div';

print_footer();
