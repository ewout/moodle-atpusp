<?php
/**
 * Thanks
 *
 * This file provides the auto reply message and auto reply url to the user
 *
 * @author Jason Hardin
 * @author Sam Chaffee
 * @version $Id: thanks.php,v 2.0 2007/09/04
 * @package block_trouble_ticket
 **/
require_once('../../config.php');
require_once($CFG->libdir.'/blocklib.php');
if(file_exists($CFG->dirroot.'/course/format/page/lib.php')){
    require_once($CFG->dirroot.'/course/format/page/lib.php');
}

$id = optional_param('id', 0, PARAM_INT);

if($id != 0){
    $noid = false;
} else {
    $noid = true;
    $id = SITEID;
}
if (!$site = get_site()) {
    redirect($CFG->wwwroot .'/'. $CFG->admin .'/index.php');
}
if (!$course = get_record('course', 'id', $id) ) {
    error(get_string('invalidcourse', 'block_trouble_ticket')." id $id");
}
if (!$blockid = get_field('block', 'id', 'name', 'trouble_ticket')) {
    error(get_string('incorrectblockinstall', 'block_trouble_ticket'));
}
if (($instance = get_record('block_instance', 'blockid', $blockid, 'pageid', $id, 'pagetype', 'course-view')) && (!$noid)) {
    $ticket = block_instance('trouble_ticket',$instance);
}

require_login($course->id);

// determine the rediect url based on if we came from a page, course or the main site
if (isset($COURSE->format) and $COURSE->format == 'page') {
    $continuepage = $CFG->wwwroot.'/course/view.php?id='.$COURSE->id;

    // Try to set the page
    if ($page = page_get_current_page($COURSE->id)) {
        $continuepage .= '&amp;page='.$page->id;
    }
    $navigation = "<a href=\"$continuepage\">$COURSE->shortname</a> ->";
} else if ($id != SITEID) {
    $continuepage = $CFG->wwwroot.'/course/view.php?id='.$id;
    $navigation = "<a href=\"$continuepage\">$COURSE->shortname</a> ->";
} else {
    $continuepage = $CFG->wwwroot.'/';
    $navigation = '';
}

// The custom auto-reply message.
if (isset($ticket->config->autoreply)) {
    $autoreply = $ticket->config->autoreply;
} else if(!isset($CFG->block_trouble_ticket_autoreply)){
    $autoreply = get_string('autoreply','block_trouble_ticket');
} else {
    $autoreply = $CFG->block_trouble_ticket_autoreply;
}
if ((!empty($CFG->block_trouble_ticket_autoreply_url)) && (!empty($CFG->block_trouble_ticket_autoreply_linktext))) {
    // take off the "http://"
    $newURL = substr($CFG->block_trouble_ticket_autoreply_url, 7);
    $autoreplyurl = get_string('seealso', 'block_trouble_ticket').'<a href="'.$CFG->block_trouble_ticket_autoreply_url.'">'.$CFG->block_trouble_ticket_autoreply_linktext.'</a>';
}
if ((!empty($ticket->config->autoreplyurl)) && (!empty($ticket->config->autoreplylinktext))) {
    // take off the "http://"
    $newURL = substr($ticket->config->autoreplyurl, 7);
    $autoreplyurl = get_string('seealso', 'block_trouble_ticket').'<a href="'.$ticket->config->autoreplyurl.'">'.$ticket->config->autoreplylinktext.'</a>';
}

print_header(strip_tags($site->fullname), $site->fullname,$navigation.get_string('thankyou', 'block_trouble_ticket'), '',
             '<meta name="description" content="'. s(strip_tags($site->summary)). '">',true, '', '');
print_box_start('generalbox boxaligncenter boxwidthnormal centerpara');
print '<p>'.stripslashes_safe($autoreply).'</p>';
if(!empty($autoreplyurl)){
    print stripslashes_safe($autoreplyurl).'<br />';
}
print "<a target=\"_top\" href=\"$continuepage\">";
print_string('continue', 'block_trouble_ticket');
print "</a>";
print_box_end();
print_footer();
?>