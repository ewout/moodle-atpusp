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


require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once('chapterlib.php');

class mod_presenter_mod_form extends moodleform_mod {

    function definition() {
        global $CFG, $COURSE;
        
        $mform    =& $this->_form;

        $mform->addElement('header', 'import', get_string('import', 'presenter'));
        $div = '<iframe src="' . $CFG->wwwroot . '/mod/presenter/form.php?course=' . $COURSE->id . '&section=' . $this->_section . '" width="100%" frameborder="0" scrolling="no" height="65">';
	    $div .= '</iframe>';
	    $mform->addElement('html', $div);
	    
	    if ($this->_instance) {
	    	$mform->addElement('header', 'export', get_string('export_this', 'presenter'));
	       	$div = '<iframe src="' . $CFG->wwwroot . '/mod/presenter/form_export.php?course=' . $COURSE->id . '&id=' . $this->_instance . '" width="100%" frameborder="0" scrolling="no" height="75">';
	    	$div .= '</iframe>';
	    	$mform->addElement('html', $div);
	    }

        $s = $this->getStyleSheet();

        $mform->addElement('html', $s);
        
//-------------------------------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));
        
        $mform->addElement('html', '<div class="first_name">');

        $mform->addElement('text', 'name', get_string('name'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addElement('html', '</div>');

        //displaying the images for the 2 types of layouts a user can choose from

        
        $layout1HTML = '<img src="' . $CFG->wwwroot . '/mod/presenter/pix/layout_type1.gif" />';
        $layout2HTML = '<img src="' . $CFG->wwwroot . '/mod/presenter/pix/layout_type2.gif" />';
        
        $html = '
            <div class="fitem">
                <table cellpadding="0" cellspacing="0" class="table_layouts">
                    <tr><th width="50%"></th><th width="50%"></th></tr>
                    <tr>
                        <td align="right" class="img1">' . $layout1HTML . '</td>
                        <td class="img2">' . $layout2HTML . '</td>
                    </tr>
                    <tr>
                        <td class="text1">
        ';

        $mform->addElement('html', $html);
        $mform->addElement('text', 'presentation_width1', get_string('presentation_width', 'presenter'), array('value' => '900'));
        $mform->addRule('presentation_width1', null, 'numeric', null, 'client');
        
        $mform->addElement('html', '</td><td class="text2">');
        $mform->addElement('text', 'presentation_width2', get_string('presentation_width', 'presenter'), array('value' => '900'));
        $mform->addRule('presentation_width2', null, 'numeric', null, 'client');

        $mform->addElement('html', '</td></tr><tr><td class="text1">');
        $mform->addElement('text', 'presentation_height1', get_string('presentation_height', 'presenter'), array('value' => '500'));
        $mform->addRule('presentation_height1', null, 'numeric', null, 'client');

        $mform->addElement('html', '</td><td class="text2">');
        $mform->addElement('text', 'presentation_height2', get_string('presentation_height', 'presenter'), array('value' => '500'));
        $mform->addRule('presentation_height2', null, 'numeric', null, 'client');

        $mform->addElement('html', '</td></tr><tr><td class="text1">');
        $mform->addElement('text', 'player_width1', get_string('player_width', 'presenter'), array('value' => '320'));
        $mform->addRule('player_width1', null, 'numeric', null, 'client');
        
        $mform->addElement('html', '</td><td class="text2">');
        $mform->addElement('text', 'player_width2', get_string('player_width', 'presenter'), array('value' => '640'));
        $mform->addRule('player_width2', null, 'numeric', null, 'client');

        $mform->addElement('html', '</td></tr><tr><td class="text1">');
        $mform->addElement('text', 'player_height1', get_string('player_height', 'presenter'), array('value' => '240'));
        $mform->addRule('player_height1', null, 'numeric', null, 'client');
        
        $mform->addElement('html', '</td><td class="text2">');
        $mform->addElement('text', 'player_height2', get_string('player_height', 'presenter'), array('value' => '500'));
        $mform->addRule('player_height2', null, 'numeric', null, 'client');
        
        $html = '</td></tr><tr><td colspan="2">&nbsp;</td></tr></table></div><div class="windowing">';
        
        $mform->addElement('html', $html);
        
        $option = array();
        $option[0] = 'Same window';
        $option[1] = 'New window';
        $mform->addElement('select', 'window', get_string('window', 'presenter'), $option);
        $mform->setDefault('window', 0);
        
        $options = array();
        $options[0] = 'Default';
        $options[1] = 'Tube';
        /*$options[2] = 'Stylish';
        $options[3] = 'Simple';
        $options[4] = 'HD plugin';*/
        $mform->addElement('select', 'player_skin', get_string('player_skin', 'presenter'), $options);
        $mform->setDefault('player_skin', 0);
        
        $options = array();
        $options[0] = 'bottom';
        $options[1] = 'over';
        $options[2] = 'none';
        $mform->addElement('select', 'control_bar', get_string('control_bar', 'presenter'), $options);
        $mform->setDefault('control_bar', 0);

        $options = array();
        $options[0] = 'uniform';
        $options[1] = 'exact fit';
        $options[2] = 'fill';
        $mform->addElement('select', 'player_streching', get_string('player_streching', 'presenter'), $options);
        $mform->setDefault('player_streching', 0);
        
        $mform->addElement('text', 'volume', get_string('volume', 'presenter'), array('size'=>'7', 'value' => '60'));
        $mform->addRule('volume', null, 'numeric', null, 'client');
        
        $mform->addElement('text', 'buffer_length', get_string('buffer_length', 'presenter'), array('size' => '7', 'value' => '3'));
        $mform->addRule('buffer_length', null, 'numeric', null, 'client');
        
        $options = array('uniform', 'exact fit', 'fill');
        $mform->addElement('select', 'slide_streching', get_string('slide_streching', 'presenter'), $options);
        $mform->setDefault('slide_streching', 0);
        
        $mform->addElement('text', 'summary_height', get_string('summary_height', 'presenter'), array('size' => '7'));
        $mform->addRule('summary_height', null, 'numeric', null, 'client');

        $mform->addElement('html', '</div>');
        
//-------------------------------------------------------------------------------
//-----------------javascript for adding multiple chapters-----------------------
		$script = '<script type="text/javascript" src="' . $CFG->wwwroot . '/mod/presenter/chapters.js"></script>';
    	if ($this->_instance){
            $repeatno = count_records('presenter_chapters', 'presenterid', $this->_instance);
        } else {
            $repeatno = 1;
        }

        $repeatarray = array();
		
        $repeatarray[] = &$mform->createElement('header', 'chapter', get_string('chapter', 'presenter').' {no}');
        $theme = current_theme();

        $showThis = '<input class="flag" type="image" src="'. $CFG->wwwroot . '/theme/'.$theme.'/pix/i/one.gif" style="float: right; margin-right: 17px;" title="Show only this chapter" onclick="showOnly(this);return false" />';
        $showAll = '<input type="image" src="'. $CFG->wwwroot . '/theme/'.$theme.'/pix/i/all.gif" style="display: none; float: right; margin-right: 17px;" title="Show all chapters" onclick="showAll(this);return false;" />';
        
        $repeatarray[] = &$mform->createElement('html', $showThis);
        $repeatarray[] = &$mform->createElement('html', $showAll);
        
        $style = 'float: right; background: url(' . $CFG->wwwroot . '/theme/'.$theme.'/pix/t/up.gif);padding:0;display: block;width: 11px; height: 11px;border: 0;cursor: pointer; margin-right: 20px;margin-top: 5px;';
        $style1 = 'float: right; background: url(' . $CFG->wwwroot . '/theme/'.$theme.'/pix/t/down.gif);padding:0;display: block;width: 11px; height: 11px;border: 0;cursor: pointer; margin-right: 20px;margin-top: 5px;';
        $style2 = 'float: right; background: url(' . $CFG->wwwroot . '/theme/'.$theme.'/pix/t/delete.gif);padding:0;display: block;width: 11px; height: 11px;border: 0;cursor: pointer; margin-right: 20px;margin-top: 5px;';
        
        $btn = '<div style="clear:both"></div><button style="' . $style2 . '" title="'. get_string('remove', 'presenter') .'" onclick="remove(this); return false;">' . '</button>';
        $repeatarray[] = &$mform->createElement('html', $btn);
        
        $btn = '<div style="clear:both"></div><button style="' . $style . '" title="' . get_string('move_up', 'presenter') . '" onclick="moveUp(this); return false;">&nbsp;' . '</button>';
        $repeatarray[] = &$mform->createElement('html', $btn);
        
        $btn = '<div style="clear:both"></div><button style="' . $style1 . '" title="' . get_string('move_down', 'presenter') . '" onclick="moveDown(this); return false;">' .  '</button>';
        $repeatarray[] = &$mform->createElement('html', $btn);
        
        $radio[] = &$mform->createElement('radio', 'layout', null,  '<img src="' . $CFG->wwwroot . '/mod/presenter/pix/layout1.gif" />', '1', array("class" => "radioBut", "id" => "id_radio", "onclick" => "enableDisable(this);"));
        $radio[] = &$mform->createElement('radio', 'layout', null,  '<img src="' . $CFG->wwwroot . '/mod/presenter/pix/layout2.gif" />', '2', array("class" => "radioBut", "id" => "id_radio", "onclick" => "enableDisable(this);"));
        $radio[] = &$mform->createElement('radio', 'layout', null,  '<img src="' . $CFG->wwwroot . '/mod/presenter/pix/layout3.gif" />', '3', array("class" => "radioBut", "id" => "id_radio", "onclick" => "enableDisable(this);"));
        $radio[] = &$mform->createElement('radio', 'layout', null,  '<img src="' . $CFG->wwwroot . '/mod/presenter/pix/layout4.gif" />', '4', array("class" => "radioBut", "id" => "id_radio", "onclick" => "enableDisable(this);"));
        $radio[] = &$mform->createElement('radio', 'layout', null,  '<img src="' . $CFG->wwwroot . '/mod/presenter/pix/layout5.gif" />', '5', array("class" => "radioBut", "id" => "id_radio", "onclick" => "enableDisable(this);"));
        $radio[] = &$mform->createElement('radio', 'layout', null,  '<img src="' . $CFG->wwwroot . '/mod/presenter/pix/layout6.gif" />', '6', array("class" => "radioBut", "id" => "id_radio", "onclick" => "enableDisable(this);"));

        $html = '<div class="radio_buttons">';
        $repeatarray[] = &$mform->createElement('html', $html);
        $repeatarray[] = &$mform->createElement('group', 'layout', '', $radio, '');
        $html = '</div>';
        
        $repeatarray[] = &$mform->createElement('html', $html);
        $repeatarray[] = &$mform->createElement('hidden', 'deleted', 'false', array('class' => 'delete'));
        $repeatarray[] = &$mform->createElement('hidden', 'showOnly', 'false', array('class' => 'showOnlyThis'));
        
        $repeatarray[] = &$mform->createElement('text', 'chapter_name', get_string('chapter_name', 'presenter'), array('class' => "names"));
        
        $repeatarray[] = &$mform->createElement('choosecoursefile', 'video_link', get_string('video_link', 'presenter'), array('width' => '1000'));
        //$mform->addRule('video_link', null, 'required', null, 'client');
        
        $repeatarray[] = &$mform->createElement('text', 'video_start', get_string('video_start', 'presenter'), array('value' => '0'));
        //$mform->addRule('video_start', null, 'numeric', null, 'client');
        
        $repeatarray[] = &$mform->createElement('text', 'video_end', get_string('video_end', 'presenter'), array('value' => '0'));
        //$mform->addRule('video_end', null, 'numeric', null, 'client');
        
        $repeatarray[] = &$mform->createElement('choosecoursefile', 'audio_track', get_string('audio_track', 'presenter'), array('width' => '1000'));
        //$mform->addRule('audio_track', null, 'required', null, 'client');
        
        $repeatarray[] = &$mform->createElement('hidden', 'audio_start', get_string('audio_start', 'presenter'), array('value' => '0'));
        //$mform->addRule('audio_start', null, 'numeric', null, 'client');
        
        $repeatarray[] = &$mform->createElement('text', 'audio_end', get_string('audio_end', 'presenter'), array('value' => '0'));
        //$mform->addRule('audio_end', null, 'numeric', null, 'client');
        
        $repeatarray[] = &$mform->createElement('choosecoursefile', 'slide_image', get_string('slide_image', 'presenter'), array('width' => '1000'));
        //$mform->addRule('slide_image', null, 'required', null, 'client');
        
        $repeatarray[] = $mform->createElement('htmleditor', 'summary', get_string('summary', 'presenter'), array(
		    'canUseHtmlEditor'=>'detect',
		    'rows'  => 10, 
		    'cols'  => 65, 
		    'width' => 0,
		    'height'=> 500, 
		    'course'=> 0,
		));
        
        $repeatarray[] = &$mform->createElement('hidden', 'order_id', $repeatno, array('class' => 'order_id'));
        
        
        $repeateloptions = array();
        $nr = $this->repeat_elements($repeatarray, $repeatno,
                    $repeateloptions, 'nr_chapters', 'add_chapters', 1, 'Add new chapter');
                    
        $md5Script = '<script type="text/javascript" src="' . $CFG->wwwroot . '/mod/presenter/md5.js"></script>';
        $mform->addElement('html', $md5Script);
        $sc = '<script type="text/javascript">
					document.getElementById("mform1").onsubmit = function() {return checkValues();};
					wwwroot = "' . $CFG->wwwroot . '";
					courseID = "' . $COURSE->id . '";
					injector_path = "' . $CFG->metadata_injector . '";
			   </script>';
        $mform->addElement('html', $sc);
        $mform->addElement('html', $script);
        
//-------------------------------------------------------------------------------        

//-------------------------------------------------------------------------------
        $features = new stdClass;
        $features->groups = false;
        $features->groupings = true;
        $features->groupmembersonly = true;
        $this->standard_coursemodule_elements($features);
//-------------------------------------------------------------------------------
// buttons
        $this->add_action_buttons();
        
    }

    function set_data($default_values)
    {
    	if (optional_param('removechapter', '', PARAM_RAW)) {
    		foreach ($_POST['removechapter'] as $k=>$v) {
    			break;
    		}
    		unset($_POST['chapter_name'][0]);
    		die;
    	}
    	parent::set_data($default_values);
    }
    /**
     * Enforce defaults here
     *
     * @param array $default_values Form defaults
     * @return void
     **/
    function data_preprocessing(&$default_values) {
        global $module;
		        
        if (!empty ($this->_instance) && $chapters = get_chapters($this->_instance)) {
        	$presenter = get_record('presenter', 'id', $this->_instance);
        	if ($presenter->control_bar == 'over') {
        		$default_values['control_bar'] = 1;
        	} else if ($presenter->control_bar == 'none') {
        		$default_values['control_bar'] = 2;
        	}
        	if ($presenter->player_streching == 'exactfit') {
        		$default_values['player_streching'] = 1;
        	} else if ($presenter->player_streching == 'fill') {
        		$default_values['player_streching'] = 2;
        	}
        	if ($presenter->slide_streching == 'exactfit') {
        		$default_values['slide_streching'] = 1;
        	} else if ($presenter->slide_streching == 'fill') {
        		$default_values['slide_streching'] = 2;
        	}
        	$i = 0;
        	foreach ($chapters as $chapter) {
        		$default_values['chapter_name'][$i] = $chapter->chapter_name;
        		$default_values['video_link'][$i] = $chapter->video_link;
        		$default_values['video_start'][$i] = $chapter->video_start;
        		$default_values['video_end'][$i] = $chapter->video_end;
        		$default_values['audio_track'][$i] = $chapter->audio_track;
        		$default_values['audio_start'][$i] = $chapter->audio_start;
        		$default_values['audio_end'][$i] = $chapter->audio_end;
        		$default_values['slide_image'][$i] = $chapter->slide_image;
        		$default_values['summary'][$i] = $chapter->summary;
        		$default_values['completion_factor'][$i] = $chapter->completion_factor;
        		$default_values['layout[' . $i . '][layout]'] = $chapter->layout;
        		$i++;
        	}
        }
        
    }

    /**
     * Enforce validation rules here
     *
     * @param object $data Post data to validate
     * @return array
     **/
    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (empty($data['maxtime']) and !empty($data['timed'])) {
            $errors['timedgrp'] = get_string('err_numeric', 'form');
        }

        return $errors;
    }

    function getStyleSheet()
    {
        global $CFG;
        return '<style type="text/css">' . file_get_contents($CFG->wwwroot . "/mod/presenter/style_presenter.css") . '</style>';
    }
}
?>

