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
    //This function executes all the backup procedure about this mod
    function presenter_backup_mods($bf, $preferences) {

        global $CFG;

        $status = true;

        //Iterate over presenter table
        $presenters = get_records("presenter", "course", $preferences->backup_course, "id");
        if ($presenters) {
            foreach ($presenters as $presenter) {
                if (backup_mod_selected($preferences,'presenter',$presenter->id)) {
                    $status = presenter_backup_one_mod($bf,$preferences,$presenter);
                }
            }
        }
        return $status;  
    }

    function presenter_backup_one_mod($bf,$preferences,$presenter) {

        global $CFG;
    
        if (is_numeric($presenter)) {
            $presenter = get_record('presenter','id',$presenter);
        }
    
        $status = true;

        //Start mod
        fwrite ($bf,start_tag("MOD",3,true));
        //Print presenter data
        fwrite ($bf,full_tag("ID",4,false,$presenter->id));
        fwrite ($bf,full_tag("MODTYPE",4,false,"presenter"));
        fwrite ($bf,full_tag("NAME",4,false,$presenter->name));
        fwrite ($bf,full_tag("NR_CHAPTERS",4,false,$presenter->nr_chapters));
        fwrite ($bf,full_tag("PRESENTATION_WIDTH1",4,false,$presenter->presentation_width1));
        fwrite ($bf,full_tag("PRESENTATION_HEIGHT1",4,false,$presenter->presentation_height1));
        fwrite ($bf,full_tag("PRESENTATION_WIDTH2",4,false,$presenter->presentation_width2));
        fwrite ($bf,full_tag("PRESENTATION_HEIGHT2",4,false,$presenter->presentation_height2));
        fwrite ($bf,full_tag("PLAYER_WIDTH1",4,false,$presenter->player_width1));
        fwrite ($bf,full_tag("PLAYER_HEIGHT1",4,false,$presenter->player_height1));
        fwrite ($bf,full_tag("PLAYER_WIDTH2",4,false,$presenter->player_width2));
        fwrite ($bf,full_tag("PLAYER_HEIGHT2",4,false,$presenter->player_height2));
        
        fwrite ($bf,full_tag("WINDOW",4,false,$presenter->window));
        fwrite ($bf,full_tag("PLAYER_SKIN",4,false,$presenter->player_skin));
        fwrite ($bf,full_tag("CONTROL_BAR",4,false,$presenter->control_bar));
        fwrite ($bf,full_tag("PLAYER_STRECHING",4,false,$presenter->player_streching));
        fwrite ($bf,full_tag("VOLUME",4,false,$presenter->volume));
        fwrite ($bf,full_tag("BUFFER_LENGTH",4,false,$presenter->buffer_length));
        fwrite ($bf,full_tag("SLIDE_STRECHING",4,false,$presenter->slide_streching));
        fwrite ($bf,full_tag("SUMMARY_HEIGHT",4,false,$presenter->summary_height));
        
        
        //Now we backup presenter chapters
        $status = backup_presenter_chapters($bf,$preferences,$presenter->id);
        if ($status) {
        	if ($preferences->backup_users == 1) {
	        	$status = backup_presenter_chapters_users($bf, $preferences, $presenter->id);
        	}
        }
        //End mod
        if ($status) {
            $status =fwrite ($bf,end_tag("MOD",3,true));
        }

        return $status;
    }
    
    function backup_presenter_chapters_users($bf, $preferences, $presenterid) {
    	$status = true;
    	global $CFG;
    
    	if ($items = get_records_sql("SELECT * FROM $CFG->prefix"."presenter_chapters_users WHERE presenter_id=$presenterid AND course_id=$preferences->backup_course")) {
    
    		$status = fwrite ($bf, start_tag("COMPLETIONS", 4, true));
    		foreach ($items as $item) {
    			fwrite ($bf, start_tag("COMPLETION", 5, true));
    			
    			fwrite ($bf, full_tag("COURSE_ID", 6, false, $item->course_id));
    			fwrite ($bf, full_tag("CHAPTER_ID", 6, false, $item->chapter_id));
    			fwrite ($bf, full_tag("PRESENTER_ID", 6, false, $item->presenter_id));
    			fwrite ($bf, full_tag("USERNAME", 6, false, $item->username));
    			
    			fwrite ($bf, end_tag("COMPLETION", 5, true));
    		}
    		$status = fwrite ($bf, end_tag("COMPLETIONS", 4, true));
    	}
    	
    	return $status;
    }

    //Backup presenter_chapters contents (executed from presenter_backup_mods)
    function backup_presenter_chapters ($bf, $preferences, $presenterid) {

        global $CFG;

        $status = true;

        // run through the chapters in their logical order, get the first page
        if ($chapters = get_records("presenter_chapters", "presenterid", $presenterid)) {
            //Write start tag
            $status =fwrite ($bf,start_tag("CHAPTERS",4,true));
            //Iterate over each page
            foreach ($chapters as $chapter) {
                //Start of page
                $status =fwrite ($bf,start_tag("CHAPTER",5,true));
                //Print page contents (prevpageid and nextpageid not needed)
                fwrite ($bf,full_tag("ID",6,false,$chapter->id)); 
                fwrite ($bf,full_tag("ORDER_ID",6,false,$chapter->order_id));
                fwrite ($bf,full_tag("PRESENTERID",6,false,$chapter->presenterid));
                fwrite ($bf,full_tag("CHAPTER_NAME",6,false,$chapter->chapter_name));
                fwrite ($bf,full_tag("VIDEO_LINK",6,false,$chapter->video_link));
                fwrite ($bf,full_tag("VIDEO_START",6,false,$chapter->video_start));
                fwrite ($bf,full_tag("VIDEO_END",6,false,$chapter->video_end));
                fwrite ($bf,full_tag("AUDIO_TRACK",6,false,$chapter->audio_track));
                fwrite ($bf,full_tag("AUDIO_START",6,false,$chapter->audio_start));
                fwrite ($bf,full_tag("AUDIO_END",6,false,$chapter->audio_end));
                fwrite ($bf,full_tag("SLIDE_IMAGE",6,false,$chapter->slide_image));
                fwrite ($bf,full_tag("SUMMARY",6,false,$chapter->summary));
                fwrite ($bf,full_tag("LAYOUT",6,false,$chapter->layout));
                
                //End of chapter
                $status =fwrite ($bf,end_tag("CHAPTER",5,true));
            }
            //Write end tag
            $status =fwrite ($bf,end_tag("CHAPTERS",4,true));
        }
        return $status;
    }
    
    //Return an array of info (name,value)
    function presenter_check_backup_mods($course,$user_data=false,$backup_unique_code,$instances=null) {
        if (!empty($instances) && is_array($instances) && count($instances)) {
            $info = array();
            foreach ($instances as $id => $instance) {
                $info += presenter_check_backup_mods_instances($instance,$backup_unique_code);
            }
            return $info;
        }
        //First the course data
        $info[0][0] = get_string("modulenameplural","presenter");
        if ($ids = presenter_ids($course)) {
            $info[0][1] = count($ids);
        } else {
            $info[0][1] = 0;
        }

        //Now, if requested, the user_data
        if ($user_data) {
            $info[1][0] = get_string("attempts","presenter");
            if ($ids = presenter_attempts_ids_by_course ($course)) { 
                $info[1][1] = count($ids);
            } else {
                $info[1][1] = 0;
            }
        }
        return $info;
    }

    //Return an array of info (name,value)
    function presenter_check_backup_mods_instances($instance,$backup_unique_code) {
        //First the course data
        $info[$instance->id.'0'][0] = '<b>'.$instance->name.'</b>';
        $info[$instance->id.'0'][1] = '';

        return $info;
    }

    //Return a content encoded to support interactivities linking. Every module
    //should have its own. They are called automatically from the backup procedure.
    function presenter_encode_content_links ($content,$preferences) {

        global $CFG;

        $base = preg_quote($CFG->wwwroot,"/");

        //Link to the list of presenters
        $buscar="/(".$base."\/mod\/presenter\/index.php\?id\=)([0-9]+)/";
        $result= preg_replace($buscar,'$@presenterINDEX*$2@$',$content);

        //Link to presenter view by moduleid
        $buscar="/(".$base."\/mod\/presenter\/view.php\?id\=)([0-9]+)/";
        $result= preg_replace($buscar,'$@presenterVIEWBYID*$2@$',$result);

        return $result;
    }

    // INTERNAL FUNCTIONS. BASED IN THE MOD STRUCTURE

    //Returns an array of presenter id 
    function presenter_ids ($course) {

        global $CFG;

        return get_records_sql ("SELECT l.id, l.course
                                 FROM {$CFG->prefix}presenter l
                                 WHERE l.course = '$course'");
    }
    
?>
