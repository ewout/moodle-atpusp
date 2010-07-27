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
require_once('lib.php');
require_once('chapterlib.php');

if (!((class_exists("ZipArchive") || $CFG->unzip) && class_exists("XMLReader"))) {
	error(get_string('xmlreader_error', 'presenter'));
}

require_login();

$id = required_param('id', PARAM_INT);   // course

if (!$course = get_record("course", "id", $id)) {
	error("Course ID is incorrect");
}

function xml2assoc($xml)
{
	$tree = null;
	while($xml->read())
	switch ($xml->nodeType) {
		case XMLReader::END_ELEMENT: return $tree;
		case XMLReader::ELEMENT:
			$node = array('tag' => $xml->name, 'value' => $xml->isEmptyElement ? '' : xml2assoc($xml));
			if($xml->hasAttributes)
			while($xml->moveToNextAttribute())
			$node['attributes'][$xml->name] = $xml->value;
			$tree[] = $node;
			break;
		case XMLReader::TEXT:
		case XMLReader::CDATA:
			$aux = str_replace("###", "&", $xml->value);
			$tree .= $aux;
	}
	return $tree;
}

$archive = required_param('archive', PARAM_CLEAN);

$section = optional_param('section', 0, PARAM_INT);

if ($archive) {
	
	$bbb = 10;
	if ($CFG->unzip) {
		exec ($CFG->unzip . ' ' . $CFG->dataroot . "/$id/" . $archive . ' -d ' . $CFG->dataroot . '/' . $id . '/');
		$aux = 1;
	} elseif (class_exists("ZipArchive")) {
		$zip = new ZipArchive();
		if (TRUE == $zip->open($CFG->dataroot . "/$id/" . $archive)) {
			$zip->extractTo($CFG->dataroot . '/' . $id . '/');
		}
		$aux = 1;
	}

	$xml = new XMLReader();
	$xml->open($CFG->dataroot . '/' . $id . '/presenter.xml');
		
	$data = xml2assoc($xml);
	if (is_array($data[0]['value'][1]['value'])) {
		$presenter = new stdClass();
		for ($i = 0; $i < count($data[0]['value'][1]['value']); $i++) {
			if (!is_array($data[0]['value'][1]['value'][$i]['value'])) {
				$field = $data[0]['value'][1]['value'][$i]['tag'];
				$value = $data[0]['value'][1]['value'][$i]['value'];
				$presenter->$field = $value;
			}
		}
		$presenter->course = $id;
	}
	if (!$presenterId = insert_record("presenter", $presenter)) {
		error(get_string('cannot_add', 'presenter'));
	}
		
	$courseModule = new stdClass();
	for ($i = 0; $i < count($data[0]['value'][0]['value']); $i++) {
		$field = $data[0]['value'][0]['value'][$i]['tag'];
		$value = $data[0]['value'][0]['value'][$i]['value'];
		$courseModule->$field = $value;
	}
	$courseModule->instance = $presenterId;
	$courseModule->module = get_presenter_module_id();
	$courseModule->idnumber = '';
	$courseModule->course = $id;
	$courseModule->id = '';
		
	if ($section) {
		
		$courseModuleId = insert_record("course_modules", $courseModule);

		$cs = get_records_sql("SELECT * FROM {$CFG->prefix}course_sections WHERE course=$id AND section=$section");

		foreach ($cs as $ccs) {
			$courseSection = $ccs;
			break;
		}

		if ($courseSection->sequence) {
			$courseSection->sequence .= ',' . $courseModuleId;
		} else {
			$courseSection->sequence = $courseModuleId;
		}

		$courseModule->section = $courseSection->id;

		if ($id == 1 && $section == 1) {
			$courseModule->section = 1;
		}
		$courseModule->id = $courseModuleId;
		update_record("course_modules", $courseModule);
		update_record("course_sections", $courseSection);
	} else {
			
		$res = get_records_sql("SELECT section FROM {$CFG->prefix}course_modules WHERE course=$id ORDER BY id DESC LIMIT 0,1");
		foreach ($res as $r) {
			$section = $r->section;
			break;
		}

		$courseModule->section = 1;
	  
		$cmid = insert_record("course_modules", $courseModule);
	  
		$r = get_records_sql("SELECT * FROM {$CFG->prefix}course_sections WHERE id=$section");
		foreach ($r as $rr) {
			$aux = $rr;
			break;
		}
	  
		$aux->sequence .= ',' . $cmid;

		update_record("course_sections", $aux);
	}

	$chapters = $data[0]['value'][1]['value'][18]['value'];
	for ($i = 0; $i < count($chapters); $i++) {
		$chapter = new stdClass();
		$chapter->presenterid = $presenterId;
		foreach ($chapters[$i]['value'] as $c) {
			$field = $c['tag'];
			$value = $c['value'];
			$chapter->$field = $value;
			if ($chapter->$field == null) {
				$chapter->$field = '';
			}
		}
			
		$chapter->id = insert_record("presenter_chapters", $chapter);

		if (!$chapter->id) {
			$bbb = 1;
		}
	}

	unlink($CFG->dataroot . '/' . $id . '/presenter.xml');
} else {
	$bbb = 1;
}

if (isset($section)) {
	if ($bbb == 10) {
		if ($id != 1) {
			$view = '/course/view.php?id=' . $id;
		} else {
			$view = '/';
		}
		rebuild_course_cache();
		echo '<div style="text-align: center; margin: 0 auto; font-size: 12px;">' . get_string('import_success', 'presenter') . '
			<a href="" onclick="parent.location.href=\'' . $CFG->wwwroot . $view . '\'">' . get_string('import_after', 'presenter') . '</a>
			' . get_string('import_after_after', 'presenter') . '<span id="counter">3</span> seconds
			</div>';

		$sc = '<script type="text/javascript">
						setTimeout(\'parent.location.href="' . $CFG->wwwroot . $view . '"\', 3000);
	var seconds = 3;
	function display()
	{
		document.getElementById("counter").innerHTML = seconds;
		if (seconds > 0)
			seconds--;
		setTimeout("display()", 900);
	}
	display();
					</script>';
		echo $sc;
		die;

	} else if ($bbb == 1) {
		echo '<div style="width: 80%; text-align: center; margin: 0 auto; font-size: 12px;">' . get_string('import_error', 'presenter') . '</div>';
		die;
	}
}

$strpresenters = get_string("modulenameplural", "presenter");
$strpresenter  = get_string("modulename", "presenter");


/// Print the header
$navlinks = array();
$navlinks[] = array('name' => $strpresenters, 'link' => '', 'type' => 'activity');

$navigation = build_navigation($navlinks);

print_header("$course->shortname: $strpresenters", $course->fullname, $navigation, "", "", true, "", navmenu($course));

if ($bbb == 10) {
	if ($id != 1) {
		$view = '/course/view.php?id=' . $id;
	} else {
		$view = '/';
	}
	rebuild_course_cache();
	echo '<div style="width: 80%; text-align: center; margin: 0 auto; font-size: 12px;">' . get_string('import_success', 'presenter') . '
		<a href="" onclick="parent.location.href=\'' . $CFG->wwwroot . $view . '\'">' . get_string('import_after', 'presenter') . '</a>
			' . get_string('import_after_after', 'presenter') . '<span id="counter">3</span> seconds
			</div>';
	 
	$sc = '
<script type="text/javascript">
	setTimeout(\'parent.location.href="' . $CFG->wwwroot . $view . '"\', 3000);
	var seconds = 3;
	function display()
	{
		document.getElementById("counter").innerHTML = seconds;
		if (seconds > 0)
			seconds--;
		setTimeout("display()", 900);
	}
	display();
</script>';
	echo $sc;
	die;
	 
} else if ($bbb == 1) {
	echo '<div style="width: 80%; text-align: center; margin: 0 auto; font-size: 12px;">' . get_string('import_error', 'presenter') . '</div>';
	die;
}






?>