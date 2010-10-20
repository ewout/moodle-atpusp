<?php // $Id: quickmail_config.php,v 2.5 2008/02/15 09:53:43 pcali1 Exp $

/**
 *   Author: Philip Cali
 *   Date: 2/15/2008
 *   Louisiana State University
 *
 *   Moodle form for the quickmail config
 */

require_once('../../config.php');
require_once("$CFG->dirroot/blocks/moodleblock.class.php");
require_once('config_form.php');
require_once($CFG->libdir . '/accesslib.php');
require_once('block_quickmail.php');

require_login();

// get the course id, and block instanceid
$id = required_param('id', PARAM_INT);
$instanceid = optional_param('instanceid', 0, PARAM_INT);

$quickmail = new block_quickmail();
$instance = new stdClass;


if (!$course = get_record('course', 'id', $id)) {
    error('Could not load course!');
}

$pinned = false;

//determine whether this instance is a pinned block, or regular instance
 if ($instanceid && $quickmailblock = get_record('block', 'name', 'quickmail')) {
    $instance = get_record('block_instance', 'id', $instanceid, 'blockid', $quickmailblock->id);
    if (!$instance){
        $pinned = true;
        $instance = get_record('block_pinned', 'id', $instanceid, 'blockid', $quickmailblock->id);
    }
 }

$quickmail->_load_instance($instance);
$quickmail->load_defaults();

//can user alter quickmail config?
if (!$quickmail->check_permission('block/quickmail:canconfig', CONTEXT_COURSE, $course)) {
    error(get_string('no_permission', 'block_quickmail'));
}

$form = new config_form();

if ($form->is_cancelled()) {
    redirect("$CFG->wwwroot/course/view.php?id=$course->id");
}

if ($data = $form->get_data()) {
    $config = new stdClass;
    $roleselection = array();

    foreach ($data as $key => $value) {
        $keyarr = explode(':', $key);
        if ($keyarr[0] == ROLE_SELECT) {
            $role = get_record('role', 'shortname', $keyarr[1]);
            $roleselection[$role->shortname] = $role->name;
        }
    }

    $config->allowstudents = $data->allowstudents;
    $config->roleselection = $roleselection;
    $config->groupmode = $course->groupmode;
   
    //Save into the instance without having to create a special table in the database or anything (using moodle's tables)
    $quickmail->instance_config_save($config, $pinned);

    //Redirect back to course
    redirect("$CFG->wwwroot/course/view.php?id=$course->id");

} else if (!$form->is_submitted()) {
    //Load the form with the correct values

   $form_data = array(
        STUDENT_SELECT => $quickmail->config->allowstudents,
        COURSE_ID => $course->id,
        INSTANCE_ID => $quickmail->instance->id
    );
   
   $quickmail_roles = $quickmail->grab_roles();
   foreach ($quickmail_roles as $role_shortname => $role_name) {
       $form_data[ROLE_SELECT.':'.$role_shortname] = true;
   }    

   $form->set_data($form_data);
}

$strquickmail = get_string('blockname', 'block_quickmail');

$navigation = array(
    array('name' => $course->shortname, 'link'=>"$CFG->wwwroot/course/view.php?id=$course->id", 'type'=>'title'),
    array('name' => $strquickmail, 'link'=>'', 'type'=>'title')
    );

print_header_simple($strquickmail, '', build_navigation($navigation));

print_heading('Configuring a '. $strquickmail .' block');
$form->display();
print_footer();

?>
