<?php
/**
 * 'Wiki index' page. Displays an index of all pages in the wiki, in 
 * various formats.
 *
 * @copyright &copy; 2007 The Open University
 * @author s.marshall@open.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package ouwiki
 *//** */

require('basicpage.php');

$treemode=optional_param('type','',PARAM_ALPHA)=='tree';

// Get basic wiki parameters
$wikiparams=ouwiki_display_wiki_parameters(null,$subwiki,$cm);

// Do header
ouwiki_print_start($ouwiki,$cm,$course,$subwiki,get_string('index','ouwiki'),$context,null,false);

// Print tabs for selecting index type
$tabrow=array();
$tabrow[]=new tabobject('alpha','wikiindex.php?'.$wikiparams,
    get_string('tab_index_alpha','ouwiki'));
$tabrow[]=new tabobject('tree','wikiindex.php?'.$wikiparams.'&amp;type=tree',
    get_string('tab_index_tree','ouwiki'));   
$tabs=array();
$tabs[]=$tabrow;
print_tabs($tabs,$treemode ? 'tree' : 'alpha');
print '<div id="ouwiki_belowtabs">';

global $orphans;

function ouwiki_display_page_in_index($indexitem,$subwiki,$cm) {    
    if($startpage=is_null($indexitem->title)) {
        $title=get_string('startpage','ouwiki');
        $output='<div class="ouw_index_startpage">';
    } else { 
        $title=$indexitem->title;
        $output='';
    }
    
    $output.='<a class="ouw_title" href="view.php?'.
        ouwiki_display_wiki_parameters($indexitem->title,$subwiki,$cm).
        '">'.htmlspecialchars($title).'</a>';
    $lastchange=new StdClass;
    $lastchange->userlink=ouwiki_display_user($indexitem,$cm->course);
    $lastchange->date=ouwiki_recent_span($indexitem->timecreated).ouwiki_nice_date($indexitem->timecreated).'</span>';
    $output.='<div class="ouw_indexinfo">';
    $output.=' <span class="ouw_lastchange">'.get_string('lastchange','ouwiki',$lastchange).'</span>';
    $output.='</div>';
    if($startpage) {
        $output.='</div>';
    }
    return $output; 
}

/**
 * Builds the tree structure for the hierarchical index. This is basically
 * a breadth-first search: we place each page on the nearest-to-home level
 * that we can find for it.
 */
function ouwiki_build_tree(&$index) {
    // Set up new data to fill
    foreach($index as $indexitem) {
        $indexitem->linksto=array();
        $indexitem->children=array();
    }
    
    // Preprocess: build links TO as well as FROM
    foreach($index as $indexitem) {
        foreach($indexitem->linksfrom as $fromid) {
            $index[$fromid]->linksto[]=$indexitem->pageid;            
        }
    }
    
    // Begin with top level, which is first in results
    reset($index);    
    $index[key($index)]->placed=true;
    $nextlevel=array(reset($index)->pageid);
    do {
        $thislevel=$nextlevel;
        $nextlevel=array();
        foreach($thislevel as $sourcepageid) {
            foreach($index[$sourcepageid]->linksto as $linkto) {
                if(empty($index[$linkto]->placed)) {
                    $index[$linkto]->placed=true;
                    $index[$sourcepageid]->children[]=$linkto;
                    $nextlevel[]=$linkto;
                }
            }
        }
    } while(count($nextlevel)>0);    
}

function ouwiki_tree_index($pageid,&$index,$subwiki,$cm) {
    $thispage=$index[$pageid];
    $output='<li>'.ouwiki_display_page_in_index($thispage,$subwiki,$cm);
    if(count($thispage->children)>0) {
        $output.='<ul>';
        foreach($thispage->children as $childid) {
            $output.=ouwiki_tree_index($childid,$index,$subwiki,$cm);
        }
        $output.='</ul>';
    }
    $output.='</li>';
    return $output;    
}

// Get actual index
$index=ouwiki_get_subwiki_index($subwiki->id);

$orphans=false;
if(count($index)==0) {
    print '<p>'.get_string('startpagedoesnotexist','ouwiki').'</p>';
} else if($treemode) {
    ouwiki_build_tree($index);
    // Print out in hierarchical form...
    print '<ul class="ouw_indextree">';
    print ouwiki_tree_index(reset($index)->pageid,$index,$subwiki,$cm);
    print '</ul>';
    
    foreach($index as $indexitem) {
        if(count($indexitem->linksfrom)==0 && !is_null($indexitem->title)) {        
            $orphans=true;
        }
    }    
} else {
    // ...or standard alphabetical
    print '<ul class="ouw_index">';
    foreach($index as $indexitem) {
        if(count($indexitem->linksfrom)!=0 || is_null($indexitem->title)) {        
            print '<li>'.ouwiki_display_page_in_index($indexitem,$subwiki,$cm).'</li>';
        } else {
            $orphans=true;
        }
    }
    print '</ul>';
}
    
if($orphans) {
    print '<h2 class="ouw_orphans">'.get_string('orphanpages','ouwiki').'</h2>';
    print '<ul class="ouw_index">';
    foreach($index as $indexitem) {
        if(count($indexitem->linksfrom)==0 && !is_null($indexitem->title)) {        
            print '<li>'.ouwiki_display_page_in_index($indexitem,$subwiki,$cm).'</li>';
        } 
    }
    print '</ul>';
}

$missing=ouwiki_get_subwiki_missingpages($subwiki->id);
if(count($missing)>0) {
    print '<div class="ouw_missingpages"><h2>'.get_string('missingpages','ouwiki').'</h2>';
    print '<p>'.get_string(count($missing)>1 ? 'advice_missingpages' : 'advice_missingpage','ouwiki').'</p>';
    print '<ul>';
    $first=true;
    foreach($missing as $title=>$from) {
        print '<li>';
        if($first) {
            $first=false;
        } else {
            print ' &#8226; ';
        }
        print '<a href="view.php?'.ouwiki_display_wiki_parameters($title,$subwiki,$cm).'">'.
            htmlspecialchars($title).'</a> <span class="ouw_missingfrom">('.
            get_string(count($from) > 1 ? 'frompages' : 'frompage','ouwiki',
                '<a href="view.php?'.ouwiki_display_wiki_parameters($from[0],$subwiki,$cm).'">'.
                ($from[0] ? htmlspecialchars($from[0]) : get_string('startpage','ouwiki')).'</a>)</span>');
        print '</li>';
    }
    print '</ul>';
    print '</div>';
}

if(count($index)!=0) {
    print '<div class="ouw_entirewiki"><h2>'.get_string('entirewiki','ouwiki').'</h2>';
    print '<p>'.get_string('onepageview','ouwiki').'</p><ul>';
    print '<li><a href="entirewiki.php?'.$wikiparams.'&amp;format=html">'.
        get_string('format_html','ouwiki').'</a></li>';
    if(file_exists(dirname(__FILE__).'/../../local/rtf.php')) {
        print '<li><a href="entirewiki.php?'.$wikiparams.'&amp;format=rtf">'.
            get_string('format_rtf','ouwiki').'</a></li>';
    }
    if(has_capability('moodle/course:manageactivities',$context)) {
        print '<li><a href="entirewiki.php?'.$wikiparams.'&amp;format=template">'.
            get_string('format_template','ouwiki').'</a></li>';
    }
    print '</ul></div>';
}

// Footer
ouwiki_print_footer($course,$cm,$subwiki,$pagename);
?>
