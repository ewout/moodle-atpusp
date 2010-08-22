<?php
   echo '<div id="mainmenu-date">';
   echo '<a href="'.$CFG->wwwroot.'/calendar/view.php">';
   echo userdate(date('U'));
   echo '</a></div>';
?>

<?php
if ($home) {
	echo '<div id="mainmenu-langopt">';
	if (empty($CFG->langmenu)) {
		$langmenu = '';
		} else {
            $currlang = current_language();
            $langs = get_list_of_languages();
            $langlabel = get_accesshide(get_string('language'));
            $langmenu = popup_form($CFG->wwwroot .'/index.php?lang=', $langs, 'chooselang', $currlang, '', '', '', true, 'self', $langlabel);
        }
	echo $langmenu;
	echo '</div>';
	}
  ?>
