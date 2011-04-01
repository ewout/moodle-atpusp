<?php
require_once(dirname(__FILE__).'/../../../config.php');
require_once($CFG->dirroot.'/lib/grouplib.php');

global $COURSE, $USER, $db;

$courseid = optional_param('course', $COURSE->id, PARAM_INT);
$groupingid = optional_param('grouping', NULL, PARAM_INT);

if (isset($_REQUEST['action']) && ($_REQUEST['action']=='edit') && (!empty($groupingid))) {
    
    $num = 0;
    $groups_grouping = get_record('block_groups_grouping', 'groupingid', $groupingid);
    $grouping = get_record('groupings', 'id', $groupingid);

    if ($groups = groups_get_all_groups($courseid, 0, $grouping->id)) {
        $groupnames = array();
        foreach ($groups as $group) {
            $groupname = ($groups_grouping->inheritgroupingname ==1 ? substr($group->name, strlen(trim($grouping->name))+1) : $group->name);
            echo "teamNames[".$num."] = '".$groupname."';\n";
            $num ++;
        }
    }
    //print_r($grouping);

    echo 'alert("groups - valor de grouping num '.$num.'");';
    

    echo 'updateTeams('.$num.');';
    echo '$("#stepperval").html("'.$num.'");';
    
}

?>
