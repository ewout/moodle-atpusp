<?php

/** 
* This view allows checking deck states
* 
* @package mod-tracker
* @category mod
* @author Valery Fremaux
* @license http://www.gnu.org/copyleft/gpl.html GNU Public License
*/

/**
* Requires and includes 
*/

require_once ($CFG->dirroot.'/course/moodleform_mod.php');

/**
* overrides moodleform for test setup
*/
class mod_tracker_mod_form extends moodleform_mod {

	function definition() {

	  global $CFG, $COURSE;
	  $mform    =& $this->_form;
	  
	  //-------------------------------------------------------------------------------
	  $mform->addElement('header', 'general', get_string('general', 'form'));
	  
	  $mform->addElement('text', 'name', get_string('name'), array('size'=>'64'));
	  $mform->setType('name', PARAM_CLEANHTML);
	  $mform->addRule('name', null, 'required', null, 'client');
	  
	  $mform->addElement('htmleditor', 'description', get_string('description'));
	  $mform->setType('description', PARAM_RAW);
	  $mform->setHelpButton('description', array('writing', 'questions', 'richtext'), false, 'editorhelpbutton');
	  // $mform->addRule('summary', get_string('required'), 'required', null, 'client');
	  
      $modeoptions['bugtracker'] = get_string('mode_bugtracker', 'tracker');
      $modeoptions['ticketting'] = get_string('mode_ticketting', 'tracker');
	  $mform->addElement('select', 'supportmode', get_string('supportmode', 'tracker'), $modeoptions);
	  $mform->setHelpButton('supportmode', array('supportmode', get_string('supportmode', 'tracker'), 'tracker'));

	  $mform->addElement('text', 'ticketprefix', get_string('ticketprefix', 'tracker'), array('size' => 5));

	  $mform->addElement('selectyesno', 'enablecomments', get_string('enablecomments', 'tracker'));
	  $mform->setHelpButton('enablecomments', array('enablecomments', get_string('enablecomments', 'tracker'), 'tracker'));

	  $mform->addElement('selectyesno', 'allownotifications', get_string('notifications', 'tracker'));
	  $mform->setHelpButton('allownotifications', array('notifications', get_string('notifications', 'tracker'), 'tracker'));

      if (isset($this->_cm->id) && $assignableusers = get_users_by_capability(get_context_instance(CONTEXT_MODULE, $this->_cm->id), 'mod/tracker:resolve', 'u.id, firstname,lastname', 'lastname,firstname')){
            $useropts[0] = get_string('none');
            foreach($assignableusers as $assignable){
                $useropts[$assignable->id] = fullname($assignable);
            }
    	    $mform->addElement('select', 'defaultassignee', get_string('defaultassignee', 'tracker'), $useropts);
    	    $mform->setHelpButton('defaultassignee', array('defaultassignee', get_string('defaultassignee', 'tracker'), 'tracker'));
      } else {
    	    $mform->addElement('hidden', 'defaultassignee', 0);
      }
      
      $options['idnumber'] = true;
      $options['groups'] = false;
      $options['groupings'] = false;
      $options['gradecat'] = false;
	  $this->standard_coursemodule_elements($options);	  
	  $this->add_action_buttons();
	}

    	/*
	function definition_after_data(){
	  $mform    =& $this->_form;
	 
	  }*/
	
	function validation($data) {
	    $errors = array();
	    
	    return $errors;
	}

}
?>
