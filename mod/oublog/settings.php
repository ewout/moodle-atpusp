<?php

// Dodgy hack to setup the global blog instance - see MDL-13808 for the proposed solution

include_once($CFG->dirroot.'/mod/oublog/lib.php');

if (!isset($CFG->oublogsetup)) {
    oublog_post_install();
}

$settings->add(new admin_setting_configcheckbox('oublog_showuserpics', 
    get_string('showuserpics', 'oublog'), 
    get_string('configshowuserpics', 'oublog'), 1));
?>