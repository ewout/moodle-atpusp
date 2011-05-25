<?php  //$Id$


$settings->add(new admin_setting_configcheckbox('block_timestat_enable', get_string('enabletimestat', 'block_timestat'),
                   get_string('configenabletimestat', 'block_timestat'), 1));

$settings->add(new admin_setting_configcheckbox('block_timestat_debug', get_string('debugtimestat', 'block_timestat'),
                   get_string('configdebugtimestat', 'block_timestat'), 0));


?>
