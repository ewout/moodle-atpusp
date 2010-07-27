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

if (block_exabis_eportfolio_feature_enabled('views')) {
	header('Location: '. $CFG->wwwroot . '/blocks/exabis_eportfolio/shared_views.php?courseid='.$courseid);
	exit;
}

$context = get_context_instance(CONTEXT_SYSTEM);

require_login($courseid);
require_capability('block/exabis_eportfolio:use', $context);

if (! $course = get_record("course", "id", $courseid) ) {
	error("That's an invalid course id");
}

block_exabis_eportfolio_print_header("sharedbookmarks");

echo "<div class='block_eportfolio_center'>\n";

echo "<br />";

print_simple_box( text_to_html(get_string("explainingshared", "block_exabis_eportfolio")) , "center");

echo "<br />";

$all_shared_users = get_records_sql(
"SELECT u.id, u.picture, u.firstname, u.lastname, COUNT(i.id) AS detail_count FROM {$CFG->prefix}user AS u".
" JOIN {$CFG->prefix}block_exabeporitem i ON u.id=i.userid".
" LEFT JOIN {$CFG->prefix}block_exabeporitemshar ishar ON i.id=ishar.itemid AND ishar.userid={$USER->id}".
" WHERE ((i.shareall=1 AND ishar.userid IS NULL) OR (i.shareall=0 AND ishar.userid IS NOT NULL))".
" GROUP BY u.id");

$detailLink = 'shared_portfolio.php';

echo "<div style='width: 400px; text-align: left;'>";

if (is_array($all_shared_users)){
	echo "<table>";
	foreach($all_shared_users as $user) {
		echo "<tr>";
		echo "<td><a href=\"".s("{$CFG->wwwroot}/blocks/exabis_eportfolio/".$detailLink."?courseid=$courseid&access=id/$user->id")."\">";
		
		print_user_picture($user->id, $courseid, $user->picture, 0, false, false);
		echo "</a>&nbsp;</td>";
		echo "<td>&nbsp;<a href=\"".s("{$CFG->wwwroot}/blocks/exabis_eportfolio/".$detailLink."?courseid=$courseid&access=id/$user->id")."\">".fullname($user, $user->id)."</a></td>";
		echo '<td style="padding-left: 30px;">'.get_string('bookmarks', 'block_exabis_eportfolio').': '.$user->detail_count."</td>";
		
		echo "</tr>";
	}
	echo "</table>";
}


echo "</div>";
echo "</div>";
print_footer($course);
