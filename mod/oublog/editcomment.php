<?php
/**
 * This page allows a user to add and edit blog comments
 *
 * @author Matt Clarkson <mattc@catalyst.net.nz>
 * @author Sam Marshall <s.marshall@open.ac.uk>
 * @package oublog
 */

    require_once("../../config.php");
    require_once("locallib.php");
    require_once('comment_form.php');

    $blog = required_param('blog', PARAM_INT);              // Blog ID
    $postid = required_param('post', PARAM_INT);            // Post ID for editing
    $commentid = optional_param('comment', 0, PARAM_INT);   // Comment ID for editing

    if (!$oublog = get_record("oublog", "id", $blog)) {
        error('Blog parameter is incorrect');
    }
    if (!$cm = get_coursemodule_from_instance('oublog', $blog)) {
        error('Course module ID was incorrect');
    }
    if (!$course = get_record("course", "id", $oublog->course)) {
        error('Course is misconfigured');
    }
    if (!$post = get_record('oublog_posts', 'id', $postid)) {
        error('Post not found');
    }
    if (!$oubloginstance = get_record('oublog_instances', 'id', $post->oubloginstancesid)) {
        error('Blog instance not found');
    }

/// Check security
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);

    oublog_check_view_permissions($oublog, $context, $cm);
    $post->userid=$oubloginstance->userid; // oublog_can_view_post needs this
    if(!oublog_can_view_post($post,$USER,$context,$oublog->global)) {
        print_error('accessdenied','oublog');
    }

    if (!oublog_can_comment($oublog,$cm)) {
        print_error('accessdenied','oublog');
    }

    if ($oublog->allowcomments == OUBLOG_COMMENTS_PREVENT || $post->allowcomments == OUBLOG_COMMENTS_PREVENT) {
        error('Comments are not allowed');
    }

    $viewurl = 'viewpost.php?post='.$post->id;
    if ($oublog->global) {
        $blogtype = 'personal';
        if (!$oubloguser = get_record('user', 'id', $oubloginstance->userid)) {
            error("User not found");
        }
    } else {
        $blogtype = 'course';
    }

/// Get strings
    $stroublogs  = get_string('modulenameplural', 'oublog');
    $stroublog   = get_string('modulename', 'oublog');
    $straddcomment  = get_string('newcomment', 'oublog');
    $streditcomment = get_string('editcomment', 'oublog');


    $mform = new mod_oublog_comment_form('editcomment.php', array('maxvisibility' => $oublog->maxvisibility, 'edit' => !empty($commentid)));

    if ($mform->is_cancelled()) {
        redirect($viewurl);
        exit;
    }

    if (!$comment = $mform->get_data()) {

        $comment = new stdClass;
        $comment->general = $straddcomment;
        $comment->blog = $blog;
        $comment->post = $postid;
        $mform->set_data($comment);

/// Generate extra navigation
        if (!empty($post->title)) {
            $postlink = array('name' => format_string($post->title), 'link' => 'viewpost.php?post='.$post->id, 'type' => 'misc');
        } else {
            $postlink = array('name' => shorten_text(format_string(strip_tags($post->message, 30))), 'link' => 'viewpost.php?post='.$post->id, 'type' => 'misc');
        }
        
/// Print the header
        if ($blogtype == 'personal') {

            $navlinks = array();
            $navlinks[] = array('name' => fullname($oubloguser), 'link' => "../../user/view.php?id=$oubloguser->id", 'type' => 'misc');
            $navlinks[] = array('name' => format_string($oubloginstance->name), 'link' => $viewurl, 'type' => 'activityinstance');
            $navlinks[] = $postlink;
            $navlinks[] = array('name' => $comment->general, 'link' => '', 'type' => 'misc');
            
            $navigation = build_navigation($navlinks);
            print_header_simple(format_string($oublog->name), "", $navigation, "", "", true);

        } else {
            $navlinks = array();
            $navlinks[] = $postlink;
            $navlinks[] = array('name' => $comment->general, 'link' => '', 'type' => 'misc');
            
            $navigation = build_navigation($navlinks, $cm);
            print_header_simple(format_string($oublog->name), "", $navigation, "", "", true,
                          update_module_button($cm->id, $course->id, $stroublog), navmenu($course, $cm));
        }

        echo '<br />';
        $mform->display();

        print_footer();

    } else {
        if(class_exists('ouflags')) {
            $DASHBOARD_COUNTER=DASHBOARD_BLOG_COMMENT;
        }
        // insert the comment
        unset($comment->id);

        $comment->userid = $USER->id;
        $comment->postid = $postid;

        if (!oublog_add_comment($course,$cm,$oublog,$comment)) {
            error('Could not add comment');
        }
        add_to_log($course->id, "oublog", "add comment", $viewurl, $oublog->id, $cm->id);
        redirect($viewurl);
    }

?>