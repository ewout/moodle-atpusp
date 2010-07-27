<?php

// In Moodle 2.0, or OU Moodle 1.9, we use a new admin setting type to let you 
// select roles with checkboxes. Otherwise you have to type in role IDs. Reason
// for using IDs is that this makes it compatible with the new system.
if(class_exists('admin_setting_pickroles')) {
    $settings->add(new admin_setting_pickroles('ouwiki_reportroles',
        get_string('reportroles','ouwiki'),
        get_string('configreportroles','ouwiki')));
} else {
    $settings->add(new admin_setting_configtext('ouwiki_reportroles', 
        get_string('reportroles', 'ouwiki'),
        get_string('configreportroles_text', 'ouwiki'), '', PARAM_SEQUENCE));
}

?>
