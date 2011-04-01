<table cellpadding="9" cellspacing="0" align="center">
    <tr valign="top">
        <td align="right">
            <?php echo print_string('config_instance_title','block_groups'); ?>
        </td>
        <td>
            <input id="title" name="title" type="" value="<?php echo (!empty($this->config->title) ? $this->config->title:''); ?>" size="50" />
        <td>
    </tr>
    <tr valign="top">
        <td align="right">
            <?php print_string('config_instance_roles', 'block_groups'); ?>:
        </td>
        <td>
            <?php
            $courseid = $this->instance->pageid;
            $context = get_context_instance(CONTEXT_COURSE, $courseid);
            $possible_roles = groups_get_possible_roles($context);
            $roles = get_records('role');
            foreach ($roles as $role) {
                if (in_array($role->id, $possible_roles)) {
                    echo '<input name="roleids[]" type="checkbox" value="'.$role->id.'" '.
                        (empty($this->config->roleids) || in_array($role->id, $this->config->roleids) ?'checked="yes"':'').
                        '/>'.$role->name.'<br/>';
                }

            }
            ?>
        </td>
    </tr>
    <tr valign="top">
        <td align="right">
            <?php print_string('config_instance_groups','block_groups'); ?>:
        </td>
        <td>
            <?php
            $sql = 'SELECT * FROM '.$CFG->prefix.'groups g WHERE courseid='.$this->instance->pageid.' AND g.id NOT IN '.
                        '(SELECT groupid FROM '.$CFG->prefix.'groupings_groups gg WHERE gg.groupingid IN '.
                        '(SELECT groupingid FROM '.$CFG->prefix.'block_groups_grouping_info))';
            $groups = get_records_sql($sql);
            if (!empty($groups)){
                foreach ($groups as $group) {
                   echo '<input name="groupids[]" type="checkbox" value="'.$group->id.'" '.
                        (empty($this->config->groupids) || in_array($group->id, $this->config->groupids)?'checked="yes"':'').' />'.$group->name.'<br/>';
                }
            }
            ?>
            <input name="withoutgroup" type="checkbox" value="1" <?php echo ((!empty($this->config->withoutgroup) && $this->config->withoutgroup) ? 'checked="yes"':''); ?> /><?php echo get_string('without_groups','block_groups'); ?><br/>
            <hr/>
            <?php
            $sql = 'SELECT * FROM '.$CFG->prefix.'groups g WHERE courseid='.$this->instance->pageid.' AND g.id IN '.
                        '(SELECT groupid FROM '.$CFG->prefix.'groupings_groups gg WHERE gg.groupingid IN '.
                        '(SELECT groupingid FROM '.$CFG->prefix.'block_groups_grouping_info))';
            $groups = get_records_sql($sql);
            if (!empty($groups)){
                foreach ($groups as $group) {
                   echo '<input name="groupids[]" type="checkbox" value="'.$group->id.'" '.
                        (empty($this->config->groupids) || in_array($group->id, $this->config->groupids)?'checked="yes"':'').' />'.$group->name.'<br/>';
                }
            }
            ?>
        </td>
    </tr>
    <tr>
        <td colspan="2" align="center">
            <input type="submit" value="<?php print_string('savechanges') ?>" />
        </td>
    </tr>
</table>
