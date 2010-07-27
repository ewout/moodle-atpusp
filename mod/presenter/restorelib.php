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

    //This function executes all the restore procedure about this mod
    function presenter_restore_mods($mod,$restore) {

        global $CFG;

        $status = true;

        //Get record from backup_ids
        $data = backup_getid($restore->backup_unique_code,$mod->modtype,$mod->id);

        if ($data) {
            //Now get completed xmlized object
            $info = $data->info;
            //if necessary, write to restorelog and adjust date/time fields
            if ($restore->course_startdateoffset) {
                restore_log_date_changes('presenter', $restore, $info['MOD']['#'], array('AVAILABLE', 'DEADLINE'));
            }
            //traverse_xmlize($info);                                                              //Debug
            //print_object ($GLOBALS['traverse_array']);                                           //Debug
            //$GLOBALS['traverse_array']="";                                                       //Debug

            //Now, build the presenter record structure
            $presenter->course = $restore->course_id;
            $presenter->name = backup_todb($info['MOD']['#']['NAME']['0']['#']);
            $presenter->nr_chapters = backup_todb($info['MOD']['#']['NR_CHAPTERS']['0']['#']);
            $presenter->presentation_width1 = backup_todb($info['MOD']['#']['PRESENTATION_WIDTH1']['0']['#']);
            $presenter->presentation_height1 = backup_todb($info['MOD']['#']['PRESENTATION_HEIGHT1']['0']['#']);
            $presenter->presentation_width2 = backup_todb($info['MOD']['#']['PRESENTATION_WIDTH2']['0']['#']);
            $presenter->presentation_height2 = backup_todb($info['MOD']['#']['PRESENTATION_HEIGHT2']['0']['#']);
            
            $presenter->player_width1 = backup_todb($info['MOD']['#']['PLAYER_WIDTH1']['0']['#']);
            $presenter->player_height1 = backup_todb($info['MOD']['#']['PLAYER_HEIGHT1']['0']['#']);
            $presenter->player_width2 = backup_todb($info['MOD']['#']['PLAYER_WIDTH2']['0']['#']);
            $presenter->player_height2 = backup_todb($info['MOD']['#']['PLAYER_HEIGHT2']['0']['#']);
            
            $presenter->window = backup_todb($info['MOD']['#']['WINDOW']['0']['#']);
            $presenter->player_skin = backup_todb($info['MOD']['#']['PLAYER_SKIN']['0']['#']);            
            $presenter->control_bar = backup_todb($info['MOD']['#']['CONTROL_BAR']['0']['#']);
            
            $presenter->player_streching = backup_todb($info['MOD']['#']['PLAYER_STRECHING']['0']['#']);
            $presenter->volume = backup_todb($info['MOD']['#']['VOLUME']['0']['#']);
            $presenter->buffer_length = backup_todb($info['MOD']['#']['BUFFER_LENGTH']['0']['#']);
            $presenter->slide_streching = backup_todb($info['MOD']['#']['SLIDE_STRECHING']['0']['#']);
            $presenter->summary_height = backup_todb($info['MOD']['#']['SUMMARY_HEIGHT']['0']['#']);

            //The structure is equal to the db, so insert the presenter
            $newid = insert_record("presenter", $presenter);

            //Do some output
            if (!defined('RESTORE_SILENTLY')) {
                echo "<li>".get_string("modulename","presenter")." \"".format_string(stripslashes($presenter->name),true)."\"</li>";
            }
            backup_flush(300);

            if ($newid) {
                //We have the newid, update backup_ids
                backup_putid($restore->backup_unique_code,$mod->modtype,
                             $mod->id, $newid);
                //We have to restore the presenter pages which are held in their logical order...
                $userdata = restore_userdata_selected($restore,"presenter",$mod->id);
                $status = presenter_chapters_restore_mods($newid,$info,$restore,$userdata);
                
                //...and the user grades, high scores, and timer (if required)
                if ($status) {
                    
                }
            } else {
                $status = false;
            }
        } else {
            $status = false;
        }
        return $status;
    }
    
    //This function restores the presenter_pages
    function presenter_chapters_restore_mods($presenterid,$info,$restore,$userdata=false) {

        global $CFG;

        $status = true;

        //Get the presenter_elements array
        $chapters = $info['MOD']['#']['CHAPTERS']['0']['#']['CHAPTER'];
		$hold = array();
        //Iterate over presenter chapters (they are held in their logical order)
        for($i = 0; $i < sizeof($chapters); $i++) {
            $chapter_info = $chapters[$i];
            //traverse_xmlize($ele_info);                                                          //Debug
            //print_object ($GLOBALS['traverse_array']);                                           //Debug
            //$GLOBALS['traverse_array']="";                                                       //Debug

            //We'll need this later!!
            $oldid[] = backup_todb($chapter_info['#']['ID']['0']['#']);

            //Now, build the presenter_chapters record structure
            $chapter->presenterid = $presenterid;
            $chapter->order_id = backup_todb($chapter_info['#']['ORDER_ID']['0']['#']);
            $chapter->chapter_name = backup_todb($chapter_info['#']['CHAPTER_NAME']['0']['#']);
            $chapter->video_link = backup_todb($chapter_info['#']['VIDEO_LINK']['0']['#']);
            $chapter->video_start = backup_todb($chapter_info['#']['VIDEO_START']['0']['#']);
            $chapter->video_end = backup_todb($chapter_info['#']['VIDEO_END']['0']['#']);
            $chapter->audio_track = backup_todb($chapter_info['#']['AUDIO_TRACK']['0']['#']);
            $chapter->audio_start = backup_todb($chapter_info['#']['AUDIO_START']['0']['#']);
            $chapter->audio_end = backup_todb($chapter_info['#']['AUDIO_END']['0']['#']);
            $chapter->slide_image = backup_todb($chapter_info['#']['SLIDE_IMAGE']['0']['#']);
            $chapter->summary = backup_todb($chapter_info['#']['SUMMARY']['0']['#']);
            /*$chapter->completion_factor = backup_todb($chapter_info['#']['COMPLETION_FACTOR']['0']['#']);*/
            $chapter->layout = backup_todb($chapter_info['#']['LAYOUT']['0']['#']);

            //The structure is equal to the db, so insert the presenter_chapters
            $newid = insert_record ("presenter_chapters",$chapter);
            $hold[] = $newid;

            
            //Do some output
            if (($i+1) % 10 == 0) {
                if (!defined('RESTORE_SILENTLY')) {
                    echo ".";
                    if (($i+1) % 200 == 0) {
                        echo "<br/>";
                    }
                }
                backup_flush(300);
            }

            if ($newid) {
                //We have the newid, update backup_ids (restore logs will use it!!)
                backup_putid($restore->backup_unique_code,"presenter_chapters", $oldid, $newid);
                
            } else {
                $status = false;
            }
        }
        
     $items = $info['MOD']['#']['COMPLETIONS']['0']['#']['COMPLETION'];
	    for ($i = 0; $i < sizeof($items); $i++) {
	    	$item_info = $items[$i];
	    	$oldchapterid = backup_todb($item_info['#']['CHAPTER_ID']['0']['#']);
	    	$info = new stdClass();
	    	$info->presenter_id = $presenterid;
	    	$info->course_id = $restore->course_id;
	    	$info->username = backup_todb($item_info['#']['USERNAME']['0']['#']);
	    	for ($j = 0; $j < count($oldid); $j++) {
	    		if ($oldid[$j] == backup_todb($item_info['#']['CHAPTER_ID']['0']['#'])) {
	    			$info->chapter_id = $hold[$j];
	    			break;
	    		}
	    	}
	    	if (!$info->chapter_id) {
	    		$info->chapter_id = 0;
	    	}
	    	insert_record("presenter_chapters_users", $info);
	    }

        return $status;
    }


?>
