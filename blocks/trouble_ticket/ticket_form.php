<?php
 /**
 * Ticket Form
 *
 * This file defines the ticket form for inclusion by ticket.php.
 *
 * @author Jason Hardin
 * @author Sam Chaffee
 * @version $Id: ticket_form.php,v 2.0 2007/09/04
 * @package block_trouble_ticket
 **/
require_once("$CFG->libdir/formslib.php");

class block_trouble_ticket_form extends moodleform {

    /**
     * Form Definition
     *
     * @return void
     **/
    function definition() {
        global $CFG, $COURSE, $USER;

        $mform =& $this->_form;

        //the header
        $mform->addElement('header', 'ticket', get_string('helpticket', 'block_trouble_ticket'));

        //the name field
        $mform->addElement('static', 'name', get_string('name').': ');
        $mform->setType('name', PARAM_TEXT);

        //the email field
        $mform->addElement('static', 'email', get_string('email').': ');
        $mform->setType('name', PARAM_TEXT);

        //the to field
        if (!isset($CFG->block_trouble_ticket_displaytoaddress) or !empty($CFG->block_trouble_ticket_displaytoaddress)) {
            $mform->addElement('static', 'to', get_string('to').': ');
            $mform->setType('to', PARAM_TEXT);
        }

        if (!empty($CFG->block_trouble_ticket_profilefields)) {
            $fields = explode(',', $CFG->block_trouble_ticket_profilefields);
            foreach ($fields as $field) {
                $mform->addElement('static', $field, block_method_result('trouble_ticket', 'profile_field_label', $field).': ');
                $mform->setType($field, PARAM_TEXT);

                if (isset($USER->$field)) {
                    $mform->setDefault($field, $USER->$field);
                }
            }
        }

        //the subject field
        $mform->addElement('text', 'subject', get_string('subject', 'block_trouble_ticket').': ');
        $mform->setType('subject', PARAM_TEXT);
        $mform->addRule('subject', get_string('required'), 'required');

        //the comments field
        $mform->addElement('htmleditor', 'comments', get_string('comments', 'block_trouble_ticket').': ');
        $mform->setType('comments', PARAM_TEXT);
        $mform->addRule('comments', get_string('required'), 'required');

        $this->set_upload_manager(new upload_manager('attachment', false, false, $COURSE, false, 0, true, true, false));
        $mform->addElement('file', 'attachment', get_string('attachment', 'block_trouble_ticket'));

        //hidden fields
        $mform->addElement('hidden', 'id');
        $mform->addElement('hidden', 'fromurl');
        $mform->addElement('hidden', 'sesskey', sesskey());
        $buttonarray = array();
        $buttonarray[] =& $mform->createElement('submit', 'submit', get_string('submit'));
        $buttonarray[] =& $mform->createElement('reset', 'reset', get_string('reset'));
        $buttonarray[] =& $mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonarr', '', array(' '), false);
        $mform->closeHeaderBefore('buttonarr');
    }
}
?>
