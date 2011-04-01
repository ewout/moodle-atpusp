<?php

function block_groups_delete_grouping_info($groupinginfo_or_id) {
    if (is_object($groupinginfo_or_id)) {
        $groupingid = $groupinginfo_or_id->id;
        $groupinginfo = $groupinginfo_or_id;
    } else {
        $groupingid = $groupinginfo_or_id;
        if (!$groupinginfo = get_record('block_groups_grouping_info', 'groupingid', $groupingid)) {
            return false;
        }
    }
    $result = delete_records('block_groups_grouping_info', 'groupingid', $groupingid);
    // if ($result) {
    //      events_trigger('block_groups_grouping_info_deleted', $groupinginfo);
    // }
    return $result;
}

function block_groups_update_grouping_info($data){
    global $CFG;
    $result = update_record('block_groups_grouping_info', $data);
    //if ($result) {
    //    events_trigger('block_groups_grouping_info_update', stripslashed_recursive($data));
    //}
    return $result;
}

function block_groups_create_grouping_info($data) {
    $id = insert_record('block_groups_grouping_info', $data);
    //if ($id) {
    //  events_trigger('block_groups_create_grouping_info_created', stripslashes_recursive($data));
    //}
    return $id;
}

?>
