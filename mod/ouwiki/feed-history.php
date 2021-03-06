<?php
/**
 * History page, feed version. Shows list of all previous versions of a page.
 *
 * @copyright &copy; 2007 The Open University
 * @author s.marshall@open.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package ouwiki
 *//** */

global $DISABLESAMS;
$DISABLESAMS=true;
require('basicpage.php');

// Get information about page
$pageversion=ouwiki_get_current_page($subwiki,$pagename,OUWIKI_GETPAGE_CREATE);

$magic=required_param('magic',PARAM_RAW);
if($magic!=$subwiki->magic) {
    error('Incorrect magic number');
}

$rss=optional_param('format','',PARAM_RAW)==='rss';

// Get history
$changes=ouwiki_get_page_history($pageversion->pageid,false,0,OUWIKI_FEEDSIZE);

$useragent=$_SERVER['HTTP_USER_AGENT'];
$oldbrowser=
  (!preg_match('/Opera/',$useragent) && preg_match('/MSIE [456]/',$useragent)) || 
  preg_match('/Firefox\/1\./',$useragent);

if($oldbrowser) {
    header('Content-Type: text/xml; charset=UTF-8');
} else if($rss) {
    header('Content-Type: application/rss+xml; charset=UTF-8');    
} else {
    header('Content-Type: application/atom+xml; charset=UTF-8');
}

$pagetitle=is_null($pageversion->title) ? get_string('startpage','ouwiki') : htmlspecialchars($pageversion->title);

$a=new StdClass;
$a->course=htmlspecialchars($course->shortname);
$a->name=htmlspecialchars($ouwiki->name);
$a->subtitle=$pagetitle;
$feedtitle=get_string('feedtitle','ouwiki',$a);
$feedlink='http://'.htmlspecialchars($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
$feeddescription=get_string('feeddescriptionhistory','ouwiki');

// Domain name, used for IDs (we assume this is owned by site operator in 2007)
$domainname=preg_replace('/^.*\/\/(www\.)?(.*?)\/.*$/','$2',$CFG->wwwroot);
$id='tag:'.$domainname.',2007:ouwiki/'.$ouwiki->id.'/wikihistory/changes/'.$pageversion->pageid;

print '<?xml version="1.0"?>
<?xml-stylesheet type="text/xsl" href="'.$CFG->wwwroot.'/mod/ouwiki/feed.xsl"?>';
if($rss) {
    print '
<rss version="2.0">
  <channel>
    <title>'.$feedtitle.'</title>
    <description>'.$feeddescription.'</description>
    <link>'.$feedlink.'</link>
    <pubDate>'.date('r',reset($changes)->timecreated).'</pubDate>';
} else {
    print '
<feed xmlns="http://www.w3.org/2005/Atom">
  <link rel="self" href="'.$feedlink.'"/>
  <title>'.$feedtitle.'</title>
  <subtitle>'.$feeddescription.'</subtitle>
  <link href="http://example.org/"/>
  <updated>'.date('c',reset($changes)->timecreated).'</updated>
  <author>
    <name>Wiki system</name>
  </author>
  <id>'.$id.'</id>';
}

$pageparams=ouwiki_display_wiki_parameters($change->title,$subwiki,$cm);

 
foreach($changes as $change) {
    
    $a=new StdClass;
    $a->name=htmlspecialchars(fullname($change));
    
    if($change->versionid==$pageversion->versionid) {
        $itemlink=$CFG->wwwroot.'/mod/ouwiki/view.php?'.$pageparams;
    } else {
        $itemlink=$CFG->wwwroot.'/mod/ouwiki/viewold.php?'.$pageparams.'&amp;version='.$change->versionid;
    }
    $itemtitle=$ouwiki->name.' - '.$pagetitle.' ('.ouwiki_nice_date($change->timecreated).')';
    
    $nextchange=next($changes);
    if($nextchange) {
        $a->url=$CFG->wwwroot.'/mod/ouwiki/diff.php?'.$pageparams.'&amp;v1='.
            $nextchange->versionid.'&amp;v2='.$change->versionid;
        $a->main=get_string('feedchange','ouwiki',$a);
    } else {
        $a->main=get_string('feednewpage','ouwiki',$a);
    }
    $itemdescription=get_string('feeditemdescriptionnodate','ouwiki',$a);
    if($rss) {
        // The 'permalink' guid just points to the wiki history page but with a unique-ifying versionid on end 
        print '
<item>
  <title>'.$itemtitle.'</title>
  <link>'.$itemlink.'</link>
  <pubDate>'.date('r',$change->timecreated).'</pubDate>
  <description>'.htmlspecialchars($itemdescription).'</description>
  <guid>'.$CFG->wwwroot.'/mod/ouwiki/history.php?'.$pageparams.'#v'.$change->versionid.'</guid>
</item>';
    } else {
        print '
<entry>
  <title>'.$itemtitle. '</title>
  <link href="'.$itemlink.'"/>
  <id>'.$id.'/'.$change->versionid.'</id>
  <updated>'.date('c',$change->timecreated).'</updated>
  <summary type="xhtml"><div xmlns="http://www.w3.org/1999/xhtml">'.$itemdescription.'</div></summary>
</entry>';
    }
}

if($rss) {
    print '</channel></rss>';
} else {
    print '</feed>';
}

?>
