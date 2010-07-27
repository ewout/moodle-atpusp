<?php
/**
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


function presenter_add_instance($presenter) {
	global $SESSION;

	presenter_process_pre_save($presenter);

	if (!$presenter->id = insert_record("presenter", $presenter)) {
		return false; // bad
	}

	presenter_process_post_save($presenter);

	return $presenter->id;
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $presenter presenter post data from the form
 * @return boolean
 **/
function presenter_update_instance($presenter) {

	$presenter->id = $presenter->instance;

	presenter_process_pre_save($presenter);

	if (!$result = update_record("presenter", $presenter)) {
		return false; // Awe man!
	}

	presenter_process_post_save($presenter);

	return $result;
}

function presenter_get_presenter($id)
{
	if (!$presenter = get_record('presenter', 'id', $id)) {
		return false;
	}
	return $presenter;
}

/*******************************************************************/
function presenter_delete_instance($id) {
	/// Given an ID of an instance of this module,
	/// this function will permanently delete the instance
	/// and any data that depends on it.

	if (! $presenter = get_record("presenter", "id", "$id")) {
		return false;
	}

	$result = true;

	if (! delete_records("presenter", "id", "$presenter->id")) {
		$result = false;
	}
	if (! delete_records("presenter_chapters", "presenterid", "$presenter->id")) {
		$result = false;
	}

	if (! delete_records("presenter_chapters_users", "presenter_id", $presenter->id)) {
		$result = false;
	}

	return $result;
}

/**
 * Given a course object, this function will clean up anything that
 * would be leftover after all the instances were deleted.
 *
 * As of now, this function just cleans the presenter_default table
 *
 * @param object $course an object representing the course that is being deleted
 * @param boolean $feedback to specify if the process must output a summary of its work
 * @return boolean
 */
function presenter_delete_course($course, $feedback=true) {

	$count = count_records('presenter_default', 'course', $course->id);
	delete_records('presenter_default', 'course', $course->id);

	//Inform about changes performed if feedback is enabled
	if ($feedback) {
		notify(get_string('deletedefaults', 'presenter', $count));
	}

	return true;
}

function presenter_get_view_actions() {
	return array('view','view all');
}

function presenter_get_post_actions() {
	return array('end','start', 'update grade attempt');
}

/**
 * Runs any processes that must run before
 * a presenter insert/update
 *
 * @param object $presenter presenter form data
 * @return void
 **/
function presenter_process_pre_save(&$presenter) {

	$chaptersnr = 0;
	foreach ($_POST['chapter_name'] as $i => $v) {
		if ($_POST['deleted'][$i] == 'false') {
			$chaptersnr++;
		}
	}

	$presenter->nr_chapters = $chaptersnr;

	switch($presenter->control_bar) {
		case 0:
			$presenter->control_bar = 'bottom';
			break;
		case 1:
			$presenter->control_bar = 'over';
			break;
		case 2:
			$presenter->control_bar = 'none';
			break;
	}

	switch ($presenter->player_streching) {
		case 0:
			$presenter->player_streching = 'uniform';
			break;
		case 1:
			$presenter->player_streching = 'exactfit';
			break;
		case 2:
			$presenter->player_streching = 'fill';
			break;
	}

	switch ($presenter->slide_streching) {
		case 0:
			$presenter->slide_streching = 'uniform';
			break;
		case 1:
			$presenter->slide_streching = 'exactfit';
			break;
		case 2:
			$presenter->slide_streching = 'fill';
			break;
	}
	unset($presenter->layout1);
	unset($presenter->video_link);
	unset($presenter->summary);
}

/**
 * Runs any processes that must be run
 * after a presenter insert/update
 *
 * @param object $presenter presenter form data
 * @return void
 **/
function presenter_process_post_save(&$presenter) {
	global $CFG;
	//delete any previous chapters in case of updating
	$oldchapters = get_records("presenter_chapters", "presenterid", $presenter->id);

	//save chapters
	$i = 0;
	foreach ($_POST['chapter_name'] as $i => $value) {
		if ($value != '' && $_POST['deleted'][$i] == 'false') {
			$chapter = new stdClass();
			$chapter->order_id = $_POST['order_id'][$i];
			$chapter->presenterid = $presenter->id;
			$chapter->chapter_name = $value;
			if (isset($_POST['video_link'][$i]['value'])) {
				$chapter->video_link = $_POST['video_link'][$i]['value'];
			}
			if (isset($_POST['audio_track'][$i]['value'])) {
				$chapter->audio_track = $_POST['audio_track'][$i]['value'];
			}
			if (isset($_POST['slide_image'][$i]['value'])) {
				$chapter->slide_image = $_POST['slide_image'][$i]['value'];
			}
			$chapter->summary = $_POST['summary'][$i];
			if (isset($_POST['video_start'][$i])) {
				$chapter->video_start = $_POST['video_start'][$i];
			}
			if (isset($_POST['video_end'][$i])) {
				$chapter->video_end = $_POST['video_end'][$i];
			}
			if (isset($_POST['audio_start'][$i])) {
				$chapter->audio_start = $_POST['audio_start'][$i];
			}
			if (isset($_POST['audio_end'][$i])) {
				$chapter->audio_end = $_POST['audio_end'][$i];
			}
			$chapter->completion_factor = $_POST['completion_factor'][$i];
			$chapter->layout = $_POST['layout'][$i]['layout'];
			if (!($chapter->id = insert_record("presenter_chapters", $chapter))) {
				error("Error updating chapter " . $chapter->chapter_name);
				$b = 1;
				break;
			}
			foreach ($oldchapters as $c) {
				if (nothing_changed($c, $chapter)) {
					$histories = get_records_sql("SELECT * FROM {$CFG->prefix}presenter_chapters_users WHERE chapter_id=$c->id");
					foreach ($histories as $history) {
						$history->chapter_id = $chapter->id;
						$history->presenter_id = $presenter->id;
						update_record("presenter_chapters_users", $history);
					}
				}
			}
		}
	}
	if (!isset($b)) {
		foreach ($oldchapters as $c) {
			delete_records("presenter_chapters", "id", "$c->id");
		}
	}

}

function nothing_changed($c1, $c2)
{
	$c1->chapter_name = addslashes($c1->chapter_name);
	$c1->video_link = addslashes($c1->video_link);
	if ($c1->chapter_name == $c2->chapter_name && $c1->video_link == $c2->video_link &&
	$c1->video_start == $c2->video_start && $c1->video_end == $c2->video_end) {
		return true;
	}

	return false;
}

?>
