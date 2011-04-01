<?php

class block_groups extends block_list {

	function init() {
		$this->title	= get_string('groups', 'block_groups');
        $this->version	= 20110301;
	}

	function get_content() {
		global $CFG, $USER, $COURSE, $SITE; 

		if ($this->content !== NULL) {
			return $this->content;
		}
        //print_r($this->instance);

        // get context
        if (!empty($this->instance->pageid)) {
            $context = get_context_instance(CONTEXT_COURSE, $this->instance->pageid);
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
        $groupings_info = get_records('block_groups_grouping_info', 'contextid', $this->instance->id);
        if (!empty($groupings_info)) {
            foreach ($groupings_info as $grouping_info) {
                $groupings_groups = get_records('groupings_groups', 'groupingid', $grouping_info->groupingid);
                if (!empty($groupings_groups)) {
                    foreach ($groupings_groups as $grouping_group){
                        $group = get_record('groups', 'id', $grouping_group->groupid);
                        if (record_exists('groups_members', 'groupid', $group->id,  'userid' , $USER->id)) {
                            $footer .= ' <a href="'.$CFG->wwwroot.'/user/index.php?id='.$this->instance->pageid.
                                       '&amp;group='.$group->id.'">'.format_string($group->name).'</a>,';
                        }
                    }
                }
            }
        }
		$this->content-> footer = $footer;

        if (has_capability('moodle/course:managegroups', $context)) {
            $this->content->items[] = '<a href="'.$CFG->wwwroot.'/blocks/groups/view.php?id='.$this->instance->id.'&course='.$this->instance->pageid.'">gerenciar grupos</a>';
		    $this->content->icons[] = '<img src="'.$CFG->pixpath.'/i/group.gif" />';
        }

		return $this->content;
	}

    

	function instance_allow_config() {
		return true;
	}

	function instance_allow_multiple() {
		return true;
	}

    function instance_config_save($data) {
        // validate form
        if (empty($data->roleids)) {
            error('Must be select one value in roles');
            return false;
        } else if (empty($data->groupids) && isset($data->withourtgroup) && !($data->withoutgroup)) {
            error('Must be select one value in groups');
            return false;
        }
        // default save values
        return parent::instance_config_save($data);
    }

	function has_config() {
		return false;
	}
}
?>
