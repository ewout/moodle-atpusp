<?php
/**
 * This page allows a user to add and edit related blog links
 *
 * @author Matt Clarkson <mattc@catalyst.net.nz>
 * @package oublog
 */

    require_once("../../config.php");
    require_once("locallib.php");
    require_once('link_form.php');

    $blog = required_param('blog', PARAM_INT);                          // Blog ID
    $bloginstancesid = optional_param('bloginstance', 0, PARAM_INT);     // Blog instances ID
    $linkid = optional_param('link', 0, PARAM_INT);                     // Comment ID for editing

    if ($blog) {
        if (!$oublog = get_record("oublog", "id", $blog)) {
            error('Blog parameter is incorrect');
        }
        if (!$cm = get_coursemodule_from_instance('oublog', $blog)) {
            error('Course module ID was incorrect');
        }
        if (!$course = get_record("course", "id", $oublog->course)) {
            error("Course is misconfigured");
        }
    }
    if ($linkid) {
        if (!$link = get_record('oublog_links', 'id', $linkid));
    }


/// Check security
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);
    oublog_check_view_permissions($oublog, $context, $cm);

    if($linkid) {
        $bloginstancesid=$link->oubloginstancesid;
    }
    $oubloginstance = $bloginstancesid ? get_record('oublog_instances', 'id', $bloginstancesid) : null;
    oublog_require_userblog_permission('mod/oublog:managelinks', $oublog,$oubloginstance,$context);

    if ($oublog->global) {
        $blogtype = 'personal';
        $oubloguser = $USER;
        $viewurl = 'view.php?user='.$oubloginstance->userid;
    } else {
        $blogtype = 'course';
        $viewurl = 'view.php?id='.$cm->id;
    }



/// Get strings
    $stroublogs  = get_string('modulenameplural', 'oublog');
    $stroublog   = get_string('modulename', 'oublog');
    $straddlink  = get_string('addlink', 'oublog');
    $streditlink = get_string('editlink', 'oublog');


    $mform = new mod_oublog_link_form('editlink.php', array('edit' => !empty($linkid)));

    if ($mform->is_cancelled()) {
        redirect($viewurl);
        exit;
    }

    if (!$frmlink = $mform->get_data()) {

        if (!isset($link)) {
            $link = new stdClass;
            $link->general = $straddlink;
        } else {
            $link->link = $link->id;
        }

        $link->blog = $blog;
        $link->bloginstance = $bloginstancesid;

        $mform->set_data($link);


/// Print the header
        if ($blogtype == 'personal') {

            $navlinks = array();
            $navlinks[] = array('name' => fullname($oubloguser), 'link' => "../../user/view.php?id=$oubloguser->id", 'type' => 'misc');
            $navlinks[] = array('name' => format_string($oublog->name), 'link' => 'view.php?blog='.$blog, 'type' => 'activityinstance');

            $navigation = build_navigation($navlinks);
            print_header_simple(format_string($oublog->name), "", $navigation, "", "", true);

        } else {
            $navlinks = array();
            $navlinks[] = array('name' => ($linkid ? $streditlink : $straddlink), 'link' => '', 'type' => 'misc');
            $navigation = build_navigation($navlinks, $cm);

            print_header_simple(format_string($oublog->name), "", $navigation, "", "", true,
                          update_module_button($cm->id, $course->id, $stroublog), navmenu($course, $cm));

        }

        echo '<br />';
        $mform->display();

        print_footer();

    } else {        
        if ($frmlink->link) {
            $frmlink->id = $frmlink->link;
            $frmlink->oublogid = $oublog->id;

            if (!oublog_edit_link($frmlink)) {
                error('Could not add link');
            }

        } else {
            unset($frmlink->id);
            $frmlink->oublogid = $oublog->id;
            $frmlink->oubloginstancesid = $bloginstancesid;

            if (!oublog_add_link($frmlink)) {
                error('Could not add link');
            }
        }

        redirect($viewurl);
    }

?>