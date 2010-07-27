<?php
/**
 * This page generates blog RSS and ATOM feeds
 *
 * @author Matt Clarkson <mattc@catalyst.net.nz>
 * @package oublog
 */

// This code tells OU authentication system to let the public access this page
// (subject to Moodle restrictions below and with the accompanying .sams file).
global $DISABLESAMS;
$DISABLESAMS = 'opt';

    require_once("../../config.php");
    require_once("locallib.php");
    require_once($CFG->libdir.'/rsslib.php');
    require_once('atomlib.php');

    $format             = required_param('format', PARAM_TEXT);
    $blogid             = optional_param('blog', 0, PARAM_INT);
    $bloginstancesid    = optional_param('bloginstance', 0, PARAM_INT);
    $comments           = optional_param('comments', 0, PARAM_INT);
    $postid             = optional_param('post', 0, PARAM_INT);
    $loggedin           = optional_param('loggedin', '', PARAM_TEXT);
    $full               = optional_param('full', '', PARAM_TEXT);
    $viewer             = optional_param('viewer', 0, PARAM_INT);
    $groupid            = optional_param('group', 0, PARAM_INT);

/// Validate Parameters
    $format = strtolower($format);

    if (empty($CFG->enablerssfeeds)) {
        error('Feeds are not enabled');
    }
    if ($format != 'atom' && $format != 'rss') {
        error('Format must be atom or rss');
    }
    if (!$blogid && !$bloginstancesid && !$postid) {
        error('A required parameter was missing');
    }
    if(($loggedin || $full) && !$viewer) {
        error('A required parameter was missing');
    }
    if ($groupid && !$viewer) {
        error('A required parameter was missing');
    }

    if (isset($bloginstancesid) && $bloginstancesid!='all') {
        $bloginstance = get_record('oublog_instances', 'id', $bloginstancesid);
        $blog         = get_record('oublog', 'id', $bloginstance->oublogid);
    } elseif ($blogid) {
        $blog = get_record('oublog', 'id', $blogid);
    } elseif ($postid) {
        $post         = get_record('oublog_posts', 'id', $postid);
        $bloginstance = get_record('oublog_instances', 'id', $post->oubloginstancesid);
        $blog         = get_record('oublog', 'id', $bloginstance->oublogid);
    }
    if (!$cm = get_coursemodule_from_instance('oublog', $blog->id)) {
        error('Course module ID was incorrect');
    }

    // Work out link for ordinary web page equivalent to requested feed
    if ($blog->global) {
        if ($bloginstancesid == 'all') {
            $url = $CFG->wwwroot . '/mod/oublog/allposts.php';
        } else {
            $url = $CFG->wwwroot . '/mod/oublog/view.php?user=' . 
                $bloginstance->userid;
        }
    } else {
        $url = $CFG->wwwroot . '/mod/oublog/view.php?id=' . $cm->id .
            ($groupid ? '&group=' . $groupid : '');
    }

/// Check browser compatibility
    if (check_browser_version('MSIE', 0) || check_browser_version('Firefox', 0)) {
        if (!check_browser_version('MSIE', '7') && !check_browser_version('Firefox', '2')) {
            if($blog->global) {
                $url='view.php?user='.$bloginstance->userid;
            } else {
                $url='view.php?id='.$cm->id.($groupid ? '&group='.$groupid : '');
            }
            error(get_string('unsupportedbrowser', 'oublog'),$url);
        }
    }
/// Determine if feed has changed since the if-modified-since HTTP header and exit if it hasn't
    // Override default Moodle behaviour which prevents all caching (ouch)
    header('Cache-Control:');
    header('Pragma: ');
    header('Expires: ');
    if ($mtime = oublog_feed_last_changed($blogid, $bloginstancesid, $postid, $comments)) {
        $mtimegm = gmdate('D, d M Y H:i:s', $mtime) . ' GMT';
        header("Last-Modified: $mtimegm");
    }
    if ($mtime && isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
        $iftime = strtotime(preg_replace('/;.*$/', '',
            $_SERVER['HTTP_IF_MODIFIED_SINCE']));
        if ($mtime <= $iftime) {
            header("HTTP/1.0 304 Not Modified");
            exit;
        }
    }

    if ($blog->global && $bloginstancesid != 'all') {
        $accesstoken = $bloginstance->accesstoken;
    } else {
        $accesstoken = $blog->accesstoken;
    }

    $showerror = false;
    if ($full) {
        // We had an issue where the system leaked 'full' view tokens to users
        // who should not get them. To resolve this, I changed the view tokens
        // to use 'v2' in the hash
        if ($full == md5($accesstoken.$viewer.OUBLOG_VISIBILITY_COURSEUSER.'v2') && $user = get_record('user', 'id', $viewer)) {
            $allowedvisibility = OUBLOG_VISIBILITY_COURSEUSER; 
        } else if ($full == md5($accesstoken.$viewer.OUBLOG_VISIBILITY_COURSEUSER) && $user = get_record('user', 'id', $viewer)) {
            // This is the old token. Ooops. We know that at least users were
            // logged in, so they get that version...            
            $allowedvisibility = OUBLOG_VISIBILITY_LOGGEDINUSER;
            if (!$blog->global) {
                // For course blogs, security was actually correct, so let's
                // keep allowing them to read the whole blog
                $allowedvisibility = OUBLOG_VISIBILITY_COURSEUSER;
            }
        } else {
            error('Access denied');
        }
    } elseif ($loggedin) {
        if ($loggedin == md5($accesstoken.$viewer.OUBLOG_VISIBILITY_LOGGEDINUSER) && $user = get_record('user', 'id', $viewer)) {
            $allowedvisibility = OUBLOG_VISIBILITY_LOGGEDINUSER;
        } else {
            error('Access denied');
        }
    } else {
        $allowedvisibility = OUBLOG_VISIBILITY_PUBLIC;
        $user = get_guest();
    }

    // Check groups
    if($groupid && !groups_is_member($groupid, $user->id) && 
        !has_capability('moodle/site:accessallgroups',get_context_instance(CONTEXT_MODULE,$cm->id),$user->id)) {
        error('Access denied');
    }

/// Get data for feed in a standard form
    if ($comments) {
        $feeddata = oublog_get_feed_comments($blogid, $bloginstancesid, $postid, $user, $allowedvisibility, $groupid, $cm);
        $feedname=strip_tags($blog->name).': '.get_string('commentsfeed','oublog');
        $feedsummary='';
    } else {
        $feeddata = oublog_get_feed_posts($blogid, 
            isset($bloginstance) ? $bloginstance : null, $user,
            $allowedvisibility, $groupid, $cm, $blog);
        $feedname=strip_tags($blog->name);
        if ($bloginstancesid=='all') {
            $feedsummary=strip_tags($blog->summary);
        } else {
        	$feedsummary=strip_tags($bloginstance->summary);
        }
    }

/// Generate feed in RSS or ATOM format
    if ($format == 'rss') {
        header('Content-type: application/rss+xml');
        echo rss_standard_header($feedname, $url, $feedsummary);
        echo rss_add_items($feeddata);
        echo rss_standard_footer();
    } else {
        header('Content-type: application/atom+xml');
        $updated=count($feeddata)==0 ? time() : reset($feeddata)->pubdate;
        echo atom_standard_header($FULLME,$FULLME,$updated,$feedname, $feedsummary);
        echo atom_add_items($feeddata);
        echo atom_standard_footer();
    }
?>