<?php // $Id: config_form.php,v 2.5 2008/02/15 09:53:43 pcali1 Exp $

/**
 *   Author: Philip Cali
 *   Date: 2/15/08
 *   Louisiana State University
 *   
 *   Moodle form for configuring the Quickmail block
 */

require_once($CFG->libdir . '/formslib.php');

define('STUDENT_SELECT', 'allowstudents');
define('ROLE_SELECT', 'roleselection');
define('COURSE_ID', 'id');
define('INSTANCE_ID', 'instanceid');

class config_form extends moodleform {
    
    function definition() {
        global $CFG, $USER;

        $form=&$this->_form;

        /*original select*/
        $student_select = array(0 => get_string('no'), 1=>get_string('yes'));
        $form->addElement('select', STUDENT_SELECT, get_string('allowstudents', 'block_quickmail'), $student_select);


        $form->addElement('static', 'static_role', get_string('select_roles', 'block_quickmail'), '');
        
        $roles = get_records('role');
        $role_select = array();
        foreach ($roles as $role) {
            $form->addElement('checkbox', ROLE_SELECT. ':'. $role->shortname, '', $role->name);
        }

        //hidden fields needed to process the form
        $form->addElement('hidden', COURSE_ID, '');
        $form->addElement('hidden', INSTANCE_ID, '');

        $buttonarray = array();
        $buttonarray[] = &$form->createElement('submit', 'submitbutton', 
                                 get_string('submit', 'block_quickmail'));
        $buttonarray[] = &$form->createElement('cancel');
        $form->addGroup($buttonarray, 'buttonarr', '', array(' '), false);
        $form->closeHeaderBefore('buttonarr');
    }
}

?>
