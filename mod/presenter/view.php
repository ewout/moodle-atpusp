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
require_once("lib.php");
require_once("../../lib/filelib.php");
require_once("chapterlib.php");

$id         	= required_param('id', PARAM_INT);                 // Course Module ID
$chapterid     	= optional_param('chapterid', '', PARAM_INT);    //Chapter ID


if (! $cm = get_coursemodule_from_id('presenter', $id)) {
	error("Course Module ID was incorrect");
}

if (! $course = get_record("course", "id", $cm->course)) {
	error("Course is misconfigured");
}

require_course_login($course, false, $cm);

if (!$presenter = presenter_get_presenter($cm->instance)) {
	error("Course module is incorrect");
}

$open = optional_param('open', 0, PARAM_INT);

if ($presenter->window == 1 && $open == 0) {
	$s  = '<script type="text/javascript" src=' . $CFG->wwwroot . '/mod/presenter/popup.js></script>';
	$s .= '<script type="text/javascript">';
	$s .= 'if (!openURL(\'view.php?open=1&id=' . $id . '\') ) ';
	$s .= 'alert(\'' . get_string('alert_new_window', 'presenter') . '\');';
	$s .= 'history.go(-1);';
	$s .= '</script>';
	echo $s;
}

if (!$chapterid) {
	$chapterid = get_first_chapter($presenter->id)->id;
}

if (! $chapter = get_chapter($chapterid)) {
	error("Chapter ID was incorrect");
}

add_to_log($course->id, "presenter", "view", "view.php?id=$cm->id", $presenter->id, $cm->id);

$strpresenter = get_string('modulename', 'presenter');
$strpresenters = get_string('modulenameplural', 'presenter');

if (!$context = get_context_instance(CONTEXT_MODULE, $cm->id)) {
	print_error('badcontext');
}

$baseUrl = $CFG->wwwroot . '/mod/presenter/view.php?open=1&id=' . $id . '&chapterid=';

$navigation = build_navigation('', $cm);
if ($presenter->window != 1) {
	print_header_simple(format_string($presenter->name), "", $navigation, "", "", true,
	update_module_button($cm->id, $course->id, $strpresenter), navmenu($course, $cm));
}

$volume = $presenter->volume;
if ($SESSION->playerVolume) {
	$volume = $SESSION->playerVolume;
}
$SESSION->playerVolume = null;

$noplay = optional_param('noplay', 0, PARAM_INT);

if ($noplay == 1) {
	$dont_play = true;
}

$summaryWidth = $presentationWidth;
$summaryHeight = $presenter->summary_height * 15;

switch ($chapter->layout) {
	case '1':
	case '2':
		$presentationWidth = $presenter->presentation_width1;
		$presentationHeight = $presenter->presentation_height1;

		$navWidth = $presenter->player_width1;
		$navHeight = $presentationHeight - $presenter->player_height1;

		$playerWidth = $presenter->player_width1;
		$playerHeight = $presenter->player_height1;

		$mp3PlayerWidth = 0;
		$mp3PlayerHeight = 0;

		$slideWidth = $presentationWidth - $playerWidth;
		$slideHeight = $presentationHeight;

		break;

	case '3':
	case '4':
		$presentationWidth = $presenter->presentation_width2;
		$presentationHeight = $presenter->presentation_height2;

		$navWidth = $presentationWidth - $presenter->player_width2;
		$navHeight = $presentationHeight;

		$playerWidth = $presenter->player_width2;
		$playerHeight = $presenter->player_height2;

		$mp3PlayerWidth = 0;
		$mp3PlayerHeight = 0;

		$slideWidth = 0;
		$slideHeight = 0;

		break;

	case '5':
	case '6':
		$presentationWidth = $presenter->presentation_width2;
		$presentationHeight = $presenter->presentation_height2;

		$navWidth = $presentationWidth - $presenter->player_width2;
		$navHeight = $presentationHeight;

		$playerWidth = 0;
		$playerHeight = 0;

		if ($chapter->audio_track) {
			$mp3PlayerWidth = $presenter->player_width2;
			$mp3PlayerHeight = 24;
			$navHeight += 24;
			$presentationHeight += 24;
		} else {
			$mp3PlayerWidth = 0;
			$mp3PlayerHeight = 0;
		}

		$slideWidth = $presenter->player_width2;
		$slideHeight = $presenter->player_height2;

		break;

}

//setting the mp3 player in back - start / end position
$mp3start = 0;
if (!empty($chapter->audio_start)) {
	$strstart = explode(":", $chapter->audio_start);
	if (is_numeric($strstart[0])) {
		$mp3start = 3600 * intval($strstart[0]);
		if (isset($strstart[1])) {
			$mp3start += 60 * intval($strstart[1]);
		}
		if (isset($strstart[2])) {
			$mp3start += $strstart[2];
		}
	}
}
 
$mp3duration = '';
if (!empty($chapter->audio_end)) {
	$strend = explode(":", $chapter->audio_end);
	$mp3end = 0;
	if (is_numeric($strend[0])) {
		$mp3end = 3600 * intval($strend[0]);
		if (isset($strend[1])) {
			$mp3end += 60 * intval($strend[1]);
		}
		if (isset($strend[2])) {
			$mp3end += $strend[2];
		}
	}
	 
	if ($mp3end != 0) {
		$mp3duration = $mp3end - $mp3start;

	} else {
		$mp3duration = 0;
	}
}

if (empty($mp3duration)) {
	$mp3duration = '0';
}

//skin
$skinUrl = $CFG->wwwroot . '/mod/presenter/flowplayer.controls-3.1.5.swf';
switch ($presenter->player_skin) {
	case '1' : //tube skin
		$skinUrl = $CFG->wwwroot . '/mod/presenter/flowplayer.controls-tube-3.1.5.swf';
		break;
}

//control bar
switch ($presenter->control_bar) {
	case "none" :
		$controlBar = 'null';
		break;
	case "bottom" :
		$controlBar = '{
		        url: \'' . $skinUrl . '\', 
		        stop : true, 
		        left: 0,
		        bottom: 0
		    }';
		break;
	case "over" :
		$controlBar = '{
		        url: \'' . $skinUrl . '\', 
		        bottom: 0,
		        autoHide: \'never\'
		    }';
		break;
}

$mp3script = '<script type="text/javascript" src="' . $CFG->wwwroot . '/mod/presenter/flowplayer-3.1.4.min.js"></script>
<script type="text/javascript">
	flowplayer("audio", "' . $CFG->wwwroot . '/mod/presenter/flowplayer-3.1.5.swf", {
		playlist : [
			{ url : \'' . get_file_url($presenter->course . '/' . $chapter->audio_track, null, 'coursefile') . '\', duration : ' . $mp3duration . ' }
		],
		plugins : {
			controls: ' . $controlBar . '
		},
        onFinish : function () {
            chapterCompleted(\'' . $course->id . '\',\'' . $USER->username . '\',\'' . $chapterid . '\',\'' . $presenter->id . '\');
		},
        clip : {';
if (isset($dont_play)) {
	$mp3script .= 'autoPlay: false,';
} else {
	$mp3script .= 'autoPlay: true,';
}
$mp3script .= '
            onStart : function () {
                this.setVolume(' . $volume . ');
			},
        }
	});
</script>
    ';

$mp3HTML = '<div id="audio" style="width: ' . $mp3PlayerWidth . 'px; height: 24px;"></div>' . $mp3script;

$videoStart = 0;
$videoEnd = 0;

if ($chapter->video_start) {
	$strstart = explode(":", $chapter->video_start);
	if (is_numeric($strstart[0])) {
		$videoStart = 3600 * intval($strstart[0]);
		if (isset($strstart[1])) {
			$videoStart += 60 * intval($strstart[1]);
		}
		if (isset($strstart[2])) {
			$videoStart += $strstart[2];
		}
	}
	 
	if ($chapter->video_end) {
		$strend = explode(":", $chapter->video_end);
		$videoEnd = 0;
		if (is_numeric($strend[0])) {
			$videoEnd = 3600 * intval($strend[0]);
			if (isset($strend[1])) {
				$videoEnd += 60 * intval($strend[1]);
			}
			if (isset($strend[2])) {
				$videoEnd += $strend[2];
			}
		}
	}
}
if ($videoEnd) {
	$duration = $videoEnd - $videoStart;
} else {
	$duration = '0';
}

$streching = $presenter->player_streching;

$bufferLength = $presenter->buffer_length;

$SESSION->c = $presenter->course;
if (!(strstr($chapter->video_link, 'http://'))) {
	$aux = explode('/', $chapter->video_link);
	for ($i = 0; $i < count($aux) - 1; $i++) {
		$SESSION->c .= '/' . $aux[$i];
	}
	$chapter->video_link = end($aux);
}

$script = '<script type="text/javascript">';

if (get_next_chapter_id($chapter)) {
	$script .= '
				var loc = \'view.php?open=1&id=' . $id . '&chapterid=' . get_next_chapter_id($chapter) . '\'; ';
} else {
	$script .= 'var loc=\'view.php?noplay=1&open=1&id=' . $id . '&chapterid=' . get_last_chapter_id($chapter) . '\';';
}
$script .= '
var xmlHttp;
function chapterCompleted(course, user, chapter, presenter)
{
	xmlHttp = GetXmlHttpObject();
		
	if (xmlHttp == null) {
		 alert ("Browser does not support HTTP Request");
		 return;
	}
    ';
if ($chapter->audio_track) {
	$script .= '
        volume = $f("audio").getVolume();
        $f("audio").stopBuffering();
        ';
} else {
	$script .= '
        volume = $f("player").getVolume();
        $f("player").stopBuffering();';
}

$script .= '
	var url = "' . $CFG->wwwroot . '" + "/mod/presenter/ajax.php?course=" + course + "&user=" + user + "&chapter=" + chapter + "&presenter=" + presenter + "&volume=" + volume;
	
	xmlHttp.onreadystatechange = function() {
		
		if (xmlHttp.readyState==4 || xmlHttp.readyState=="complete") {
			 
			var r = xmlHttp.responseText;';
$script .= 'if (loc != \'\') { location.href = loc; }';
$script .= '
		 }
	};
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);	
}

function chapterCompletedYoutube(course, user, chapter, presenter)
{
	xmlHttp = GetXmlHttpObject();
		
	if (xmlHttp == null) {
		 alert ("Browser does not support HTTP Request");
		 return;
	}
	
	//ytplayer should alreaby be instantiated
	volume = ytplayer.getVolume();
	var url = "' . $CFG->wwwroot . '" + "/mod/presenter/ajax.php?course=" + course + "&user=" + user + "&chapter=" + chapter + "&presenter=" + presenter + "&volume=" + volume;
	
	xmlHttp.onreadystatechange = function() {
		
		if (xmlHttp.readyState==4 || xmlHttp.readyState=="complete") {
			 
			var r = xmlHttp.responseText;';
$script .= 'if (loc != \'\') { location.href = loc; }';
$script .= '
		 }
	};
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function GetXmlHttpObject()
{
	var xmlHttp=null;
	try
	 {
	 // Firefox, Opera 8.0+, Safari
	 	xmlHttp=new XMLHttpRequest();
	 }
	catch (e)
	 {
	 //Internet Explorer
	 try
	  {
	 	 xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
	  }
	 catch (e)
	  {
	 	 xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
	  }
	 }
	return xmlHttp;
}
	</script>';

echo $script;

switch ($presenter->player_streching) {
	case 'uniform':
		$scaling = 'orig';
		break;
	case 'exactfit':
		$scaling = 'fit';
		break;
	case 'fill':
		$scaling = 'scale';
		break;
}
$url = $CFG->wwwroot . '/mod/presenter/flowplayer_streamer.php/' . $chapter->video_link;
$player = '<div style="overflow: hidden; float:left; width: ' . $playerWidth . 'px;height: ' . $playerHeight . 'px"><div style="overflow: hidden;float:left; width: ' . $playerWidth . 'px;height: ' . $playerHeight . 'px" id="player"></div></div>';

if (strstr($chapter->video_link, 'http://')) {
	$yt = 1;
	$movie_id = get_movie_id($chapter->video_link);
}

if (isset($yt)) {
	$autoplay = isset($dont_play) ? '0' : '1';

	$color = '';
	if ($presenter->player_skin == 1) {
		$color = '&color2=0xff0000';
	}

	$playerScript = '<script type="text/javascript" src="' . $CFG->wwwroot . '/mod/presenter/swfobject.js"></script>
<script type="text/javascript">
	var params = { allowScriptAccess: "always" };
	var atts = { id: "ytplayer" };
	swfobject.embedSWF("http://www.youtube.com/v/' . $movie_id . '?showsearch=0&showinfo=1&iv_load_policy=3&rel=0&enablejsapi=1&playerapiid=ytplayer' . $color . '&autoplay=' . $autoplay . '&start=' . $videoStart . '&fs=1", 
                       "player", "' . $playerWidth . '", "' . $playerHeight . '", "8", null, null, params, atts);
	var ytplayer = null;
	function onYouTubePlayerReady() {
		ytplayer = document.getElementById("ytplayer");
		ytplayer.setVolume(' . $volume . ');
		ytplayer.addEventListener("onStateChange", "youTubePlayerStateChange");
    }
    function youTubePlayerStateChange(newState) {
    	if (newState == 0) {
    		chapterCompletedYoutube(\'' . $course->id . '\',\'' . $USER->username . '\',\'' . $chapterid . '\',\'' . $presenter->id . '\');
    	}
    }
    
</script>
	';
} else {
	$playerScript = '<script type="text/javascript" src="' . $CFG->wwwroot . '/mod/presenter/flowplayer-3.1.4.min.js"></script>
	<script type="text/javascript">
		var stopped = 0;
		flowplayer("player", "' . $CFG->wwwroot . '/mod/presenter/flowplayer-3.1.5.swf", {
			plugins: {  
		        pseudo: { url: \'' . $CFG->wwwroot . '/mod/presenter/flowplayer.pseudostreaming-3.1.3.swf\' },
		        controls: ' . $controlBar . '
		    }, 
	     
	    	// clip properties  
	    	clip: { 
	         	start : ' . $videoStart . ',';
	if (isset($dont_play)) {
		$playerScript .= 'autoPlay: false,';
	} else {
		$playerScript .= 'autoPlay: true,';
	}
	$playerScript .= '
	         	autoBuffer : true,
	         	bufferLength : ' . $bufferLength . ', 
	         	duration : ' . $duration . ',
		        url : \'' . $url . '\',
				scaling : \'' . $scaling . '\',
				provider : \'pseudo\',
				onFinish : function () {
					chapterCompleted(\'' . $course->id . '\',\'' . $USER->username . '\',\'' . $chapterid . '\',\'' . $presenter->id . '\');
				},
				onStart : function () {
					stopped = 0;
					this.setVolume(' . $volume . ');
				},
				onStop : function () {
					if (stopped == 0) {
						stopped = 1;
						this.stopBuffering();
					}
					this.closeConnection();
				}
			}
		});
	</script>';
}

if ($chapter->layout == '5' || $chapter->layout == '6') {
	$playerScript = '';
}
$player .= $playerScript;

$imgUrl = get_file_url($presenter->course . '/' . $chapter->slide_image, null, 'coursefile');

$slide = '<div style="line-height: 0;overflow: hidden; text-align: center; width: ' . $slideWidth . 'px;height: ' . ($slideHeightDiv) . 'px">';

$ext = strtolower(substr($chapter->slide_image, -3));

switch($ext) {
	case 'jpg':
		$image = imagecreatefromjpeg($CFG->dataroot . '/' . $presenter->course . '/' . $chapter->slide_image);
		break;
	case 'gif':
		$image = imagecreatefromgif($CFG->dataroot . '/' . $presenter->course . '/' . $chapter->slide_image);
		break;
	case 'png':
		$image = imagecreatefrompng($CFG->dataroot . '/' . $presenter->course . '/' . $chapter->slide_image);
		break;
}

if (isset($image)) {
	$imgWidth = imagesx($image);
	$imgHeight = imagesy($image);
	if ($slideWidth * $imgHeight / $imgWidth <= $slideHeight) {
		$imgStyle = 'width: ' . $slideWidth . 'px';
	} else {
		$imgStyle = 'height: ' . $slideHeight . 'px';
	}
} else {
	$imgStyle = 'width: ' . $slideWidth . 'px';
}

$style = 'style="';
switch ($presenter->slide_streching) {
	case 'uniform':
		break;
	case 'fill':
		$style .= 'width: ' . $slideWidth . 'px; height: ' . $slideHeight . 'px';
		break;
	case 'exactfit':
		$style .= $imgStyle;
		break;
}

$style .= '"';
if ($chapter->slide_image != '') {
	$slide .=  "<img src=\"{$imgUrl}\" {$style} />";
}
if (!empty($chapter->audio_track)) {
	$slide .= $mp3HTML;
}

$slide .= '</div>';
//end image settings

if ($summaryHeight) {
	//summary
	$summary = '<div class="summarytext" style="border-top: 1px solid #CCCCCC; font-size: 14px; overflow-y: scroll; width: ' . $summaryWidth . 'px;height: ' . ($summaryHeight - 1) . 'px">';
	$summary .= $chapter->summary;

	$summary .= '&nbsp;</div>';
}

//building navigation
$nav = '<div id="aaaa" style="clear: left;margin-right: 0;width: ' . $navWidth . 'px;height: ' . ($navHeight) . 'px; overflow-x: hidden; overflow-y: auto">';
$aux = $navWidth - 20;
$nav .= '<table width="' . $aux . 'px" cellpadding="0" cellspacing="0">';
$nav .= '<th width="5%"></th><th width="5%"></th><th width="90%"></th>';
$chapters = get_chapters($presenter->id, 0);
$i = 1;
$index = 0;
foreach ($chapters as $ch) {
	$aux = "<td style=\"vertical-align: top\">";
	if (chapter_completed($ch->id, $USER->username) === $USER->username) {
		$aux .= '<img src="' . $CFG->wwwroot . '/mod/presenter/pix/check.gif" width="16" style="margin-bottom: -3px;" border="0" />';
	}
	$aux .= "</td>";
	$nav .= '<tr>' . $aux . '<td style="text-align: right; vertical-align: top">' . $i . '.</td><td style="padding-left: 5px"><a name="' . $ch->id .'" href="' . $baseUrl . $ch->id . '"';
	if ($ch->id == $chapter->id) {
		$nav .= ' style="color:#AA0000"';
		$index = $i;
	}
	$nav .= ">{$ch->chapter_name}</a></td></tr>";
	$i++;
}

$nav .= '</table></div>';

$col1 = '<div style="float: left">';
$col2 = '<div style="float: left">';


if ($presenter->window != 1) {
	echo '<div class="box generalbox generalboxcontent boxaligncenter" style="width: ' . $presentationWidth . 'px; height: ' . ($presentationHeight + $summaryHeight) . 'px">';
}

switch($chapter->layout) {
	case '1':
		$col1 .= $player . $nav;
		$col2 .= $slide;
		break;
	case '2':
		$col1 .= $slide;
		$col2 .= $player . $nav;
		break;
	case '3':
		$col1 .= $nav;
		$col2 .= $player;
		break;
	case '4':
		$col1 .= $player;
		$col2 .= $nav;
		break;
	case '5':
		$col1 .= $nav;
		$col2 .= $slide;
		break;
	case '6':
		$col1 .= $slide;
		$col2 .= $nav;
		break;
}

$col1 .= '</div>';
$col2 .= '</div>';
$clear = '<div style="clear: both; float: none; height: 0px"></div>';

if ($presenter->window == 1) {
	$aaaaaaaaa = 2 + $pageWidth;
	echo '<div style="width: 100%; text-align: center;">';
	echo '<div style="width: ' . $aaaaaaaaa . 'px; margin: 0 auto; border: 1px solid #CCC;">';
}

echo $col1 . $col2 . $clear . $summary;

if ($presenter->window == 1) {
	echo '</div></div>';
}

$number = ($navHeight / 18) - 1;

$number = intval($number / 2);
if ($index > $number) {
	$scrollTop = ($index - $number) * 18;
} else {
	$scrollTop = 0;
}

$s = '<script type="text/javascript">document.getElementById("aaaa").scrollTop = ' . $scrollTop . ';</script>';
echo $s;
if ($presenter->window != 1) {
	print_footer($course);
}

?>
