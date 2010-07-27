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
ouwiki_print_start($ouwiki,$cm,$course,$subwiki,$pagename,$context);

// Look for activity integration frig
if(class_exists('ouflags')) {
    require_once(dirname(__FILE__).'/../../local/lib.php');
    check_activity_integration('ouwiki', $ouwiki->id);
}

// Print javascript
print '<script type="text/javascript" src="ouwiki.js"></script><script type="text/javascript">
strCloseComments="'.addslashes_js(get_string('closecomments','ouwiki')).'";
strCloseCommentForm="'.addslashes_js(get_string('closecommentform','ouwiki')).'";
</script>';

// Get the current page version
$pageversion=ouwiki_get_current_page($subwiki,$pagename);

ouwiki_print_tabs('view',$pagename,$subwiki,$cm,$context,$pageversion?true:false);

if(($pagename==='' || $pagename===null) && strlen(preg_replace('/\s|<br\s*\/?>|<p>|<\/p>/','',$ouwiki->summary))>0) {
    print '<div class="ouw_summary">'.format_text($ouwiki->summary).'</div>';
}

if($pageversion) {
    // Print page content
    print ouwiki_display_page($subwiki,$cm,$pageversion,true);
    print ouwiki_display_create_page_form($subwiki,$cm,$pageversion);
} else {
    // Page does not exist
    print '<p>'.get_string($pagename ? 'pagedoesnotexist' : 'startpagedoesnotexist','ouwiki').'</p>';
    if($subwiki->canedit) {
        print '<p>'.get_string('wouldyouliketocreate','ouwiki').'</p>';
        print "<form method='get' action='edit.php'>";
        print ouwiki_display_wiki_parameters($pagename,$subwiki,$cm,OUWIKI_PARAMS_FORM);
        print "<input type='submit' value='".get_string('createpage','ouwiki')."' /></form>";  
    }
}

if($timelocked=ouwiki_timelocked($subwiki,$ouwiki,$context)) {
    print '<div class="ouw_timelocked">'.$timelocked.'</div>';
}

// Show dashboard feature if enabled, on start page only
if (class_exists('ouflags') && ($pagename==='' || $pagename===null)) {
    require_once($CFG->dirroot . '/local/externaldashboard/external_dashboard.php');
    external_dashboard::print_favourites_button($cm);
}

// Footer
if(class_exists('ouflags')) {
    completion_set_module_viewed($course,$cm);    
}
ouwiki_print_footer($course,$cm,$subwiki,$pagename);
?>

