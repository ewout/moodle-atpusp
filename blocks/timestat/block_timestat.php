<?PHP
class block_timestat extends block_list {
	
    function init() {
        $this->title = get_string('blocktitle','block_timestat');
        $this->version = 2011050200;
    }
	
    function get_content() {
    	global $CFG,$COURSE, $USER;	

	$context = get_context_instance(CONTEXT_BLOCK, $this->instance->id);
	if (!has_capability('block/timestat:view', $context)){
		$this->content = NULL;
		return $this->content;
	}
	
	if (has_capability('block/timestat:view', $context) ) {
    		$this->content=new stdClass;
    		$this->content->items=array();
  			$this->content->icons=array();
    			$url= $CFG->wwwroot.'/blocks/timestat/counttime.php?param_course_id='.$COURSE->id;

			$this->content->items[] = '<a href="'.$url.'">'.get_string('link','block_timestat').'</a>';
  			$this->content->icons[] = '<img src="'.$CFG->wwwroot.'/blocks/timestat/images/zegar.gif" class="icon" alt="brak" />';
		
		$this->content->footer = NULL;
		
    	return $this->content;
	}
    }
	  
	
    function applicable_formats() {
 	 return array(
           'site-index' => false,
           'course-view' => true, 
   	   'course-view-social' => false,
           'mod' => false, 
           'mod-quiz' => false,
		   'course' => true
           );
    }
	
    function instance_allow_multiple() {
  	return false;
    }

}

?>
