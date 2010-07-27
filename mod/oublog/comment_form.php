<?php

require_once($CFG->libdir.'/formslib.php');

class mod_oublog_comment_form extends moodleform {

    function definition() {

        global $CFG;

        $maxvisibility = $this->_customdata['maxvisibility'];
        $edit          = $this->_customdata['edit'];

        $mform    =& $this->_form;


        $mform->addElement('header', 'general', '');

        $mform->addElement('text', 'title', get_string('title', 'oublog'), 'size="48"');
        $mform->setType('title', PARAM_TEXT);

        $mform->addElement('htmleditor', 'message', get_string('message', 'oublog'), array('cols'=>50, 'rows'=>30));
        $mform->setType('message', PARAM_RAW);
        $mform->addRule('message', get_string('required'), 'required', null, 'client');
        $mform->setHelpButton('message', array('reading', 'writing', 'questions', 'richtext'), false, 'editorhelpbutton');

        if ($edit) {
            $submitstring = get_string('savechanges');
        } else {
            $submitstring = get_string('addcomment', 'oublog');
        }

        $this->add_action_buttons(true, $submitstring);

    /// Hidden form vars
        $mform->addElement('hidden', 'blog');
        $mform->setType('blog', PARAM_INT);

        $mform->addElement('hidden', 'post');
        $mform->setType('post', PARAM_INT);

    }
}

?>