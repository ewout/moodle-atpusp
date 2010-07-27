<?php

// This file keeps track of upgrades to
// the newmodule module
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

function xmldb_oublog_upgrade($oldversion=0) {

    global $CFG, $THEME, $db;

    $result = true;

    if ($result && $oldversion < 2008022600) {

    /// Define field views to be added to oublog_instances
        $table = new XMLDBTable('oublog_instances');
        $field = new XMLDBField('views');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'accesstoken');

    /// Launch add field views
        $result = $result && add_field($table, $field);

        $table = new XMLDBTable('oublog');
        $field = new XMLDBField('views');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'global');

    /// Launch add field views
        $result = $result && add_field($table, $field);

    }

    if ($result && $oldversion < 2008022700) {

    /// Define field oublogid to be added to oublog_links
        $table = new XMLDBTable('oublog_links');
        $field = new XMLDBField('oublogid');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null, 'id');

    /// Launch add field oublogid
        $result = $result && add_field($table, $field);

    /// Define key oublog_links_oublog_fk (foreign) to be added to oublog_links
        $table = new XMLDBTable('oublog_links');
        $key = new XMLDBKey('oublog_links_oublog_fk');
        $key->setAttributes(XMLDB_KEY_FOREIGN, array('oublogid'), 'oublog', array('id'));

    /// Launch add key oublog_links_oublog_fk
        $result = $result && add_key($table, $key);

    /// Changing nullability of field oubloginstancesid on table oublog_links to null
        $table = new XMLDBTable('oublog_links');
        $field = new XMLDBField('oubloginstancesid');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null, 'oublogid');

    /// Launch change of nullability for field oubloginstancesid
        $result = $result && change_field_notnull($table, $field);
    }

    if ($result && $oldversion < 2008022701) {

    /// Define field sortorder to be added to oublog_links
        $table = new XMLDBTable('oublog_links');
        $field = new XMLDBField('sortorder');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null, 'url');

    /// Launch add field sortorder
        $result = $result && add_field($table, $field);
    }

    if ($result && $oldversion < 2008030704) {
    /// Add search data
        require_once(dirname(__FILE__).'/../locallib.php');
        require_once(dirname(__FILE__).'/../lib.php');
        if(oublog_search_installed()) {
            global $db;
            $olddebug=$db->debug;
            $db->debug=false;
            print '<ul>';
            oublog_ousearch_update_all(true);
            print '</ul>';
            $db->debug=$olddebug;
        }
    }
    
    if ($result && $oldversion < 2008030707) {

    /// Define field lasteditedby to be added to oublog_posts
        $table = new XMLDBTable('oublog_posts');
        $field = new XMLDBField('lasteditedby');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null, 'visibility');

    /// Launch add field lasteditedby
        $result = $result && add_field($table, $field);
        
    /// Transfer edit data to lasteditedby
        $result = $result && execute_sql("
UPDATE {$CFG->prefix}oublog_posts SET lasteditedby=(
    SELECT userid FROM {$CFG->prefix}oublog_edits WHERE {$CFG->prefix}oublog_posts.id=postid ORDER BY id DESC LIMIT 1 
) WHERE editsummary IS NOT NULL
        ");
        
    /// Define field editsummary to be dropped from oublog_posts
        $table = new XMLDBTable('oublog_posts');
        $field = new XMLDBField('editsummary');

    /// Launch drop field editsummary
        $result = $result && drop_field($table, $field);
    }    
    
    if ($result && $oldversion < 2008073000) {

    /// Define field completionposts to be added to oublog
        $table = new XMLDBTable('oublog');
        $field = new XMLDBField('completionposts');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '9', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'views');

    /// Launch add field completionposts
        $result = $result && add_field($table, $field);

    /// Define field completioncomments to be added to oublog
        $field = new XMLDBField('completioncomments');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '9', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'completionposts');

    /// Launch add field completioncomments
        $result = $result && add_field($table, $field);
    }

    if ($result && $oldversion < 2008121100) {
        // remove oublog:view from legacy:user roles
        $roles = get_roles_with_capability('moodle/legacy:user',CAP_ALLOW);
        foreach ($roles as $role) {
            $result = $result && unassign_capability('mod/oublog:view', $role->id);
        }
    }

    if ($result && $oldversion < 2009012600) {
        // Remove oublog:post and oublog:comment from legacy:user roles (if present)
        $roles = get_roles_with_capability('moodle/legacy:user',CAP_ALLOW);
        // Also from default user role if not already included
        if(!array_key_exists($CFG->defaultuserroleid,$roles)) {
            $roles[] = get_record('role', 'id', $CFG->defaultuserroleid);
        }
        
        print '<p><strong>Warning</strong>: The OU blog system capabilities 
            have changed (again) in order to fix bugs and clarify access control.
            The system will automatically remove the capabilities 
            <tt>mod/oublog:view</tt>, <tt>mod/oublog:post</tt>, and
            <tt>mod/oublog:comment</tt> from the following role(s):</p><ul>';
        foreach ($roles as $role) {
            print '<li>'.htmlspecialchars($role->name).'</li>';
            $result = $result && unassign_capability('mod/oublog:view', $role->id);
            $result = $result && unassign_capability('mod/oublog:post', $role->id);
            $result = $result && unassign_capability('mod/oublog:comment', $role->id);
        }
        print '</ul><p>On a default Moodle installation this is the correct 
            behaviour. Personal blog access is now controlled via the 
            <tt>mod/oublog:viewpersonal</tt> and 
            <tt>mod/oublog:contributepersonal</tt>
            capabilities. These capabilities have been added to the 
            authenticated user role and any equivalent roles.</p>
            <p>If in doubt, please examine your role configuration with regard
            to these <tt>mod/oublog</tt> capabilities.</p>';
    }

    return $result;
}

?>