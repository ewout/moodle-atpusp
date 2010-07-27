<?php
/**
 * Library of functions used by the oublog module.
 *
 * This contains functions that are called from within the oublog module only
 * Functions that are also called by core Moodle are in {@link lib.php}
 *
 * @author Matt Clarkson <mattc@catalyst.net.nz>
 * @author Sam Marshall <s.marshall@open.ac.uk>
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package oublog
 */

// OU shared APIs which (for OU system) are present in local, elsewhere
// are incorporated in module
@include_once(dirname(__FILE__).'/../../local/transaction_wrapper.php');
if (!class_exists('transaction_wrapper')) {
    require_once(dirname(__FILE__).'/null_transaction_wrapper.php');
}

/**#@+
 * Constants defining the visibility levels of blog posts
 */
define('OUBLOG_VISIBILITY_COURSEUSER',   100);
define('OUBLOG_VISIBILITY_LOGGEDINUSER', 200);
define('OUBLOG_VISIBILITY_PUBLIC',       300);
/**#@-*/

/**#@+
 * Constants defining the ability to post comments
 */
define('OUBLOG_COMMENTS_PREVENT', 0);
define('OUBLOG_COMMENTS_ALLOW',   1);
/**#@-*/

/**#@+
 * Constant defining the number of posts to display per page
 */
define('OUBLOG_POSTS_PER_PAGE', 20);
/**#@-*/

/**#@+
 * Constant defining the max number of items in an RSS or Atom feed
 */
define('OUBLOG_MAX_FEED_ITEMS', 20);
/**#@-*/



/**
 * Get a blog from a user id
 *
 * @param int $userid
 * @return mixed Oublog object on success, false on failure
 */
function oublog_get_personal_blog($userid) {
    global $CFG;

    $sql = "SELECT b.*
            FROM {$CFG->prefix}oublog b
            WHERE b.global = 1";
    if (!$blog = get_record_sql($sql)) {
        error('Global blog is missing');
    }

    if (!$oubloginstance = get_record('oublog_instances', 'oublogid', $blog->id, 'userid', $userid)) {
        $user = get_record('user', 'id', $userid);
        oublog_add_bloginstance($blog->id, $userid, addslashes(get_string('defaultpersonalblogname', 'oublog', fullname($user))));
        if (!$oubloginstance = get_record('oublog_instances', 'oublogid', $blog->id, 'userid', $user->id)) {
            error('Could not get blog');
        }
    }

    return(array($blog, $oubloginstance));
}


/**
 * Checks if a user is allowed to view a blog. If not, will not return (calls
 * an error function and exits).
 *
 * @param object $oublog
 * @param object $context
 * @param object $cm
 * @return bool
 */
function oublog_check_view_permissions($oublog, $context, $cm=null) {
    global $COURSE;
    
    $capability=$oublog->global ? 'mod/oublog:viewpersonal' : 'mod/oublog:view';

    switch ($oublog->maxvisibility) {
        case OUBLOG_VISIBILITY_PUBLIC:
            course_setup($oublog->course);
            return;

        case OUBLOG_VISIBILITY_LOGGEDINUSER:
            require_login(SITEID, false);
            course_setup($oublog->course);
            // Check oublog:view cap
            if (!has_capability($capability, $context)) {
                error(get_string('accessdenied', 'oublog'));
            }
            return;

        case OUBLOG_VISIBILITY_COURSEUSER:
            require_course_login($oublog->course, false, $cm);
            // Check oublog:view cap
            if (!has_capability($capability, $context)) {
                error(get_string('accessdenied', 'oublog'));
            }
            return; 

        default:
            error('Unkown visibility level');
    }
}

/**
 * Determines whether the user can make a post to the given blog.
 * @param $oublog Blog object
 * @param $bloguserid Userid of person who owns blog (only needed for
 *   personal blog)
 * @param $cm Course-module (only needed if not personal blog)
 * @return bool True if user is allowed to make posts
 */
function oublog_can_post($oublog, $bloguserid=0, $cm=null) {
    global $USER;
    if($oublog->global) {
        if($bloguserid==0) {
            debugging('Calls to oublog_can_post for personal blogs must supply userid!',DEBUG_DEVELOPER);
        }
        // This needs to be your blog and you need the 'contributepersonal'
        // permission at system level
        return $bloguserid==$USER->id &&
            has_capability('mod/oublog:contributepersonal',
                get_context_instance(CONTEXT_SYSTEM));
    } else {
        // Need specific post permission in this blog
        return has_capability('mod/oublog:post',
            get_context_instance(CONTEXT_MODULE,$cm->id));
    }
}

/**
 * Determines whether the user can comment on the given blog.
 * @param $oublog Blog object
 * @param $cm Course-module (if not personal blog)
 * @return bool True if user is allowed to make comments
 */
function oublog_can_comment($oublog, $cm=null) {
    global $USER;
    if($oublog->global) {
        // Just need the 'contributepersonal' permission at system level
        return has_capability('mod/oublog:contributepersonal',
            get_context_instance(CONTEXT_SYSTEM));
    } else {
        // Need specific comment permission in this blog
        return has_capability('mod/oublog:comment',
            get_context_instance(CONTEXT_MODULE,$cm->id));
    }
}

/**
 * Determine if a user can view a post. Note that you must also call 
 * oublog_check_view_permissions for the blog as a whole.
 *
 * @param object $post
 * @param object $user
 * @param object $context
 * @param bool $personalblog True if this is on a personal blog
 * @return bool
 */
function oublog_can_view_post($post, $user, $context, $personalblog) {

    // Public visibility means everyone
    if($post->visibility==OUBLOG_VISIBILITY_PUBLIC) {
        return true;
    }
    // Logged-in user visibility means everyone logged in, but no guests
    if($post->visibility==OUBLOG_VISIBILITY_LOGGEDINUSER &&
        (isloggedin() && !isguestuser())) {
        return true;
    } elseif ($post->visibility==OUBLOG_VISIBILITY_LOGGEDINUSER) {
        return false;
    }
    
    if($post->visibility!=OUBLOG_VISIBILITY_COURSEUSER) {
        error('Invalid visibility level '. $post->visibility);
    }

    // Otherwise this is set to course visibility
    if($personalblog) {
         return $post->userid==$user->id;
    } else {
        // Check oublog:view capability at module level
        // This might not have been checked yet because if the blog is
        // set to public, you're allowed to view it, but maybe not this
        // post.
        return has_capability('mod/oublog:view',$context, $user->id);
    }
}



/**
 * Add a new blog post
 *
 * @param mixed $post An object containing all required post fields
 * @param object $cm Course-module for blog
 * @return mixed PostID on success or false
 */
function oublog_add_post($post,$cm,$oublog,$course) {
    global $CFG;

    if (!isset($post->oubloginstancesid)) {
        if (!$post->oubloginstancesid = get_field('oublog_instances', 'id', 'oublogid', $post->oublogid, 'userid', $post->userid)) {
            if (!$post->oubloginstancesid = oublog_add_bloginstance($post->oublogid, $post->userid)) {
                return(false);
            }
        }
    }
    if (!isset($post->timeposted)) {
        $post->timeposted = time();
    }

    // Begin transaction
    $tw=new transaction_wrapper();

    if (!$postid = insert_record('oublog_posts', $post)) {
        $tw->rollback();
        return(false);
    }
    if (isset($post->tags)) {
        oublog_update_item_tags($post->oubloginstancesid, $postid, $post->tags,$post->visibility);
    }

    $post->id=$postid; // Needed by the below
    if(!oublog_search_update($post,$cm)) {
        $tw->rollback();
        return(false);
    }

    // Inform completion system, if available
    if(class_exists('ouflags')) {
        if(completion_is_enabled($course,$cm) && ($oublog->completionposts)) {
            completion_update_state($course,$cm,COMPLETION_COMPLETE);
        }    
    }
    
    $tw->commit();

    return($postid);
}



/**
 * Update a blog post
 *
 * @param mixed $post An object containing all required post fields
 * @param object $cm Course-module for blog
 * @return bool
 */
function oublog_edit_post($post,$cm) {
    global $USER;

    if(!isset($post->id) || !$oldpost = get_record('oublog_posts', 'id', $post->id)) {
        return(false);
    }

    if (!$post->oubloginstancesid = get_field('oublog_instances', 'id', 'oublogid', $post->oublogid, 'userid', $post->userid)) {
        return(false);
    }

    // Begin transaction
    $tw=new transaction_wrapper();

    // insert edit history
    $edit = new stdClass();
    $edit->postid       = $post->id;
    $edit->userid       = $USER->id;
    $edit->timeupdated  = time();
    $edit->oldtitle     = addslashes($oldpost->title);
    $edit->oldmessage   = addslashes($oldpost->message);

    if (!insert_record('oublog_edits', $edit)) {
        $tw->rollback();
        return(false);
    }
    // Update tags
    if (!oublog_update_item_tags($post->oubloginstancesid, $post->id, $post->tags,$post->visibility)) {
        $tw->rollback();
        return(false);
    }

    // Update the post
    $post->timeupdated = $edit->timeupdated;
    $post->lasteditedby = $USER->id;

    if (isset($post->groupid)) {
        unset($post->groupid); // Can't change group
    }

    if (!update_record('oublog_posts', $post)) {
        $tw->rollback();
        return(false);
    }

    if(!oublog_search_update($post,$cm)) {
        $tw->rollback();
        return(false);
    }

    $tw->commit();

    return(true);
}



/**
 * Get all data required to print a list of blog posts as efficiently as possible
 *
 *
 * @param object $oublog
 * @param int $offset
 * @param int $userid
 * @return mixed all data to print a list of blog posts
 */
function oublog_get_posts($oublog, $context, $offset=0, $cm, $groupid, $userid=null, $tag='', $canaudit=false) {
    global $CFG, $USER;

    $sqlwhere = "bi.oublogid = $oublog->id";
    $sqljoin = '';

    if (isset($userid)) {
        $sqlwhere .= " AND bi.userid = $userid ";
    }
    if (isset($groupid) && $groupid) {
        $sqlwhere .= " AND p.groupid = $groupid ";
    }
    if (!$canaudit) {
        $sqlwhere .= " AND p.deletedby IS NULL ";
    }
    if ($tag) {
        $sqlwhere .= " AND t.tag = '".addslashes($tag)."' ";
        $sqljoin  .= " INNER JOIN {$CFG->prefix}oublog_taginstances ti ON p.id = ti.postid
                       INNER JOIN {$CFG->prefix}oublog_tags t ON ti.tagid = t.id ";
    }

    // visibility check
    if (!isloggedin() || isguestuser()){
    	$sqlwhere .= " AND p.visibility=" . OUBLOG_VISIBILITY_PUBLIC;
    } else {
    	if ($oublog->global) {
    	   $sqlwhere .= " AND (p.visibility >" . OUBLOG_VISIBILITY_COURSEUSER .
                " OR (p.visibility=".OUBLOG_VISIBILITY_COURSEUSER." AND u.id=". $USER->id ."))";
    	} 
    }
    
    // Get posts
    $fieldlist = "p.*, bi.oublogid, u.firstname, u.lastname, bi.userid, u.idnumber, u.picture, u.imagealt, u.email, u.username,
                ud.firstname AS delfirstname, ud.lastname AS dellastname,
                ue.firstname AS edfirstname, ue.lastname AS edlastname";
    $from = "FROM {$CFG->prefix}oublog_posts p
                INNER JOIN {$CFG->prefix}oublog_instances bi ON p.oubloginstancesid = bi.id
                INNER JOIN {$CFG->prefix}user u ON bi.userid = u.id
                LEFT JOIN {$CFG->prefix}user ud ON p.deletedby = ud.id
                LEFT JOIN {$CFG->prefix}user ue ON p.lasteditedby = ue.id
                $sqljoin";
    $sql = "SELECT $fieldlist
            $from                  
            WHERE  $sqlwhere 
            ORDER BY p.timeposted DESC
            ";
    $countsql = "SELECT count(p.id) $from WHERE $sqlwhere";

    if (!$rs = get_recordset_sql($sql, $offset,OUBLOG_POSTS_PER_PAGE)) {
        return(false);
    }

   // Get paging info
    $recordcnt = count_records_sql($countsql);//$rs->RecordCount();

    $cnt        = 0;
    $posts      = array();
    $postids    = array();

    while (($post = rs_fetch_next_record($rs)) && $cnt < OUBLOG_POSTS_PER_PAGE) {
    	if (oublog_can_view_post($post, $USER, $context, $oublog->global)) {
            if ($oublog->maxvisibility < $post->visibility) {
                $post->visibility = $oublog->maxvisibility;
            }
            if ($oublog->allowcomments == OUBLOG_COMMENTS_PREVENT) {
                $post->allowcomments = OUBLOG_COMMENTS_PREVENT;
            }

            $posts[$post->id] = $post;
            $postids[] = $post->id;
            $cnt++;
        }
    }

     rs_close($rs);

    if (empty($posts)) {
        return(true);
    }

    // Get tags for all posts on page
    $sql = "SELECT t.*, ti.postid
            FROM {$CFG->prefix}oublog_taginstances ti
            INNER JOIN {$CFG->prefix}oublog_tags t ON ti.tagid = t.id
            WHERE ti.postid IN ('".implode("','", $postids)."') ";

    $rs = get_recordset_sql($sql);
    while ($tag = rs_fetch_next_record($rs)) {
        $posts[$tag->postid]->tags[$tag->id] = $tag->tag;
    }

    rs_close($rs);

    // Get comments for post on the page
    $sql = "SELECT c.id, c.postid, c.timeposted, u.firstname, u.lastname, u.picture, u.imagealt, u.email, u.idnumber
            FROM {$CFG->prefix}oublog_comments c
            INNER JOIN {$CFG->prefix}user u ON c.userid = u.id
            WHERE c.postid IN ('".implode("','", $postids)."') AND c.deletedby IS NULL
            ORDER BY c.timeposted ASC ";

    $rs = get_recordset_sql($sql);
    while ($comment = rs_fetch_next_record($rs)) {
        $posts[$comment->postid]->comments[$comment->id] = $comment;
    }

    rs_close($rs);

    return(array($posts, $recordcnt));
}




/**
 * Get all data required to print a single blog post as efficiently as possible
 *
 *
 * @param int $postid
 * @return mixed all data to print a list of blog posts
 */
function oublog_get_post($postid, $canaudit=false) {
    global $CFG, $USER;

    // Get post
    $sql = "SELECT p.*, bi.oublogid, u.firstname, u.lastname, u.picture, u.imagealt, bi.userid, u.idnumber, u.email, u.username,
                    ud.firstname AS delfirstname, ud.lastname AS dellastname,
                    ue.firstname AS edfirstname, ue.lastname AS edlastname
            FROM {$CFG->prefix}oublog_posts p
                INNER JOIN {$CFG->prefix}oublog_instances bi ON p.oubloginstancesid = bi.id
                INNER JOIN {$CFG->prefix}user u ON bi.userid = u.id
                LEFT JOIN {$CFG->prefix}user ud ON p.deletedby = ud.id
                LEFT JOIN {$CFG->prefix}user ue ON p.lasteditedby = ue.id                
            WHERE p.id = $postid
            ORDER BY p.timeposted DESC
            ";

    if (!$post = get_record_sql($sql)) {
        return(false);
    }

    // Get tags for all posts on page
    $sql = "SELECT t.*, ti.postid
            FROM {$CFG->prefix}oublog_taginstances ti
            INNER JOIN {$CFG->prefix}oublog_tags t ON ti.tagid = t.id
            WHERE ti.postid = $postid ";


    $rs = get_recordset_sql($sql);
    while ($tag = rs_fetch_next_record($rs)) {
        $post->tags[$tag->id] = $tag->tag;
    }

    rs_close($rs);

    // Get comments for post on the page
    if ($post->allowcomments) {
        $sql = "SELECT c.*, u.firstname, u.lastname, u.picture, u.imagealt, u.email, u.idnumber,
                    ud.firstname AS delfirstname, ud.lastname AS dellastname
                FROM {$CFG->prefix}oublog_comments c
                INNER JOIN {$CFG->prefix}user u ON c.userid = u.id
                LEFT JOIN {$CFG->prefix}user ud ON c.deletedby = ud.id
                WHERE c.postid = $postid ";

        if (!$canaudit) {
            $sql .= "AND c.deletedby IS NULL ";
        }

        $sql .= "ORDER BY c.timeposted ASC ";

        $rs = get_recordset_sql($sql);
        while ($comment = rs_fetch_next_record($rs)) {
            $post->comments[$comment->id] = $comment;
        }

        rs_close($rs);
    }


    // Get edits for this post
    $sql = "SELECT e.id, e.timeupdated, e.oldtitle, e.userid, u.firstname, u.lastname, u.picture, u.imagealt, u.email, u.idnumber
            FROM {$CFG->prefix}oublog_edits e
            INNER JOIN {$CFG->prefix}user u ON e.userid = u.id
            WHERE e.postid = $postid
            ORDER BY e.timeupdated DESC ";

    $rs = get_recordset_sql($sql);
    while ($edit = rs_fetch_next_record($rs)) {
        $post->edits[$edit->id] = $edit;
    }

    rs_close($rs);

    return($post);
}



/**
 * Print a single blog post
 *
 * @param object $oublog Blog object
 * @param object $post
 * @param string $baseurl
 * @param string $blogtype
 * @param bool $canmanageposts
 * @param bool $canaudit
 * @param bool $cancomment
 * @return bool
 */
function oublog_print_post($oublog, $post, $baseurl, $blogtype, $canmanageposts=false, $canaudit=false, $cancomment=false, $commentcount=true) {
    global $CFG, $USER;

    // Get rid of any existing tag from the URL as we only support one at a time
    $baseurl=preg_replace('~&amp;tag=[^&]*~','',$baseurl);

    $strcomment     = get_string('comment', 'oublog');
    $strtags        = get_string('tags', 'oublog');
    $stredit        = get_string('edit', 'oublog');
    $strdelete      = get_string('delete', 'oublog');

    $extraclasses = $post->deletedby ? ' oublog-deleted':'';
    if($CFG->oublog_showuserpics) {
        $extraclasses.=' oublog-hasuserpic';
    }

    echo '<div class="oublog-post'.$extraclasses.'">';

    if($CFG->oublog_showuserpics) {
        print '<div class="oublog-userpic">';
        $postuser = new object();
        $postuser->id        = $post->userid;
        $postuser->firstname = $post->firstname;
        $postuser->lastname  = $post->lastname;
        $postuser->imagealt  = $post->imagealt;
        $postuser->picture   = $post->picture;
        print_user_picture($postuser,$oublog->course);
        print '</div>';
    }

    $formattedtitle=format_string($post->title);
    if(trim($formattedtitle)!=='') {
        echo '<h2 class="oublog-title">'.format_string($post->title).'</h2>';
    }
    
    if ($post->deletedby) {
        $deluser = new stdClass();
        $deluser->firstname = $post->delfirstname;
        $deluser->lastname  = $post->dellastname;

        $a = new stdClass();
        $a->fullname = '<a href="../../user/view.php?id='.$post->deletedby.'">'.fullname($deluser).'</a>';
        $a->timedeleted = oublog_date($post->timedeleted);

        echo '<div class="oublog-post-deletedby">'.get_string('deletedby', 'oublog', $a).'</div>';
    }

    echo '<div class="oublog-post-date">';
    echo oublog_date($post->timeposted);
    echo ' ';
    if ($blogtype == 'course' || strpos($_SERVER['REQUEST_URI'],'allposts.php')!=0) {
        echo '<div class="oublog-postedby">';
        echo get_string('postedby', 'oublog', '<a href="../../user/view.php?id='.$post->userid.'&amp;course='.$oublog->course.'">'.fullname($post).'</a>');
        echo '</div> ';
    }
    echo '</div>';
    
        if (isset($post->edits) && ($canaudit || $post->userid == $USER->id)) {
        echo '<div class="oublog-post-editsummary">';
        foreach ($post->edits as $edit) {
            $a = new stdClass();
            $a->editby = fullname($edit);
            $a->editdate = oublog_date($edit->timeupdated);
            if ($edit->userid == $post->userid) {
               echo '- <a href="viewedit.php?edit='.$edit->id.'">'.get_string('editsummary', 'oublog', $a).'</a><br />';
            } else {
               echo '- <a href="viewedit.php?edit='.$edit->id.'">'.get_string('editonsummary', 'oublog', $a).'</a><br />';
            }
        }
        echo '</div>';
    } else if ($post->lasteditedby) {
        $edit=new StdClass;
        $edit->firstname=$post->edfirstname;
        $edit->lastname=$post->edlastname;
        
        $a = new stdClass();
        $a->editby = fullname($edit);
        $a->editdate = oublog_date($post->timeupdated);        
        echo '<div class="oublog-post-editsummary">'.get_string('editsummary', 'oublog', $a).'</div>';
    }

    echo '<div class="oublog-post-visibility">';
    echo oublog_get_visibility_string($post->visibility,$blogtype=='personal');
    echo '</div>';

    echo '<div class="oublog-post-content">';
    echo format_text($post->message, FORMAT_HTML);
    echo '</div>';

    if (isset($post->tags)) {
        echo '<div class="oublog-post-tags">'.$strtags.': ';
        foreach ($post->tags as $taglink) {
            echo '<a href="'.$baseurl.'&amp;tag='.urlencode($taglink).'">'.$taglink.'</a> ';
        }
        echo '</div>';
    }

    echo '<div class="oublog-post-links">';
    if (!$post->deletedby) {
        if (($post->userid == $USER->id || $canmanageposts)) {
            echo ' <a href="'.$CFG->wwwroot.'/mod/oublog/editpost.php?blog='.$post->oublogid.'&amp;post='.$post->id.'">'.$stredit.'</a>';
            echo ' <a href="'.$CFG->wwwroot.'/mod/oublog/deletepost.php?blog='.$post->oublogid.'&amp;post='.$post->id.'">'.$strdelete.'</a>';
        }
        if ($post->allowcomments) {
            if (isset($post->comments) && $commentcount) {
                echo " <a href=\"{$CFG->wwwroot}/mod/oublog/viewpost.php?post={$post->id}\">".
                    get_string(count($post->comments)==1 ? '1comment' : 'ncomments', 'oublog', count($post->comments))."</a> ";
                $last = array_pop($post->comments);
                array_push($post->comments, $last);
                $a = new stdClass();
                $a->fullname    = fullname($last);
                $a->timeposted  = oublog_date($last->timeposted,true);
                echo get_string('lastcomment', 'oublog', $a);

            } elseif ($cancomment) {
                echo " <a href=\"{$CFG->wwwroot}/mod/oublog/editcomment.php?blog={$post->oublogid}&amp;post={$post->id}\">$strcomment</a>";
            }
        }
    }
    echo '</div>';
    echo '</div>';

    return(true);
}



/**
 * Add a blog_instance
 *
 * @param int $oublogid
 * @param int $userid
 * @param string $name
 * @param string $summary
 * @return mixed oubloginstancesid on success or false
 */
function oublog_add_bloginstance($oublogid, $userid, $name='', $summary=null) {

    $oubloginstance = new stdClass;
    $oubloginstance->oublogid      = $oublogid;
    $oubloginstance->userid        = $userid;
    $oubloginstance->name          = $name;
    $oubloginstance->summary       = $summary;
    $oubloginstance->accesstoken   = md5(uniqid(rand(), true));

    return(insert_record('oublog_instances', $oubloginstance));
}

/**
 * Clarifies a $tags value which may be a string or an array of values,
 * returning an array of strings.
 * @param mixed $tags
 * @return array Array of tag strings
 */
function oublog_clarify_tags($tags) {
    if (is_string($tags)) {
        if (!$tags = explode(',', $tags)) {
            return array();
        }
    } elseif (!is_array($tags)) {
        return array();
    }

    $tl=textlib_get_instance();
    foreach($tags as $idx => $tag) {
        $tag = stripslashes($tl->strtolower(trim($tag)));
        if (empty($tag)) {
            unset($tags[$idx]);
            continue;
        }

        $tags[$idx] = $tag;
    }

    $tags = array_unique($tags);

    return $tags;
}

/**
 * Update a posts tags
 *
 * @param int $oubloginstanceid
 * @param int $postid
 * @param mixed $tags Comma separated string or an array
 * @uses $CFG
 */
function oublog_update_item_tags($oubloginstancesid, $postid, $tags, $postvisibility=OUBLOG_VISIBILITY_COURSEUSER) {
    global $CFG;
    
    $tagssql = array();
    $tagids = array();

    // Removed any existing
    delete_records('oublog_taginstances', 'postid', $postid);

    $tags=oublog_clarify_tags($tags);
    
    if (class_exists('ouflags')) {
        require_once($CFG->dirroot.'/tag/lib.php');
        // now copy to core tags table
        if ($postvisibility==OUBLOG_VISIBILITY_COURSEUSER) {
           	tag_set('oublog',$postid,array());
        } else {
        	tag_set('oublog',$postid,$tags);
        }
    }
    if (empty($tags)) {
    	return(true);
    }

    foreach($tags as $tag) {
        $tagssql[] = "'".addslashes($tag)."'";
    }

    // get the id's of the know tags
    $sql = "SELECT tag, id FROM {$CFG->prefix}oublog_tags WHERE tag IN (".implode(',', $tagssql).")";
    $tagids = get_records_sql($sql);

    // insert the remainder
    foreach ($tags as $tag) {
        if (!isset($tagids[$tag])) {
            $tagobj->tag = addslashes($tag);
            $tagids[$tag]->id = insert_record('oublog_tags', $tagobj);
        }
        $taginstance = new stdClass();
        $taginstance->tagid = $tagids[$tag]->id;
        $taginstance->postid = $postid;
        $taginstance->oubloginstancesid = $oubloginstancesid;

        insert_record('oublog_taginstances', $taginstance);

    }
    
    return(true);
}



/**
 * Get post tags in a CSV format
 *
 * @param int $postid
 * @return string
 * @uses $CFG;
 */
function oublog_get_tags_csv($postid) {
    global $CFG;

    $sql = "SELECT t.tag
            FROM {$CFG->prefix}oublog_taginstances ti
            INNER JOIN {$CFG->prefix}oublog_tags t ON ti.tagid = t.id
            WHERE ti.postid = $postid ";

    if ($tags = get_fieldset_sql($sql)) {
        return(implode(', ', $tags));
    } else {
        return('');
    }
}



/**
 * Get weighted tags for a given blog or blog instance
 *
 * @param int $oublogid
 * @param int $oubloginstanceid
 * @return array Tag data
 */
function oublog_get_tags($oublogid, $groupid, $cm, $oubloginstanceid=null) {
    global $CFG;

    $tags = array();

    $sqlwhere = "bi.oublogid = $oublogid ";
    if (isset($oubloginstanceid)) {
        $sqlwhere .= "AND ti.oubloginstancesid = $oubloginstanceid ";
    }

    if (isset($groupid) && $groupid) {
        $sqlwhere .= " AND p.groupid = $groupid ";
    }
    if (!empty($CFG->enablegroupings) && !empty($cm->groupingid)) {
        if ($groups = get_records('groupings_groups', 'groupingid', $cm->groupingid, null, 'groupid')) {
            $sqlwhere .= "AND p.groupid IN (".implode(',', array_keys($groups)).") ";
        }
    }

    $sql = "SELECT t.id, t.tag, COUNT(ti.id) AS count
            FROM {$CFG->prefix}oublog_instances bi
                INNER JOIN {$CFG->prefix}oublog_taginstances ti ON ti.oubloginstancesid = bi.id
                INNER JOIN {$CFG->prefix}oublog_tags t ON ti.tagid = t.id
                INNER JOIN {$CFG->prefix}oublog_posts p ON ti.postid = p.id
            WHERE $sqlwhere
            GROUP BY t.id, t.tag
            ORDER BY count DESC";

    if ($tags = get_records_sql($sql)) {

        $first = array_shift($tags);
        $max = $first->count;
        array_unshift($tags, $first);

        $last = array_pop($tags);
        $min = $last->count;
        array_push($tags, $last);

        $delta = $max-$min+0.00000001;

        foreach($tags as $idx => $tag) {
            $tags[$idx]->weight = round(($tag->count-$min)/$delta*4);
        }
        sort($tags);
    }
    return($tags);
}



/**
 * Print a tag cloud for a given blog or blog instance
 *
 * @param string $baseurl
 * @param int $oublogid
 * @param int $groupid
 * @param object $cm
 * @param int $oubloginstanceid
 * @return string Tag cloud HTML
 */
function oublog_get_tag_cloud($baseurl, $oublogid, $groupid, $cm, $oubloginstanceid=null) {
    $cloud = '';
    $urlparts= array();

    $baseurl = oublog_replace_url_param($baseurl, 'tag');

    if (!$tags = oublog_get_tags($oublogid, $groupid, $cm, $oubloginstanceid)) {
        return($cloud);
    }

    foreach($tags as $tag) {
        $cloud .= '<a href="'.$baseurl.'&amp;tag='.urlencode($tag->tag).'" class="oublog-tag-cloud-'.$tag->weight.'"><span class="oublog-tagname">'.strtr(($tag->tag), array(' '=>'&nbsp;')).'</span><span class="oublog-tagcount">('.$tag->count.')</span></a> ';
    }

    return($cloud);
}



/**
 * Translate a visibility number into a language string
 *
 * @param int $vislevel
 * @param bool $personal True if this is a personal blog
 * @return string
 */
function oublog_get_visibility_string($vislevel,$personal) {

    // Modify visibility string for optional shared activity blog
    global $CFG, $COURSE;
    $visibleusers = 'visiblecourseusers';
    $sharedactvfile = $CFG->dirroot.'/course/format/sharedactv/sharedactv.php';
    if (file_exists($sharedactvfile)) {
        include_once($sharedactvfile);
        if (function_exists('sharedactv_is_magic_course') && sharedactv_is_magic_course($COURSE)) {
            $visibleusers = 'visibleblogusers';
        }
    }

    switch ($vislevel) {
        case OUBLOG_VISIBILITY_COURSEUSER:
            return get_string($personal ? 'visibleyou' : $visibleusers, 'oublog');    
        case OUBLOG_VISIBILITY_LOGGEDINUSER:
            return(get_string('visibleloggedinusers', 'oublog'));
        case OUBLOG_VISIBILITY_PUBLIC:
            return(get_string('visiblepublic', 'oublog'));
        default:
            error('Invalid visibility level');
    }
}


/**
 * Add a blog comment
 *
 * @param object $comment
 * @return mixed commentid on success or false
 */
function oublog_add_comment($course,$cm,$oublog,$comment) {
    global $CFG;
    
    if (!isset($comment->timeposted)) {
        $comment->timeposted = time();
    }

    $id=insert_record('oublog_comments', $comment);
    if($id) {
        // Inform completion system, if available
        if(class_exists('ouflags')) {
            if(completion_is_enabled($course,$cm) && ($oublog->completioncomments)) {
                completion_update_state($course,$cm,COMPLETION_COMPLETE);
            }    
        }
    }
    return $id;
}



/**
 * Update the hit count for a blog and return the current hits
 *
 * @param object $oublog
 * @param object $oubloginstance
 * @return int
 */
function oublog_update_views($oublog, $oubloginstance) {
    global $SESSION, $CFG;

    if ($oublog->global && isset($oubloginstance)) {
        if (!isset($SESSION->bloginstanceview[$oubloginstance->id])) {
            $SESSION->bloginstanceview[$oubloginstance->id] = true;
            $oubloginstance->views++;
            $sql = "UPDATE {$CFG->prefix}oublog_instances SET views = views + 1 WHERE id = ".$oubloginstance->id;
            execute_sql($sql, false);
        }
        return($oubloginstance->views);
    } else {
        if (!isset($SESSION->blogview[$oublog->id])) {
            $SESSION->blogview[$oublog->id] = true;
            $oublog->views++;
            $sql = "UPDATE {$CFG->prefix}oublog SET views = views + 1 WHERE id = ".$oublog->id;
            execute_sql($sql, false);
        }
        return($oublog->views);
    }


}

/**
 * Checks for a permission which you have EITHER if you have the specific 
 * permission OR if it's your own personal blog and you have post permission to
 * that blog.
 *
 * @param string $capability
 * @param object $oublog
 * @param object $oubloginstance (required for personal blog access)
 * @param object $context
 * @return bool True if you have permission
 */
function oublog_has_userblog_permission($capability,$oublog,$oubloginstance,$context) {    
    // For personal blogs you can do these things EITHER if you have the capability 
    // (ie for admins) OR if you are that user and you are allowed to post 
    // to blog (not banned)    
    global $USER;
    if($oublog->global && $oubloginstance && $USER->id == $oubloginstance->userid && 
        has_capability('mod/oublog:contributepersonal', $context)) {
        return true;
    }
    // Otherwise require the capability (note this also allows eg admin access
    // to personal blogs)
    return has_capability($capability, $context);
}

function oublog_require_userblog_permission($capability,$oublog,$oubloginstance,$context) {
    if(!oublog_has_userblog_permission($capability,$oublog,$oubloginstance,$context)) {
        require_capability($capability,$context);
    }
}

/**
 * Get the list of relevant links in HTML format
 *
 * @param object $oublog
 * @param object $oubloginstance
 * @param object $context
 * @return string HTML on success, false on failure
 */
function oublog_get_links($oublog, $oubloginstance, $context) {
    global $CFG;

    $strmoveup      = get_string('moveup');
    $strmovedown    = get_string('movedown');
    $stredit        = get_string('edit');
    $strdelete      = get_string('delete');
    
    $canmanagelinks = oublog_has_userblog_permission('mod/oublog:managelinks', $oublog,$oubloginstance,$context);    

    if ($oublog->global) {
        $links = get_records('oublog_links', 'oubloginstancesid', $oubloginstance->id, 'sortorder');
    } else {
        $links = get_records('oublog_links', 'oublogid', $oublog->id, 'sortorder');
    }
    $html = '';

    if ($links) {

        $html .= '<ul class="unlist">';
        $numlinks = count($links);
        $i=0;
        foreach ($links as $link) {
            $i++;
            $html .= '<li>';
            $html .= '<a href="'.htmlentities($link->url).'">'.format_string($link->title).'</a> ';

            if ($canmanagelinks) {
                if ($i > 1) {
                    $html .= '<form action="movelink.php" method="post" style="display:inline">';
                    $html .= '<input type="image" src="'.$CFG->pixpath.'/t/up.gif" />';
                    $html .= '<input type="hidden" name="down" value="0" />';
                    $html .= '<input type="hidden" name="link" value="'.$link->id.'" />';
                    $html .= '<input type="hidden" name="returnurl" value="'.$_SERVER['REQUEST_URI'].'" />';
                    $html .= '<input type="hidden" name="sesskey" value="'.sesskey().'" />';
                    $html .= '</form>';
                }
                if ($i < $numlinks) {
                    $html .= '<form action="movelink.php" method="post" style="display:inline">';
                    $html .= '<input type="image" src="'.$CFG->pixpath.'/t/down.gif" />';
                    $html .= '<input type="hidden" name="down" value="1" />';
                    $html .= '<input type="hidden" name="link" value="'.$link->id.'" />';
                    $html .= '<input type="hidden" name="returnurl" value="'.$_SERVER['REQUEST_URI'].'" />';
                    $html .= '<input type="hidden" name="sesskey" value="'.sesskey().'" />';
                    $html .= '</form>';
                }
                $html .= '<a href="editlink.php?blog='.$oublog->id.'&amp;link='.$link->id.'"><img src="'.$CFG->pixpath.'/t/edit.gif" alt="'.$stredit.'" class="iconsmall" /></a>';
                $html .= '<a href="deletelink.php?blog='.$oublog->id.'&amp;link='.$link->id.'"><img src="'.$CFG->pixpath.'/t/delete.gif" alt="'.$strdelete.'" class="iconsmall" /></a>';
            }
            $html .= '</li>';
        }
        $html .= '</ul>';
    }

    if ($canmanagelinks) {
        $html .= '<br />';
        if ($oublog->global) {
            $html .= '<a href="editlink.php?blog='.$oublog->id.'&amp;bloginstance='.$oubloginstance->id.'" class="oublog-links">'.get_string('addlink', 'oublog').'</a>';
        } else {
            $html .= '<a href="editlink.php?blog='.$oublog->id.'"  class="oublog-links">'.get_string('addlink', 'oublog').'</a>';
        }
    }

    return($html);

}



/**
 * Insert a link into the DB
 *
 * @param object $link
 * @return bool true on success, false on faulure
 */
function oublog_add_link($link) {
    global $CFG;

    // $link->oubloginstancesid is only set for personal blogs
    if (isset($link->oubloginstanceid)) {
        $sql = "SELECT MAX(sortorder) AS sortorder FROM {$CFG->prefix}oublog_links WHERE oubloginstancesid = ".$link->oubloginstancesid;
        $sortorder = get_field_sql($sql);
        $sortorder++;
    } else {
        $sql = "SELECT MAX(sortorder) AS sortorder FROM {$CFG->prefix}oublog_links WHERE oublogid = ".$link->oublogid;
        $sortorder = get_field_sql($sql);
        $sortorder++;
    }

    $link->sortorder = $sortorder;
    if (!insert_record('oublog_links', $link)) {
        return(false);
    }

    return(true);
}



/**
 * Update a link in the DB
 *
 * @param object $link
 * @return bool true on success, false on faulure
 */
function oublog_edit_link($link) {

    unset($link->sortorder);

    return(update_record('oublog_links', $link));
}



/**
 * Delete a link from the DB
 *
 * @param object $oublog
 * @param object $link
 * @return bool true on success, false on faulure
 */
function oublog_delete_link($oublog, $link) {
    global $CFG;

    if ($oublog->global) {
        $where = "oubloginstancesid = {$link->oubloginstancesid} ";
    } else {
        $where = "oublogid = {$link->oublogid} ";
    }

    if (!delete_records('oublog_links', 'id', $link->id)) {
        return(false);
    }

    $sql = "UPDATE {$CFG->prefix}oublog_links
            SET sortorder = sortorder - 1
            WHERE $where AND sortorder > {$link->sortorder}
            ";

    return(execute_sql($sql, false));
}



/**
 * Return a timestamp of when a blog, or comment was last updated
 *
 * @param int $blogid
 * @param int $bloginstancesid
 * @param int $postid
 * @param bool $comments
 * @return int last modified timestamp
 */
function oublog_feed_last_changed($blogid, $bloginstancesid, $postid, $comments) {
    global $CFG;

    // Comments or posts?
    if ($comments) {
        $sql = "SELECT MAX(c.timeposted) AS timeposted
                FROM {$CFG->prefix}oublog_comments c ";
        if ($postid) {
            $sqljoin = '';
            $sqlwhere = "WHERE p.postid = $postid ";
        } elseif ($bloginstancesid) {
            $sqljoin  = "INNER JOIN {$CFG->prefix}oublog_posts p ON c.postid = p.id ";
            $sqlwhere = "WHERE p.oubloginstancesid = $bloginstancesid ";
        } else {
            $sqljoin  = "INNER JOIN {$CFG->prefix}oublog_posts p ON c.postid = p.id
                         INNER JOIN {$CFG->prefix}oublog_instances i ON p.oubloginstancesid = i.id ";
            $sqlwhere = "WHERE i.oublogid = $blogid ";
        }

    } else {
        $sql = "SELECT MAX(p.timeposted) AS timeposted
                FROM {$CFG->prefix}oublog_posts p ";

        if ($bloginstancesid) {
            $sqljoin  = '';
            $sqlwhere = "WHERE p.oubloginstancesid = $bloginstancesid ";
        } else {
            $sqljoin  = "INNER JOIN {$CFG->prefix}oublog_instances i ON p.oubloginstancesid = i.id ";
            $sqlwhere = "WHERE i.oublogid = $blogid ";
        }
    }

    return(get_field_sql($sql.$sqljoin.$sqlwhere));
}



/**
 * Get blog comments in a format compatable with RSS lib
 *
 * @param int $blogid
 * @param int $bloginstancesid
 * @param int $postid
 * @param object $user
 * @param int $allowedvisibility
 * @param int $groupid
 * @param object $cm
 * @return array
 */
function oublog_get_feed_comments($blogid, $bloginstancesid, $postid, $user, $allowedvisibility, $groupid, $cm) {
    global $CFG;

    $items = array();

    if ($postid) {
        $sqlwhere = "AND p.id = $postid ";
    } elseif ($bloginstancesid) {
        $sqlwhere = "AND p.oubloginstancesid = $bloginstancesid ";
    } else {
        $sqlwhere = "AND i.oublogid = $blogid ";
    }

    if (isset($groupid) && $groupid) {
        $sqlwhere .= " AND p.groupid = $groupid ";
    }
    if (!empty($CFG->enablegroupings) && !empty($cm->groupingid)) {
        if ($groups = get_records('groupings_groups', 'groupingid', $cm->groupingid, null, 'groupid')) {
            $sqlwhere .= "AND p.groupid IN (".implode(',', array_keys($groups)).") ";
        }
    }


    $sql = "SELECT c.id, c.postid, c.title, c.message AS description, c.timeposted AS pubdate, i.userid, u.firstname, u.lastname, u.picture, u.imagealt, u.email, u.idnumber
            FROM {$CFG->prefix}oublog_comments c
            INNER JOIN {$CFG->prefix}oublog_posts p ON c.postid = p.id
            INNER JOIN {$CFG->prefix}oublog_instances i ON p.oubloginstancesid = i.id
            INNER JOIN {$CFG->prefix}user u ON i.userid = u.id
            WHERE c.deletedby IS NULL AND p.visibility >= $allowedvisibility $sqlwhere
            ORDER BY c.timeposted DESC ";

    $rs = get_recordset_sql($sql, 0, OUBLOG_MAX_FEED_ITEMS);

    while($item = rs_fetch_next_record($rs)) {
        $item->link = $CFG->wwwroot.'/mod/oublog/viewpost.php?post='.$item->postid.'#oublog-comment-'.$item->id;
        $item->author = fullname($item);
        $items[] = $item;
    }
    return($items);
}



/**
 * Get post in a format compatable with RSS lib
 *
 * @param int $blogid
 * @param int $bloginstancesid
 * @param object $user
 * @param bool $allowedvisibility
 * @param int $groupid
 * @param object $cm
 * @return array
 */
function oublog_get_feed_posts($blogid, $bloginstance, $user, $allowedvisibility, $groupid, $cm, $oublog) {
    global $CFG;

    $items = array();

    if ($bloginstance) {
        $sqlwhere = "AND p.oubloginstancesid = $bloginstance->id ";
    } else {
        $sqlwhere = "AND i.oublogid = $blogid ";
    }

    if ($groupid) {
        $sqlwhere .= " AND p.groupid = $groupid ";
    }
    if (!empty($CFG->enablegroupings) && !empty($cm->groupingid)) {
        if ($groups = get_records('groupings_groups', 'groupingid', $cm->groupingid, null, 'groupid')) {
            $sqlwhere .= "AND p.groupid IN (".implode(',', array_keys($groups)).") ";
        }
    }

    // Scheme URL for tags    
    $scheme = $CFG->wwwroot . '/mod/oublog/';
    if ($oublog->global) {
        if (!$bloginstance) {
            $scheme .= 'allposts.php?tag=';
        } else {
            $scheme .= 'view.php?user=' . $bloginstance->userid . '&tag=';
        }
    } else {
        $scheme .= 'view.php?id=' . $cm->id;
        if($groupid) {
            $scheme .= '&group=' . $groupid;
        }
        $scheme .= '&tag=';
    }

    // Get posts
    $sql = "SELECT p.id, p.title, p.message AS description, p.timeposted AS pubdate, i.userid, u.firstname, u.lastname, u.email, u.picture, u.imagealt, u.idnumber
            FROM {$CFG->prefix}oublog_posts p
            INNER JOIN {$CFG->prefix}oublog_instances i ON p.oubloginstancesid = i.id
            INNER JOIN {$CFG->prefix}user u ON i.userid = u.id
            WHERE p.deletedby IS NULL AND p.visibility >= $allowedvisibility $sqlwhere
            ORDER BY p.timeposted DESC ";

    $rs = get_recordset_sql($sql, 0, OUBLOG_MAX_FEED_ITEMS);
    while($item = rs_fetch_next_record($rs)) {
        $item->link = $CFG->wwwroot.'/mod/oublog/viewpost.php?post='.$item->id;
        $item->author = fullname($item);
        $item->tags = array();
        $item->tagscheme = $scheme;
        $items[$item->id] = $item;
    }
    rs_close($rs);

    // Get all tags related to these posts and fill them in
    $sql = "SELECT p.id AS postid, t.id AS tagid, t.tag
            FROM {$CFG->prefix}oublog_posts p
            INNER JOIN {$CFG->prefix}oublog_instances i ON p.oubloginstancesid = i.id
            INNER JOIN {$CFG->prefix}oublog_taginstances ti ON p.id = ti.postid
            INNER JOIN {$CFG->prefix}oublog_tags t ON ti.tagid = t.id
            WHERE p.deletedby IS NULL AND p.visibility >= $allowedvisibility $sqlwhere";

    $rs = get_recordset_sql($sql);
    while($tag = rs_fetch_next_record($rs)) {
        if(array_key_exists($tag->postid, $items)) {
            $items[$tag->postid]->tags[$tag->tagid] = $tag->tag;
        }
    }
    rs_close($rs);

    return($items);
}



/**
 * Get a url to a feed
 *
 * @param string $format atom or rss
 * @param object $oublog
 * @param object $bloginstance
 * @param int $groupid
 * @param bool $comments
 * @param int $postid
 * @param unknown_type $context
 * @return string
 * @uses $CFG
 * @uses $USER
 */
function oublog_get_feedurl($format, $oublog, $bloginstance, $groupid, $comments, $postid, $cm) {
    global $CFG, $USER;

    $url  = $CFG->wwwroot.'/mod/oublog/feed.php';
    $url .= '?format='.$format;
    $url .= '&amp;blog='.$oublog->id;
    if ($oublog->global) {
        if ((is_null($bloginstance) || is_string($bloginstance) && $bloginstance=='all')) {
            $url .= '&amp;bloginstance=all';
            $accesstoken = $oublog->accesstoken;
        } else {
            $url .= '&amp;bloginstance='.$bloginstance->id;
            $accesstoken = $bloginstance->accesstoken;
        }
    } else {
        $accesstoken = $oublog->accesstoken;
    }

    if ($groupid) {
        $url .= '&amp;group='.$groupid;
    }

    $url .= '&amp;comments='.$comments;

    // Visibility level
    if (!isloggedin() || isguestuser()) {
        // pub
    } else {
        $url .= '&amp;viewer='.$USER->id;
        // Don't use the 'full' token in personal blogs. We don't need personal
        // blog feeds to include draft posts, even for the user (who's the only
        // one allowed to see them) and it generates potential confusion.
        if (!$oublog->global && oublog_can_post($oublog, 0, $cm)) {
            // Full token changed to v2 after a security issue
            $url .= '&amp;full='.md5($accesstoken.$USER->id.OUBLOG_VISIBILITY_COURSEUSER . 'v2');
        } else {
            $url .= '&amp;loggedin='.md5($accesstoken.$USER->id.OUBLOG_VISIBILITY_LOGGEDINUSER);
        }
    }

    return($url);
}



/**
 * Get a block containing links to the Atom and RSS feeds
 *
 * @param object $oublog
 * @param object $bloginstance
 * @param int $groupid
 * @param int $postid
 * @param object $context
 * @return string HTML of block
 * @uses $CFG
 */
function oublog_get_feedblock($oublog, $bloginstance, $groupid, $postid, $cm) {
    global $CFG;

    if (!$CFG->enablerssfeeds) {
        return(false);
    }

    $blogurlatom = oublog_get_feedurl('atom',  $oublog, $bloginstance, $groupid, false, false, $cm);
    $blogurlrss = oublog_get_feedurl('rss',  $oublog, $bloginstance, $groupid, false, false, $cm);

    if (!is_string($bloginstance)) {
    	$commentsurlatom = oublog_get_feedurl('atom',  $oublog, $bloginstance, $groupid, true, $postid, $cm);
        $commentsurlrss = oublog_get_feedurl('rss',  $oublog, $bloginstance, $groupid, true, $postid, $cm);
    }
    
    $html  = get_string('subscribefeed', 'oublog');
    $html .= helpbutton('feed', get_string('feedhelp', 'oublog'), 'oublog', true, false, '', true);
    $html .= '<br />';//<br /><img src="'.$CFG->pixpath.'/i/rss.gif" alt="'.get_string('blogfeed', 'oublog').'"  class="feedicon" />';
    $html .= get_string('blogfeed', 'oublog').': ';
    $html .= '<a href="'.$blogurlatom.'">'.get_string('atom', 'oublog').'</a> ';
    $html .= '<a href="'.$blogurlrss.'">'.get_string('rss', 'oublog').'</a>';

    if($oublog->allowcomments) {
        if (!is_string($bloginstance)) {
            $html .= '<div class="oublog-links">'.get_string('commentsfeed', 'oublog').': ';
            $html .= '<a href="'.$commentsurlatom.'">'.get_string('atom', 'oublog').'</a> ';
            $html .= '<a href="'.$commentsurlrss.'">'.get_string('rss', 'oublog').'</a>';
            $html .= '</div>';
    	}
    }
    return ($html);
}



/**
 * Get extra meta tags that need to go into the page header
 *
 * @param object $oublog
 * @param object $bloginstance
 * @param int $groupid
 * @param object $context
 * @return string
 */
function oublog_get_meta_tags($oublog, $bloginstance, $groupid, $cm) {
    global $CFG;

    $meta = '';
    $blogurlatom = oublog_get_feedurl('atom',  $oublog, $bloginstance, $groupid, false, false, $cm);
    $blogurlrss = oublog_get_feedurl('rss',  $oublog, $bloginstance, $groupid, false, false, $cm);

    if ($CFG->enablerssfeeds) {
        $meta .= '<link rel="alternate" type="application/atom+xml" title="'.get_string('atomfeed', 'oublog').'" href="'.$blogurlatom .'" />';
        $meta .= '<link rel="alternate" type="application/atom+xml" title="'.get_string('rssfeed', 'oublog').'" href="'.$blogurlrss .'" />';
    }

    return ($meta);
}



/**
 * replace a variable withing a querystring
 *
 * @param string $url
 * @param string $replacekey
 * @param string $newvalue
 * @return string
 */
function oublog_replace_url_param($url, $replacekey, $newvalue=null) {

    $urlparts = parse_url(html_entity_decode($url));

    $queryparts = array();

    parse_str($urlparts['query'], $queryparts);

    unset($queryparts[$replacekey]);

    if ($newvalue) {
        $queryparts[$replacekey] = $newvalue;
    }

    foreach($queryparts as $key => $value) {
        $queryparts[$key] = "$key=$value";
    }
    $url = $urlparts['path'].'?'.implode('&amp;', $queryparts);

    return($url);
}

/** @return True if OU search extension is installed */
function oublog_search_installed() {
    return @include_once(dirname(__FILE__).'/../../blocks/ousearch/searchlib.php');
}

/**
 * Obtains a search document relating to a particular blog post.
 *
 * @param object $post Post object. Required fields: id (optionally also 
 *   groupid, userid save a db query)
 * @param object $cm Course-module object. Required fields: id, course
 * @return ousearch_doument
 */
function oublog_get_search_document($post,$cm) {
    // Set up 'search document' to refer to this post
    $doc=new ousearch_document();
    $doc->init_module_instance('oublog',$cm);
    if(!isset($post->userid) || !isset($post->groupid)) {
        global $CFG;
        $results=get_record_sql("
SELECT
    p.groupid,i.userid
FROM
    {$CFG->prefix}oublog_posts p 
    INNER JOIN {$CFG->prefix}oublog_instances i ON p.oubloginstancesid=i.id
WHERE
    p.id=$post->id");
        if(!$results) {
            error("Can't find details for blog post $post->id"); 
        }
        $post->userid=$results->userid;
        $post->groupid=$results->groupid;
    }
    if($post->groupid) {
        $doc->set_group_id($post->groupid);
    }
    $doc->set_user_id($post->userid);
    $doc->set_int_refs($post->id);
    return $doc;
}

/**
 * Obtains tags for a $post object whether or not it currently has them
 * defined in some way. (If they're not defined, uses a database query.)
 *
 * @param object $post Post object, must contain ->id at least
 * @param bool $includespaces If true, replaces the _ with space again
 * @return array Array of tags (may be empty)
 */
function oublog_get_post_tags($post,$includespaces=false) {
    global $CFG;

    // Work out tags from existing data if possible (to save adding a query)
    if(isset($post->tags)) {
        $taglist=oublog_clarify_tags($post->tags);
    } else {
        // Tags aren't in post so use database query
        $rs=get_recordset_sql("
SELECT
    t.tag
FROM
    {$CFG->prefix}oublog_taginstances ti
    INNER JOIN {$CFG->prefix}oublog_tags t ON ti.tagid = t.id
WHERE
    ti.postid={$post->id}");
        $taglist=array();
        while($rec=rs_fetch_next_record($rs)) {
            $taglist[]=$rec->tag;
        }
    }
    if($includespaces) {
        foreach($taglist as $ix=>$tag) {
            // Make the spaces in tags back into spaces so they're searchable (sigh)
            $taglist[$ix]=str_replace('_',' ',$tag);
        }
    }

    return $taglist;
}

/**
 * Updates the fulltext search information for a post which is being added or
 * updated.
 * @param object $post Post data, including slashes for database. Must have
 *   fields id,userid,groupid (if applicable), title, message
 * @param object $cm Course-module
 * @return True if search update was successful
 */
function oublog_search_update($post,$cm) {
    // Do nothing if OU search is not installed
    if (!oublog_search_installed()) {
        return true;
    }

    // Get search document
    $doc=oublog_get_search_document($post,$cm);

    // Sort out tags for use as extrastrings
    $taglist=oublog_get_post_tags($post,true);
    if(count($taglist)==0) {
        $taglist=null;
    }

    // Update information about this post (works ok for add or edit)
    return $doc->update(stripslashes($post->title),stripslashes($post->message),
        null,null,$taglist);
}

function oublog_date($time,$insentence=false) {
    if(function_exists('specially_shrunken_date')) {
        return specially_shrunken_date($time,$insentence);
    } else {
        return userdate($time);
    }
}

/**
 * Creates navigation for a blog page header.
 * @param object $cm Moodle course-modules object
 * @param object $oublog Row object from 'oublog' table
 * @param object $oubloginstance Row object from 'oubloginstance' table
 * @param object $oubloguser Moodle user object
 * @param array $extranav Optional additional navigation entry (array with 
 *   'name', optional 'link', and 'type' fields); null for none
 * @return object Navigation item object
 */
function oublog_build_navigation($cm, $oublog, $oubloginstance, $oubloguser,
    $extranav=null) {
    global $CFG;
    $navlinks = array();
    if ($oublog->global) {
        // Personal blog
        $navlinks[] = array(
            'name' => fullname($oubloguser),
            'link' => $CFG->wwwroot . "/user/view.php?id=$oubloguser->id",
            'type' => 'misc');
        $navlinks[] = array(
            'name' => format_string($oubloginstance->name),
            'link' => "view.php?user=$oubloguser->id",
            'type' => 'misc');
        $cm = null;
    }
    if ($extranav) {
        $navlinks[] = $extranav;
    }
    return build_navigation($navlinks, $cm);
}

/**
 * Prints the summary block. This includes the blog summary
 * and possibly links to change it, depending on the type of blog and user
 * permissions.
 * @param object $oublog Blog object
 * @param object $oubloginstance Blog instance object
 * @param bool $canmanageposts True if they're allowed to edit the blog
 */
function oublog_print_summary_block($oublog, $oubloginstance, $canmanageposts) {
    global $USER;
    $links = '';
    if ($oublog->global) {
        $summary = $oubloginstance->summary;
        $name = $oubloginstance->name;
        if (($oubloginstance->userid == $USER->id) || $canmanageposts ) {
            $strblogoptions = get_string('blogoptions','oublog');
            $links = "<br /><a href=\"editinstance.php?instance={$oubloginstance->id}\" class=\"oublog-links\">$strblogoptions</a>";
        }
        $links .= "<br/><a href=\"allposts.php\" class=\"oublog-links\">".
            get_string('siteentries','oublog')."</a>";
    } else {
        $summary = $oublog->summary;
        $name = $oublog->name;
    }

    print_side_block(format_string($name), 
        format_text($summary, FORMAT_HTML) . $links, NULL, NULL, NULL, 
        array('id' => 'oublog-summary'), get_string('bloginfo','oublog'));
}


?>
