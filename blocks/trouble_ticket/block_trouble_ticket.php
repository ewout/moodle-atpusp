<?php
/**
 * Block Trouble Ticket
 *
 * @author Jason Hardin
 * @version $Id: block_trouble_ticket.php,v 2.0 2007/04/09 jason Exp $
 * @package block_trouble_ticket
 **/

/**
 * Trouble Block Class Definition
 *
 * @package block_trouble_ticket
 * @author Jason Hardin
 **/
class block_trouble_ticket extends block_base {

    /**
     * Title and version
     *
     * @return void
     **/
    function init() {
        $this->title = get_string('title', 'block_trouble_ticket');
        $this->content_type = BLOCK_TYPE_TEXT;
        $this->version = 2007090400;
    }

    /**
     * We only want this block used in course-view and the site
     *
     * @return array
     */
    function applicable_formats() {
        return array('all'=>false,'course-view' => true, 'site' => true);
    }

    /**
     * Set instance variables
     *
     * @return void
     **/
    function specialization() {
        // set the block title
        if (!empty($this->config) && !empty($this->config->title)) {
            $this->title = $this->config->title;
        } else {
            //we're displaying on the site page or in a course or on a blog
            $this->title = get_string('title', 'block_trouble_ticket');
        }
    }

    /**
     * Block instance content.  Link or button to trouble ticket interface.
     *
     * @return object
     **/
    function get_content() {
        global $USER, $CFG;

        if ($this->content !== NULL) {
            return $this->content;
        }
        $this->content = new stdClass;
        $this->content->footer = '';
        $this->content->text = ''; //empty to start, will be populated below
        $this->content->header = $this->title;

        if (empty($this->instance)) {
            // We're being asked for content without an associated instance
            return $this->content;
        }

        $fromcourse = $this->instance->pageid;

        //init subject, email and displaytype to defaults
        if (isset($CFG->block_trouble_ticket_address)) {
            $address = $CFG->block_trouble_ticket_address;
        } else {
            $admin = get_admin();
            $address = $admin->email;
        }
        $subject = '';
        $linktext = $this->title;
        if (isset($CFG->block_trouble_ticket_autoreply)) {
            $autoreply = $CFG->block_trouble_ticket_autoreply;
        } else {
            $autoreply = get_string('autoreply', 'block_trouble_ticket');
        }
        // since the autoreply seems to filter out html, put in a separate
        // field for the link
        if (isset($CFG->block_trouble_ticket_autoreply_url)) {
            $autoreplyurl = $CFG->block_trouble_ticket_autoreply_url;
        } else {
            $autoreplyurl = '';
        }
        if (isset($CFG->block_trouble_ticket_autoreply_linktext)) {
            $autoreplylinktext = $CFG->block_trouble_ticket_autoreply_linktext;
        } else {
            $autoreplylinktext = '';
        }
        if (isset($CFG->block_trouble_ticket_displaytype)) {
            $displaytype = $CFG->block_trouble_ticket_displaytype;
        } else {
            $displaytype = 0;
        }

        //set subject, email, linktext and displaytype to stored values if present
        if (!empty($this->config)) {
            if (!empty($this->config->address)) {
                $address = $this->config->address;
            }
            if (!empty($this->config->subject)) {
                $subject = $this->config->subject;
            }
            if (!empty($this->config->linktext)) {
                $linktext = $this->config->linktext;
            }
            if (!empty($this->config->autoreply)) {
                $autoreply = $this->config->autoreply;
            }
            if (!empty($this->config->autoreplyquote)) {
                $autoreplyurl = $this->config->autoreplyurl;
            }
            if (!empty($this->config->autoreplylinktext)) {
                $autoreplylinktext = $this->config->autoreplylinktext;
            }
            if (!empty($this->config->displaytype)) {
                $displaytype = $this->config->displaytype;
            }
        }

        $this->content->text = '<center>';

        //check our configuration setting to see what format we should display
        // 0 == display a form button
        // 1 == display a link
        if ($displaytype == 1) {
            $this->content->text .= '<a href="'. $CFG->wwwroot .'/blocks/trouble_ticket/ticket.php?id='. $fromcourse .'&amp;instanceid='.$this->instance->id.'&amp;subject='. $subject .'">';
            $this->content->text .=  $linktext;
            $this->content->text .=  '</a>';
        } else {
            $this->content->text .= '<form name="form" method="post" action="'. $CFG->wwwroot .'/blocks/trouble_ticket/ticket.php">';
            $this->content->text .= '<input type="hidden" name="id" value="'. $fromcourse .'" />';
            $this->content->text .= '<input type="submit" name="Submit" value="'. $linktext .'" />';
            $this->content->text .= '<input type="hidden" name="subject" value="'. $subject .'" />';
            $this->content->text .= '</form>';
        }
        $this->content->text .= '</center>';
        return $this->content;
    }

    /**
     * Has global config
     *
     * @return boolean
     **/
    function has_config() {
        return true;
    }

    /**
     * Modify the global config
     * save routine.
     *
     * @return boolean
     **/
    function config_save($data) {
        // Convert this setting into a string for storage
        if (isset($data->block_trouble_ticket_profilefields)) {
            $data->block_trouble_ticket_profilefields = implode(',', $data->block_trouble_ticket_profilefields);
        } else {
            $data->block_trouble_ticket_profilefields = '';
        }
        // If not passed, then not checked - disable
        if (!isset($data->block_trouble_ticket_displaytoaddress)) {
            $data->block_trouble_ticket_displaytoaddress = 0;
        }

        return parent::config_save($data);
    }

    /**
     * Has instance config
     *
     * @return boolean
     **/
    function instance_allow_config() {
        return true;
    }

    /**
     * Gets the user profile fields
     * that can be optionally sent
     * along with the trouble ticket
     * email.  Arranged array(fieldname => display string)
     *
     * @return array
     **/
    function get_profile_fields() {
        return array('city' => $this->profile_field_label('city'),
                     'country' => $this->profile_field_label('country'));

        // Here is a semi-general way to do this - not all get_string calls work though
        // To get around this - modify array in profile_field_label() to get the
        // correct string call
        // $fieldnames = get_user_fieldnames();
        // foreach ($fieldnames as $fieldname) {
        //     $fields[$fieldname] = $this->profile_field_label($fieldname);
        // }
    }

    /**
     * Gets a string value for a profile field
     *
     * @param string $field Profile field name
     * @return string
     **/
    function profile_field_label($field) {
        // Some user profile field names do not directly translate
        // to a get_string call, example: phone1 maps to phone
        // Define any of those here as array(fieldname => get string key)
        $exceptions = array();

        if (array_key_exists($field, $exceptions)) {
            return get_string($exceptions[$field]);
        } else {
            return get_string($field);
        }
    }
}
?>