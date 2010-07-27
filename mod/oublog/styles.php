#oublog-tags a {
    text-decoration:none;
}
#oublog-tags a:hover .oublog-tagname{
    text-decoration:underline;
}

#oublog-feeds a {
    text-decoration:none;
}
#oublog-feeds a:hover .oublog-tagname{
    text-decoration:underline;
}

.oublog-tagcount {
    margin-left:2px;
    font-size: 0.85em;
}

.oublog-tag-cloud-0 .oublog-tagname {
    font-size: 0.85em;
}

.oublog-tag-cloud-1 .oublog-tagname {
    font-size: 0.95em;
}

.oublog-tag-cloud-2 .oublog-tagname {
    font-size: 1.1em;
}

.oublog-tag-cloud-3 .oublog-tagname {
    font-size: 1.25em;
}

.oublog-tag-cloud-4 .oublog-tagname {
    font-size: 1.4em;
}

#mod-oublog-view #middle-column .singlebutton {
    margin-bottom:1em;
} 


.oublog-post, .oublog-comment {
    border: 1px solid #ddd;
    margin: 0 0 1em;
    padding: 0.5em;
    background-color: #FCFCFC;
    min-height:0; /* ie7 */
}
.ie6 .oublog-post *, .ie6 .oublog-comment *,
.ie6 .oublog-post, .ie6 .oublog-comment {
    height:0;
}
.ie6 .oublog-post img,
.ie6 .oublog-comment img,
.ie6 .oublog-post ul,
.ie6 .oublog-comment ul,
.ie6 .oublog-post ol,
.ie6 .oublog-comment ol,
.ie6 .oublog-post li,
.ie6 .oublog-comment li {
    height:auto;
}

.oublog-post-date, .oublog-post-visibility, .oublog-post-tags, .oublog-post-links, .oublog-post-editsummary, .oublog-links {
    font-size: 0.85em;
}

.oublog-post-tags {
    margin-bottom:0.7em;
}

.oublog-post-links {
    margin-bottom:1.5em;
}

.oublog-postedby {
    font-size: 0.85em;
    margin-top: 0.4em;
    margin-bottom: 0.4em;
}

.oublog-post-visibility {
    margin-top: 0.4em;
    color:#aaa;
}

.oublog-post h2.oublog-title {
    margin: 0 0 0.5em 0;
    font-size:1em;
}

.oublog-post-content,
.oublog-comment-content {
    margin:0.4em 0;
    margin-bottom:1.2em;
    min-height:0; /* ie7... */
}
.ie6 .oublog-post-content,
.ie6 .oublog-comment-content {
    height:auto !important;
    width:99%; /* for some reason it actually believes 'height' on this element */
}

.oublog-deleted {
    color: #aaa;
}


#oublog-single-post-view .oublog-post {
    border: none;
    background-color: transparent;
    padding: 0;
}

.oublog-comment h3, .oublog-comment h4 {
    margin: 0em;
}

.oublog-comment-date, .oublog-comment-visibility, .oublog-comment-tags, .oublog-comment-links, .oublog-comment-editsummary {
    font-size: 0.85em;
}

.oublog-views {
    text-align: center;
    font-size: 0.85em;
}

.feedicon {
    vertical-align: middle;
    margin-right: 4px;
    margin-left: 120px;
    border:0px;
 }

.oublog-post-deletedby, .oublog-comment-deletedby {
    color:#8D0047;
    font-weight: bold;
}
.oublog-comment-deletedby {
    margin-left: -42px;
    margin-bottom: 8px;
}

.oublog-topofpage {
    clear:both;
    padding-bottom:1em;
}

#mod-oublog-view #left-column, 
#mod-oublog-allposts #left-column {
  width:12em;
	float:left;
}
#mod-oublog-view #right-column,
#mod-oublog-viewpost #right-column,
#mod-oublog-viewedit #right-column,
#mod-oublog-allposts #right-column {
  width:12em;
	float:right;
}
#mod-oublog-view #middle-column.has-right-column,
#mod-oublog-viewpost #middle-column.has-right-column,
#mod-oublog-viewedit #middle-column.has-right-column,
#mod-oublog-allposts #middle-column.has-right-column {
  margin-right:13em;
}
#mod-oublog-view #middle-column.has-left-column,
#mod-oublog-viewpost #middle-column.has-left-column,
#mod-oublog-allposts #middle-column.has-left-column {
  margin-left:13em;
}

.oublog-post.oublog-hasuserpic,
.oublog-comment.oublog-hasuserpic {
  padding-left:50px;
  position:relative;
}
.oublog-userpic {
  position:absolute;
  left:8px;
}
/* I don't like CSS hacks, but unfortunately standard Moodle does not have the
   OU's .ie6 class on body. */
* html .oublog-hasuserpic {
  padding-left:0;
  margin-left:50px;
}
* html .oublog-userpic {
  left:-42px;
}

#mod-oublog-view .groupselector {
  float:none;
  margin-bottom:1.5em;
}

.oublog-post-content,.oublog-comment-content {
  overflow:hidden;
}