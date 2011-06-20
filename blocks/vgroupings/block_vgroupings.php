<?php

include_once('downgrade.php');

class block_vgroupings extends block_list {

	function init() {
		$this->title	= get_string('pluginname', 'block_vgroupings');
        $this->version	= 2011062000;
	}

	function get_content() {
		global $CFG, $USER, $COURSE, $SITE, $DB, $OUTPUT;

		if ($this->content !== NULL) {
			return $this->content;
		}

        // get context
        if (!empty($this->instance->pageid)) {
            $context = get_context_instance(CONTEXT_COURSE,
                                            $this->instance->pageid);
            if ($COURSE->id == $this->instance->pageid) {
                $course = $COURSE;
            } else {
                $course = get_record('course', 'id', $this->instance->pageid);
            }
        } else {
            $context = get_context_instance(CONTEXT_SYSTEM);
            $course = $SITE;
        }


		$this->content = new stdClass();
		$this->content->items = array();
		$this->content->icons = array();

        // print groups associate
        $footer = '<center><strong>'.(isset($this->config->title)?' '.$this->config->title:'').'</strong></center><br/>';
        $footer .= '<strong>'.get_string('group').':</strong> ';

        //if ($groupings_info = $DB->get_records('block_vgroupings_info', array('modid' => $this->instance->id))) {
            
            // refactor this part to show groups in course and grouping of course
            /*
            foreach ($groupings_info as $grouping_info) {
                if ($groupings_groups = $DB->get_records('groupings_groups', array('groupingid' => $grouping_info->groupingid))) {
                    foreach ($groupings_groups as $grouping_group) {
                        $group = $DB->get_record('groups',
                            array('id' => $grouping_group->groupid));
                        if ($DB->record_exists('groups_members',
                            array('groupid' => $group->id,  'userid' => $USER->id))) {
                            $footer .= ' <a href="'.$CFG->wwwroot.
                                       '/user/index.php?id='.
                                       //$this->instance->pageid.
                                       '&amp;group='.$group->id.'">'.
                                       format_string($group->name).'</a>,';
                        }
                    }
                }
            }*/
            
        //}
        
		$this->content-> footer = $footer;
        
        if (has_capability('moodle/course:managegroups', $context)) {
            $this->content->items[] = '<a href="'.$CFG->wwwroot.'/blocks/vgroupings/grouping.php?course='.$COURSE->id.'">gerenciar agrupamentos</a>';
		    $this->content->icons[] = '<img src="'.$OUTPUT->pix_url('i/group').'" />';
        }
		return $this->content;
	}
    
	function instance_allow_config() {
		return true;
	}

	function instance_allow_multiple() {
		return false;
	}
    
	function has_config() {
		return false;
	}

}

