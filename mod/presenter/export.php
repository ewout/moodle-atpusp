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
global $CFG;

require_login();

$id = required_param('course', PARAM_INT);
$presenter_id = required_param('id', PARAM_INT);

$presenter = presenter_get_presenter($presenter_id);

$values = array (
	'name' 					=> $presenter->name,
	'nr_chapters'			=> $presenter->nr_chapters,
	'presentation_width1'	=> $presenter->presentation_width1,
	'presentation_height1'	=> $presenter->presentation_height1,
	'presentation_width2'	=> $presenter->presentation_width2,
	'presentation_height2'	=> $presenter->presentation_height2,
	'player_width1'			=> $presenter->player_width1,
	'player_height1'		=> $presenter->player_height1,
	'player_width2'			=> $presenter->player_width2,
	'player_height2'		=> $presenter->player_height2,
	'window'				=> $presenter->window,
	'player_skin'			=> $presenter->player_skin,
	'control_bar'			=> $presenter->control_bar,
	'player_streching'		=> $presenter->player_streching,
	'volume'				=> $presenter->volume,
	'buffer_length'			=> $presenter->buffer_length,
	'slide_streching'		=> $presenter->slide_streching,
	'summary_height'		=> $presenter->summary_height
);
	
if ($CFG->zip) {
	$zip = 1;
	$command = "cd $CFG->dataroot/$id/; $CFG->zip Presenter/";
	$files = '';
} else {
	$zip = 0;
}

if (!$zip) {
	$createZip = new createZip();
}

$presenterName = str_replace(" ", "_", $presenter->name);

$fh = fopen($CFG->dataroot . '/' . $id . "/presenter.xml", "w");
write_tag('root');
write_tag('course_module');

$courseModule = get_course_module($presenter_id, $id);
foreach ($courseModule as $module) {
	foreach ($module as $k => $v) {
		write_tag($k, $v);
		if ($v == '') {
			close_tag($k);
		}
	}
	break;
}

close_tag('course_module');

write_tag('presenter');

//presenter details
foreach ($values as $k => $v) {
	write_tag($k, $v);
	if ($v == '') {
		close_tag($k);
	}
}

$chapters = get_chapters($presenter->id);

write_tag('chapters');
foreach ($chapters as $chapter) {
	write_tag('chapter');
	$values = array (
		'order_id' 			=> $chapter->order_id,
		'chapter_name'		=> $chapter->chapter_name,
		'video_link'		=> str_replace("&", "###", $chapter->video_link),
		'video_start'		=> $chapter->video_start,
		'video_end'			=> $chapter->video_end,
		'audio_track'		=> $chapter->audio_track,
		'audio_start'		=> $chapter->audio_start,
		'audio_end'			=> $chapter->audio_end,
		'slide_image'		=> $chapter->slide_image,
		'summary'			=> '<![CDATA[' . $chapter->summary . ']]>',
		'layout'			=> $chapter->layout
	);

	foreach ($values as $k => $v) {
		write_tag($k, $v);
		if ($v == '') {
			close_tag($k);
		}
	}
	close_tag('chapter');
}

close_tag('chapters');
close_tag('presenter');
close_tag('root');

fclose($fh);

foreach ($chapters as $chapter) {
	if ($chapter->video_link && !strstr($chapter->video_link, 'http://')) {
		if ($zip) {
			$files .= $chapter->video_link . ' ';
		} else {
			$videolink = addslashes($chapter->video_link);
			$fileContents = file_get_contents($CFG->dataroot . '/' . $presenter->course . '/' . $videolink);
			$createZip -> addFile($fileContents, $videolink);
		}
	}
	if ($chapter->audio_track) {
		if ($zip) {
			$files .= $chapter->audio_track . ' ';
		} else {
			$fileContents = file_get_contents(get_file_url($presenter->course . '/' . $chapter->audio_track, null, 'coursefile'));
			$createZip -> addFile($fileContents, $chapter->audio_track);
		}
	}
	if ($chapter->slide_image) {
		if ($zip) {
			$files .= $chapter->slide_image . ' ';
		} else {
			$fileContents = file_get_contents(get_file_url($presenter->course . '/' . $chapter->slide_image, null, 'coursefile'));
			$createZip -> addFile($fileContents, $chapter->slide_image);
		}
	}
}
if ($zip) {
	$files .= 'presenter.xml ';
} else {
	$fileContents = file_get_contents($CFG->dataroot . '/' . $id . '/presenter.xml');
	$createZip->addFile($fileContents, 'presenter.xml');
}

if (!is_dir($CFG->dataroot . '/' . $id . '/Presenter')) {
	@mkdir($CFG->dataroot . '/' . $id . '/Presenter', 0777);
}
if ($zip == 0) {
	@unlink($CFG->dataroot . '/' . $id . '/presenter.xml');
}

$date = date('Ymd');

$nr = $presenter->id;

$archiveName = optional_param('archiveName', '', PARAM_CLEAN);

if (!$archiveName) {
	$archiveName = $presenterName . '_' . $date . '_' . $nr . ".zip";
}

$presenter->export_file = $archiveName;
update_record("presenter", $presenter);

if ($zip) {
	$command .= $archiveName . ' ';
	$command .= $files;
	exec($command);
	CreateZip::forceDownload($CFG->dataroot . '/' . $id . '/Presenter/' . $archiveName);
} else {
	$fileName = $CFG->dataroot . '/' . $id . '/Presenter/' . $archiveName;
	$fd = fopen ($fileName, "wb");
	$out = fwrite ($fd, $createZip -> getZippedfile());
	fclose ($fd);
	$createZip -> forceDownload($fileName);
}

function write_tag($name, $value = '')
{
	global $fh;
	fwrite($fh, '<' . $name . '>');
	if ($value != '') {
		fwrite($fh, $value);
		close_tag($name);
	}
}

function close_tag($name)
{
	global $fh;
	fwrite($fh, '</' . $name . '>');
}

/**
 * Class to dynamically create a zip file (archive)
 *
 * @author Rochak Chauhan
 */

class createZip  {

	public $compressedData = array();
	public $centralDirectory = array(); // central directory
	public $endOfCentralDirectory = "\x50\x4b\x05\x06\x00\x00\x00\x00"; //end of Central directory record
	public $oldOffset = 0;

	/**
	 * Function to create the directory where the file(s) will be unzipped
	 *
	 * @param $directoryName string
	 *
	 */

	public function addDirectory($directoryName) {
		$directoryName = str_replace("\\", "/", $directoryName);

		$feedArrayRow = "\x50\x4b\x03\x04";
		$feedArrayRow .= "\x0a\x00";
		$feedArrayRow .= "\x00\x00";
		$feedArrayRow .= "\x00\x00";
		$feedArrayRow .= "\x00\x00\x00\x00";

		$feedArrayRow .= pack("V",0);
		$feedArrayRow .= pack("V",0);
		$feedArrayRow .= pack("V",0);
		$feedArrayRow .= pack("v", strlen($directoryName) );
		$feedArrayRow .= pack("v", 0 );
		$feedArrayRow .= $directoryName;

		$feedArrayRow .= pack("V",0);
		$feedArrayRow .= pack("V",0);
		$feedArrayRow .= pack("V",0);

		$this -> compressedData[] = $feedArrayRow;

		$newOffset = strlen(implode("", $this->compressedData));

		$addCentralRecord = "\x50\x4b\x01\x02";
		$addCentralRecord .="\x00\x00";
		$addCentralRecord .="\x0a\x00";
		$addCentralRecord .="\x00\x00";
		$addCentralRecord .="\x00\x00";
		$addCentralRecord .="\x00\x00\x00\x00";
		$addCentralRecord .= pack("V",0);
		$addCentralRecord .= pack("V",0);
		$addCentralRecord .= pack("V",0);
		$addCentralRecord .= pack("v", strlen($directoryName) );
		$addCentralRecord .= pack("v", 0 );
		$addCentralRecord .= pack("v", 0 );
		$addCentralRecord .= pack("v", 0 );
		$addCentralRecord .= pack("v", 0 );
		$ext = "\x00\x00\x10\x00";
		$ext = "\xff\xff\xff\xff";
		$addCentralRecord .= pack("V", 16 );

		$addCentralRecord .= pack("V", $this -> oldOffset );
		$this -> oldOffset = $newOffset;

		$addCentralRecord .= $directoryName;

		$this -> centralDirectory[] = $addCentralRecord;
	}

	/**
	 * Function to add file(s) to the specified directory in the archive
	 *
	 * @param $directoryName string
	 *
	 */

	public function addFile($data, $directoryName)   {

		$directoryName = str_replace("\\", "/", $directoryName);

		$feedArrayRow = "\x50\x4b\x03\x04";
		$feedArrayRow .= "\x14\x00";
		$feedArrayRow .= "\x00\x00";
		$feedArrayRow .= "\x08\x00";
		$feedArrayRow .= "\x00\x00\x00\x00";

		$uncompressedLength = strlen($data);
		$compression = crc32($data);
		$gzCompressedData = gzcompress($data);
		$gzCompressedData = substr( substr($gzCompressedData, 0, strlen($gzCompressedData) - 4), 2);
		$compressedLength = strlen($gzCompressedData);
		$feedArrayRow .= pack("V",$compression);
		$feedArrayRow .= pack("V",$compressedLength);
		$feedArrayRow .= pack("V",$uncompressedLength);
		$feedArrayRow .= pack("v", strlen($directoryName) );
		$feedArrayRow .= pack("v", 0 );
		$feedArrayRow .= $directoryName;

		$feedArrayRow .= $gzCompressedData;

		$feedArrayRow .= pack("V",$compression);
		$feedArrayRow .= pack("V",$compressedLength);
		$feedArrayRow .= pack("V",$uncompressedLength);

		$this -> compressedData[] = $feedArrayRow;

		$newOffset = strlen(implode("", $this->compressedData));

		$addCentralRecord = "\x50\x4b\x01\x02";
		$addCentralRecord .="\x00\x00";
		$addCentralRecord .="\x14\x00";
		$addCentralRecord .="\x00\x00";
		$addCentralRecord .="\x08\x00";
		$addCentralRecord .="\x00\x00\x00\x00";
		$addCentralRecord .= pack("V",$compression);
		$addCentralRecord .= pack("V",$compressedLength);
		$addCentralRecord .= pack("V",$uncompressedLength);
		$addCentralRecord .= pack("v", strlen($directoryName) );
		$addCentralRecord .= pack("v", 0 );
		$addCentralRecord .= pack("v", 0 );
		$addCentralRecord .= pack("v", 0 );
		$addCentralRecord .= pack("v", 0 );
		$addCentralRecord .= pack("V", 32 );

		$addCentralRecord .= pack("V", $this -> oldOffset );
		$this -> oldOffset = $newOffset;

		$addCentralRecord .= $directoryName;

		$this -> centralDirectory[] = $addCentralRecord;
	}

	/**
	 * Fucntion to return the zip file
	 *
	 * @return zipfile (archive)
	 */

	public function getZippedfile() {

		$data = implode("", $this -> compressedData);
		$controlDirectory = implode("", $this -> centralDirectory);

		return
		$data.
		$controlDirectory.
		$this -> endOfCentralDirectory.
		pack("v", sizeof($this -> centralDirectory)).
		pack("v", sizeof($this -> centralDirectory)).
		pack("V", strlen($controlDirectory)).
		pack("V", strlen($data)).
			"\x00\x00";                             
	}

	/**
	 *
	 * Function to force the download of the archive as soon as it is created
	 *
	 * @param archiveName string - name of the created archive file
	 */

	public function forceDownload($archiveName) {
		$headerInfo = '';
			
		if(ini_get('zlib.output_compression')) {
			ini_set('zlib.output_compression', 'Off');
		}

		// Security checks
		if( $archiveName == "" ) {
				
			exit;
		}
		elseif ( ! file_exists( $archiveName ) ) {
				
			exit;
		}

		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private",false);
		header("Content-Type: application/zip");
		header("Content-Disposition: attachment; filename=".basename($archiveName).";" );
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: ".filesize($archiveName));
		readfile("$archiveName");

	}

}
?>
