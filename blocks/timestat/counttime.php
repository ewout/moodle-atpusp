<?PHP  
	  //PLIK odpowiadjacy za wyswietlanie strony z interfejsem pozwalajacej na generowanie raportu, 
	  //oraz odpowiedzialnej za jego prezentacje
	  //This file is reposnsible for printing page with interface for generating and displaying report
	  require_once('../../config.php');
	  global $CFG,$USER;
	  require_login();
	  require_once($CFG->libdir.'/ddllib.php');
	  require_once($CFG->libdir.'/xmldb/classes/XMLDBTable.class.php');
	  require_once($CFG->libdir.'/xmldb/classes/XMLDBField.class.php');
	  require_once($CFG->libdir.'/dmllib.php');
	  require_once($CFG->libdir.'/adminlib.php');
	  require_once($CFG->libdir.'/pagelib.php');
	  require_once($CFG->libdir.'/grouplib.php');	 
	  require_once($CFG->libdir.'/accesslib.php');	
	  require_once('printlib.php');
	  require_once('ajaxlib.php');	
	  require_once($CFG->libdir.'/pear/HTML/AJAX/JSON.php');
	  require_js('yui_yahoo');
  	  require_js('yui_dom');
      	  require_js('yui_utilities');
      	  require_js('yui_connection');
	  //Skrypt zaimplementowany w ramach pracy, znajduje sie on w katalogu bloku timestat,
	  //odpowiada za autouzupelniania list zawartych w formularzu
		
	  require_js('ajaxlib.js',2);			  		   
				   
	 //require_login(); //Wymagane jest od uzytkownika zalogowanie
	 //Inicjalizacja parametrów	okreslajacych szczególy raportu   
	 $param_course_id= required_param('param_course_id',PARAM_INT); //identyfikator kursu
	 $param_instanceid= optional_param('param_instanceid', 0, PARAM_INT); //identyfikator modulu
	 $param_roleid= optional_param('param_roleid', 0, PARAM_INT); //identyfikator roli uzytkowników
	 $param_userid= optional_param('param_userid', 0, PARAM_INT); //identyfikator uzytkownika
	 $page=optional_param('page', 0, PARAM_INT); //strona raportu (wykorzystywana w przypadku wyswietlania go na www)
	 $lastinitial=optional_param('lastinitial','', PARAM_ALPHA); //pierwsza litera nazwiska
	 $format=optional_param('format','',PARAM_ALPHA); //format okresla forme prezentacji raportu (strona www lub XLS)
	 $timeperiod=optional_param('timeperiod', false, PARAM_BOOL); //czt wyznaczont jest przedzial czasowy
	 $startday = optional_param('startday', 0, PARAM_INT);
    	 $startmonth = optional_param('startmonth', 0, PARAM_INT);
     	 $startyear = optional_param('startyear', 0, PARAM_INT);
    	 $starthour = optional_param('starthour', 0, PARAM_INT);
    	 $startminute = optional_param('startminute', 0, PARAM_INT);
    	 $endday = optional_param('endday', 0, PARAM_INT);
    	 $endmonth = optional_param('endmonth', 0, PARAM_INT);
    	 $endyear = optional_param('endyear', 0, PARAM_INT);
    	 $endhour = optional_param('endhour', 0, PARAM_INT);
    	 $endminute = optional_param('endminute', 0, PARAM_INT);
    	 $mintime = optional_param('mintime', 0, PARAM_INT);
    	 $maxtime = optional_param('maxtime', 0, PARAM_INT);
		
		 //Jezeli checkbox PRZEDZIAL CZASOWY zostal wybrany, oraz przedzial czasowy zostal wyznaczony przy pomocy formularza
		 //To wartosci brzegowe tego przedzialu wyrazone sa przez rok,miesiac,dzien,godzine,minute 
		 // kolejno zostaja one zamienione na znaczniki czasowe, gdyz w takiej postaci sa one zapisane w bazie danych.
		 // Po transformacji przedzial czasowy jest okreslany przez dwa parametry mintime oraz maxtime, 
		 //ktore oznaczaja poczatek i koneic przedzialu.
		 //Dodatkowo jezeli przedzial czasowy jest wyznaczony, w zmiennej podpis zostaje zawarty podpis
		 //który wyswietlany jest wraz z z raportem i informuje jakiego okresu dotyczy raport.
		 //If time interval was set, then border values of it are converted to unix timestamp: mintime and maxtime.
		 if ($timeperiod) {
         	$mintime = make_timestamp($startyear, $startmonth, $startday, $starthour, $startminute, 0);
         	$maxtime = make_timestamp($endyear, $endmonth, $endday, $endhour, $endminute, 0);
			$a->strmintime = userdate($mintime);
            $a->strmaxtime = userdate($maxtime);
		    $podpis=get_string('timeperiod','block_timestat',$a);
    	 }else{
		 	$mintime=NULL;
			$maxtime=NULL;
			$podpis='';
		 }
		 	
if($param_course_id!=0){
	//Instrukcja switch steruje formatem prezentacji raportu,
	//Jezeli parametr $format jest domyslny, czyli rózny od 'downloadinexcel' to raport wyswietlany jest na stronie www.
	//variable $format defines if report should be displayed on page, or downloaded in excel format
switch($format){
default:
		
		//SPRAWDZANIE PRAWA DOSTEPU DO BLOKU
		//Pobierana jest instancja bloku, która znajduje sie na stronie kursu,
		//którego ma dotyczyc raport.
		//Kolejno sprawdzane jest prawo dostepu, czy uzytkownik chcacy wygenerowac raport,
		//posiada prawo dostepu do bloku zamieszczonego na stronie tego kursu
		//Check role permissions
		$block = get_record('block', 'name', 'timestat');	
		$blockinstance = get_record('block_instance','blockid', $block->id,'pageid',$param_course_id);
		$context_block = get_context_instance(CONTEXT_BLOCK, $blockinstance->id);
		require_capability('block/timestat:view', $context_block);
		
		$course = get_record('course','id',  $param_course_id);   
		
		//BUDOWANA jest tablica z odnosnikami do elementów kursu ktorych dotyczy raport.
		//Odnosniki te wyswietlone beda na pasku tytulowym przy wyswietlaniu raportu na stronie www.
		//Building navilinks table. It will be displayed on title bar
		$navlinks = array(array('name'=>$course->shortname, 'link'=>$CFG->wwwroot.'/course/view.php?id='.$param_course_id, 'type'=>'misc'));
		//Jezeli rarpot ma dotyczyc konkretnego modulu, to do linków zostaje dodany odpwiadjacy mu odnosnik
		//If report is about single module, add corresponding link
		if($param_course_id!=0 and $param_instanceid!=0){
			$nazwa=get_modname($param_course_id,$param_instanceid);
			$navlinks[]=array('name'=>$nazwa, 'link'=>'', 'type'=>'misc');
		}   		
		//Z tablicy likow zostaje zbudowany panel nawigacyjny. 
		//Build navigation bar from table
		$navigation = build_navigation($navlinks);
		
		//WYSWIETLENIE paska tytulowego wraz z odnosnikami nawigacyjnymi.
		//Display title bar with links
		$site = get_site();
		print_header(get_string('title','block_timestat'),$course->fullname.$podpis,$navigation,'',
   		'<meta name="description" content="'. s(strip_tags($site->summary)) .'">', true,'');
		
		//FORMULARZ umozliwiajacy generowanie raportu.
		//Form to generate a report
		echo "<form class=\"logselectform\" style=\"margin-top:20px;\" action=\"$CFG->wwwroot/blocks/timestat/counttime.php\" method=\"get\" name='form_counttime'>\n";
		echo '<div style="text-align:center;">';
		
		//WYSWIETLANIE LISTY z dostepnymi kursami 
		//display list with accessible courses
		$courses = array();
		$courses=get_courses('all');
			//wszystkie kursy zostaja przefiltrowane, tak aby na liscie z kursami znalazly sie tylko te
			//do których uzytkownik posiada uprawnienia block/timestat:view
			//apply filter to provide only accessible courses
			//display only accessible courses
			
		$courses=filtrCourses($courses);
			//wszystkie kursy nalezy zamiescic w tablicy, której indeks to identyfikator kursu, a wartosc elementu tablicy
			//dla tego identyfikatora to nazwa kursu. Tak przygotowana tablica z kursami moze zostac automatycznie
			//podana jako zrodlo elementów wyboru do listy rozwijanej.
			//all courses are inserted into table, where index is course ID and value of reocrd is course name. This format table can be
			//automatically given as source element to drop-down list
		$tab_courses=array();
		foreach($courses as $cour)$tab_courses[$cour->id]=$cour->fullname;
			//Wyswietlenie etykiety 'Wybierz kurs'
			//print label 'Choose course'
		echo '<label for="menuinstanceid">'.get_string('label_courses','block_timestat').'</label>'."\n";
			//Wyswietlenie listy rozwijanej, przy pomocy bibliotecznej funkcji Moodle
			//Funkcja podana jako parametr piaty-  onChange_Course($USER->id) - definiuje funkcje JavaScript,jakie maja zostac
			//wykonane przy zmianie wartosci na liscie (jej definicja znajduje sie w pliku ajaxlib.php z katalogu timestat)
			//Print drop-down list
choose_from_menu($tab_courses,"param_course_id",$param_course_id,'',onChange_Course($USER->id),'0',false,false,0,'param_course_id');
		
		//WYSWIETLANIE LISTY z modulami zawartymi w kursie
		//print list which contains course modules
		 $instanceoptions = array();
		 if($param_course_id!=0){ 
		 		//Oprócz wszystkich modulow dostepnych w kursie na liscie rozwijanej znajduje sie tez pozycja
				//"lacznie w calym kursie" - i wartosc parametru wówczas wynosi -1
				//value -1 describes total time of whole course
		 	 $instanceoptions[' '][-1]=get_string('all_modules','block_timestat');
				//Wyswietlenie wszystkich modulów zawartych w kursie.
				//Mechanizm pobierania modulów zawartuch w kursie zaczerpniety z Moodle,
		 		//z COURSE->REPORT->PARTICIPATION.
		 		//get all course modules
		 	$course = get_record('course','id', $param_course_id);
		 	$modinfo = get_fast_modinfo($course);  
  		 	$modules = get_records_select('modules', "visible = 1 AND name <> 'label'", 'name ASC');
         	foreach ($modules as $module) {
            	if (empty($modinfo->instances[$module->name])) {
                	continue;
            	}
            	$agroup = get_string('modulenameplural', $module->name);
            	$instanceoptions[$agroup] = array();
            	foreach ($modinfo->instances[$module->name] as $cm) {
						//Moduly wyswietlane sa na liscie zawierajacej podgrupy stad koneiczne jest 
						//specyficzne przygotowanie tablicy zawierajacej wszystkie moduly:
						//$instanceoptions['nazwagrupy']['identyfikator modulu']=nazwa modulu w postaci tekstowej.
						//create table with groups of modules
                	$instanceoptions[$agroup][$cm->id] = format_string($cm->name);
            	}
        	}
		}
			//Wyswietlenie etykiety i listy rozwijanej zawierajacej moduly zawarte w kursie.
			//display label and drop-down list with modules
		echo '<label for="menuinstanceid">'.get_string('label_modules','block_timestat').'</label>'."\n";
	 	choose_from_menu_nested($instanceoptions,'param_instanceid',$param_instanceid,get_string('summary','block_timestat'));
		//id=menu+param_instanceid
		
		//WYSWIETLENIE LISTY rozwijanej zawierajacej role uzytkowników zdefiniowane dla danego kursu
		//display drop-down list with user roles
		$roleoptions = array(); //tablica zawierajaca wszystkie role
		$roleoptions_students=array(); //tablica zawierajaca tylko jedna role ( studnet )
		if($param_course_id!=0){   	 
		   $context = get_context_instance(CONTEXT_COURSE, $course->id);
 		   $roles = get_roles_used_in_context($context);
 	       foreach ($roles as $r) {
 	              $roleoptions[$r->id] = $r->name;
		   		  if($r->id==5)$roleoptions_students[$r->id] = $r->name;
		   }
		}		
			//W instrukcji switch sprawdzane jest kto korzysta, z aplikacji
			//jezeli jest to admin - to ma dostep do danych wszystkich uzytkowników
			//natomiast kazdy inny uzytkownik posiada dostep wylacznie do danych studentow.
			//check role permissions, admin has access to everything
		switch(getRoleString($param_course_id,$USER->id)){
		case 'admin':  
			//Dla dministratora do listy rol dla dodane sa wszystkie pozycje, czyli
			//zawarte w tablicy roleoptions.
			//Jako parametr piaty podana jest funkcja OnChangeRole - zwraca ona kod JavaScript jaki ma zostac wykokany
			//po zmianie wartosci na liscie z rolami (funkcja OnChangeRole w pliku ajaxlib.php z katalogu timestat).
			echo '<label for="param_roleid">'.get_string('label_rols','block_timestat').'</label>'."\n"; 	
			choose_from_menu($roleoptions,'param_roleid',$param_roleid,get_string('choose','block_timestat'),
			OnChange_Role(),'0',false,false,0,'param_roleid');
		break;

		default:
			//Dla pozostalych uzytkownikow do listy ról dodanajest tablica roleoptions_students,
			//która zawiera tylko jedna pozycje -role studentów.
			//for ordinary users list contains only one role - student
			$param_roleid=5;
			echo '<label for="param_roleid">'.get_string('label_rols','block_timestat').'</label>'."\n"; 	
			choose_from_menu($roleoptions_students,'param_roleid',$param_roleid,get_string('choose','block_timestat'),
			OnChange_Role(),'0',false,false,0,'param_roleid');
		break;
		}	 
			 
		//WYSWIETLENIE LISTY z uzytkownikami nalezacymi do wczesniej wybranej roli.
		//Jezeli rola ta nie zostala wybrana to lista z uzytkownikami jest pusta.
		//Print list with users who belong to choosen role.
		//If role was not chosen, then list with users is empty
		  $tab_users=array();	
		  if($param_course_id!=0 and $param_roleid!=0){
		  		//Mechanizm pobierania uzytkownikow z okreslonej roli i zapisanych do danego kursu pobrano
				// z Moodle - COURSE-REPORT--PARTICIPATION 
		  	$context = get_context_instance(CONTEXT_COURSE, $param_course_id);
		  	$relatedcontexts = get_related_contexts_string($context);
		  	$sql = "SELECT ra.userid, u.firstname, u.lastname, u.idnumber 
                    FROM {$CFG->prefix}role_assignments ra
                          JOIN {$CFG->prefix}user u ON u.id = ra.userid
                    WHERE ra.contextid $relatedcontexts AND ra.roleid = $param_roleid ORDER BY u.lastname";
			$users = get_records_sql($sql);
				//Pobrani uzytkownicy, aby mogli zostac wyswietleni w liscie, musza zostac
				//zorganizowani w tablice tab_user[id_uzytkownika]='Nazwisko i imie';
				//make table with tab_user[id_uzytkownika]='Nazwisko i imie';
		   	foreach($users as $user)$tab_users[$user->userid]=$user->lastname.' '.$user->firstname;
		   }
		   		//Wyswietlenie etykiety i listy z uzytkownikami.
		   		//Display label and list of users
		  echo '<label for="menuinstanceid">'.get_string('label_users','block_timestat').'</label>'."\n"; 	
		  choose_from_menu($tab_users,'param_userid',$param_userid,get_string('choose','block_timestat'),'','0',false,false,0,'param_userid');

		//PRZEDZIALY CZASOWE - panel umozliwijacy ustawienie przedzialu.
		//Przedzialy czasowe znajduja sie w osobnym elementcie DIV zawartym na stronie www.
		//Interval settings panel
		echo'<div style="text-align:center;margin-top:5px;">';
				
				//Po zaznaczeniu checkboxa "przedzial czasowy" na stronie pojawia sie nowy element formularza
				//który umozliwia okreslenie poczatku i konca przedzialu.
				//Skrypt w zmiennej $script steruje widocznoscia owych elementów.
				//show interval menu after checking interval checkbox
				$script="
						var period_fields=document.getElementById('period_fields');
						if(this.checked)period_fields.style.display='block';
						else period_fields.style.display='none';	
				";
				//Wyswietlenie checkboxa, ktorego klikniecie powoduje pojawienie sie formularza,
				//parametr szósty funkcji print_checkbox to wartosc $script, czyli skrypt który zostaje
				//uruchamiany podczas klikniecia w checkbox, steruje on widocznoscia panelu umozliwijacego
				//wybor wartosci brzegowych przedzialu czasowego.
				echo'<table align="center">';
				echo '<tr><td></td><td align="left">';
					print_checkbox('timeperiod','yes', $timeperiod, $label = get_string('choosetimeperiod', 'block_timestat'),'', $script,false);
				echo '</td></tr>';	
				echo '</table>';
					
					//W zaleznosci czy checkbox "pdzedzial czasowy" jest zaznaczony czy tez nie
					//odpowiednio w CSS musi zostac zdefiniowana widocznosc panelu.
					//define css if interval checkbox is checked
				if($timeperiod)$style="display:block;";
				else $style="display:none;";
				
					//Dedinicja panelu umozliwiajacego okreslenie wartosci brzegowych przedzialu.
					//definition of panel which allows to chose interval
				echo '<div  id="period_fields" style="'.$style.'"><table align="center">';
				echo '<tr><td> Od:</td><td>';
							$data_tmp=NULL;
							if($timeperiod)$data_tmp=$mintime;
							else $data_tmp=$course->startdate;
					print_date_selector('startday', 'startmonth', 'startyear', $data_tmp);
					print_time_selector('starthour', 'startminute', $data_tmp);
				echo '</td></tr>';
				echo '<tr><td> Do: </td><td>';
							$data_tmp=0;
							if($timeperiod)$data_tmp=$maxtime;
					print_date_selector('endday', 'endmonth', 'endyear',$data_tmp);
        			print_time_selector('endhour', 'endminute',$data_tmp);
				echo '</td></tr>';	
			echo'</table></div>';
		echo '</div>';	
		echo '</div>';
		
		//ZATWIERDZANIE FORMULARZA
		//Lista rozwijana zawierajaca dwa elementy do wyboru oraz przycisk zatwierdzajacy formularz.
		//form submitting
		echo'<div style="text-align:center;">';
			   $logformats = array('showonpage' => get_string('showonpage','block_timestat'),
                           'downloadinexcel' => get_string('downloadinexcel','block_timestat'));
 		       choose_from_menu ($logformats, 'format', $format, false);
			  
		echo '<input type="submit" value="'.get_string('getstats','block_timestat').'" style="margin:0 auto;margin:3px;"/>';
		echo '</div>';	
		echo '</form>';
				
			//Wyswietlenie raportu na stronie, tuz pod formularzem umozliwiajacym jego wybór.
			//Funkcja print_to_html zdefiniowana w pliku printlib.php (katalog timestat).		
			//Print report on page
		print_to_html($param_course_id,$param_instanceid, $param_roleid,$param_userid,$lastinitial,$page,$mintime,$maxtime);
			
			//Kazde wyswietlenie aplikacji do generowania raportu dodaje do logów wpis.
		add_to_log(0,0,'timestat');
		print_footer();//Wyswietlenie stopkkie - funkcja z bibliotecznej Funkcji Moodle weblib.php.	
			
break;
		//Zapisywanie raportu do pliku XLS
		//Funkcja download_in_excel zdefiniowana w pliku printlib.php (katalog timestat).	
		//Save form to xls file, funciton download_in_excel is defined in file printlib.php
case 'downloadinexcel':
download_in_excel($param_course_id,$param_instanceid, $param_roleid,$param_userid,$lastinitial,$mintime,$maxtime);
exit;
break;
}
}	
			
//Funkcja pobiera tablice wszystkich kursów,
//natomiast zwraca tablice tylko tych kursów w obrebie ktorych znajduje sie blok timestat i operator aplikacji
//posiada do niego uprawnienia block/timestat:view.
//This function gets tables of all courses, and return table which contains data of courses where is installed block timestat, and where
//user has permission block/timestat:view
function filtrCourses($courses){
		$block = get_record('block', 'name', 'timestat');
		foreach($courses as $key=>$cour){			
				$blockinstance = get_record('block_instance','blockid', $block->id,'pageid',$cour->id);
				if(!$blockinstance){unset($courses[$key]);continue;}
				$context_block = get_context_instance(CONTEXT_BLOCK, $blockinstance->id);
				if(!has_capability('block/timestat:view', $context_block))unset($courses[$key]);
		}	
		return $courses;		
}		
		
?>

