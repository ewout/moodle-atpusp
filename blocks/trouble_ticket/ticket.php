<?php
/**
 * Ticket
 *
 * This file generate the ticket form, processes the ticket form,
 * generates the ticket email, uploads the screen shot and send the
 * email to the address specificed in the block or site configuration.
 *
 * @author Jason Hardin
 * @author Sam Chaffee
 * @version $Id: ticket.php,v 2.0 2007/09/04
 * @package block_trouble_ticket
 **/
require_once('../../config.php');
require_once($CFG->libdir.'/blocklib.php');
require_once($CFG->dirroot.'/blocks/trouble_ticket/ticket_form.php');
if(file_exists($CFG->dirroot.'/course/format/page/lib.php')){
    require_once($CFG->dirroot.'/course/format/page/lib.php');
}

$id = optional_param('id', 0, PARAM_INT);

//If the id was not passed then we need to set it to SITEID but make 
//sure we grab the site block configuration incase there is a trouble ticket 
//block on the front page that has a different configuration.
if($id != 0){
    $noid = false;
} else {
    $noid = true;
    $id = SITEID;
}
$comments = optional_param('comments', '', PARAM_TEXT);
$subject = optional_param('subject', '', PARAM_TEXT);

if (!$course = get_record('course', 'id', $id)) {
    error(get_string('invalidcourse', 'block_trouble_ticket')." id $id");
}
if (!$blockid = get_field('block', 'id', 'name', 'trouble_ticket')) {
    error(get_string('incorrectblockinstall', 'block_trouble_ticket'));
}
if (($instance = get_record('block_instance', 'blockid', $blockid, 'pageid', $id, 'pagetype', 'course-view')) && (!$noid)) {
    $ticket = block_instance('trouble_ticket',$instance);
}

require_login($course->id);

require_capability('block/trouble_ticket:submitticket', get_context_instance(CONTEXT_COURSE, $COURSE->id));

// determine the rediect url based on if we came from a page, course or the main site
if (isset($COURSE->format) and $COURSE->format == 'page') {
    $redirect = $CFG->wwwroot.'/course/view.php?id='.$COURSE->id;

    // Try to set the page
   if ($page = page_get_current_page($COURSE->id)) {
        $redirect .= '&amp;page='.$page->id;
    }
    $navigation = "<a href=\"$redirect\">$COURSE->shortname</a> ->";
} else if ($COURSE->id != SITEID) {
    $redirect = $CFG->wwwroot.'/course/view.php?id='.$COURSE->id;
    $navigation = "<a href=\"$redirect\">$COURSE->shortname</a> ->";
} else {
    $redirect = $CFG->wwwroot.'/';
    $navigation = '';
}

$ticketform = new block_trouble_ticket_form();

if ($ticketform->is_cancelled()) {
    redirect($redirect);
} else if ($fromform = $ticketform->get_data()) {
    //process the form
    $from->id = $USER->id;
    $from->email = $USER->email;
    $from->firstname = $USER->firstname;
    $from->lastname = $USER->lastname;
    $from->maildisplay = true;
    $uploaddir = $CFG->dataroot.'/temp/block_trouble_ticket';

    if (isset($CFG->block_trouble_ticket_attachment_directory)) {
        $ticketform->save_files($uploaddir);
        $attachment = $ticketform->get_new_filename();
    }

    // recipient must be an object; create it here.
    if (isset($ticket->config->address)) {
        $emailaddress = $ticket->config->address;
    } else if (!isset($CFG->block_trouble_ticket_address)) {
        $admin = get_admin();
        $emailaddress = $admin->email;
    } else {
        $emailaddress = $CFG->block_trouble_ticket_address;
    }

    $recipient->email = $emailaddress;
    $recipient->firstname = '';
    $recipient->lastname = '';
    $recipient->maildisplay = true;

    $userfromtext = get_string('userfromtext','block_trouble_ticket')."$USER->email\n";
    $bodysubjecttext = get_string('bodysubjecttext','block_trouble_ticket')."$subject\n";
    $datetimetext = get_string('datetimetext','block_trouble_ticket').date('n-j-Y g:i A')."\n";
    $commentstext = get_string('commentstext','block_trouble_ticket').filter_text($comments)."\n";
    $coursetext = get_string('coursetext','block_trouble_ticket').$fromform->fromurl."\n";
    $usertext = get_string('usertext','block_trouble_ticket').$USER->username.', '.$USER->firstname.' '.$USER->lastname."\n";

    if (!empty($CFG->block_trouble_ticket_profilefields)) {
        $fields = explode(',', $CFG->block_trouble_ticket_profilefields);
        foreach ($fields as $field) {
            $a = new stdClass;
            $a->field = block_method_result('trouble_ticket', 'profile_field_label', $field);

            if (isset($USER->$field)) {
                $a->value = $USER->$field;
            } else {
                $a->value = '';
            }
            $userfieldtexts[] = get_string('userfieldtext', 'block_trouble_ticket', $a);
        }
        $userfieldtext = implode("\n", $userfieldtexts)."\n";
    } else {
        $userfieldtext = '';
    }

    // Get browser requires browscap to be set
    if (ini_get('browscap')) {
        $browser = get_browser();

        $browsertext = get_string('browsertext','block_trouble_ticket').$browser->browser.', '.$browser->version.', '.$browser->platform."\n";
    } else {
        $browsertext = get_string('browsertext','block_trouble_ticket').$_SERVER['HTTP_USER_AGENT']."\n";
    }

    //Site Role information
    $coursecontext = get_context_instance(CONTEXT_SYSTEM, SITEID);   // SYSTEM context
    if ($rolestring = get_user_roles_in_context($USER->id, $coursecontext)) {
        $rolestext = get_string('siterolestext','block_trouble_ticket').strip_tags($rolestring)."\n";
    }

    //Current context role information
    if ($COURSE->id != SITEID) {
        $coursecontext = get_context_instance(CONTEXT_COURSE, $COURSE->id);   // Course context
    }

    if ($rolestring = get_user_roles_in_context($USER->id, $coursecontext)) {
        $rolestext .= get_string('rolestext','block_trouble_ticket').strip_tags($rolestring)."\n";
    } else if (!isset($rolestext)) {
        $rolestext .= get_string('rolestext','block_trouble_ticket').get_string('noroles','block_trouble_ticket');
    }

    // New variable needed here to send the autoreply without loads of unnecessary data.
    $toadmintext = $userfromtext.$bodysubjecttext.$datetimetext.$commentstext.$coursetext.$usertext.$userfieldtext.$browsertext.$rolestext;
    $toadmintext = wordwrap( $toadmintext, 1024 );

    //Email message test print uncomment and comment out the redirect to thanks.php to view errors with emailing to a user.
    //print($toadmintext);

    // Readying the subject
    if (isset($ticket->config->subject_prefix)) {
        //set the subject to start with [shortname]
        $subject = $ticket->config->subject_prefix . $subject;
    } else {
        if (!isset($CFG->block_trouble_ticket_subject_prefix)) {
            if ($site = get_site()) {
                set_config('block_trouble_ticket_subject_prefix','['. strip_tags($site->shortname) .']');
            } else {
                set_config('block_trouble_ticket_subject_prefix', '[moodle contact]');
            }
        }
        $subject = $CFG->block_trouble_ticket_subject_prefix . $subject;
    }
    $subject = clean_param($subject, PARAM_NOTAGS);
    $subject = stripslashes_safe($subject);
    $toadmintext = stripslashes_safe($toadmintext);
    $toadmintexthtml = text_to_html($toadmintext, false, false, false);
    // Sending the actual e-mail
    // Check for error condition the hard way. Workaround for a bug in moodle discovered by Dan Marsden. If email is not configured properly and email_to_user() is called then "ERROR:" with no message prints out.
    ob_start();
    if (!empty($attachment)) {
        email_to_user($recipient, $from, $subject, $toadmintext, $toadmintexthtml, $uploaddir.'/'.$attachment, $attachment);
        if(!fulldelete($uploaddir.'/'.$attachment)){
            add_to_log($COURSE->id, get_string('title','block_trouble_ticket'), get_string('attachmentlog','block_trouble_ticket'), '', get_string('attachmentlogtext','block_trouble_ticket').$uploaddir.'/'.$attachment );
        }
    } else {
        email_to_user($recipient, $from, $subject, $toadmintext, $toadmintexthtml);
    }
    $error = ob_get_contents();
    ob_end_clean();
    if (debugging() && preg_match("/^ERROR:/", $error)) {
        error(get_string('emailconfigerror','block_trouble_ticket'). $error);
    }
    add_to_log($COURSE->id, get_string('title','block_trouble_ticket'), get_string('sendmaillog','block_trouble_ticket'), '', $subject." ". $toadmintext);

    //Once the data is entered, redirect the user to give them visual confirmation
    if (isset($instanceid) && (!$noid)) {
        redirect($CFG->wwwroot.'/blocks/trouble_ticket/thanks.php?id='. $COURSE->id.'&amp;instanceid='.$instanceid);
    } else if($noid) {
        redirect($CFG->wwwroot.'/blocks/trouble_ticket/thanks.php');
    } else {
        redirect($CFG->wwwroot.'/blocks/trouble_ticket/thanks.php?id='. $COURSE->id);
    }
} else {
    //form didn't validate or this is the first display
    $site = get_site();

    print_header(strip_tags($site->fullname), $site->fullname, $navigation.get_string('title', 'block_trouble_ticket'), '', '<meta name="description" content="'. s(strip_tags($site->summary)) .'">',true, '', '');

    $toform = array();
    if($noid){
        $toform['id'] = 0;
    } else {
        $toform['id'] = $COURSE->id;
    }
    $toform['fromurl'] = $redirect;
    $toform['name'] = fullname($USER);
    $toform['email'] = (!empty($email) ? $email : $USER->email);

    if (isset($ticket->config->address)){
        $emailaddress = $ticket->config->address;
    } else if (!isset($CFG->block_trouble_ticket_address)) {
        $admin = get_admin();
        $emailaddress = $admin->email;
    } else {
        $emailaddress = $CFG->block_trouble_ticket_address;
    }
    $toform['to'] = $emailaddress;
    $toform['subject'] = $subject;
    $toform['comments'] = $comments;
    $ticketform->set_data($toform);
    $ticketform->display();
    print_footer();
}
?>
