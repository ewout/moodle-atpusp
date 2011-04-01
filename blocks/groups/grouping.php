<?php

require_once (dirname(__FILE__).'/../../config.php');
require_once($CFG->dirroot.'/lib/grouplib.php');
require_once('lib.php');

global $COURSE, $USER, $db;

// get parameters
$id = required_param('id', PARAM_INT);
$courseid = required_param('course', PARAM_INT);
$action = required_param('action', PARAM_TEXT);
$groupingid = optional_param('grouping', NULL, PARAM_INT);
$referer = $_SERVER['HTTP_REFERER'];


// validate if user can edit
$course = get_record('course', 'id', $courseid);
require_login($course, true);
$context = get_context_instance(CONTEXT_COURSE, $courseid);
if (!has_capability('moodle/course:managegroups', $context)){
    error('You dont have capability to edit groups and grouping');
    die;
}

// get information about course and block_groups
$instance = get_record('block_instance', 'id', $id);
$block_groups = block_instance('groups', $instance);

// actions
if ($action == 'delete') {
    if (!isset($groupingid)) {
        error('A required parameter (grouping) was missing', $referer);
    }
    $groups = groups_get_all_groups($courseid, 0, $groupingid);
    if (!empty($groups)) {
        foreach ($groups as $groupid => $group) {
            if (!groups_delete_group($groupid)) {
                print_error('errordeletegroup', 'group');
            }
        }
    }
    groups_delete_grouping($groupingid);
    block_groups_delete_grouping_info($groupingid);
} else if ($action == 'create' || $action == 'update') {
    // get values in parameters
    $groupingname = trim($_REQUEST['groupingname']);
    $inheritgroupingname = (isset($_REQUEST['inheritgroupingname']) && $_REQUEST['inheritgroupingname'] == 1 ? True : False);
    $includeme = (isset($_REQUEST['includeme']) && $_REQUEST['includeme'] == 1 ? True : False);
    $commonteam = $_REQUEST['commonteam'];
    $teams = $_REQUEST['teams'];
    
    // add or update groupings (groupings, grouping_info)
    $grouping = new stdClass;
    $grouping_info = new stdClass;
    if ($action == 'update' ) {
        if (!isset($groupingid)) {
            error('A required parameter (grouping) was missing', $referer);
        }
        $grouping = get_record('groupings', 'id', $groupingid);
        $grouping_info = get_record('block_groups_grouping_info', 'groupingid', $groupingid);
    }
    $grouping->name = $groupingname;
    $grouping->description = $groupingname . ' (created used block_groups)';
    $grouping->courseid = $courseid;
    $grouping_info->authorid = $USER->id;
    $grouping_info->inheritgroupingname = ($inheritgroupingname ? 1 : 0);
    $grouping_info->includeme = ($includeme ? 1 : 0);
    $grouping_info->commonteam = implode(',', $commonteam);
    $grouping_info->contextid = $id;
    if ($action == 'update') {
        groups_update_grouping($grouping);
        block_groups_update_grouping_info($grouping_info);
    } else if ($action == 'create') {
        $groupingid = groups_create_grouping($grouping);
        $grouping_info->groupingid = $groupingid;
        block_groups_create_grouping_info($grouping_info);
    }

    //print_r($teams);
    // update members of groups
    $teams = $_REQUEST['teams'];
    $keys = array_keys($teams);
    //print_r($keys);
    if ($action == 'update') {
        $groups = groups_get_all_groups($courseid, 0, $groupingid);
        foreach ($groups as $group) {
            $team = array_shift($teams);
            if (!empty($team)) {
                $name = array_shift($keys);
                $group->name = ($inheritgroupingname ? $groupingname.' '.$name : $name);
                groups_update_group($group);
                
                //update members of group
                delete_records('groups_members', 'groupid', $group->id); 
                foreach($team as $userid) {
                    echo "<hr>groupid " . $group->id . ' userid ' . $userid ."</hr>";
                    groups_add_member($group->id, $userid);
                }
                //$name = array_shift($keys);
                //$group->name = ($inheritgroupingname ? $groupingname.' '.$name : $name);
                //groups_update_group($group);
            } else {
                groups_delete_group($group->id);
            }
        }
    }

    //print_r($teams);
    //echo count($groups);

    // add or update groups
    if (!empty($teams)) {
        foreach ($teams as $name => $team) {
            $groupname = ($inheritgroupingname ? $groupingname.' '.$name : $name);
            $groupdata = new stdClass;
            $groupdata->courseid = $courseid;
            $groupdata->name = $groupname;
            $groupid = groups_create_group($groupdata);

            // add users to groups
            if (!empty($team)) {
                foreach($team as $userid) {
                    groups_add_member($groupid, $userid);
                }
            }
            // add common users to groups
            if (!empty($_REQUEST['commonteam'])) {
                foreach ($_REQUEST['commonteam'] as $userid) {
                    groups_add_member($groupid, $userid);
                }
            }

            // add group to grouping
            groups_assign_grouping($groupingid, $groupid);
        }
    }
}

//echo ("referer::".$referer);
header('location:'.$referer);

?>
