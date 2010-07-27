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

$courseid = optional_param('courseid', 0, PARAM_INT);

$context = get_context_instance(CONTEXT_SYSTEM);

require_login($courseid);
require_capability('block/exabis_eportfolio:use', $context);

if (! $course = get_record("course", "id", $courseid) ) {
	error("That's an invalid course id");
}

block_exabis_eportfolio_print_header("exportimport");
			 
if (isset($USER->realuser)) {
	error("You can't access portfolios in 'Login As'-Mode.");
}


echo "<br />";

echo "<div class='block_eportfolio_center'>";

print_simple_box( text_to_html(get_string("explainexport","block_exabis_eportfolio")) , "center");


if (has_capability('block/exabis_eportfolio:export', $context)) {
	echo "<p ><img src=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/pix/export.png\" height=\"16\" width=\"16\" alt='".get_string("export", "block_exabis_eportfolio")."' /> <a title=\"" . get_string("export","block_exabis_eportfolio") . "\" href=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/export_scorm.php?courseid=".$courseid."\">".get_string("export","block_exabis_eportfolio")."</a></p>";
	echo "<p ><img src=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/pix/export.png\" height=\"16\" width=\"16\" alt='".get_string("exportepx", "block_exabis_eportfolio")."' /> <a title=\"" . get_string("exportepx","block_exabis_eportfolio") . "\" href=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/export_epx.php?courseid=".$courseid."\">".get_string("exportepx","block_exabis_eportfolio")."</a></p>";
}

if (has_capability('block/exabis_eportfolio:import', $context)) {    
	echo "<p ><img src=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/pix/import.png\" height=\"16\" width=\"16\" alt='".get_string("import", "block_exabis_eportfolio")."' /> <a title=\"" . get_string("import","block_exabis_eportfolio") . "\" href=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/import_file.php?courseid=".$courseid."\">".get_string("import","block_exabis_eportfolio")."</a></p>";
}

if (has_capability('block/exabis_eportfolio:importfrommoodle', $context)) {    
	echo "<p ><img src=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/pix/import.png\" height=\"16\" width=\"16\" alt='".get_string("moodleimport", "block_exabis_eportfolio")."' /> <a title=\"" . get_string("moodleimport","block_exabis_eportfolio") . "\" href=\"{$CFG->wwwroot}/blocks/exabis_eportfolio/import_moodle.php?courseid=".$courseid."\">".get_string("moodleimport","block_exabis_eportfolio")."</a></p>";
}

echo "</div>";

print_footer($course);
