<?
//PLIK odpowiedzialny za osadzenie skryptu w portalu Moodle
if(isloggedin()){	//skrypt dolaczany jest wylacznie w przypadku zalogowanych uzytkownik�w
      
	  require_once($CFG->libdir.'/ddllib.php');
	  require_once($CFG->libdir.'/dmllib.php');
	  require_js($CFG->wwwroot.'/lib/ajax_connection.js',2);//plik zawierajacy fukncje odpowiadajace za polaczenie synchroniczne AJAX
		
	  //Pobierany identyfikator wpisu do rejestru zdarzen, kt�ry zostanie zaktuyalizowany przez obliczony czas aktywnosci	
	  $register_id = get_field_sql('SELECT max(id) FROM '.$CFG->prefix.'log WHERE userid='.$USER->id.' and course='.$COURSE->id.'');
	 
	 //Przekazanie dw�ch parametr�w do JavaScript
	 //start_of_url jest to aders pliku, skryptu kt�ry odpowiada za aktualizacje wpisu do rejestru zdarzen i dodanie
	 //obliczonej wartosci czasu
	 //Drugi parametr isPopup informuje czy skrypt ma byc uruchomiony w wersji standardowej czy w wersji
	 //dla czas uruchomionego w wersji z ramkami i JavaScript
	  echo "	<script type='text/javascript'>
	            var start_of_url='$CFG->wwwroot/blocks/timestat/update_register.php?id=$register_id&time=';
				var isPopup=".isPopupWindow().";
	  			</script> ";
	//Dolaczenie pliku ze skryptem JavaScript, uruchamianego w oknie przegladarki i 
	//badajacego czynnosci dokonywane przez uzytkownika			
     require_js($CFG->wwwroot.'/lib/timestatscript.js');	
	}
	
	//Funkcja badajaca czy strona do kt�ej jest dolaczany skrypt jest strona czatu z ramkami i JavaScript
	//W tym celu sprawdzana jest nazwa skryptu kt�re jest aktualnie uruchamiany w oknie przegladarki,
	//jezeli w nazwie teog skryptu znajduje sie czlon 'mod/chat/gui_header_js/' oznacza to, ze jest to skrypt modulu czat
	//opartego o ramki i JavaScript
	function isPopupWindow(){
		global $CFG;
		if(strpos($CFG->pagepath,'mod/chat/gui_header_js/')>0)return 'true';
		return 'false';
	}	
	
?>