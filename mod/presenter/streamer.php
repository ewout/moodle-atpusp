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


	require_once('../../config.php');
	global $CFG;

	$courseID = $SESSION->c;
	
	
    /*
    
        xmoov-php 0.9
        Development version 0.9.3 beta
        
        by: Eric Lorenzo Benjamin jr. webmaster (AT) xmoov (DOT) com
        originally inspired by Stefan Richter at flashcomguru.com
        bandwidth limiting by Terry streamingflvcom (AT) dedicatedmanagers (DOT) com
        
        This work is licensed under the Creative Commons Attribution-NonCommercial-ShareAlike 3.0 License.
        For more information, visit http://creativecommons.org/licenses/by-nc-sa/3.0/
        For the full license, visit http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode 
        or send a letter to Creative Commons, 543 Howard Street, 5th Floor, San Francisco, California, 94105, USA.
        
        
    */


    
    //    SCRIPT CONFIGURATION
    
    //------------------------------------------------------------------------------------------
    //    MEDIA PATH
    //
    //    you can configure these settings to point to video files outside the public html folder.
    //------------------------------------------------------------------------------------------
    
    // points to server root
    define('XMOOV_PATH_ROOT', '');
    
    // points to the folder containing the video files.
    define('XMOOV_PATH_FILES', $CFG->dataroot . "/$courseID/");
    
    
    
    //------------------------------------------------------------------------------------------
    //    SCRIPT BEHAVIOR
    //------------------------------------------------------------------------------------------
    
    //set to TRUE to use bandwidth limiting.
    define('XMOOV_CONF_LIMIT_BANDWIDTH', TRUE);
    
    //set to FALSE to prohibit caching of video files.
    define('XMOOV_CONF_ALLOW_FILE_CACHE', FALSE);
    
    
    
    //------------------------------------------------------------------------------------------
    //    BANDWIDTH SETTINGS
    //
    //    these settings are only needed when using bandwidth limiting.
    //    
    //    bandwidth is limited my sending a limited amount of video data(XMOOV_BW_PACKET_SIZE),
    //    in specified time intervals(XMOOV_BW_PACKET_INTERVAL). 
    //    avoid time intervals over 1.5 seconds for best results.
    //    
    //    you can also control bandwidth limiting via http command using your video player.
    //    the function getBandwidthLimit($part) holds three preconfigured presets(low, mid, high),
    //    which can be changed to meet your needs
    //------------------------------------------------------------------------------------------    
    
    //set how many kilobytes will be sent per time interval
    define('XMOOV_BW_PACKET_SIZE', 90);
    
    //set the time interval in which data packets will be sent in seconds.
    define('XMOOV_BW_PACKET_INTERVAL', 0.3);
    
    //set to TRUE to control bandwidth externally via http.
    define('XMOOV_CONF_ALLOW_DYNAMIC_BANDWIDTH', TRUE);
    
    
    
    //------------------------------------------------------------------------------------------
    //    DYNAMIC BANDWIDTH CONTROL
    //------------------------------------------------------------------------------------------
    
    function getBandwidthLimit($part)
    {
        switch($part)
        {
            case 'interval' :
                switch($_GET[XMOOV_GET_BANDWIDTH])
                {
                    case 'low' :
                        return 0.5;
                    break;
                    case 'mid' :
                        return 0.5;
                    break;
                    case 'high' :
                        return 0.2;
                    break;
                    case 'off' :
                        return 0;
                    break;
                    default :
                        return XMOOV_BW_PACKET_INTERVAL;
                    break;
                }
            break;
            case 'size' :
                switch($_GET[XMOOV_GET_BANDWIDTH])
                {
                    case 'low' :
                        return 20;
                    break;
                    case 'mid' :
                        return 40;
                    break;
                    case 'high' :
                        return 90;
                    break;
                    default :
                        return XMOOV_BW_PACKET_SIZE;
                    break;
                }
            break;
        }
    }
    
    
    
    //------------------------------------------------------------------------------------------
    //    INCOMING GET VARIABLES CONFIGURATION
    //    
    //    use these settings to configure how video files, seek position and bandwidth settings are accessed by your player
    //------------------------------------------------------------------------------------------
    
    define('XMOOV_GET_FILE', 'file');
    define('XMOOV_GET_POSITION', 'start');
    define('XMOOV_GET_AUTHENTICATION', 'key');
    define('XMOOV_GET_BANDWIDTH', 'bw');
    
    
    //    END SCRIPT CONFIGURATION - do not change anything beyond this point if you do not know what you are doing
    
    
    
    //------------------------------------------------------------------------------------------
    //    PROCESS FILE REQUEST
    //------------------------------------------------------------------------------------------
    
    if(isset($_GET[XMOOV_GET_FILE]) && isset($_GET[XMOOV_GET_POSITION]))
    {
        //    PROCESS VARIABLES
        
        # get seek position
        $seekPos = intval($_GET[XMOOV_GET_POSITION]);
        # get file name
        $fileName = htmlspecialchars($_GET[XMOOV_GET_FILE]);
        
        # assemble file path
        $file = XMOOV_PATH_ROOT . XMOOV_PATH_FILES . $fileName;
        
        # assemble packet interval
        $packet_interval = (XMOOV_CONF_ALLOW_DYNAMIC_BANDWIDTH && isset($_GET[XMOOV_GET_BANDWIDTH])) ? getBandwidthLimit('interval') : XMOOV_BW_PACKET_INTERVAL;
        # assemble packet size
        $packet_size = ((XMOOV_CONF_ALLOW_DYNAMIC_BANDWIDTH && isset($_GET[XMOOV_GET_BANDWIDTH])) ? getBandwidthLimit('size') : XMOOV_BW_PACKET_SIZE) * 1042;
        
        # security improved by by TRUI www.trui.net
        if (!file_exists($file))
        {
            print('<b>ERROR:</b> xmoov-php could not find (' . $fileName . ') please check your settings.'); 
            exit();
        }
        if(file_exists($file) && strrchr($fileName, '.') == '.flv' && strlen($fileName) > 2 && !eregi(basename($_SERVER['PHP_SELF']), $fileName) && ereg('^[^./][^/]*$', $fileName))
        {
            $fh = fopen($file, 'rb') or die ('<b>ERROR:</b> xmoov-php could not open (' . $fileName . ')');
                
            $fileSize = filesize($file) - (($seekPos > 0) ? $seekPos  + 1 : 0);
            
            //    SEND HEADERS
            if(!XMOOV_CONF_ALLOW_FILE_CACHE)
            {
                # prohibit caching (different methods for different clients)
                session_cache_limiter("nocache");
                header("Expires: Thu, 19 Nov 1981 08:52:00 GMT");
                header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
                header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0");
                header("Pragma: no-cache");
            }
            
            # content headers
            header("Content-Type: video/x-flv");
            header("Content-Disposition: attachment; filename=\"" . $fileName . "\"");
            header("Content-Length: " . $fileSize);
            
            # FLV file format header
            if($seekPos != 0) 
            {
                print('FLV');
                print(pack('C', 1));
                print(pack('C', 1));
                print(pack('N', 9));
                print(pack('N', 9));
            }
            
            # seek to requested file position
            fseek($fh, $seekPos);
            
            # output file
            while(!feof($fh)) 
            {
                # use bandwidth limiting - by Terry
                if(XMOOV_CONF_LIMIT_BANDWIDTH && $packet_interval > 0)
                {
                    # get start time
                    list($usec, $sec) = explode(' ', microtime());
                    $time_start = ((float)$usec + (float)$sec);
                    # output packet
                    print(fread($fh, $packet_size));
                    # get end time
                    list($usec, $sec) = explode(' ', microtime());
                    $time_stop = ((float)$usec + (float)$sec);
                    # wait if output is slower than $packet_interval
                    $time_difference = $time_stop - $time_start;
                    if($time_difference < (float)$packet_interval)
                    {
                        usleep((float)$packet_interval * 1000000 - (float)$time_difference * 1000000);
                    }
                }
                else
                {
                    # output file without bandwidth limiting
                    print(fread($fh, filesize($file))); 
                }
            }
            
        }
        
    }
?> 