<?php

class htmlEditor {

   public function __construct() { }


    public function configure($editor = NULL, $courseid = NULL) {
        global $CFG;
        static $configured = Array();

        if (!isset($CFG->htmleditor) or (!$CFG->htmleditor)) {
            return;
        }

        if ($editor == '') {
            $editor = (isset($CFG->defaulthtmleditor) ? $CFG->defaulthtmleditor : '');
        }
        if (isset($configured[$editor])) {
            return $configured[$editor];
        }

        $configuration = array();

        switch ($editor) {
            case 'tinymce':
                $configuration[] = $CFG->httpswwwroot ."/lib/editor/tinymce/jscripts/tiny_mce/tiny_mce.js";
                $configuration[] = $CFG->httpswwwroot ."/lib/editor/tinymce.js.php?course=". $courseid;
                $configured['tinymce'] = true;
                break;
            case 'fckeditor':
                $configuration[] = $CFG->httpswwwroot ."/lib/editor/fckeditor/fckeditor.js";
                $configuration[] = $CFG->httpswwwroot ."/lib/editor/fckeditor.js.php?course=". $courseid;
                $configured['fckeditor'] = true;
                break;
            default:
                $configured[$editor] = false;
                break;
        }
        if (isset($CFG->editorsrc) && is_array($CFG->editorsrc)) {
            $CFG->editorsrc = $configuration + $CFG->editorsrc;
        } else {
            $CFG->editorsrc = $configuration;
        }
        return $configured[$editor];
    }

}
?>
