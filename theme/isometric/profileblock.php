
    <?php
       echo '<div id="profilename">';

	    function get_content () {
        global $USER, $CFG, $SESSION, $COURSE;
        $wwwroot = '';
        $signup = '';}

        if (empty($CFG->loginhttps)) {
            $wwwroot = $CFG->wwwroot;
        } else {
            $wwwroot = str_replace("http://", "https://", $CFG->wwwroot);
        }


       if (!isloggedin() or isguestuser()) {
       echo '<ul><li><a href="'.$CFG->wwwroot.'/login/index.php">'.get_string('loggedinnot').'</a>';
       echo '</li></ul></div>';

       } else {
       echo '<ul><li><a href="'.$CFG->wwwroot.'/user/view.php?id='.$USER->id.'&amp;course='.$COURSE->id.'">'.$USER->firstname.' '.$USER->lastname.'</a>';
        echo '</li></ul></div>';

       }
    ?>
 <?php
    echo '<div id="profileoptions">';
	if (!isloggedin() or isguestuser()) {
	echo '<ul><form class="loginform" id="login" method="post" action="'.$wwwroot.'/login/index.php">';
	echo '<li><label for="login_username">'.get_string('username').'</label>:&nbsp;<input class="loginform" type="text" name="username" id="login_username" value="" /></li>';
	echo '<li><label for="login_password">'.get_string('password').'</label>:&nbsp;<input class="loginform" type="password" name="password" id="login_password" value="" /></li>';
	echo '<li><input id="submit" type="submit" value="'.get_string('login').'" /></li>';
	echo '</form></ul>';
	} else {
	echo '<ul>';
	echo '<li><a href="'.$CFG->wwwroot.'/login/logout.php?sesskey='.sesskey().'">'.get_string('logout').'</a></li>';
	echo '<li><a href="'.$CFG->wwwroot.'/my">'.get_string('mycourses').'</a></li>';
    echo '<li><a href="'.$CFG->wwwroot.'/user/edit.php?id='.$USER->id.'&amp;course='.$COURSE->id.'">'.get_string('updatemyprofile').'</a></li>';
	echo '</ul>';
	}
echo '</div>';
?>

