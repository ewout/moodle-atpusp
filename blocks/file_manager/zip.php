<?php
/**********************************************************/
// view.php
// 
// This file allows a user to view a list of their links and
// Gives them the ability to manage many aspects of their files,
// Including sharing to other users, organizing, submitting to
// course assignments, etc.
/*********************************************************/
    global $USER, $CFG;
    require_once("../../config.php");
    require_once("lib.php");
	require_once("print_lib.php");

	$id = required_param('id', PARAM_INT);
	$groupid = optional_param('groupid', 0, PARAM_INT);
	$rootdir = optional_param('rootdir', "0", PARAM_INT);		// 0 == root
    $action  = optional_param('action', '', PARAM_ACTION);
    if($action=='' || ! $action) $action  = optional_param('what', '', PARAM_ACTION);
	$zipname = optional_param('zipname',NULL,PARAM_CLEAN);
	$zipid = optional_param('zipid',NULL,PARAM_INT);
	if ($zipname == NULL) {
		$zipname = 'zip01';
	}
	$cb = fm_clean_checkbox_array();
	if ($cb != NULL) {
		$USER->fm_cb = $cb;
	}

    if (! $course = get_record('course', 'id', $id) ) {
        error("That's an invalid course id", "view.php?id={$id}&amp;rootdir={$rootdir}");
    }
	require_login($course->id);
	// Ensures the user is able to view the fmanager
	fm_check_access_rights($course->id);	
	$courselink = '';
	if ($course->id != 1) {		// To display properly at front page
		$courselink = "<a href=\"{$SESSION->fromdiscussion}\">{$course->shortname}</a> -> ";
	}

    print_header(strip_tags(get_string("filemanager", 'block_file_manager')), "$course->fullname",
        " $courselink <a href=\"view.php?id={$id}&amp;rootdir={$rootdir}\">".get_string("filemanager", 'block_file_manager')."</a> -> ".get_string('zipfiles','block_file_manager'), "", "", true, "",
        navmenu($course, 1),"","");

	if ($action == 'zipsel') {
		$headingstr = get_string('zipfiles','block_file_manager')/*.helpbutton('ziphelp',get_string('ziphelp','block_file_manager'),'filemanager',true,false,'',true)*/;
	} elseif ($action == 'unzip') {
		$headingstr = get_string('unzipfiles','block_file_manager')/*.helpbutton('unziphelp',get_string('unziphelp','block_file_manager'),'filemanager',true,false,'',true)*/;
	}
	print_heading($headingstr);
	
	switch($action) {
		case 'viewzip':
			$list = fm_view_zipped(fm_get_user_link($zipid));
			print_simple_box_start('center','375','#C0C0C0');
			echo "<table name=\"viewziptable\" cellspacing=\"5\" align=\"center\" width=\"375\">";
				echo "<tr>";
				echo "<td align='center'><b><u>".get_string('file', 'block_file_manager')."</u></b></td>";
				echo "<td align='center'><b><u>".get_string('compressedsize', 'block_file_manager')."</u></b></td>";
				echo "<td align='center'><b><u>".get_string('actualsize', 'block_file_manager')."</u></b></td>";
				echo "</tr>";
			foreach($list as $l) {
				echo "<tr>";
				echo "<td align='center'>{$l->name}</td>";
				echo "<td align='center'>".fm_readable_filesize($l->compsize)."</td>";
				echo "<td align='center'>".fm_readable_filesize($l->actualsize)."</td>";
				echo "</tr>";
			}
			echo "</table>";
			print_simple_box_end();
			break;
			
		case 'zipsel':
			if (isset($_POST['standardzip'])) {
				$destination = $CFG->dataroot."/".fm_get_user_dir_space();
				$originalfiles = array();
				foreach($USER->fm_cb as $c) {
					if ($c != 0 || substr($c,0,2) == "f-") {
						if (substr($c,0,2) == "f-") {
							$originalfiles[] = $destination.fm_get_folder_path(substr($c,2), false, $groupid);
						} else {
							$file = get_record('fmanager_link',"id",$c,"type",1);
							if ($file) {
								$originalfiles[] = $destination.fm_get_folder_path($file->folder, false, $groupid)."/".$file->link;
								}
						}
					}
				}
				$destination = $destination.fm_get_folder_path($rootdir, false, $groupid)."/";
				if (!fm_zip_files($originalfiles,$destination, $zipname, $rootdir)) {
					echo "zip: $zipname";
					error(get_string("errnozip",'block_file_manager'));
				}
				redirect("view.php?id={$id}&rootdir={$rootdir}");
			} elseif (isset($_POST['moodlezip'])) {
				$destination = $CFG->dataroot."/".fm_get_user_dir_space().fm_get_folder_path($rootdir, false, $groupid);
				$originalfiles = array();
				foreach($USER->fm_cb as $c) {
					if ($c != 0 || substr($c,0,2) == "f-") {
						if (substr($c,0,2) == "f-") {
							$originalfiles[] = $destination.fm_get_folder_path(substr($c,2), false, $groupid);
						} else {
							$file = get_record('fmanager_link',"id",$c);
							// File type
							if ($file->type == TYPE_FILE) {
								$originalfiles[] = $destination.fm_get_folder_path($file->folder, false, $groupid)."/".$file->link;
							}
						}
					}
				}
				$destination = $destination."/";
				if (!fm_zip_files($originalfiles,$destination, $zipname, $rootdir)) {
					error(get_string("errnozip",'block_file_manager'));
				}
				break;
			}
			
			$fmdir = fm_get_root_dir();
			echo "<form name=\"zipform\" method=\"post\" action=\"$CFG->wwwroot/$fmdir/zip.php?id={$id}&rootdir={$rootdir}&what=$action\">";
			
			// Prints how many files are being zipped
			print_simple_box_start("center", "500", "#C0C0C0");
			$msgbox = get_string('msgzipthese','block_file_manager');
			$count = 0;
			foreach ($USER->fm_cb as $c) {
				if ($c != 0 || substr($c,0,2) == "f-") {
					if (substr($c,0,2) == "f-") {
						$folder = get_record('fmanager_folders',"id",substr($c,2));
						$msgbox .= "<br/>&nbsp;&nbsp;&nbsp;&nbsp;<img border=\"0\" src=\"$CFG->wwwroot/blocks/file_manager/pix/folder.gif\" alt=\"".get_string('msgfolder','block_file_manager',$folder->name)."\">$folder->name";
						$count++;
					} else {
						$link = get_record('fmanager_link',"id",$c);
						if ($link->type == TYPE_URL) {
							$msgbox .= "<br/>&nbsp;&nbsp;&nbsp;&nbsp;<img src=\"$CFG->wwwroot/blocks/file_manager/pix/www.gif\" >$link->name &nbsp;&nbsp;&nbsp;&nbsp;<font color=\"red\">".get_string("msgnotincludedzip",'block_file_manager')."</font>";
						} elseif ($link->type == TYPE_FILE) {
							$msgbox .= "<br/>&nbsp;&nbsp;&nbsp;&nbsp;<img src=\"$CFG->wwwroot/blocks/file_manager/pix/file.gif\" >$link->name";
						} elseif ($link->type == TYPE_ZIP) {
							$msgbox .= "<br/>&nbsp;&nbsp;&nbsp;&nbsp;<img src=\"$CFG->wwwroot/blocks/file_manager/pix/zip.gif\" >$link->name";
						}	
						$count++;
					}
				}
			}
			echo "<b><i>".$count."</i></b>".$msgbox;
			echo "<br/><br/>".get_string('zipname','block_file_manager').": <input name=\"zipname\" value=\"zip01\"><br/><br/>";
			echo "<center>";
			echo "<input type=\"submit\" name=\"standardzip\" value=\"".get_string('btnstandardzip','block_file_manager')."\">";
			echo "&nbsp;&nbsp;<input type=\"submit\" name=\"moodlezip\" value=\"".get_string('btnmoodlezip','block_file_manager')."\">";
			echo "</center>";
			print_simple_box_end();
			echo "</form>";
			break;
			
		case 'unzip':
			if (isset($_POST['cancel'])) {
				redirect("link_manage.php?id=$id&linkid=$zipid&rootdir=$rootdir");
			} else if (isset($_POST['unzip'])) {
				$zipfile = fm_get_user_link($zipid);
				$zipfileloc = $CFG->dataroot."/".fm_get_user_dir_space().fm_get_folder_path($zipfile->folder, false, $groupid)."/".$zipfile->link;
				fm_unzip_file($zipfileloc,'',false,$zipfile->folder,$groupid);
				
				redirect("view.php?id={$id}&rootdir={$rootdir}");
			}
			$list = fm_view_zipped(fm_get_user_link($zipid));
			$fmdir = fm_get_root_dir();
			echo "<form name=\"unzipform\" method=\"post\" action=\"$CFG->wwwroot/$fmdir/zip.php?id={$id}&rootdir={$rootdir}&zipid=$zipid&what=$action\">";
			print_simple_box_start('center','500','#C0C0C0');
			echo "<table name=\"viewziptable\" cellspacing=\"5\" align=\"center\" width=\"375\">";
				echo "<tr>";
				echo "<td align='center' nowrap><b><u>".get_string("file",'block_file_manager')."</u></b></td>";
				echo "<td align='center' nowrap><b><u>".get_string("compressedsize",'block_file_manager')."</u></b></td>";
				echo "<td align='center' nowrap><b><u>".get_string("actualsize",'block_file_manager')."</u></b></td>";
				echo "</tr>";
			foreach($list as $l) {
				echo "<tr>";
				echo "<td align='center'>$l->name</td>";
				echo "<td align='center'>".fm_readable_filesize($l->compsize)."</td>";
				echo "<td align='center'>".fm_readable_filesize($l->actualsize)."</td>";
				echo "</tr>";
			}
			echo "</table><br/><br/>";
			echo "<center><input type=\"submit\" name=\"unzip\" value=\"".get_string('btnunzip','block_file_manager')."\">&nbsp;&nbsp;";
			echo "<input type=\"submit\" name=\"cancel\" value=\"".get_string('btncancel','block_file_manager')."\"></center>";
			
			print_simple_box_end();
			echo "</form>";
		break;
	}

    print_footer($course);
?>