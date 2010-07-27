<?php
/*
 * Created on August 16, 2007
 * Modified by Jason Hardin from HSU's code origianlly created by Sam Chaffee
 *
 * Capabilities for trouble_ticket block
 */
$block_trouble_ticket_capabilities = array(
    'block/trouble_ticket:submitticket' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'legacy' => array(
            'guest' => CAP_PREVENT,
            'student' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'coursecreator' => CAP_ALLOW,
            'admin' => CAP_ALLOW
        )
    )
);
?>
