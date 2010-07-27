<?php
/**
 * Shared initialisation from wiki PHP pages.
 *
 * @copyright &copy; 2007 The Open University
 * @author s.marshall@open.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package ouwiki
 *//** */

// The dirname is needed as this file is being included by other modules which cant find the files
// if dirname is not used
require_once(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__).'/ouwiki.php');

global $CFG,$USER;

$id=required_param('id',PARAM_INT);           // Course Module ID that defines wiki
$pagename=stripslashes(optional_param('page',null,PARAM_RAW));    // Which page to show. Omitted for start page
$groupid=optional_param('group',0,PARAM_INT); // Group ID. If omitted, uses first appropriate group
$userid=optional_param('user',0,PARAM_INT);   // User ID (for individual wikis). If omitted, uses own

// Restrict page name
$tl = textlib_get_instance();
if ($tl->strlen($pagename) > 200) {
    print_error('pagenametoolong', 'ouwiki');
}

// Get basic information about this wiki
if(!$cm=get_coursemodule_from_id('ouwiki', $id)) {
    error("Course module ID was incorrect");
}
if(!$course=get_record("course", "id",$cm->course)) {
    error("Course is misconfigured");
}
if(!$ouwiki=get_record("ouwiki", "id",$cm->instance)) {
    error("Wiki ID is incorrect in database");
}
$context = get_context_instance(CONTEXT_MODULE, $cm->id);

global $DISABLESAMS;
if(empty($DISABLESAMS)) {
    // Make sure they're logged in and check they have permission to view
    require_course_login($course,true,$cm);
    require_capability('mod/ouwiki:view',$context);
}

// Get subwiki, creating it if necessary
$subwiki=ouwiki_get_subwiki($course,$ouwiki,$cm,$context,$groupid,$userid,true);

?>