<?php

require_once (dirname(__FILE__).'/../../config.php');
require_once($CFG->dirroot.'/lib/grouplib.php');

require_js($CFG->wwwroot.'/blocks/groups/js/jquery.js');
require_js($CFG->wwwroot.'/blocks/groups/js/jquery.ui.js');
require_js($CFG->wwwroot.'/blocks/groups/js/json2.js');
require_js($CFG->wwwroot.'/blocks/groups/js/build.js');

global $COURSE, $USER, $db;

// get parameters
$id = required_param('id', PARAM_INT);
$courseid = required_param('course', PARAM_INT);
$groupingid = optional_param('grouping', NULL, PARAM_INT);

// get information about course
$course = get_record('course','id', $courseid);
$instance = get_record('block_instance','id', $id);
$block = block_instance('groups', $instance);

// build the head part of page
require_login($course, true);
$context = get_context_instance(CONTEXT_COURSE, $courseid);
if (!has_capability('moodle/course:managegroups', $context)){
    error('You dont have capability to edit groups and grouping');
    die;
}

$navigation = build_navigation(array(
array('name' => get_string('build_groups','block_groups'))));
print_header(get_string('build_groups','block_groups'), $course->shortname, $navigation, '', '<link rel="stylesheet" href="css/custom-theme/jquery.ui.css" type="text/css" media="screen" title="no title" charset="utf-8" />');
print_heading(get_string('build_groups','block_groups'));

// tab menu display
$tabs = array();
$tabs[0][] = new tabobject('view', $CFG->wwwroot.'/blocks/groups/view.php?id='.$id.'&course='.$courseid,
               get_string('view_groups','block_groups'));
$tabs[0][] = new tabobject('build', $CFG->wwwroot.'/blocks/groups/build.php?id='.$id.'&course='.$courseid,
               get_string('build_groups','block_groups'));
print_tabs($tabs,'build');

// get role identifiers $roleids of block's config
$roleids = array();
if (!empty($block->config->roleids)) {
    $roleids = $block->config->roleids;
} else {
    $roles = get_records('role');
    foreach ($roles as $role) {
        if (in_array($role->id, groups_get_possible_roles($context))) {
            $roleids[] = $role->id;
        }
    }
}

// get groups $groups of block's config
$groups = groups_get_all_groups($courseid);

// get participant $users_groupids by roleids and with groupids information
$users_groupids = array();
$users = get_role_users($roleids, $context, false, '', 'u.lastname ASC', true, '');
foreach ($users as $user) {
    $users_groupids[$user->id] = array('user' => $user, 'groupids' => array());
}
if (!empty($groups)) {
    foreach ($groups as $g) {
        $users = get_role_users($roleids, $context, false, '', 'u.lastname ASC', true, $g->id);
        if (!empty($users)) {
            foreach($users as $user) {
                array_push($users_groupids[$user->id]['groupids'], $g->id);
            }
        }
    }
}

// recover grouping and groping_info data
$grouping = new stdClass;
$grouping->name = '';
$groping_info = new stdClass;
$grouping_info->inheritgroupingname = 1;
$grouping_info->includeme = 0;
if ($groupingid) {
    $grouping = get_record('groupings', 'id', $groupingid);
    $grouping_info = get_record('block_groups_grouping_info', 'groupingid', $groupingid);
}

//////////// must be programming using form of moodle  with params $grouping, $grouping_info, $groups e $users_groupids

// print select box of groups can be edit
$htmlgroups = '<select id="groupselect" >';
$htmlgroups .= '<option value ="0"> -- All -- </option>';
if (!empty($groups)) {
    foreach ($groups as $g) {
        if (empty($block->config->groupids) || in_array($g->id,$block->config->groupids)) {
            $htmlgroups .=  '<option value="'.$g->id .'">'.$g->name.'</option>';
        }
    }
}
if (!empty($block->config->withoutgroup) && $block->config->withoutgroup) {
    $htmlgroups .= '<option value="without"> ('.get_string('without_groups','block_groups').') </option>';
}

$htmlgroups .= '</select>';
echo '<div style="text-align:center">'.get_string('show_participants','block_groups').' '.$htmlgroups.'</div>';
echo '<script type="text/javascript">';
echo '$("#groupselect").change(function () {';
echo '  $("#unassigned .student").hide();';
echo '  $("#unassigned .student[groups*=\'," + $("#groupselect").val()  + ",\']").show();';
echo '  if ($("#groupselect").val() == 0) $("#unassigned .student").show();';
echo '});';
echo '</script>';

// print interface
$add_criterion = get_string('add_criterion', 'block_groups');
$build_groups = get_string('build_groups_by_criterion', 'block_groups');
$reset_groups = get_string('reset_groups','block_groups');
$number_groups = get_string('number_groups','block_groups');
$without_groups = get_string('without_groups','block_groups');
$participant = get_string('participant','block_groups');
$randomly = get_string('randomly','block_groups');

$count = (isset($groupingid) ? count_records('groupings_groups', 'groupingid', $groupingid) : 2);

echo <<<HTML
<div id="predicate">
</div>
<div style="text-align:center;margin:10px;">
    <!--button type="button" onclick="addNewCriterion();" disabled="disabled">$add_criterion</button>&nbsp;-->
    <!--button type="button" onclick="buildTeams();" disabled="disabled"><strong>$build_groups</strong></button>&nbsp;-->
    <button type="button" onclick="resetTeams();">$reset_groups</button></div>
<div style="text-align:center;margin:10px;">$number_groups: <span id="stepper" class="stepper">$count</span></div>
<div id="unassigned"><h2>$participant</h2><button type="button" onclick="assignRandomly();">$randomly</button>
<div class="sortable">
HTML;

foreach($users_groupids as $userid => $user_groupids) {
    $attr_groups = 'without';
    if (!empty($user_groupids['groupids'])){
        $attr_groups = implode(',',$user_groupids['groupids']);
    }

    $result = array();
    if (!empty($block->config->groupids)) {
        $result = array_intersect($block->config->groupids, $user_groupids['groupids']);
    }

    if (!empty($result)) {
        $exist = False;
        if (isset($groupingid)) {
            $groupings_groups = get_records('groupings_groups', 'groupingid', $groupingid);
            foreach($groupings_groups as $grouping_group) {
                $exist = $exist || in_array($grouping_group->groupid, $user_groupids['groupids']);
            }
        }
    
        if (!isset($groupingid) || !$exist) {
            echo '<div id="student-'.$userid.'" class="student ui-state-default" groups=",'.
            $attr_groups.',">'.$user_groupids['user']->firstname.'&nbsp;'.$user_groupids['user']->lastname.'</div>';
        }
    }
    
    // without groups
    if (!empty($block->config->withoutgroup) && $block->config->withoutgroup)   {
        if ($attr_groups == 'without') {
            echo '<div id="student-'.$userid.'" class="student ui-state-default" groups=",'.
            $attr_groups.',">'.$user_groupids['user']->firstname.'&nbsp;'.$user_groupids['user']->lastname.'</div>';
        }
    }
}

$build_groups = get_string('build_groups','block_groups');
$msg_build_groups = get_string('msg_build_groups','block_groups');
$msg_grouping_name = get_string('msg_grouping_name', 'block_groups');
$msg_prefix_name = get_string('msg_prefix_name','block_groups');
$msg_include_me = get_string('msg_include_me','block_groups');
echo <<<HTML
</div>
</div>
<div id="teams">
HTML;

echo '<div class="team" id="commonteam" style="min-width: 150px; max-width: 175px;">';
echo '<h2 readonly="true">Common</h2>';
echo '<div class="sortable ui-sortable">';
// .. recover groups print
if (isset($groupingid)) {
    $grouping_info = get_record('block_groups_grouping_info', 'groupingid', $groupingid);

    $commonteamids = explode(',', $grouping_info->commonteam);
    // create common team
    if (!empty($commonteamids)){
        foreach ($commonteamids as $userid) {
            if (!empty($userid)) {
                $user_groupids = $users_groupids[$userid];
                $attr_groups = implode(',',$user_groupids['groupids']);
                echo '<div id="student-'.$userid.'" class="student ui-state-default" groups=",'.
                $attr_groups.',">'.$user_groupids['user']->firstname.'&nbsp;'.$user_groupids['user']->lastname.'</div>';
            }
        }
    }
    echo '</div>';
    echo '</div>';

    // create others groups
    $count = 0;
    $groupings_groups = get_records('groupings_groups', 'groupingid', $groupingid);
    foreach ($groupings_groups as $grouping_group) {
        $group = get_record('groups', 'id', $grouping_group->groupid);
        $name = ($grouping_info->inheritgroupingname == 1 ? substr($group->name, strlen($grouping->name)) : $group->name); 

        echo '<div class="team" id="team-'.$count.'" style="min-width: 150px; max-width: 175px;" >';
        echo '<h2>'.$name.'</h2>';
        echo '<div class="sortable ui-sortable">';

        if ($groups_members = get_records('groups_members','groupid',$group->id)) {
            foreach ($groups_members as $group_member) {
                //print_r($group_member);
                if (!in_array($group_member->userid, $commonteamids)) {    
                    if (array_key_exists($group_member->userid,$users_groupids) && $user_groupids = $users_groupids[$group_member->userid]) {
                        $attr_groups = implode(',',$user_groupids['groupids']);
                        echo '<div id="student-'.$group_member->userid.'" class="student ui-state-default" groups=",'.
                        $attr_groups.',">'.$user_groupids['user']->firstname.'&nbsp;'.$user_groupids['user']->lastname.'</div>';
                    }
                }
            }
        }

        echo '</div>';
        echo '</div>';
        $count ++;
    }

} else {
    echo '</div>';
    echo '</div>';
}

echo <<<HTML
</div>
<div style="text-align:center;margin:15px 50px 0px;border-top:1px solid black;padding-top:15px;">
    <button type="button" onclick="$('#createGroupsForm').slideDown(300);" style="font-size:1.5em;font-weight:bold;">$build_groups</button>
HTML;

echo <<<HTML
    <div style="display:none" id="createGroupsForm"><p>$msg_build_groups</p>
        <table style="margin:auto;">
            <tr><th scope="row"><label for="groupingname">$msg_grouping_name</label></th><td><input type="text" id="groupingname" value="$grouping->name"  /></td></tr>
            <tr><th scope="row"><label for="inheritgroupingname">$msg_prefix_name </label></th><td style="text-align:left;">
HTML;
echo '<input type="checkbox" id="inheritgroupingname" value="1" '.
     ($grouping_info->inheritgroupingname ? 'checked="checked"': '').' />';
echo <<<HTML
            </td></tr>
            <tr><th scope="row"><label for="includeme">$msg_include_me</label></th><td style="text-align:left;">
HTML;
echo '<input type="checkbox" id="includeme" value="1" '.
     ($grouping_info->includeme ? 'checked="checked"': '').' />';
echo <<<HTML
            </td></tr>
        </table>
        <button type="button" onclick="$('#createGroupsForm').slideUp(300);">Cancel</button>&nbsp;<button type="button" onclick="createGroups();">OK</button>
    </div>
</div>
<div id="debug"></div>
HTML;

print_footer();

?>
