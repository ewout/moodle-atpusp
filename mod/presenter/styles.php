/*
This file is part of the Presenter Activity Module for Moodle

The Presenter Activity Module for Moodle software package is Copyright © 2008 onwards NetSapiensis AB and is provided under the terms
of the GNU GENERAL PUBLIC LICENSE Version 3 (GPL). This program is free software: you can redistribute it and/or modify it under the terms
of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any
later version.
This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program. If not, see <http://www.gnu.org/licenses/>.

The Presenter Activity Module for Moodle includes Flowplayer free version. For more information on Flowplayer see http://www.flowplayer.org

The Flowplayer Free version is released under the GNU GENERAL PUBLIC LICENSE Version 3 (GPL).
The GPL requires that you not remove the Flowplayer copyright notices from the user interface. See section 5.d below.
Commercial licenses are available. The commercial player version does not require any Flowplayer notices or texts and also provides some
additional features.

ADDITIONAL TERM per GPL Section 7 for Flowplayer
If you convey this program (or any modifications of it) and assume contractual liability for the program to recipients of it, you agree to
indemnify Flowplayer, Ltd. for any liability that those contractual assumptions impose on Flowplayer, Ltd.

Except as expressly provided herein, no trademark rights are granted in any trademarks of Flowplayer, Ltd. Licensees are granted a limited,
non-exclusive right to use the mark Flowplayer and the Flowplayer logos in connection with unmodified copies of the Program and the copyright
notices required by section 5.d of the GPL license. For the purposes of this limited trademark license grant, customizing the Flowplayer by
skinning, scripting, or including PlugIns provided by Flowplayer, Ltd. is not considered modifying the Program.

Licensees that do modify the Program, taking advantage of the open-source license, may not use the Flowplayer mark or Flowplayer logos and must
change the fullscreen notice (and the non-fullscreen notice, if that option is enabled), the copyright notice in the dialog box, and the notice
on the Canvas as follows:

the full screen (and non-fullscreen equivalent, if activated) noticeshould read: "Based on Flowplayer source code"; in the context menu
(right-click menu), the link to "About Flowplayer free version #.#.#" can remain. The copyright notice can remain, but must be supplemented
with an additional notice, stating that the licensee modified the Flowplayer. A suitable notice might read
"Flowplayer Source code modified by ModOrg 2009"; for the canvas, the notice should read "Based on Flowplayer source code".
In addition, licensees that modify the Program must give the modified Program a new name that is not confusingly similar to Flowplayer
and may not distribute it under the name Flowplayer.

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the
Free Software Foundation, either version 3 of the License, or (at your option) any later version. This program is distributed in the hope that
it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the GNU General Public License for more details. You should have received a copy of the GNU General Public License along with this program.
If not, see <http://www.gnu.org/licenses/>.
 */
 
.mod-lesson .contents {
    text-align: left;
}

.mod-lesson #layout-table {
    width: 100%;
}

.mod-lesson .edit_buttons form,
.mod-lesson .edit_buttons input {
    display: inline;
}

.mod-lesson .clock .content {
    text-align: center;
}

.mod-lesson .addlinks {
    font-size: .8em;
}

.mod-lesson .userinfotable .cell,
.mod-lesson .userinfotable .userpicture {
    vertical-align: middle;
}

.mod-lesson .invisiblefieldset.fieldsetfix {
    display: block;
}

.mod-lesson .invisiblefieldset.fieldsetfix tr {
    text-align: left;
}

/***
 *** Style for view.php
 ***/

#mod-lesson-view .password-form {
    text-align: center;
    margin-top: 20px;
}

#mod-lesson-view .password-form .submitbutton {
    display: inline;
}

/***
 *** Style for essay.php
 ***/

#mod-lesson-essay .graded {
    color:#DF041E;
}

#mod-lesson-essay .sent {
    color:#006600;
}

#mod-lesson-essay .ungraded {
    color:#999999;
}

#mod-lesson-essay .gradetable {
    margin-bottom: 20px;
}

#mod-lesson-essay .buttons {
    text-align: center;
}

/***
 *** Style for responses
 ***/

/* .response style is applied for both .correct and .incorrect */
.mod-lesson .response {
    padding-top: 10px;
}

/* for correct responses (can override .response) */
.mod-lesson .correct {
    /*color: green;*/
}

/* for incorrect responses (can override .response) */
.mod-lesson .incorrect {
    /*color: red;*/
}

/* for highlighting matches in responses for short answer regular expression (can override .incorrect) */
.mod-lesson .matches {
    /*color: red;*/
}

/***
 *** Slide show Style
 ***/

/* NOTE: background color, height and width are set in the lesson settings */
.mod-lesson .slideshow {  
    overflow: auto;
    padding-right: 16px; /* for the benefit of macIE5 only */ 
    /* \ commented backslash hack - recover from macIE5 workarounds, it will ignore the following rule */
    padding-right: 0;
    padding: 15px;
}

/***
 *** Left Menu Styles
 ***/
.mod-lesson .menu .content {
    padding: 0px;
}

.mod-lesson .menu .menuwrapper {
    max-height: 400px;
    overflow: auto;
    vertical-align: top;
    margin-bottom: 10px;
}

.mod-lesson .menu ul {
    list-style: none;
    padding: 5px 0px 0px 5px;
    margin: 0px;
}

.mod-lesson .menu li {
    padding-bottom: 5px;
}

.mod-lesson .leftmenu_selected_link {
}

.mod-lesson .leftmenu_not_selected_link {
}

.mod-lesson .skip {
    position: absolute;
    left: -1000em;
    width: 20em;
}


/***
 *** Lesson Buttons
 ***/

.mod-lesson .lessonbutton a {
  padding-left:1em;
  padding-right:1em;
}

.mod-lesson .lessonbutton a:link,
.mod-lesson .lessonbutton a:visited, 
.mod-lesson .lessonbutton a:hover {
    color: #000;
    text-decoration: none;
}

.mod-lesson .lessonbutton a:link,
.mod-lesson .lessonbutton a:visited {
  border-top: 1px solid #cecece;
  border-bottom: 2px solid #4a4a4a;
  border-left: 1px solid #cecece;
  border-right: 2px solid #4a4a4a;
}

.mod-lesson .lessonbutton a:hover {
  border-bottom: 1px solid #cecece;
  border-top: 2px solid #4a4a4a;
  border-right: 1px solid #cecece;
  border-left: 2px solid #4a4a4a;
}

/* Branch table buttons when displayed horizontally */
.mod-lesson .branchbuttoncontainer.horizontal div,
.mod-lesson .branchbuttoncontainer.horizontal form {
    display: inline;
}

/* Branch table buttons when displayed vertically */
.mod-lesson .branchbuttoncontainer.vertical .lessonbutton {
    padding: 5px;
}

/***
 *** Lesson Progress Bar
 ***    Default styles for this are very basic right now.
 ***    User is supposed to configure this to their liking (like using pictures)
 ***/

.mod-lesson .progress_bar {
    padding: 20px;
}

.mod-lesson .progress_bar_table {
    width: 80%;
    padding: 0px;
    margin: 0px;
}

.mod-lesson .progress_bar_completed {
    /*  Example Use of Image
    background-image: url(<?php echo $CFG->wwwroot ?>/mod/lesson/completed.gif);
    background-position: center;
    background-repeat: repeat-x;
    */
    background-color: green;
    padding: 0px;
    margin: 0px;    
}

.mod-lesson .progress_bar_todo {
    /*  Example Use of Image
    background-image: url(<?php echo $CFG->wwwroot ?>/mod/lesson/todo.gif);
    background-repeat: repeat-x;
    background-position: center;
    */
    background-color: red;
    text-align: left;
    padding: 0px;
    margin: 0px;
}

.mod-lesson .progress_bar_token {
    /*  Example Use of Image
    background-image: url(<?php echo $CFG->wwwroot ?>/mod/lesson/token.gif);
    background-repeat: repeat-none;
    */
    background-color: #000000;
    height: 20px;
    width: 5px;
    padding: 0px;
    margin: 0px;
}



.summarytext ul {
	list-style-type:disc;
	list-style-position:inside;
	padding-left:1.5em;
	
}
