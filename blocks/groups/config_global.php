<div style="text-align: center;">
 <input type="hidden" name="block_simplehtml_strict" value="0" />
 <input type="checkbox" name="block_simplehtml_strict" value="1"
   <?php if(!empty($CFG->block_simplehtml_strict)) 
             echo 'checked="checked"'; ?> />
   <?php print_string('donotallowhtml', 'block_simplehtml'); ?>
 <p>
    Can change groups and roles <br/>
    <?php
    $roles = get_records('role');
    foreach ($roles as $role) {
    echo '<input name="config_roleids[]" type="checkbox" value="'.$role->id.'" '.
        (empty($CFG->config_roleids) || in_array($role->id, $CFG->config_roleids) ?'checked="yes"':'').
        '/>'.$role->name.'&nbsp;&nbsp;&nbsp;&nbsp;';
    }
    ?>
 </p>
 <p>
 <input type="submit" value="<?php print_string('savechanges'); ?>" />
 </p>
</div>
