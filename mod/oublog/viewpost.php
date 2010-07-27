<?php
/**
 * This page prints a particular post from an oublog, including any comments.
 *
 * @author Matt Clarkson <mattc@catalyst.net.nz>
 * @author Sam Marshall <s.marshall@open.ac.uk>
 * @package oublog
 */
// This code tells OU authentication system to let the public access this page
// (subject to Moodle restrictions below and with the accompanying .sams file).
global $DISABLESAMS;
$DISABLESAMS = 'opt';

    require_once("../../config.php");
    require_once("locallib.php");

    if(class_exists('ouflags')) {
        $DASHBOARD_COUNTER=DASHBOARD_BLOG_VIEW;
    }

    $postid = required_param('post', PARAM_INT);       // Post id


    if (!$post = oublog_get_post($postid, true)) {
        error("Post ID was incorrect");
    }

    if (!$cm = get_coursemodule_from_instance('oublog', $post->oublogid)) {
        error("Course module ID was incorrect");
    }

    if (!$course = get_record("course", "id", $cm->course)) {
        error("Course is misconfigured");
    }

    if (!$oublog = get_record("oublog", "id", $cm->instance)) {
        error("Course module is incorrect");
    }

    if (!$oubloginstance = get_record('oublog_instances', 'id', $post->oubloginstancesid)) {
        error("Blog instance not found");
    }

    $context = get_context_instance(CONTEXT_MODULE, $cm->id);
    oublog_check_view_permissions($oublog, $context, $cm);
    if(!oublog_can_view_post($post,$USER,$context,$oublog->global)) {
        print_error('accessdenied','oublog');
    }

/// Check security
    $cancomment        = oublog_can_comment($oublog,$cm);
    $canmanageposts    = has_capability('mod/oublog:manageposts', $context);
    $canmanagecomments = has_capability('mod/oublog:managecomments', $context);
    $canaudit          = has_capability('mod/oublog:audit', $context);

/// Get strings
    $stroublogs     = get_string('modulenameplural', 'oublog');
    $stroublog      = get_string('modulename', 'oublog');
    $straddpost     = get_string('newpost', 'oublog');
    $strdelete      = get_string('delete', 'oublog');
    $strtags        = get_string('tags', 'oublog');
    $strcomments    = get_string('comments', 'oublog');
    $strlinks       = get_string('links', 'oublog');
    $strfeeds       = get_string('feeds', 'oublog');
    
/// Set-up groups
    $currentgroup = groups_get_activity_group($cm, true);
    $groupmode = groups_get_activity_groupmode($cm);
    
/// Check permissions for group (of post)
    if($groupmode==VISIBLEGROUPS && !groups_is_member($post->groupid) &&
        !has_capability('moodle/site:accessallgroups',$context)) {
        $canpost=false;
        $canmanageposts=false;
        $cancomment=false;
        $canaudit=false;
    } 

    /// Generate extra navigation
    $extranav = array();
    if (!empty($post->title)) {
        $extranav = array('name' => format_string($post->title), 'link' => '', 'type' => 'misc');
    } else {
        $extranav = array('name' => shorten_text(format_string(strip_tags($post->message, 30))), 'link' => '', 'type' => 'misc');
    }

/// Print the header
    if ($oublog->global) {
        $blogtype = 'personal';
        $returnurl = 'view.php?user='.$oubloginstance->userid;
        $blogname = format_string($oubloginstance->name);

        if (!$oubloguser = get_record('user', 'id', $oubloginstance->userid)) {
            error("User not found");
        }

        $navlinks = array();
        $navlinks[] = array('name' => fullname($oubloguser), 'link' => "../../user/view.php?id=$oubloguser->id", 'type' => 'misc');
        $navlinks[] = array('name' => $blogname, 'link' => $returnurl, 'type' => 'activityinstance');
        if ($extranav) {
            $navlinks[] = $extranav;
        }

        $navigation = build_navigation($navlinks);
        print_header_simple(format_string($oublog->name), "", $navigation, "", oublog_get_meta_tags($oublog, $oubloginstance, $currentgroup, $cm), true,
                    update_module_button($cm->id, $course->id, $stroublog), navmenu($course, $cm));
    } else {
        $blogtype = 'course';
        $returnurl = 'view.php?id='.$cm->id;
        $blogname = $oublog->name;

        $navlinks = array();
        if ($extranav) {
            $navlinks[] = $extranav;
        }
        $navigation = build_navigation($navlinks, $cm);

        print_header_simple(format_string($oublog->name), "", $navigation, "", oublog_get_meta_tags($oublog, $oubloginstance, $currentgroup, $cm), true,
                      update_module_button($cm->id, $course->id, $blogname), navmenu($course, $cm));
    }

/// Print the main part of the page
    echo '<div class="oublog-topofpage"></div>';

    // The right column, BEFORE the middle-column.
    print '<div id="right-column">';    
    
// Title & Print summary
    // Name, summary, related links
    oublog_print_summary_block($oublog, $oubloginstance, $canmanageposts);
        
    // Tag Cloud
    if ($tags = oublog_get_tag_cloud($returnurl, $oublog->id, $currentgroup, $cm, $oubloginstance->id)) {
        print_side_block($strtags, $tags, NULL, NULL, NULL, array('id' => 'oublog-tags'));
    }


/// Links
    if ($links = oublog_get_links($oublog, $oubloginstance, $context)) {
        print_side_block($strlinks, $links, NULL, NULL, NULL, array('id' => 'oublog-links'));
    }


    if ($feeds = oublog_get_feedblock($oublog, $oubloginstance, $currentgroup, false, $cm)) {
        print_side_block($strfeeds, $feeds, NULL, NULL, NULL, array('id' => 'oublog-tags'));
    }
    
    print '</div>';

// Print blog posts
    echo '<div id="middle-column" class="has-right-column">';
    oublog_print_post($oublog, $post, $returnurl, $blogtype, $canmanageposts, $canaudit, $cancomment, false);

    if (!empty($post->comments)) {
        echo "<h2>$strcomments</h2>";

        foreach($post->comments as $comment) {
            if ($comment->deletedby && !$canaudit) {
                continue;
            }
            $extraclasses = $comment->deletedby ? 'oublog-deleted':'';
            if($CFG->oublog_showuserpics) {
                $extraclasses.=' oublog-hasuserpic';
            }
            ?>
            <div class="oublog-comment <?php print $extraclasses; ?>"><?php
            if ($comment->deletedby) {
                $deluser = new stdClass();
                $deluser->firstname = $comment->delfirstname;
                $deluser->lastname  = $comment->dellastname;

                $a = new stdClass();
                $a->fullname = '<a href="../../user/view.php?id=' . $comment->deletedby . '">' . fullname($deluser) . '</a>';
                $a->timedeleted = oublog_date($comment->timedeleted);

                echo '<div class="oublog-comment-deletedby">'.get_string('deletedby', 'oublog', $a).'</div>';
            }
            if($CFG->oublog_showuserpics) {
                print '<div class="oublog-userpic">';
                $commentuser = new object();
                $commentuser->id        = $comment->userid;
                $commentuser->firstname = $comment->firstname;
                $commentuser->lastname  = $comment->lastname;
                $commentuser->imagealt  = $comment->imagealt;
                $commentuser->picture   = $comment->picture;
                print_user_picture($commentuser,$oublog->course);
                print '</div>';
            }
            ?>
                <?php if(trim(format_string($comment->title))!=='') { ?><h3><?php print format_string($comment->title); ?></h3><?php } ?>
                <div class="oublog-comment-date">
                    <?php print oublog_date($comment->timeposted); ?>
                </div>
                <div class="oublog-posted-by"><?php 
            echo get_string('postedby', 'oublog', '<a href="../../user/view.php?id='.$comment->userid.'&amp;course='.$oublog->course.'">'.fullname($comment).'</a>');
                ?></div>
                <div class="oublog-comment-content"><?php print format_text($comment->message, FORMAT_HTML); ?></div>
                <div class="oublog-post-links">              
            <?php
            if (!$comment->deletedby) {
                // You can delete your own comments, or comments on your own
                // personal blog, or if you can manage comments
                if ($comment->userid == $USER->id || 
                    ($oublog->global && $post->userid == $USER->id) ||
                    $canmanagecomments) {
                    echo '<a href="deletecomment.php?comment='.$comment->id.'">'.$strdelete.'</a>';
                }
            }
                ?>
                </div>
            </div>
        <?php
        }
    }
    echo '</div>';


/// Finish the page
    echo '<div class="clearfix"></div>';
    print_footer($course);
?>