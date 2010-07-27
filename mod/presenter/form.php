<?php
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

require_once("../../config.php");
require_once("lib.php");
require_once("../../lib/filelib.php");
require_once("chapterlib.php");

$course = required_param('course', PARAM_INT);
$section = required_param('section', PARAM_INT);

require_login();

?>
    
<script type="text/javascript">
	function openpopup(url, name, options, fullscreen) {
		var fullurl = "<?php echo $CFG->wwwroot ?>" + url;
		var windowobj = window.open(fullurl, name, options);
		if (!windowobj) {
			return true;
		}
		if (fullscreen) {
			windowobj.moveTo(0, 0);
		    windowobj.resizeTo(screen.availWidth, screen.availHeight);
		}
		windowobj.focus();
		return false;
	}
</script>
<div style="font-size: 12px; margin: 5px auto;text-align: center;">
<?php if (!((class_exists("ZipArchive") || $CFG->unzip) && class_exists("XMLReader"))) : ?>
    <br />
    <?php echo get_string('xmlreader_required', 'presenter') ?>
<?php else : ?>
    <form id="sub-form" action="<?php echo $CFG->wwwroot  ?>/mod/presenter/import.php" method="POST" enctype="multipart/form-data">
    	<?php echo get_string('zip_file_info', 'presenter') ?><br />
	    <input style="margin: 5px 0;" size="48" name="archive" id="archive" type="text">
	    <input style="margin: 5px 0;" name="archive_popup" value="Choose or upload a file ..." title="Choose or upload a file" onclick="return openpopup('/files/index.php?id=<?php echo $course ?>&amp;choose=archive', 'popup', 'menubar=0,location=0,scrollbars,resizable,width=750,height=500', 0);" id="archive_popup" type="button">
	    <input type="hidden" name="id" value="<?php echo $course ?>" />
	    <input type="hidden" name="section" value="<?php echo $section ?>" /> 
	    <input type="submit" value="OK" style="height: 24px; margin: 5px 0" />
    </form>
<?php endif ?>
</div>
