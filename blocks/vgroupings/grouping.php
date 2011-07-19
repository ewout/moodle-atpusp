<?php

require_once (dirname(__FILE__).'/../../config.php');
require_once ('downgrade.php');

require_once('grouping_form.php');
require_once($CFG->dirroot.'/lib/grouplib.php');
require_once($CFG->dirroot.'/group/lib.php');

// get parameters
$courseid   = required_param('course', PARAM_INT);
$action     = optional_param('action', 'new', PARAM_TEXT);
$groupingid = optional_param('grouping', NULL, PARAM_INT);

// get url of page menu
$url = new moodle_url('/blocks/vgrouping/view.php');
$url_params = compact('mod','course','action','grouping');
foreach ($url_params as $var => $val) {
    if (empty($val)) unset($url_params[$var]);
}

// require login and set context
require_login($courseid, false);
$context = get_context_instance(CONTEXT_COURSE, $courseid);
if (!has_capability('moodle/course:managegroups', $context)) {
    error('You dont have capability to edit groups and grouping');
    die;
}

/* ---------------------------------------------------------------- */

// build the head part of page
$PAGE2->set_context($context);
$PAGE2->set_url($url, $url_params);

// set title and navbar
$PAGE2->set_title(get_string('msg_view_management', 'block_vgroupings'));
$PAGE2->navbar->add(get_string('msg_view_management', 'block_vgroupings'));

// show message of remove event
if (isset($action) && $action == 'delete') {
    $groupingid = required_param('grouping', PARAM_INT); 
    if (optional_param('confirm', false, PARAM_BOOL)) {
        $groups = groups_get_all_groups($courseid, 0, $groupingid);
        if (!empty($groups)) {
            foreach ($groups as $groupid => $group) {
                if (!groups_delete_group($groupid)) {
                    print_error('errordeletegroup', 'group');
                }
            }
        }
        groups_delete_grouping($groupingid);
        header('Location: grouping.php?course='.$courseid);
    } else {
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('msg_view_management', 'block_vgroupings'));
        echo $OUTPUT->confirm(get_string('delete_confirm_label', 'block_vgroupings'),
                    'grouping.php?course='.$courseid.'&grouping='.$groupingid.'&action=delete&confirm=true',
                    'grouping.php?course='.$courseid.'&grouping='.$groupingid);
        echo $OUTPUT->footer();
        die; 
    }
}

// ------------------------------------------------------------------------>
$select_groups = $DB->get_records('groups',array('courseid'=>$courseid));
$members = groups_get_potential_members($courseid);
$grouping_form = new grouping_form($context, $members, $select_groups);

// save or update groupings
if ($data = $grouping_form->get_data()) {

    //print_r($data);

    // add or update groupings (groupings, grouping_info)
    $grouping = $data->grouping;
    $grouping->description = $grouping->name . ' (created used block_vgroupings)';
    $grouping->courseid = $courseid;
    if ($data->isupdate) {
        groups_update_grouping($grouping);
    } else {
        $groupingid = groups_create_grouping($grouping);
        $grouping->id = $groupingid;
    }
    
    // update members of groups
    $all_groups = groups_get_all_groups($courseid, 0, $groupingid);
    $DB->delete_records('groupings_groups', array('groupingid'=>$groupingid));
    $count = 0;
    foreach($data->groups as $group) {
        $group->courseid = $courseid;
        if (!empty($group->id) &&  $group->id!=0) {
            groups_update_group($group);
            unset($all_groups[$group->id]);
        } else {
            $group->id = groups_create_group($group);
        }
        
        // change members of groups
        $DB->delete_records('groups_members', array('groupid'=>$group->id));
        foreach($data->group_members[$count] as $userid) {
            groups_add_member($group->id, $userid);
        }
        // add group in groupings
        groups_assign_grouping($grouping->id, $group->id);

        $count++;
    }

    // remove all groups without members
    foreach($all_groups as $group) {
        groups_delete_group($group->id);
    } 
}

$PAGE2->requires->js('/blocks/vgroupings/js/jquery.js');
$PAGE2->requires->js('/blocks/vgroupings/js/jquery.ui.js');
$PAGE2->requires->js('/blocks/vgroupings/js/grouping.js');

echo $OUTPUT->header();
//echo $OUTPUT->heading(get_string('msg_view_management', 'block_vgroupings'));

// get groupings
$groupings = $DB->get_records('groupings', array('courseid'=>$courseid));
// tab menu display
$tabs = array();
if (!empty($groupings)) {
    foreach ($groupings as $grouping) {
        $tabs[0][] = new tabobject('grouping'.$grouping->id,
                    $CFG->wwwroot.'/blocks/vgroupings/grouping.php?course='.$courseid.'&grouping='.$grouping->id,
                    $grouping->name);
    }
}
$tabs[0][] = new tabobject('new',
                $CFG->wwwroot.'/blocks/vgroupings/grouping.php?&course='.$courseid.'&action=new',
                get_string('new_grouping_label','block_vgroupings'));

// print tabs and content
print_tabs($tabs, (isset($groupingid) ? 'grouping'.$groupingid : 'new'));

// display groupings
if (!empty($groupingid) && $grouping = $groupings[$groupingid]) {
    $grouping_form->set_data($grouping);
}

$grouping_form->display();

// print foot
echo $OUTPUT->footer();
