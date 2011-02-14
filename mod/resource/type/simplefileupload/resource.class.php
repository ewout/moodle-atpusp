<?php
// This file is part of SimpleFileUpload
//
// SimpleFileUpload provides a simpler mechanism for adding file resources to
// a Moodle Course and link them on the course page than the standard Moodle mechanism.
//
// SimpleFileUpload is (C) Copyright 2010 by John Ennew and Steve Coppin
// of the University of Kent, Canterbury, UK http://www.kent.ac.uk/
// Contact info: John Ennew; J.Ennew@kent.ac.uk
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * This is the resource_file details
 *
 * Language file requirements are:
 *  kentfiletermsandconditions in moodle.php (optional)
 *  resourcetypesimplefileupload in resource.php
 *
 * @package simplefileupload
 * @copyright 2010 John Ennew, Steve Coppin
 * @copyrigth 2010 University of Kent, Canterbury, UK http://www.kent.ac.uk
 * @author John Ennew
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// class extends file_resource
require_once($CFG->dirroot."/mod/resource/type/file/resource.class.php");

/**
* Extend the base resource class for file resources
*/
class resource_simplefileupload extends resource_file {

    private $update_instance_flag = false;

    function resource_simplefileupload($cmid=0) {

        try {

            if (strpos($_SERVER['PHP_SELF'], 'modedit.php') !== FALSE) {

                if (!empty($_POST)) {

                    if (!isset($_POST['instance'])) {
                        throw new MoodleSimpleFileUploadResourceFatalException('Cant determine if we are adding or updating (0)');
                    }
                    
                    if (strlen($_POST['instance'])<1 || $_POST['instance']==="0") {
                        $this->update_instance_flag = false;
                    } else {
                        $this->update_instance_flag = true;
                    }
                    
                } else if (!empty($_GET)) {

                    if (isset($_GET['update'])) {
                        $this->update_instance_flag = true;
                    } else if (isset($_GET['add'])) {
                        $this->update_instance_flag = false;
                    } else {
                        throw new MoodleSimpleFileUploadResourceFatalException('Cant determine if we are adding or updating (1)');
                    }

                } else throw new MoodleSimpleFileUploadResourceFatalException('Cant determine if we are adding or updating (2)');

                if (!$this->update_instance_flag) {
                    global $CFG;
                    require_js(array('yui_yahoo',
                     'yui_json',
                     'yui_dom',
                     'yui_animation',
                     'yui_event',
                     'yui_element',
                     'yui_dragdrop',
                     'yui_connection',
                     'yui_container',
                     'ajaxcourse_blocks',
                     'ajaxcourse_sections'));
                    require_js($CFG->wwwroot.'/mod/resource/type/simplefileupload/simplefileupload.js');
                }
            }
        } catch (MoodleSimpleFileUploadResourceFatalException $e) {
            if (debugging('', DEBUG_MINIMAL)) {
                simplefileupload_print_debug($e);
                die();
            }
            @email_to_admin('PHP application error', array('plugin'=>'simplefileupload', 'post' =>$_POST, 'GET'=> $_GET, 'script'=>$_SERVER['PHP_SELF']));
            error('Module Fault: There is a problem with the simple file upload');
        }
                
        parent::resource_file($cmid);
    }

    function setup_elements(&$mform) {               
       
       if (!$this->update_instance_flag) {
           
           // take away the Moodle standard form elements we dont want
           $mform->removeElement('general');
           $mform->removeElement('name');
           $mform->removeElement('summary');

           // get the language elements
           $struplodadafile = get_string("uploadafile");
           $struploadthisfile = get_string("uploadthisfile");
           $termsandconditions = get_string("fileuploadtermsandconditions", 'resource');
           if ($termsandconditions!="[[fileuploadtermsandconditions]]") {
               $mform->addElement('html', $termsandconditions);
           }

           // add the simple file upload elements           
           $mform->addElement('hidden', 'action', 'upload');

           $mform->addElement('file', 'simplefileupload_FILE', $struplodadafile, array('size'=>'45'));
           $mform->addElement('text', 'name', 'Name', array('size'=>'45'));

        } else {
            // if updating, use the standard file resource form
            //parent::setup_elements($mform);
            error('Module Fault: Simple file upload is not used for updating a file resource (1)');
        }
        
    }

    function add_instance(&$resource) {

        global $CFG;

        try {

            require_once('SfuController.class.php');
            $sfu = new SfuController($resource->course, $resource->section); 
            $resource = $sfu->create($resource, true);
            return parent::add_instance($resource);
            
        } catch (MoodleSimpleFileUploadResourceFatalException $e) {
            if (debugging('', DEBUG_MINIMAL)) {
                simplefileupload_print_debug($e);
                die();
            }
            @email_to_admin('PHP application error', array('plugin'=>'simplefileupload', 'post' =>$_POST, 'GET'=> $_GET, 'script'=>$_SERVER['PHP_SELF']));
            error('Module Fault: There is a problem with the simple file upload');
            return false;
        }
    }

    function update_instance(&$resource) {
        error('Module Fault: Simple file upload is not used for updating a file resource (2)');
    }

    function _postprocess(&$resource) {
        return true;
    }

    function display(){
        error('Module Fault: Simple file upload is not used for viewing a file resource!');
    }

}

function simplefileupload_print_debug($obj) {
    if (debugging('', DEBUG_MINIMAL)) {
        echo "<pre>";
        var_dump($obj);
        echo "</pre>";
    }
}

/**
 * MoodleCLAResourceException is used to identify errors thrown in the CLA resource
 */
class MoodleSimpleFileUploadResourceException extends Exception {}

/**
 * MoodleCLAResourceFatalException is used to identify errors thrown in the CLA resource
 * Messages set using this method are only seen if debugging is switched on
 */
class MoodleSimpleFileUploadResourceFatalException extends Exception { }

?>
