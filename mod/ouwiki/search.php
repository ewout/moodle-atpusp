<?php
/**
 * View page. Displays wiki pages.
 *
 * @copyright &copy; 2007 The Open University
 * @author s.marshall@open.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package ouwiki
 *//** */

require('basicpage.php');
ouwiki_print_start($ouwiki,$cm,$course,$subwiki,get_string('searchresults'),$context);

require_once('../../blocks/ousearch/searchlib.php');

$querytext=stripslashes(required_param('query',PARAM_RAW));
$query=new ousearch_search($querytext);
$query->set_coursemodule($cm);
if($subwiki->groupid) {
    $query->set_group_id($subwiki->groupid);
}
if($subwiki->userid) {
    $query->set_user_id($subwiki->userid);
}

$foundsomething=ousearch_display_results(
    $query,'search.php?'.ouwiki_display_wiki_parameters(null,$subwiki,$cm));
    

// Footer
ouwiki_print_footer($course,$cm,$subwiki,null,'search.php?query='.urlencode($querytext),$foundsomething?null:'searchfailure',$querytext);
?>

