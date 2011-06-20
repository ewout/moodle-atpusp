<?php

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

/// get url variables
class grouping_form {

    private $context;
    private $roles = array();
    private $members = array();
    private $grouping;
    private $groups = array();
    private $group_members = array();
    private $select_groups;
    private $isupdate = false;

    function grouping_form ($context, $members, $select_groups = array()) {
        global $DB;
        
        $this->context = $context;
        $roleids = groups_get_possible_roles($context);
        $this->roles = $DB->get_records_list('role', 'id', $roleids);
        
        $this->select_groups = $select_groups;

        $this->members = $members; 
        $this->grouping = new stdClass();
    }

    //function validation($data) {
    //    global $COURSE, $DB;
    //}

    function set_data($grouping) {
        global $DB;

        $this->grouping = $grouping;
        if ($this->groups = $DB->get_records_sql('SELECT * FROM {groups} WHERE id IN '.
                            '(SELECT groupid FROM {groupings_groups} WHERE groupingid=?)', array($grouping->id))) {
            foreach($this->groups as $groupid=>$group) {
                $this->group_members[$groupid] = $DB->get_records_sql('SELECT * FROM {user} WHERE id IN '.
                                        '(SELECT userid FROM {groups_members} WHERE groupid=?)', array($group->id));
            }
        }
        $this->isupdate = true;
    }

    function is_submitted() {
        return (!empty($_REQUEST['grouping_form']) ? true : false);
    }

    function get_data() {
        $result = array();
        if ($this->is_submitted()) { 
            $result = new stdClass();

            $groupingname = trim($_REQUEST['groupingname']);
            $inherit = isset($_REQUEST['inherit']) && $_REQUEST['inherit']==1 ? true : false;
            $commonteam = isset($_REQUEST['commonteam']) ? $_REQUEST['commonteam'] : array();
            $teams = $_REQUEST['teams'];
            $isupdate = $_REQUEST['action'] != 'update' ? false : true;
            
            // update isupdate
            $result->isupdate = $isupdate;
            
            // update grouping
            $result->grouping->id = $isupdate ? $_REQUEST['grouping'] : 0;
            $result->grouping->name = $groupingname;

            //print_r($_REQUEST);
            
            $count = 0;
            foreach ($_REQUEST['teams'] as $team) {
                // update groups
                $group = new stdClass();
                $group->id = $team['groupid'];
                $group->name = $inherit ? $groupingname.' '.$team['name'] : $team['name'];
                $result->groups[$count] = $group;
                // update group_members
                $result->group_members[$count] = array();
                if (!empty($team['members'])) {
                    foreach ($team['members'] as $userid) {
                        $result->group_members[$count][] = $userid;
                    }
                }
                if (!empty($_REQUEST['commonteam'])) {
                    foreach ($_REQUEST['commonteam'] as $userid) {
                        $result->group_members[$count][] = $userid;
                    }
                }
                $count++;
            }
            //print_r($result);
            return $result;
        }
        return NULL;
    }
   
    private function get_roleids($userid) {
        global $DB;
        $listofcontexts = get_related_contexts_string($this->context);
        $user_roleids = $DB->get_fieldset_select('role_assignments', 'roleid', 'userid='.$userid.' AND contextid '.$listofcontexts);
        $result = 'no_roles';
        if (!empty($user_roleids)) { $result = implode(',', $user_roleids); }
        return $result;
    }

    private function get_groupids($userid){
        global $DB;
        $user_groupids = $DB->get_fieldset_sql('SELECT groupid FROM {groups_members} WHERE groupid IN '.
                            '(SELECT id FROM {groups} WHERE courseid=?) AND userid=?', array($this->context->instanceid, $userid));
        $result = 'no_groups';
        if (!empty($user_groupids)) { $result = implode(',', $user_groupids); }
        return $result;
    }

    private function get_common_members() {
        global $DB;
        $result = array();
        if (!empty($this->groups)) { $result = $this->members; }
        if (!empty($this->group_members)) {
           foreach($this->group_members as $groupid=>$members) {
                $result = array_intersect_key($result, $members);
            }
        }
        return $result;
    }
 
    private function isinherit() {
        $result = true;
        if (!empty($this->groups)) {
            foreach ($this->groups as $groupid=>$group) {
                $pos = strpos($group->name, $this->grouping->name);
                $result = ($pos!==false ? ($pos==0 ? true : false) : false);
            }
        }
        return $result;
    }

    function display() {

        // get role select
        $htmlgroups = '<select id="groupselect">';
        $htmlgroups .= '<option value="0"> -- '.get_string('all_groups', 'block_vgroupings').' -- </option>';
        if (!empty($this->select_groups)) {
            foreach ($this->select_groups as $group) {
                $htmlgroups .= '<option value="'.$group->id.'">'.$group->name.'</option>';
            }
        } 
        $htmlgroups .= '<option value="no_groups">'.get_string('no_groups', 'block_vgroupings').'</option>';
        $htmlgroups .= '</select>';

        // get role select
        $htmlroles  = '<select id="roleselect">';
        $htmlroles .= '<option value="0"> -- '.get_string('all_roles','block_vgroupings').' -- </option>';
        foreach ($this->roles as $role) {
            $htmlroles .= '<option value="'.$role->id.'">'.$role->name.'</option>';
        }
        $htmlroles .= '<option value="no_roles">'.get_string('no_roles','block_vgroupings').'</option>';
        $htmlroles .= '</select>';

        $userids = array();
        // buil div for commons members
        $commondivs = '';
        $common_members = $this->get_common_members();
        if ($common_members = $common_members) {
            foreach ($common_members as $userid => $user) {
                $commondivs .= '<div id="student-'.$userid.'" class="student ui-state-default" groups="'.$this->get_groupids($userid).'," ';
                $commondivs .= ' roles="'.$this->get_roleids($userid).',">'.$user->firstname.'&nbsp;'.$user->lastname.'</div>';
                $userids[] = $userid;
            }
        }

        // buil divs for groups with members
        $count = 0;
        $groupdivs = '';
        if (!empty($this->groups)) {
            foreach ($this->groups as $group) {
                $groupname = ($this->isinherit() ? substr($group->name, strlen($this->grouping->name)) : $group->name);
                $groupid = (isset($group->id) ? $group->id : 0);
                $groupdivs .= '<div class="team" id="team-'.$count.'" groupid="'.$groupid.'" >';
                $groupdivs .= '<h2>'.$groupname.'</h2>';
                $groupdivs .= '<div class="sortable ui-sortable" >';
                if (!empty($this->group_members[$groupid])) {
                    foreach ($this->group_members[$groupid] as $userid=>$user) {
                        if (!array_key_exists($userid, $common_members)) {
                            $groupdivs .= '<div id="student-'.$userid.'" class="student ui-state-default" groups="'.$this->get_groupids($userid).'," ';
                            $groupdivs .= ' roles="'.$this->get_roleids($userid).',">'.$user->firstname.'&nbsp;'.$user->lastname.'</div>';
                            $userids[] = $userid;
                        }
                    }
                }
                $groupdivs .= '</div>';
                $groupdivs .= '</div>';
                $count++;
            }
        }
        
        // get user divs
        $userdivs = '';
        foreach($this->members as $userid => $user) {
            if (!in_array($userid, $userids)) {
                $userdivs .= '<div id="student-'.$userid.'" class="student ui-state-default" groups="'.$this->get_groupids($userid).'," ';
                $userdivs .= ' roles="'.$this->get_roleids($userid).',">'.$user->firstname.'&nbsp;'.$user->lastname.'</div>';
            }
        }
        
        // get inherit name
        $inherit_check = ($this->isinherit() ? 'checked="checked"' : '');
        
        // get all label messages
        $build_groups_label = get_string('build_group', 'block_vgroupings');
        $grouping_name_label = get_string('grouping_name', 'block_vgroupings');
        $prefix_name_label = get_string('prefix_name', 'block_vgroupings');
        $number_groups_label = get_string('number_groups', 'block_vgroupings');
        $reset_groups_label = get_string('reset_groups', 'block_vgroupings');
        $participant_label = get_string('participant', 'block_vgroupings');
        $randomly_label = get_string('randomly', 'block_vgroupings');
        $show_roles_label = get_string('show_roles', 'block_vgroupings');
        $common_members_label = get_string('common_members', 'block_vgroupings');
        $show_groups_label = get_string('show_groups', 'block_vgroupings');
        
        $grouping_name = (isset($this->grouping->name) ? $this->grouping->name : '');

        // print forms of groupings
        if ($this->isupdate) {
            echo '<form action="?course='.$_REQUEST['course'].'&grouping='.$this->grouping->id.'&action=delete" method="post"  >';
            echo '<input type="hidden" id="isupdate" name="isupdate" value="'.$this->grouping->id.'" />';
            echo '<input type="submit" value="'.get_string('delete_grouping','block_vgroupings').'" />';
            echo '</form>';
        }
        echo <<<HTML
        <div style="text-align:center;">$show_roles_label : $htmlroles<br/>$show_groups_label : $htmlgroups</div>
            <div style="text-align:center;">$number_groups_label : <span id="stepper" class="stepper">$count</span></div>
            <div id="unassigned" >
                <h2>$participant_label</h2>
                <button type="button" onclick="assignRandomly();">$randomly_label</button>
                <button type="button" onclick="resetTeams();">$reset_groups_label</button>
                <div class="sortable">$userdivs</div>
            </div>
            <div id="teams">
                <div class="team" id="commonteam">
                    <h2 readonly="true">$common_members_label</h2>
                    <div class="sortable ui-sortable" >$commondivs</div>
                </div>
                $groupdivs
            </div>
            <div>
                <button type="button" onclick="$('#createGroupsForm').slideDown(300);" style="font-size:1.5em;font-weight:bold;">$build_groups_label</button>
                <div style="display:none" id="createGroupsForm">
                <table style="margin:auto;">
                    <tr>
                        <th scope="row"><label for="inheritgroupingname">$prefix_name_label</label></th>
                        <td style="text-align:left;"><input type="checkbox" id="inherit" value="1" $inherit_check /></td>
                    </tr>
                    <tr>
                        <th colspan="row"><label for="groupingname">$grouping_name_label</label></th>
                        <td><input type="text" id="groupingname" value="$grouping_name" /></td>
                    </tr>
                </table>
                <button type="button" onclick="createGroups();">OK</button>&nbsp;
                <button type="button" onclick="$('#createGroupsForm').slideUp(300);">Cancel</button>
            </div>
        </div>
HTML;
    }

}

