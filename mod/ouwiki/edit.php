<?php
/**
 * Edit page. Allows user to edit and/or preview wiki pages.
 *
 * @copyright &copy; 2007 The Open University
 * @author s.marshall@open.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package ouwiki
 *//** */

// Validate request
require_once(dirname(__FILE__).'/../../config.php');
if (!empty($_POST) && !confirm_sesskey()) {
    print_error('invalidrequest');
}

// We even display header etc. for preview posts (default is to hide for post)
require('basicpage.php');
require_once(dirname(__FILE__).'/../../lib/ajax/ajaxlib.php');
global $CFG;
// Check permission
if(!$subwiki->canedit) {
    error('You do not have permission to edit this wiki');
}

// Get content if entered yet
$content=optional_param('content',null,PARAM_CLEAN);
if($content) {
    // Remove slashes
    $content=stripslashes($content);
    
    // Check if they used the plaintext editor, if so fixup linefeeds
    $format=optional_param('format',0,PARAM_INT);
    if($format) {
        $content=ouwiki_plain_to_xhtml($content);
    }
    
    // Tidy up HTML
    $content=ouwiki_format_xhtml_a_bit($content);
}

// Get create link if set 
$newsectionname = optional_param('newsectionname',null,PARAM_RAW);  // new section name
$originalpagename = optional_param('originalpagename',null,PARAM_RAW);

$createnewpage = false;
$addnewsection = false;

// the if statement below needs to be in the order it is due to the fact that 
// originalpagename will be set either way from the form
if($newsectionname) {
    $addnewsection = true;
} else if($originalpagename !== null) {
    $createnewpage = true;
}

$originalpagename = stripslashes($originalpagename);
$newsectionname = stripslashes($newsectionname);

// add new section to content for display purposes
if ( $addnewsection) {
    $headingsize = 3;
    $a=new StdClass;
    $a->name= ouwiki_display_user($USER,$course->id);
    $a->date= userdate(time());
    $sectionheader .= '<h'.$headingsize.'>'.$newsectionname.'</h'.$headingsize.'><p>('.get_string('createdbyon','ouwiki',$a).' )</p>';
} // endif add new section from add button


// Get action types
$actionsave=array_key_exists('save',$_POST);
$actioncancel=array_key_exists('cancel',$_POST);

$wikiformfields=ouwiki_display_wiki_parameters($pagename,$subwiki,$cm,OUWIKI_PARAMS_FORM);

if ( $createnewpage) {
    
    $wikiformfields .= ouwiki_get_parameter('originalpagename',$originalpagename,OUWIKI_PARAMS_FORM);
    
    // Get the current page version, creating page if needed
    $pageversion=ouwiki_get_current_page($subwiki,$originalpagename,OUWIKI_GETPAGE_CREATE);
    $pageversion->xhtml = '';
    
} else {
    if ($addnewsection) {
        $wikiformfields .= ouwiki_get_parameter('newsectionname',$newsectionname,OUWIKI_PARAMS_FORM);
        $pageversion=ouwiki_get_current_page($subwiki,$page);
    } else {
        // Get the current page version, creating page if needed
        $pageversion=ouwiki_get_current_page($subwiki,$pagename,OUWIKI_GETPAGE_CREATE);
    } // endif
} // endif

// Need list of known sections on current version
$knownsections=ouwiki_find_sections($pageversion->xhtml);

// Get section, make sure the name is valid
$section=optional_param('section','',PARAM_RAW);
if(!preg_match('/^[0-9]+_[0-9]+$/',$section)) {
    $section=null;
}
if($section) {
    if(!array_key_exists($section,$knownsections)) {
        error("Unknown section $section"); 
    }
    $sectiontitle=$knownsections[$section];
    $sectiondetails=ouwiki_get_section_details($pageversion->xhtml,$section);    
}

// For everything except cancel we need to obtain a lock.
if(!$actioncancel) {
    // Get lock
    list($lockok,$lock)=ouwiki_obtain_lock($ouwiki,$pageversion->pageid);
}

// Handle save 
if($actionsave) {
    if (!$addnewsection ) {
        // Check we started editing the right version
        $startversionid=required_param('startversionid',PARAM_INT);
        $versionok=$startversionid==$pageversion->versionid;
    } else {
        $versionok=true;
    }

    // If we either don't have a lock or are editing the wrong version...    
    if(!$versionok || !$lockok) {
        ouwiki_release_lock($pageversion->pageid);
        
        ouwiki_print_start($ouwiki,$cm,$course,$subwiki,$pagename,$context);
        
        $savefailtitle=get_string('savefailtitle','ouwiki');
        $specificmessage=get_string(!$versionok?'savefaildesynch':'savefaillocked','ouwiki');
        $returntoview=get_string('returntoview','ouwiki');
        $savefailcontent=get_string('savefailcontent','ouwiki');
        $actualcontent=ouwiki_convert_content($content,$subwiki,$cm);
        print "
<div id='ouwiki_savefail'>
  <h2>$savefailtitle</h2>
  <p>$specificmessage</p>
  <form action='view.php' method='get'>
    $wikiformfields
    <input type='submit' value='$returntoview' />
  </form>
  <p>$savefailcontent</p>
  <div class='ouwiki_savefailcontent'>
    $actualcontent
  </div>
</div>";
        print_footer($course);
        exit;        
    }
    
    $section=optional_param('section',null,PARAM_RAW);
    if($section) {
        ouwiki_save_new_version_section($course,$cm,$ouwiki,$subwiki,$pagename,$pageversion->xhtml,$content,$sectiondetails);
    } else {
        if($createnewpage) {
            ouwiki_create_new_page($course, $cm, $ouwiki, $subwiki,$originalpagename, $pagename, $content);
        } else {
            if($addnewsection) {
                ouwiki_create_new_section($course,$cm, $ouwki, $subwiki, $pagename, $content, $sectionheader);
            } else {
                // do normal save
                ouwiki_save_new_version($course,$cm,$ouwiki, $subwiki,$pagename,$content);
            } // endif
        } // endif
    }        
}
// Redirect for save or cancel
if($actionsave || $actioncancel) {
    ouwiki_release_lock($pageversion->pageid);

    if ($actioncancel && $createnewpage) {
        $pagename = $originalpagename;
    }
    redirect('view.php?'.ouwiki_display_wiki_parameters($pagename,$subwiki,$cm,OUWIKI_PARAMS_URL),'',0);
    exit;
}
// OK, not redirecting...

if($section) {
    $sectionfields="<input type='hidden' name='section' value='$section' />";
} else {
    $sectionfields='';
}

// $title = get_string('editingpage','ouwiki');



// Handle case where page is locked by someone else
if(!$lockok) {
    // Print header etc
    ouwiki_print_start($ouwiki,$cm,$course,$subwiki,$pagename,$context);
    
    $details=new StdClass;
    $lockholder=get_record('user','id',$lock->userid);
    $details->name=fullname($lockholder);
    $details->lockedat=ouwiki_nice_date($lock->lockedat);
    $details->seenat=ouwiki_nice_date($lock->seenat);
    $pagelockedtitle=get_string('pagelockedtitle','ouwiki');
    $pagelockedtimeout='';
    if($lock->seenat > time()) {
        // When the 'seen at' value is greater than current time, that means
        // their lock has been automatically confirmed in advance because they
        // don't have JavaScript support.
        $details->nojs=ouwiki_nice_date($lock->seenat+OUWIKI_LOCK_PERSISTENCE);
        $pagelockeddetails=get_string('pagelockeddetailsnojs','ouwiki',$details);
    } else {
        $pagelockeddetails=get_string('pagelockeddetails','ouwiki',$details);
        if($lock->expiresat) {
            $pagelockedtimeout=get_string('pagelockedtimeout','ouwiki',userdate($lock->expiresat));
        }
    }
    $canoverride=has_capability('mod/ouwiki:overridelock',$context);
    $pagelockedoverride=$canoverride ? '<p>'.get_string('pagelockedoverride','ouwiki').'</p>' : '';
    $overridelock=get_string('overridelock','ouwiki');
    $overridebutton=$canoverride ? "
<form class='ouwiki_overridelock' action='override.php' method='post'>
  $wikiformfields
  <input type='submit' value='$overridelock' />
</form>
" : '';    
    $cancel=get_string('cancel');
    $tryagain=get_string('tryagain','ouwiki');
    print "
<div id='ouwiki_lockinfo'>
  <h2>$pagelockedtitle</h2>
  <p>$pagelockeddetails $pagelockedtimeout</p>
  $pagelockedoverride
  <div class='ouwiki_lockinfobuttons'>
    <form action='edit.php' method='get'>
      $wikiformfields
      $sectionfields
      <input type='submit' value='$tryagain' />
    </form>
    <form action='view.php' method='get'>
      $wikiformfields
      <input type='submit' value='$cancel' />
    </form>
    $overridebutton
  </div>
</div>";
    print_footer($course);
    exit;        
} 

// The page is now locked to us! Go ahead and print edit form


// get title of the page

$title = get_string('editingpage','ouwiki');
$wikiname=format_string(htmlspecialchars($ouwiki->name));
$name = '';
if($pagename) {
    $name = $pagename; 
} else {
    if($addnewsection) {
        $title= get_string('editingsection','ouwiki');
        $sectiontitle = $newsectionname;
        $name = htmlspecialchars($newsectionname);
        $section = true;
    } else {
        if(!$section) {
            $name=get_string('startpage','ouwiki');
        } else {
            $title= get_string('editingsection','ouwiki');
            $name = htmlspecialchars($sectiontitle);
        }
    } // endif
}
    
$title = $wikiname.' - '.$title.' : '.$name;

// Print header
ouwiki_print_start($ouwiki,$cm,$course,$subwiki,$pagename,$context,
    array(array('name'=>
        $section 
            ? get_string('editingsection','ouwiki',htmlspecialchars($sectiontitle)) 
            : get_string('editingpage','ouwiki'),'type'=>'ouwiki')),
            false, false, '', $title);

if ($addnewsection) {
    $section = false;
}

// Tabs
ouwiki_print_tabs('edit',$pagename,$subwiki,$cm,$context,$pageversion->versionid?true:false);

// Prepare the warning about lock without JS...
$a=new StdClass;
$a->now=userdate(time(),get_string('strftimetime'));
$a->minutes=(int)(OUWIKI_LOCK_NOJS/60);
$a->deadline=userdate(time()+$a->minutes*60,get_string('strftimetime'));
$nojswarning=get_string('nojswarning','ouwiki',$a);
$nojsstart='<p class="ouw_nojswarning">';

// Put in the AJAX for keeping the lock, if on a supported browser
$ie=check_browser_version('MSIE', 6.0);
$ff=check_browser_version('Gecko', 20051106);
$op=check_browser_version('Opera', 9.0);
$sa=check_browser_version('Safari', 412);
$js=$ie||$ff||$op||$sa;
if($js) {
    $nojsdisabled=get_string('nojsdisabled','ouwiki');
    $nojs=$nojsstart.$nojsdisabled.' '.$nojswarning.
        '<img src="nojslock.php?lockid='.$lock->id.'" alt=""/></p>';
    
    print require_js(array('yui_yahoo','yui_event','yui_connection'),true);
    $strlockcancelled=ouwiki_javascript_escape(get_string('lockcancelled','ouwiki'));
    $intervalms=OUWIKI_LOCK_RECONFIRM*1000;
    
    $timeoutscript='';
    if($ouwiki->timeout) { 
        $countdownurgent=ouwiki_javascript_escape(get_string('countdownurgent','ouwiki'));
        $timeoutscript="
    var ouw_countdownto=(new Date()).getTime()+1000*{$ouwiki->timeout};
    var ouw_countdowninterval=setInterval(function() {
        var countdown=document.getElementById('ouw_countdown');
        var timeleft=ouw_countdownto-(new Date().getTime());
        if(timeleft<0) {
            clearInterval(ouw_countdowninterval);
            document.forms['ouw_edit'].elements['save'].click();
            return;
        }
        if(timeleft<2*60*1000) {
            var urgent=document.getElementById('ouw_countdownurgent');
            if(!urgent.firstChild) {
                urgent.appendChild(document.createTextNode(\"".$countdownurgent."\"));
                countdown.style.fontWeight='bold';
                countdown.style.color='red';
            }
        }
        var minutes=Math.floor(timeleft/(60*1000));
        var seconds=Math.floor(timeleft/1000) - minutes*60;
        var text=minutes+':';
        if(seconds<10) text+='0';
        text+=seconds;
        while(countdown.firstChild) {
            countdown.removeChild(countdown.firstChild);
        }
        countdown.appendChild(document.createTextNode(text));    
    },500);
    ";
    }
    
    print "
    <script type='text/javascript'>
    var intervalID;
    function handleResponse(o) {
        if(o.responseText=='cancel') {
            document.forms['ouw_edit'].elements['preview'].disabled=true;
            document.forms['ouw_edit'].elements['save'].disabled=true;
            clearInterval(intervalID);
            alert(\"$strlockcancelled\");
        }
    }
    function handleFailure(o) {
        // Ignore for now
    }
    intervalID=setInterval(function() {
        YAHOO.util.Connect.asyncRequest('POST','confirmlock.php',
            {success:handleResponse,failure:handleFailure},'lockid={$lock->id}');    
        },$intervalms);
    $timeoutscript
    </script>
    <noscript>
    $nojs
    </noscript>
    ";
} else {
    // If they have a non-supported browser, update the lock time right now without
    // going through the dodgy image method, to reserve their 15-minute slot. 
    // (This means it will work for Lynx, for instance.) 
    print $nojsstart.get_string('nojsbrowser','ouwiki').' '.$nojswarning.'.</p>';
    $lock->seenat=time()+OUWIKI_LOCK_NOJS;
    update_record('ouwiki_locks',$lock);
}

// Calculate initial text for html editor
if($section) {
    $existing=$sectiondetails->content;
} else if ($addnewsection) {
    $existing=$sectionheader;
} else if ($pageversion) {
    $existing=$pageversion->xhtml;
} else {
    $existing='';
}

if($content) {
    print '<p class="ouw_warning">'.get_string('previewwarning','ouwiki').'</p><div class="ouw_preview">';
    print ouwiki_display_preview($content,$pagename,$subwiki,$cm);
    print '</div>';    
    $existing=$content;
}

$a ='
<span class="helplink">
<a title="Help with creating a new wiki page(new window)" href="'.$CFG->wwwroot.'/help.php?module=ouwiki&amp;file=createlinkedwiki.html&amp;forcelang=" onclick="this.target=\'popup\'; return openpopup(\'/help.php?module=ouwiki&amp;file=createlinkedwiki.html&amp;forcelang=\', \'popup\', \'menubar=0,location=0,scrollbars,resizable,width=500,height=400\', 0);">
<img class="iconhelp" alt="Help with with creating a new wiki page" src="'.$CFG->pixpath.'/help.gif" />
</a>
</span>';
print get_string('advice_edit','ouwiki', $a);

if($ouwiki->timeout && $js) {
    $countdowntext=get_string('countdowntext','ouwiki',$ouwiki->timeout/60);
    print "<script type='text/javascript'>
document.write('<p><div id=\"ouw_countdown\"></div>$countdowntext<span id=\"ouw_countdownurgent\"></span></p>');
</script>";
}

print "<form name='ouw_edit' id='ouw_edit' action='edit.php' method='post'>
$wikiformfields
<input type='hidden' name='startversionid' value='{$pageversion->versionid}' />
$sectionfields
<table><tr><td>";
$usehtmleditor=can_use_html_editor();
// This is a bit evil. What happens is we print the plaintext version of the format (in case
// they have javascript off) then set the content to the original version if they have JS on
// and are using the editor.
print_textarea($usehtmleditor, 30, 100, 0, 0, 'content', ouwiki_xhtml_to_plain($existing));
print '</td></tr></table>
<input type="hidden" id="ouw_format" name="format" value="1" />
<input type="hidden" name="sesskey" value="'.sesskey().'" />
<input type="submit" id="ouw_save" name="save" value="'.get_string('savechanges').'" />
<input type="submit" id="ouw_preview" name="preview" value="'.get_string('preview','ouwiki').'" />
<input type="submit" name="cancel" value="'.get_string('cancel').'" />';
if($usehtmleditor) {
    print '
<script type="text/javascript">
document.getElementById("edit-content").value="'.ouwiki_javascript_escape($existing).'";
document.getElementById("ouw_format").value="0";
var button1 = document.getElementById("ouw_save"), button2 = document.getElementById("ouw_preview");
var fn = function() { setTimeout(function() { button1.disabled = true; button2.disabled = true; }, 0); };
button1.onclick = fn;
button2.onclick = fn;
</script>
';
}

print '</form>';   

if($usehtmleditor) {
    use_html_editor();
}

// Footer
ouwiki_print_footer($course,$cm,$subwiki,$pagename);
?>
