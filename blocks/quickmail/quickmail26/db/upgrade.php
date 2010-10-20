<?php  //$Id: upgrade.php,v 1.1.4.2 2007/03/02 03:01:45 mark-nielsen Exp $

// This file keeps track of upgrades to this block
//
// Sometimes, changes between versions involve
// alterations to database structures and other
// major things that may break installations.
//
// The upgrade function in this file will attempt
// to perform all the necessary actions to upgrade
// your older installtion to the current version.
//
// If there's something it cannot do itself, it
// will tell you what you need to do.
//
// The commands in here will all be database-neutral,
// using the functions defined in lib/ddllib.php

function xmldb_block_quickmail_upgrade($oldversion=0) {
    global $CFG;

    $result = true;
    
/*    //add can:config capability to admin
    if (!empty($CFG->rolesactive) && $oldversion < 2008021500) { 
        $admin = get_record('role', 'shortname', 'admin');
        
        if (!assign_capability('block/quickmail:canconfig', CAP_ALLOW, $admin->id, 1)){
            $result = false;
        }
    }*/

    return $result;
}

?>
