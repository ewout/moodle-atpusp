<?php

class htmlEditor {

    public $configuration = array();

    public function __construct() { }

    public function configure($editor = NULL, $courseid = NULL) {
        global $CFG, $USER;
        static $configured = Array();
        
        if (!isset($CFG->htmleditor) or (!$CFG->htmleditor)) {
            return;
        }

        if ($editor == '') {
            $defaulteditor = isset($CFG->defaulthtmleditor) ? $CFG->defaulthtmleditor  : 'htmlarea' ;
            $editor = isset($USER->htmleditorid) ? $USER->htmleditorid : $defaulteditor;
        }

        if (isset($configured[$editor])) {
            return $configured[$editor];
        }
        
        switch ($editor) {
            case 'tinymce':
                $this->configuration[] = $CFG->httpswwwroot ."/lib/editor/tinymce/jscripts/tiny_mce/tiny_mce.js";
                $this->configuration[] = $CFG->httpswwwroot ."/lib/editor/tinymce.js.php?course=". $courseid;
                $configured['tinymce'] = true;
                break;
            case 'fckeditor':
                $this->configuration[] = $CFG->httpswwwroot ."/lib/editor/fckeditor/fckeditor.js";
                $this->configuration[] = $CFG->httpswwwroot ."/lib/editor/fckeditor.js.php?course=". $courseid;
                $configured['fckeditor'] = true;
                break;
            default:
                $this->configuration[] = $CFG->httpswwwroot . '/lib/editor/'.$editor.'/'.$editor.'.php?id='. $courseid;
                $this->configuration[] = $CFG->httpswwwroot . '/lib/editor/'.$editor.'/lang/en.php?id='.$courseid; // TODO fix-me bug (algem deve criar o arquivo pt.js ou pt.php no textarea)
                $configured[$editor] = true;
                $CFG->defaulthtmleditor='htmlarea'; //TODO fix-me. To avoid this var this variable must be auto-load in config.php
                break;
        }
        if (isset($CFG->editorsrc) && is_array($CFG->editorsrc)) {
            $CFG->editorsrc = $this->configuration + $CFG->editorsrc;
        } else {
            $CFG->editorsrc = $this->configuration;
        }
        return $configured[$editor];
    }

}
?>
