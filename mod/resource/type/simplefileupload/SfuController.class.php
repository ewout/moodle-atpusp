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
 * This is the controller for the SimpleFileUpload implementing the functions
 * create(): Saves a new file resource either using Moodle native functionality or
              the plugins
 * read():   lists file resources in a section 
 *
 * @package simplefileupload
 * @copyright 2010 John Ennew, Steve Coppin
 * @copyrigth 2010 University of Kent, Canterbury, UK http://www.kent.ac.uk
 * @author John Ennew
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class SfuController {

    private $section = null;
    private $course = null;

    /**
    * Constructor for SfuController
    * @param int $course_id The id of the Moodle course to be worked on
    * @param int $section_number The section number within the course (not the section id)
    */
    function SfuController($course_id, $section_number) {
        $this->course = get_record('course','id',$course_id);
        $this->section = get_record('course_sections', 'course', $course_id, 'section', $section_number);
    }

    /**
     * Create a new file resource within the current section
     * @param Resource $resource : A resource object which reflects the elements in the resource db table
     * @param boolean $usingmoodle (optional) : Set to true and course_modules record is not created,
     *          it is assumed Moodle is doing this later, false, all the necessary links and records are
     *          setup by the SfuController
     * @return Resource : The updated resource (should now have a reference field set and id and course_module
     *                    if usingmoodle=false
     */
    public function create($resource, $usingmoodle=false) {
        $resource->reference = $this->uploadFile();
        $resource = $this->setupViewingOptions($resource);
        if (!$usingmoodle) {
            $resource->id = $this->addResourceInstance($resource);
            $course_module = $this->addCourseModule($resource);
            $this->updateSectionSequence($course_module->id);
        }

        return $resource;
    }

    /**
     * Return all the file resources for the specified section
     * @return Array[String] : all the reference fields in the resource table for resource items
     *                         in this course_section. If none, retruns an empty array
     */
    function read() {
        $rs = get_recordset_select('course_modules',"module=13 AND id IN ({$this->section->sequence})", '', 'instance');
        $result = recordset_to_array($rs);
        if (!is_array($result)) return array();

        $instances = "";
        foreach($result as $instance) {
            $instances .= $instance->instance . ",";
        }
        $instances[strlen($instances)-1] = ' ';

        $rs = get_recordset_select('resource',"type='file' AND id IN ({$instances}) AND reference not like 'http%' AND reference not like 'www.%'", '', 'reference');
        if ($rs===false) throw new Exception("Error in reading the database");        
        $result = recordset_to_array($rs);
        if (!is_array($result)) return array();
        else return array_values($result);
    }

    private function uploadFile() {
        global $CFG;

        // skip if no file provided
        if (($_FILES['simplefileupload_FILE']['error']===4)) throw new Exception("No file attached");
        $filedetails = $_FILES['simplefileupload_FILE'];

        // make the filename beautiful
        $new_filename = $filedetails['name'];
        $new_filename = strToLower($new_filename);
        $new_filename = str_replace(' ', '_', trim($new_filename));
        $_FILES['simplefileupload_FILE']['name'] = $new_filename;

        require_once($CFG->dirroot.'/lib/uploadlib.php');

        $basedir = make_upload_directory($this->course->id);
        if ($basedir===false) throw new MoodleSimpleFileUploadResourceFatalException('could not create course directory');

        $destinationdir = "{$basedir}/";
        $this->course->maxbytes = 0;  // We are ignoring course limits

        $um = new upload_manager('simplefileupload_FILE',false,false,$this->course,false,0);

        if (!$um->process_file_uploads($destinationdir)) {
            throw new MoodleSimpleFileUploadResourceFatalException("Failed to upload file");
        }            

        // the upload manager strips bad characters from the filename
        return $um->get_new_filename();
    }

    private function setupViewingOptions($resource) {
        global $CFG;

        $resource->type = 'file';
        $resource->summary = 'File resource created using the SimpleFileUploader';
        $resource->alltext = '';
        $resource->timemodified = time();

        // change the viewing options depending on file extension
        // these are configured in config.php
        $ext = end(explode('.', $resource->reference));

        include_once($CFG->dirroot.'/mod/resource/type/simplefileupload/config.php');

        $resource->popup = '';
        $resource->options = '';

        // set the configured default value
        if (isset($simplefileuploadoptions['default']['popup'])) {
            $resource->popup = $simplefileuploadoptions['default']['popup'];
        }

        if (isset($simplefileuploadoptions['default']['options'])) {
            $resource->options = $simplefileuploadoptions['default']['options'];
        }

        // set the configured actual value
        if (array_key_exists($ext, $simplefileuploadoptions)) {
            if (isset($simplefileuploadoptions[$ext]['popup'])) {
                $resource->popup = $simplefileuploadoptions[$ext]['popup'];
            }

            if (isset($simplefileuploadoptions[$ext]['options'])) {
                $resource->options = $simplefileuploadoptions[$ext]['options'];
            }
        }

        return $resource;
    }

    private function addResourceInstance($resource) {

        $new_resource = new Object();
        $new_resource->course = $this->course->id;
        $new_resource->name = $resource->name;
        $new_resource->type = $resource->type;
        $new_resource->reference = $resource->reference;
        $new_resource->summary = $resource->summary;
        $new_resource->alltext = $resource->alltext;
        $new_resource->popup = $resource->popup;
        $new_resource->options = $resource->options;
        $new_resource->timemodified = $resource->timemodified;

        $id = insert_record('resource', $new_resource);
        if ($id===false) throw new Exception('could not save resource');
        return $id;
    }

    private function addCourseModule($resource) {
        $course_module = new Object();
        $course_module->course = $resource->course;
        $course_module->module = 13;
        $course_module->instance = $resource->id;
        $course_module->section = $this->section->id;
        $course_module->idnumber='';
        $course_module->added = time();
        $course_module->score = 0;
        $course_module->indent = 0;
        $course_module->visible = 1;
        $course_module->visibleold = 1;
        $course_module->groupmode = 0;
        $course_module->groupingid = 0;
        $course_module->groupmembersonly = 0;
        $course_module->id = insert_record('course_modules', $course_module);
        if ($course_module->id===false) throw new Exception("Could not save course module");
        return $course_module;
    }

    private function updateSectionSequence($coursemoduleid) {
        // update the section sequence
        $section_updater = new Object();
        $section_updater->id = $this->section->id;
        $section_updater->sequence = $this->section->sequence .= ",{$coursemoduleid}";
        update_record('course_sections', $section_updater);
    }
}
