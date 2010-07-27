<?php
/*
 This file is part of the Presenter Activity Module for Moodle

 The Presenter Activity Module for Moodle software package is Copyright © 2008 onwards NetSapiensis AB and is provided under the terms
 of the GNU GENERAL PUBLIC LICENSE Version 3 (GPL). This program is free software: you can redistribute it and/or modify it under the terms
 of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any
 later version.
 This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

 You should have received a copy of the GNU General Public License along with this program. If not, see <http://www.gnu.org/licenses/>.

 The Presenter Activity Module for Moodle includes Flowplayer free version. For more information on Flowplayer see http://www.flowplayer.org

 The Flowplayer Free version is released under the GNU GENERAL PUBLIC LICENSE Version 3 (GPL).
 The GPL requires that you not remove the Flowplayer copyright notices from the user interface. See section 5.d below.
 Commercial licenses are available. The commercial player version does not require any Flowplayer notices or texts and also provides some
 additional features.

 ADDITIONAL TERM per GPL Section 7 for Flowplayer
 If you convey this program (or any modifications of it) and assume contractual liability for the program to recipients of it, you agree to
 indemnify Flowplayer, Ltd. for any liability that those contractual assumptions impose on Flowplayer, Ltd.

 Except as expressly provided herein, no trademark rights are granted in any trademarks of Flowplayer, Ltd. Licensees are granted a limited,
 non-exclusive right to use the mark Flowplayer and the Flowplayer logos in connection with unmodified copies of the Program and the copyright
 notices required by section 5.d of the GPL license. For the purposes of this limited trademark license grant, customizing the Flowplayer by
 skinning, scripting, or including PlugIns provided by Flowplayer, Ltd. is not considered modifying the Program.

 Licensees that do modify the Program, taking advantage of the open-source license, may not use the Flowplayer mark or Flowplayer logos and must
 change the fullscreen notice (and the non-fullscreen notice, if that option is enabled), the copyright notice in the dialog box, and the notice
 on the Canvas as follows:

 the full screen (and non-fullscreen equivalent, if activated) noticeshould read: "Based on Flowplayer source code"; in the context menu
 (right-click menu), the link to "About Flowplayer free version #.#.#" can remain. The copyright notice can remain, but must be supplemented
 with an additional notice, stating that the licensee modified the Flowplayer. A suitable notice might read
 "Flowplayer Source code modified by ModOrg 2009"; for the canvas, the notice should read "Based on Flowplayer source code".
 In addition, licensees that modify the Program must give the modified Program a new name that is not confusingly similar to Flowplayer
 and may not distribute it under the name Flowplayer.

 This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the
 Free Software Foundation, either version 3 of the License, or (at your option) any later version. This program is distributed in the hope that
 it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 See the GNU General Public License for more details. You should have received a copy of the GNU General Public License along with this program.
 If not, see <http://www.gnu.org/licenses/>.
 */

require_once("../../config.php");
require_once($CFG->dirroot.'/course/lib.php');
require_once("../../lib/datalib.php");
require_once("../../lib/filelib.php");
require_once($CFG->dirroot.'/mod/presenter/lib.php');
require_once($CFG->dirroot.'/mod/presenter/chapterlib.php');

$id = required_param('id', PARAM_INT);   // course
if (!$course = get_record("course", "id", $id)) {
	error("Course ID is incorrect");
}

require_login($course->id);

add_to_log($course->id, "presenter", "view all", "index.php?id=$course->id", "");


/// Get all required strings

$strpresenters = get_string("modulenameplural", "presenter");
$strpresenter  = get_string("modulename", "presenter");


/// Print the header
$navlinks = array();
$navlinks[] = array('name' => $strpresenters, 'link' => '', 'type' => 'activity');

$navigation = build_navigation($navlinks);

print_header("$course->shortname: $strpresenters", $course->fullname, $navigation, "", "", true, "", navmenu($course));

/// Get all the appropriate data

if (! $presenters = get_all_instances_in_course("presenter", $course)) {
	notice(get_string('thereareno', 'moodle', $strpresenters), "../../course/view.php?id=$course->id");
	die;
}

/// Print the list of instances (your module will probably extend this)

$timenow = time();
$strname  = get_string("name");
$strweek  = get_string("week");
$strtopic  = get_string("topic");

$strchapters = get_string('nr_chapters', 'presenter');

$strexportlong = get_string('export_long', 'presenter');
$strexport = get_string('export_short', 'presenter');
$strdownload = get_string('download', 'presenter');

$table = new stdClass;

if ($course->format == "weeks") {
	$table->head  = array ($strweek, $strname, $strchapters, $strexportlong);
	$table->align = array ("center", "left", "center", "center");
} else if ($course->format == "topics") {
	$table->head  = array ($strtopic, $strname, $strchapters, $strexportlong);
	$table->align = array ("center", "left", "center", "center");
} else {
	$table->head  = array ($strname, $strchapters, $strexportlong);
	$table->align = array ("left", "center", "center");
}

foreach ($presenters as $presenter) {
	if (!$presenter->visible) {
		//Show dimmed if the mod is hidden
		$link = "<a class=\"dimmed\" href=\"view.php?id=$presenter->coursemodule\">".format_string($presenter->name,true)."</a>";
	} else {
		//Show normal if the mod is visible
		$link = "<a href=\"view.php?id=$presenter->coursemodule\">".format_string($presenter->name,true)."</a>";
	}
	$cm = get_coursemodule_from_instance('presenter', $presenter->id);
	$context = get_context_instance(CONTEXT_MODULE, $cm->id);

	$chapters = $presenter->nr_chapters;
	$export = '';
	if ($presenter->export_file && file_exists($CFG->dataroot . '/' . $id . '/Presenter/' . $presenter->export_file)) {
		$export .= '<a style="float: right" href="' . get_file_url($id . '/Presenter/' . $presenter->export_file) . '">' . $strdownload . '</a>';
	}
	$defaultName = str_replace(" ", "_", $presenter->name);
	$defaultName .= '_' . date('Ymd') . '_' . $presenter->id . '.zip';
	$export .= '<form style="float: left;" action="export.php" method="POST" id="export">';
	$export .= '<input style="width: 350px; margin-right: 30px;" type="text" name="archiveName" value="' . $defaultName . '" />';
	$export .= '<input type="hidden" name="course" value="' . $id . '" />';
	$export .= '<input type="hidden" name="id" value="' . $presenter->id . '" />';
	$export .= '<button type="submit">' . $strexport . '</button>';
	$export .= '</form>';

	if ($course->format == "weeks" or $course->format == "topics") {
		$table->data[] = array ($presenter->section, $link, $chapters, $export);
	} else {
		$table->data[] = array ($link, $chapters, $export);
	}
}

echo "<br />";

print_table($table);
if (!((class_exists("ZipArchive") || $CFG->unzip) && class_exists("XMLReader"))) {
	$aux = get_string('xmlreader_required', 'presenter') ;
}


$div = '<div style="width: 80%; padding-bottom: 15px; font-size: 11px; margin: 20px auto;text-align: center; border: 1px solid #EEEEEE;">
			<h3 align="center" style="font-size: 18px; margin: 5px 0;">' . get_string('import_str', 'presenter') . '</h1>';
if (isset($aux)) {
	$div .= $aux;
} else {
	$div .= '<form action="import.php" method="POST" enctype="multipart/form-data">';
	$div .= 'Location of .zip file (must be created with Presenter "export" functionality)<br />';
	$div .= '<input style="margin: 5px 0;" size="48" name="archive" id="archive" type="text">';
	$div .= '<input style="margin: 5px 0;" name="archive_popup" value="Choose or upload a file ..." title="Choose or upload a file" onclick="return openpopup(\'/files/index.php?id=' . $id . '&amp;choose=archive\', \'popup\', \'menubar=0,location=0,scrollbars,resizable,width=750,height=500\', 0);" id="archive_popup" type="button">';
	$div .= '<input type="hidden" name="id" value="' . $id . '" />';
	$div .= '<input type="submit" value="OK" style="height: 24px; margin: 5px 0; />';
	$div .= '</form>';
}
$div .= '</div><div style="clear: both"></div>';

echo $div;

/// Finish the page

print_footer($course);
?>
