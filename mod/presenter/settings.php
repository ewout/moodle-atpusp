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

$settings->add(new admin_setting_heading('metadata_injector', get_string('generalconfig', 'presenter'),
                   get_string('explaininjector', 'presenter')));

$settings->add(new admin_setting_configtext('metadata_injector', get_string('injector', 'presenter'),
                   get_string('injector_method', 'presenter'), null));
                   
                   
$settings->add(new admin_setting_heading('zip_path', get_string('zip_path', 'presenter'),
                   get_string('explainzippath', 'presenter')));

$settings->add(new admin_setting_configtext('zip_path', get_string('zippath', 'presenter'),
                   get_string('zip_path_method', 'presenter'), $CFG->zip, PARAM_RAW, '30" readonly="readonly" style="background-color: #EEE; border: 1px solid #ccc'));
                                      
                   
$settings->add(new admin_setting_heading('unzip_path', get_string('unzip_path', 'presenter'),
                   get_string('explainunzippath', 'presenter')));

$settings->add(new admin_setting_configtext('unzip_path', get_string('unzippath', 'presenter'),
                   get_string('unzip_path_method', 'presenter'), $CFG->unzip, PARAM_RAW, '30" readonly="readonly" style="background-color: #EEE; border: 1px solid #ccc'));
                   
?>
