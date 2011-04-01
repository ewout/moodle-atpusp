<?php

require_once (dirname(__FILE__).'/../../config.php');
require_once($CFG->dirroot.'/lib/grouplib.php');

require_js($CFG->wwwroot.'/blocks/groups/js/jquery.js');
require_js($CFG->wwwroot.'/blocks/groups/js/jquery.ui.js');
require_js($CFG->wwwroot.'/blocks/groups/js/json2.js');

global $COURSE, $USER, $db;

// get parameters
$courseid = required_param('course', PARAM_INT);
$id = required_param('id', PARAM_INT);
$action = optional_param('action', NULL, PARAM_TEXT);
$groupingid = optional_param('grouping', NULL, PARAM_INT);

// get information about course and block
$course = get_record('course','id', $courseid);
$instance = get_record('block_instance','id', $id);
$block = block_instance('groups', $instance);

// build the head part of page
require_login($course, true);
$navigation = build_navigation(array(array('name' => get_string('msg_view_management','block_groups'))));
print_header(get_string('msg_view_management', 'block_groups'), $course->shortname, $navigation);
print_heading(get_string('msg_view_management', 'block_groups'));

// tab menu display
$tabs = array();
$tabs[0][] = new tabobject('view', $CFG->wwwroot.'/blocks/groups/view.php?id='.$id.'&course='.$courseid,
               get_string('view_groups','block_groups'));
$tabs[0][] = new tabobject('build', $CFG->wwwroot.'/blocks/groups/build.php?id='.$id.'&course='.$courseid,
               get_string('build_groups','block_groups'));
print_tabs($tabs,'view');


// show message of remove event
if (isset($action) && $action == 'delete' && isset($groupingid)) {
    if ($grouping = get_record('groupings', 'id', $groupingid)) {
        $optionsno  = array('id'=>$id, 'course'=>$courseid);
        $optionsyes = array('id'=>$id, 'course'=>$courseid, 'grouping'=>$groupingid, 'action'=>'delete');
        notice_yesno(get_string('deletegroupingconfirm', 'block_groups', $grouping->name),
                     'grouping.php', 'grouping.php', $optionsyes, $optionsno, 'get', 'get');
        print_footer();
        die;
    }
}


// list groupings ids
if ($block_groups_groupings_info = get_records('block_groups_grouping_info', 'authorid', $USER->id)) {
    $groupings_ids = '';
    foreach ($block_groups_groupings_info as $block_group_grouping_info) {
        $groupings_ids.=','.$block_group_grouping_info->groupingid;
    }
    $groupings_ids = (!empty($groupings_ids) ? substr($groupings_ids,1): $groupings_ids);
    // get groupings info in table
    $groupings = get_records_list('groupings', 'id', $groupings_ids);
}

$data = array();
if (!empty ($groupings)) {
    foreach ($groupings as $grouping) {
        $line = array();
        $line[0] = format_string($grouping->name);

        if ($groups = groups_get_all_groups($courseid, 0, $grouping->id)) {
            $groupnames = array();
            foreach ($groups as $group) {
                $groupnames[] = format_string($group->name);
            }
            $line[1] = implode(', ', $groupnames);
        } else {
            $line[1] = get_string('none');
        }
        $line[2] = (int) count_records('course_modules', 'course', $courseid, 'groupingid', $grouping->id);

        $buttons  = '<a title="'.get_string('edit').'" href="build.php?id='.$id.'&amp;course='.$courseid.'&amp;grouping='.
                    $grouping->id.'"><img src="'.$CFG->pixpath.'/t/edit.gif" class="iconsmall" alt="'.get_string('edit').'" /></a>';
        $buttons .= '<a title="'.get_string('delete').'" href="view.php?id='.$id.'&amp;course='.$courseid.
                    '&amp;action=delete&amp;grouping='.$grouping->id.'"><img src="'.$CFG->pixpath.
                    '/t/delete.gif" class="iconsmall" alt="'.get_string('delete').'" /></a>';
         $line[3] = $buttons;
         $data[] = $line;
    }
}

$table = new object();
$table->head  = array(get_string('grouping','group'), get_string('group'), get_string('activities'), get_string('edit'));
$table->size  = array('30%', '50%', '10%', '10%');
$table->align = array('left', 'left', 'center', 'center');
$table->width = '90%';
$table->data  = $data;
print_table($table);

// print foot
print_footer();

?>
