<?php // $Id: email.php,v 2.5 2008/02/15 09:53:43 pcali1 Exp $
    
    /**
     *   Edited by: Philip Cali
     *   Date: 2/15/08
     *   Louisiana State University
     */
     
    /**
     * email.php - Used by Quickmail for sending emails to users enrolled in a specific course.
     *      Calls email.html at the end.
     *
     * @Original author Mark Nielsen and Michael Penney. 
     * @Updated by Bibek Bhattarai, Neha Arora, Wen Hao Chuang
     * @version $Id: email.php,v 2.01 2007/04/05 09:55:18 wenhaochuang Exp $
     * @package quickmailv2
     **/
    
	/** 
 	* Updated feature:
 	*	- Replaces checkbox with selection list for effective usability in case of larger classes
	* - For simplicity purpose removed the options group mailing for now, will be added in next version
 	**/ 

    //Load libary files
    require_once('../../config.php');
    require_once("$CFG->dirroot/blocks/moodleblock.class.php");
    require_once($CFG->libdir . '/accesslib.php');
    require_once('block_quickmail.php');

    //Check user have logged in to the system or not
    require_login();

	//Read parameter values courseid, quickmail instance id, and action  
    $id = required_param('id', PARAM_INT);  // course id
    $hidden = optional_param('hidden', 0, PARAM_BOOL);
    $instanceid = required_param('instanceid', PARAM_INT);
    $action = optional_param('action', '', PARAM_ALPHA);
    
	//Setup quickmail block
    $quickmail = new block_quickmail();
    $instance = new stdClass;
    
    //If course doesnot exists error out
    if (! $course = get_record('course', 'id', $id)) {
        error(get_string('incorrect_id', 'block_quickmail'));
    }

    //Makes sure we're ina the right context
    if (!$context = get_context_instance(CONTEXT_COURSE, $course->id)){
        error(get_string('wrong_context', 'block_quickmail'));
    }
    
	//Have to figure out .............................. Bibek
    $quickmailresult = get_record('block', 'name', 'quickmail');
    if ($instanceid && $quickmailresult) {
      $instance = get_record('block_instance', 'id', $instanceid, 'blockid', $quickmailresult->id);
      if (!$instance){
          $instance = get_record('block_pinned', 'id', $instanceid, 'blockid', $quickmailresult->id);
      }
   }

/// This block of code ensures that Quickmail will run 
/// whether it is in the course or not

    if (empty($instance)) {
        $groupmode = $course->groupmode;
        if (isGuest()) {
            $haspermission = false;
        } else {
            $haspermission = true;
        }
    } else {
        // create a quickmail block instance
        $quickmail->_load_instance($instance);
        $quickmail->load_defaults();
        
        $groupmode      = $quickmail->groupmode();
        $haspermission  = $quickmail->check_permission('block/quickmail:cansend', CONTEXT_COURSE, $course) || 
                          $quickmail->allow_students_to_email($USER->id);
    }
   
 
	if(!$quickmail->check_permission('block/quickmail:canimpersonate', CONTEXT_COURSE, $course) && !isset($USER->id)){
		error(get_string('wrong_user', 'block_quickmail'));
	}

    if (!$haspermission) {
        error(get_string('no_permission', 'block_quickmail'));
    }

    $groupmembers_to_groupid = array();
    $roles_to_userid = array();
    $courseusers = array();

    $groups = get_records('groups', 'courseid', $course->id, 'name asc');
    
    $sql = "SELECT DISTINCT CONCAT(r.id, '_', usr.username), r.id AS roleid, r.name, 
            r.shortname, usr.id AS userid, usr.username, usr.email, usr.firstname, 
            usr.lastname
     FROM mdl_course AS c         
        INNER JOIN mdl_context AS cx ON cx.id={$context->id}
        INNER JOIN mdl_role_assignments AS ra ON cx.id = ra.contextid
        INNER JOIN mdl_role AS r ON ra.roleid = r.id
        INNER JOIN mdl_user AS usr ON ra.userid = usr.id
     WHERE (c.id = {$course->id}) ORDER BY usr.lastname ASC";

    $dbcontent = get_records_sql($sql);

    $sql = "SELECT CONCAT(u.id, '_', u.username, '_', g.id) AS uniquecode, 
                   u.id AS userid, g.id AS groupid, g.name 
            FROM mdl_user AS u, 
                 mdl_groups AS g, 
                 mdl_groups_members AS gm 
            WHERE g.courseid = {$course->id} 
                AND g.id = gm.groupid 
                AND gm.userid = u.id";

    $groupcontent = get_records_sql($sql);

    foreach($dbcontent as $content_record) {
        $userid = $content_record->userid;
        $roleid = $content_record->roleid;

        if (!isset($roles_to_userid[$userid])){
            $roles_to_userid[$userid] = array();
        }

        $role = new stdClass;
        $role->name = $content_record->name;
        $role->shortname = $content_record->shortname;
        $roles_to_userid[$userid][] = $role;

        foreach ($groups as $group) {
	    $index = $userid .'_'. $content_record->username .'_'. $group->id;
	    if (array_key_exists($index, $groupcontent)) {

            	$record = $groupcontent[$index];
                if (!isset($groupmembers_to_groupid[$record->userid])) {
                    $groupmembers_to_groupid[$record->userid] = array();
                }
            
                if (isset($groupmembers_to_groupid[$record->userid][$record->groupid])) {
                    continue;
                }
                $groupmembers_to_groupid[$record->userid][$record->groupid] = $groups[$record->groupid];
            }
        }

        if (isset($courseusers[$userid])) {
            continue;
        }

        $user = new stdClass;
        $user->firstname = $content_record->firstname;
        $user->lastname = $content_record->lastname;
        $user->email = $content_record->email;
        $user->id = $userid;
        $courseusers[$userid] = $user;
    }

	//Get list of users enrolled in the course including teachers and students
    if (!$courseusers) {
        error('No course users found to email');
    }

	// set up some strings
    $readonly       = '';
    $strchooseafile = get_string('chooseafile', 'resource');
    $strquickmail   = get_string('blockname', 'block_quickmail');
 
    $navigation = array(
                array('name' => $course->shortname, 'link' => "{$CFG->wwwroot}/course/view.php?id=$course->id", 'type'=> 'title'),
                array('name' => $strquickmail, 'link'=>'', 'type'=>'title'),
                );
 
    print_header_simple($strquickmail, '', build_navigation($navigation));

  // if action is view display the email user wants to view
	if ($action == 'view') {
        // viewing an old email.  Hitting the db and puting it into the object $form
        $emailid = required_param('emailid', PARAM_INT);
        $form = get_record('block_quickmail_log', 'id', $emailid);
        $mail_list = explode(',', stripslashes($form->mailto)); // convert mailto back to an array        
    } 
	else if ($form = data_submitted()) {   // data was submitted to be mailed
    	confirm_sesskey();

        if (!empty($form->cancel)) {
            // cancel button was hit...
            redirect("$CFG->wwwroot/course/view.php?id=$course->id");
        }
        
        // prepare variables for email      
        $form->subject = stripslashes($form->subject);
        $form->subject = clean_param($form->subject, PARAM_CLEAN);
        $form->subject = strip_tags($form->subject, '<lang>');        // Strip all tags except lang
        $form->subject = break_up_long_words($form->subject);

        $form->message = stripslashes($form->message); // needed to get slashes off of the post
        $form->message = clean_param($form->message, PARAM_CLEANHTML);

        // get the correct formating for the emails
        $form->plaintxt = format_text_email($form->message, $form->format); // plain text
        $form->html = format_text($form->message, $form->format);        // html
       	$mail_list = explode(',',stripslashes($form->mailuser));
		
        //multiple attachments
        $attach_list = explode(',', stripslashes($form->attachids));

		// make sure the user didn't miss anything
		if (sizeof($mail_list)<=1 && $mail_list[0]=="") {
            $form->error = get_string('toerror', 'block_quickmail');
        } /*else if (!$form->subject) {
            $form->error = get_string('subjecterror', 'block_quickmail');
        } else if (!$form->message) {
            $form->error = get_string('messageerror', 'block_quickmail');
        }*/
        
        // process the attachment
        $attachment = array();
        
		require_once($CFG->libdir.'/uploadlib.php');
        
        foreach ($attach_list as $attach_id) {
            $um = new upload_manager($attach_id, false, true, $course, false, 0, true);
			$attachment_success = true;
            
			// process the student posted attachment if it exists
            foreach ($_FILES as $name => $file){
				$file['originalname'] = $file['name'];
			}
			$attachment_success = $um->process_file_uploads('temp/block_quickmail');
			if ($attachment_success) {
                // original name gets saved in the database
                $form->attachment .= $um->get_original_filename() .' ';
				// check if file is there
                if (file_exists($um->get_new_filepath())) {
                    // get path to the file without $CFG->dataroot
                    $attachment[$um->get_new_filename()] = 'temp/block_quickmail/'.$um->get_new_filename();
                    //$attachment = 'temp/block_quickmail/'.$um->get_new_filename();
                	
                    // get the new name (name may change due to filename collisions)
                    //$attachname = $um->get_new_filename();
                } else {
                    $form->error = get_string("attachmenterror", "block_quickmail", $form->attachment);
                }
            } 
			elseif($file['originalname']!='' && !$attachment_sucess){
				$form->error = get_string("attachmentmaxsize", "block_quickmail", $form->attachment);
			}
			else{
				$form->attachment .= ''; // no attachment
            }
        }        
        
        // no errors, then email
        if(!isset($form->error)) {
            $mailedto = array(); // holds all the userid of successful emails
            $blockedTo = array();
			$failedTo = array();
            //print_heading(get_string('pleasewait', 'block_quickmail'), 'center', 3);  // inform the user to wait

            // run through each user id and send a copy of the email to him/her
            // not sending 1 email with CC to all user ids because emails were required to be kept private
            foreach ($mail_list as $userid) {
				$userid = stripslashes($userid);
				$userid = str_replace("\"","",$userid);
				//$userid =   
                if (!$courseusers[$userid]->emailstop) {
                     //if($userid != $USER->id){
						$mailresult = modified_email_to_user($courseusers[$userid], $USER, $form->subject, 
                                        $form->plaintxt, $form->html, $attachment);
                    	// checking for errors, if there is an error, store the name
                    	if (!$mailresult || (string) $mailresult == 'emailstop') {
                        	$form->error = get_string('emailfailerror', 'block_quickmail');
                        	$form->usersfail['emailfail'][] = $courseusers[$userid]->lastname.', '.$courseusers[$userid]->firstname;
							$failedTo[] = $userid;
                    	} else {
                        	// success
                        	$mailedto[] = $userid;						
                    	}
					//}
                } else {
                    // blocked email
                    $form->error = get_string('emailfailerror', 'block_quickmail');
                    $form->usersfail['emailstop'][] = $courseusers[$userid]->lastname.', '.$courseusers[$userid]->firstname;
					$blockedTo[] = $userid;
                }
            }

            if ($form->receive_receipt) {
            if(count($mailedto)>0){
				$messagetext = $messagetext."----------------------------------------------------\n";
				$messagetext = $messagetext."Following email was successfully sent to: \n";
				$messagetext = $messagetext."----------------------------------------------------\n";				
				foreach($mailedto as $userid){
					$messagetext = $messagetext.$courseusers[$userid]->firstname." ".$courseusers[$userid]->lastname.";\t";
				}
				$messagetext = $messagetext."\n";
			}
			
			if(count($blockedTo)>0){
				$messagetext = $messagetext."\n-------------------------------------------------------------------------------\n";
				$messagetext = $messagetext."Following user(s) have chosen not to recieve any email through Moodle: \n";
				$messagetext = $messagetext."-------------------------------------------------------------------------------\n";
				foreach($blockedTo as $userid){
					$messagetext = $messagetext.$courseusers[$userid]->firstname." ".$courseusers[$userid]->lastname.";\t";
				}
				$messagetext = $messagetext."\n";
			}
			
			if(count($failedTo)>0){
				$messagetext = $messagetext."\n---------------------------------------------------------------------------------------------------------------\n";
				$messagetext = $messagetext."Moodle was unable to send email to following user(s), please contact Moodle support to report the error: \n";
				$messagetext = $messagetext."-----------------------------------------------------------------------------------------------------------------\n";
				foreach($failedTo as $userid){
					$messagetext = $messagetext.$courseusers[$userid]->firstname." ".$courseusers[$userid]->lastname.";\t";
				}
				$messagetext = $messagetext."\n";
			}
			
			$messagetext = $messagetext."\n==================================\nMessage Content\n----------------------------------\n";
			$messagetext = $messagetext."\n".$form->plaintxt;
			$messagetext = $messagetext."\n\n=================================\n";
			$form->subject = "Quickmail dispatch receipt: ".$form->subject;
			$mailresult = modified_email_to_user($USER, $USER, $form->subject, $messagetext, '', $attachment);
            }

            // cleanup - delete the uploaded file
            if (isset($um) and file_exists($um->get_new_filepath())) {
                unlink($um->get_new_filepath());
            }

            // prepare an object for the insert_record function
            $emaillog = new stdClass;
            $emaillog->courseid   = $course->id;
            $emaillog->userid     = $USER->id;
            $emaillog->mailto     = implode(',', $mailedto);
            $emaillog->subject    = addslashes($form->subject);
            $emaillog->message    = addslashes($form->message);
            $emaillog->attachment = $form->attachment;
            $emaillog->format     = $form->format;
            $emaillog->timesent   = time();
			
			if (!insert_record('block_quickmail_log', $emaillog)) {
                error('Email not logged.');
            }

            if(!isset($form->error)) {  // if no emailing errors, we are done
                // inform of success and continue
                redirect("$CFG->wwwroot/course/view.php?id=$course->id", get_string('successfulemail', 'block_quickmail'));
                print_footer($course);
                exit();
            }
        }
        // so people can use quotes.  It will display correctly in the subject input text box
        $form->subject = htmlentities($form->subject, ENT_QUOTES);  

    } else {
        // set them as blank
        $form->subject = $form->message = $form->format = $form->attachment = '';		
    }

    // get the default format       
    if ($usehtmleditor = can_use_richtext_editor()) {
        $defaultformat = FORMAT_HTML;
    } else {
        $defaultformat = FORMAT_MOODLE;
    }

    // print the email form START
    print_heading($strquickmail);
    
    // error printing
    if (isset($form->error)) {
        echo '<b><center><font color="#FF0000" align="center"> There was an error, please contact your Moodle administrator.';
		notify($form->error);
		echo '</font></center></b>';
        if (isset($form->usersfail)) {
            $errorstring = '';
            
            if (isset($form->usersfail['emailfail'])) {
                $errorstring .= get_string('emailfail', 'block_quickmail').'<br />';
                foreach($form->usersfail['emailfail'] as $user) {
                    $errorstring .= $user.'<br />';
                }               
            }

            if (isset($form->usersfail['emailstop'])) {
                $errorstring .= get_string('emailstop', 'block_quickmail').'<br />';
                foreach($form->usersfail['emailstop'] as $user) {
                    $errorstring .= $user.'<br />';
                }               
            }
            notify($errorstring);
            
            // print continue button
            print_continue("$CFG->wwwroot/course/view.php?id=$course->id");
            print_footer($course);
            exit();
        }
    }

 	print_simple_box_start('center'); 	       
	require('email.html');  // email form
    print_simple_box_end();
    
    if ($usehtmleditor) {
        use_html_editor('message');
    }

    print_footer($course);

    //////////////MODIFIED EMAIL TO USER FUNCTION////////////////////
    function modified_email_to_user($user, $from, $subject, $messagetext, $messagehtml='', $attachment=null, $usetrueaddress=true, $replyto='', $replytoname='') {
        global $CFG, $FULLME;

        include_once($CFG->libdir .'/phpmailer/class.phpmailer.php');
        $textlib = textlib_get_instance();
        
        if (empty($user)) {
             return false;
        } 

        // skip mail to suspended users
        if (isset($user->auth) && $user->auth=='nologin') {
            return true;
        }

        if (!empty($user->emailstop)) {
            return 'emailstop';
        }
                       
        if (over_bounce_threshold($user)) {
            error_log("User $user->id (".fullname($user).") is over bounce threshold! Not sending.");
            return false;
        }

        $mail = new phpmailer;
         
        $mail->Version = 'Moodle '. $CFG->version;           // mailer version
        $mail->PluginDir = $CFG->libdir .'/phpmailer/';      // plugin directory (eg smtp plugin)
                 
        $mail->CharSet = 'UTF-8';
                          
          // some MTAs may do double conversion of LF if CRLF used, CRLF is required line ending in RFC 822bis
            // hmm, this is a bit hacky because LE should be private
        if (isset($CFG->mailnewline) and $CFG->mailnewline == 'CRLF') {
             $mail->LE = "\r\n";
        } else {
             $mail->LE = "\n";
        }
                                                              
        if ($CFG->smtphosts == 'qmail') {
              $mail->IsQmail();                              // use Qmail system
                                                                                     
        } else if (empty($CFG->smtphosts)) {
             $mail->IsMail();                               // use PHP mail() = sendmail
                                                                                          
        } else {
             $mail->IsSMTP();                               // use SMTP directly
             if (!empty($CFG->debugsmtp)) {
                echo '<pre>' . "\n";
                $mail->SMTPDebug = true;
              }
             $mail->Host = $CFG->smtphosts;               // specify main and backup servers
                                                                                                                                                                  
             if ($CFG->smtpuser) {                          // Use SMTP authentication
               $mail->SMTPAuth = true;
               $mail->Username = $CFG->smtpuser;
               $mail->Password = $CFG->smtppass;
            }
        }
        
        $supportuser = generate_email_supportuser();
                
                 
              // make up an email address for handling bounces
        if (!empty($CFG->handlebounces)) {
            $modargs = 'B'.base64_encode(pack('V',$user->id)).substr(md5($user->email),0,16);
            $mail->Sender = generate_email_processing_address(0,$modargs);
        } else {
            $mail->Sender   = $supportuser->email;
        }
                                                             
        if (is_string($from)) { // So we can pass whatever we want if there is need
            $mail->From     = $CFG->noreplyaddress;
            $mail->FromName = $from;
        } else if ($usetrueaddress and $from->maildisplay) {
            $mail->From     = $from->email;
            $mail->FromName = fullname($from);
        } else {
            $mail->From     = $from->email;
            $mail->FromName = fullname($from);
            if (empty($replyto)) {
              $mail->AddReplyTo($CFG->noreplyaddress,get_string('noreplyname'));
           }
       }

       if (!empty($replyto)) {
         $mail->AddReplyTo($replyto,$replytoname);
     }
 
     $mail->Subject = substr(stripslashes($subject), 0, 900);
 
     $mail->AddAddress($user->email, fullname($user) );
 
     $mail->WordWrap = 79;                               // set word wrap
 
     if (!empty($from->customheaders)) {                 // Add custom headers
         if (is_array($from->customheaders)) {
             foreach ($from->customheaders as $customheader) {
                 $mail->AddCustomHeader($customheader);
             }
         } else {
             $mail->AddCustomHeader($from->customheaders);
         }
     }
 
     if (!empty($from->priority)) {
         $mail->Priority = $from->priority;
     }
 
     if ($messagehtml && $user->mailformat == 1) { // Don't ever send HTML to users who don't want it
         $mail->IsHTML(true);
         $mail->Encoding = 'quoted-printable';           // Encoding to use
         $mail->Body    =  $messagehtml;
         $mail->AltBody =  "\n$messagetext\n";
     } else {
         $mail->IsHTML(false);
     $mail->Body =  "\n$messagetext\n";
     }
 
     if ($attachment) {
        foreach ($attachment as $attachname => $attachvalue) {
         if (ereg( "\\.\\." ,$attachvalue )) {    // Security check for ".." in dir path
             $mail->AddAddress($supportuser->email, fullname($supportuser, true) );
             $mail->AddStringAttachment('Error in attachment.  User attempted to attach a filename with a unsafe name.', 'error.txt', '8bit', 'text/plain');
         } else {
             require_once($CFG->libdir.'/filelib.php');
             $mimetype = mimeinfo('type', $attachname);
             $mail->AddAttachment($CFG->dataroot .'/'. $attachvalue, $attachname, 'base64', $mimetype);
         }

        }
     }
 
 
 /// If we are running under Unicode and sitemailcharset or allowusermailcharset are set, convert the email
 /// encoding to the specified one
     if ((!empty($CFG->sitemailcharset) || !empty($CFG->allowusermailcharset))) {
     /// Set it to site mail charset
         $charset = $CFG->sitemailcharset;
     /// Overwrite it with the user mail charset
         if (!empty($CFG->allowusermailcharset)) {
             if ($useremailcharset = get_user_preferences('mailcharset', '0', $user->id)) {
                 $charset = $useremailcharset;
             }
         }
     /// If it has changed, convert all the necessary strings
         $charsets = get_list_of_charsets();
         unset($charsets['UTF-8']);
         if (in_array($charset, $charsets)) {
         /// Save the new mail charset
             $mail->CharSet = $charset;
         /// And convert some strings
             $mail->FromName = $textlib->convert($mail->FromName, 'utf-8', $mail->CharSet); //From Name
             foreach ($mail->ReplyTo as $key => $rt) {                                      //ReplyTo Names
                 $mail->ReplyTo[$key][1] = $textlib->convert($rt, 'utf-8', $mail->CharSet);
             }
             $mail->Subject = $textlib->convert($mail->Subject, 'utf-8', $mail->CharSet);   //Subject
             foreach ($mail->to as $key => $to) {
                 $mail->to[$key][1] = $textlib->convert($to, 'utf-8', $mail->CharSet);      //To Names
             }
             $mail->Body = $textlib->convert($mail->Body, 'utf-8', $mail->CharSet);         //Body
             $mail->AltBody = $textlib->convert($mail->AltBody, 'utf-8', $mail->CharSet);   //Subject
         }
     }
 
     if ($mail->Send()) {
         set_send_count($user);
         $mail->IsSMTP();                               // use SMTP directly
         if (!empty($CFG->debugsmtp)) {
             echo '</pre>';
         }
         return true;
     } else {
         mtrace('ERROR: '. $mail->ErrorInfo);
         add_to_log(SITEID, 'library', 'mailer', $FULLME, 'ERROR: '. $mail->ErrorInfo);
         if (!empty($CFG->debugsmtp)) {
             echo '</pre>';
         }
         return false;        
      }
    }

?>
