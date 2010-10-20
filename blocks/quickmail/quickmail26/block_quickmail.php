<?php // $Id: block_quickmail.php,v 2.5 2008/02/15 09:53:43 pcali1 Exp $

/**
    Edited by: Philip Cali
    Date: 2/15/2008
    Louisiana State University
**/

/**
 * Quickmail - Allows teachers and students to email one another
 *      at a course level. This version of Quickmail disabled the group
 *      mode as it is still not stable in Moodle 1.8
 *
 * @Original author Mark Nielsen, updated by Bibek Bhattarai and Wen Hao Chuang
 * @package quickmailv2
 **/ 

/**
 * This is the Quickmail block class.  Contains the necessary
 * functions for a Moodle block.  Has some extra functions as well
 * to increase its flexibility and useability
 *
 * @package moodleblock
 * @author Mark Nielsen
 * @todo Make a global config so that admins can set the defaults (default for student (yes/no) default for groupmode (select a groupmode or use the courses groupmode)) 
 * NOTE: make sure email.php and emaillog.php use the global config settings
 **/
class block_quickmail extends block_list {    

    /**
     * Sets the block name and version number
     *
     * @return void
     * @author Mark Nielsen
     **/
    function init() {
        $this->title = get_string('blockname', 'block_quickmail'). ' v2.5';
		//$this->title = this->title.' Beta Test Version';
        $this->version = 2008021500;  // YYYYMMDDXX
    }
    
    /**
     * Limits where the block can be added.
     **/
    function applicable_formats() {
        return array('site' => false, 'my' => false, 'course' => true);
    }

    /**
     * Gets the contents of the block (course view)
     *
     * @return object An object with an array of items, an array of icons, and a string for the footer
     * @author Mark Nielsen
     **/
    function get_content() {
        global $USER, $CFG, $COURSE;

        if($this->content !== NULL) {
            return $this->content;
        }
        
        $this->content = new stdClass;
        $this->content->footer = '';
        $this->content->items = array();
        $this->content->icons = array();
        
        if (empty($this->instance)) {
            return $this->content;
        }

    /// load defaults (will only load if config is empty)
        $this->load_defaults();

        $this->load_course();
        
        if ($this->check_permission('block/quickmail:cansend', CONTEXT_COURSE, $COURSE) || $this->allow_students_to_email($USER->id)) {
                
    /// link to composing an email
    /// here we revised a little bit to add a question mark for HELP button - Wen Hao Chuang
        $this->content->icons[] = '<img src="'.$CFG->pixpath.'/i/email.gif" height="16" width="16" alt="'.get_string('email').'" />';
        $this->content->items[] = '<a href="'.$CFG->wwwroot.'/blocks/quickmail/email.php?id='.$this->course->id.'&instanceid='.$this->instance->id.'">'.
                                    get_string('composeemail', 'block_quickmail').'</a>'.'	<a target="popup" title="Quickmail" href="../help.php?module=moodle&file=quickmail.html" onclick="return openpopup(\'/help.php?module=moodle&file=quickmail.html\', \'popup\', \'menubar=0,location=0,scrollbars,resizable,width=500,height=400\', 0);"><img height="17" width="17" alt="quickmail" src="../pix/help.gif" /></a>';

        

        if ($this->check_permission('block/quickmail:cansend', CONTEXT_COURSE, $COURSE)) {
    /// link to history log
        $this->content->icons[] = '<img src="'.$CFG->pixpath.'/t/log.gif" height="14" width="14" alt="'.get_string('log').'" />';
       $this->content->items[] = '<a href="'.$CFG->wwwroot.'/blocks/quickmail/emaillog.php?id='.$this->course->id.'&instanceid='.$this->instance->id.'">'.
                                   get_string('emailhistory', 'block_quickmail').'</a>';

        }
    
        }

    /// link to config for teachers
        if ($this->check_permission('block/quickmail:canconfig', CONTEXT_COURSE, $COURSE)) {
            //$this->content->footer = "<a href=\"$CFG->wwwroot/course/view.php?id={$this->instance->pageid}&instanceid={$this->instance->id}&sesskey=$USER->sesskey&blockaction=config\">".
              $this->content->footer = '<a href="'. $CFG->wwwroot.'/blocks/quickmail/quickmail_config.php?id='.$this->course->id.'&instanceid='.$this->instance->id.'">'.
              get_string('settings').'...</a>';
        }
        
        return $this->content;
    }
    
    /**
     * Allows the block to be configurable at an instance level.
     *
     * @return boolean
     * @author Mark Nielsen
     **/
    function instance_allow_config() {
        return false;
    }
   
    /**
     * New validation method checks by role capability
     *
     */
    function check_permission($action, $context_constant, $course) {
        switch ($context_constant) {
            case CONTEXT_COURSE:
                $context = get_context_instance ($context_constant, $course->id);
                break;
            case CONTEXT_BLOCK:
                $context = get_context_instance ($context_constant, $this->instance->id);
            default:
                return false;
        }
        return has_capability($action, $context);
    }

    /**
     * Special validation method for allowing student to use quickmail
     *
     */
    function allow_students_to_email($userid) {
        global $CFG;

        if (!$this->config->allowstudents) {
            return false;
        }

        $sql = "SELECT COUNT(*)
                 FROM {$CFG->prefix}role_assignments ra,
                      {$CFG->prefix}role r
                 WHERE r.id = ra.roleid
                   AND r.shortname = 'student'
                   AND ra.userid = {$userid}";
        
        return (count_records_sql($sql) ? true : false);
    }
 
    /**
     * Get the groupmode of Quickmail.  This function pays
     * attention to the course group mode force.
     *
     * @return int The group mode of the block
     * @author Mark Nielsen
     **/
    function groupmode() {
        $this->load_course();
                
        if ($this->course->groupmodeforce) {
            return $this->course->groupmode;
        } else {
            return $this->config->groupmode;
        }
    }

 
    /**
     * Loads default config data when config is empty (that way we know it exists).
     *
     * Defaults:
     *      group mode           = course group mode
     *      allow student access = yes
     * @return void
     * @author Mark Nielsen
     * @todo Make a global config so that admins can set the defaults (default for student (yes/no) default for groupmode (select a groupmode or use the courses groupmode))  NOTE: make sure email.php and emaillog.php use the global config settings
     **/
    function load_defaults() {
        if (empty($this->config)) {
        /// blank config
            global $CFG;
            $this->load_course();

            $defaults = new stdClass;
            $defaults->groupmode = $this->course->groupmode;
            $defaults->allowstudents = 0;

            //Student is a default role selection
            $student = get_record('role', 'shortname', 'student');
            
            $defaults->roleselection[$student->shortname] = $student->name;

            $this->instance_config_save($defaults);
        }
    }
    
    /**
        Convenience method to retrieve the configured role selections
    */
    function grab_roles() {
        return $this->config->roleselection;
    }


    /**
     * Loads the course record into $this->course.
     *
     * This function first checks to make sure that
     * the course is not already loaded first.  If not,
     * then grab it from the database
     *
     * @return void
     * @author Mark Nielsen
     **/
    function load_course() {
        if (empty($this->course)) {
            $this->course = get_record('course', 'id', $this->instance->pageid);
        }
    }
}

?>
