<?PHP
//PLIK  zawierający funkcje odpowiadające za prezentacje raportu oraz pobieranie jego rekordów z bazy danych.
//This file contains functions responsible for presentation of reports

//FUNKCJA odpowiedzialna za zapisywanie raportu do pliku XLS.
//This function is responsible for downloading reports in xls format
function download_in_excel($courseid,$modid,$roleid,$userid,$lastinitial,$mintime=NULL,$maxtime=NULL){
 global $CFG;
 require_once("$CFG->libdir/excellib.class.php");
 require_login();
 //Pobranie rekordów raportu.
 //Get report records
 $results=create_time_array($courseid,$modid,$roleid,$userid,$lastinitial, NULL, NULL,$mintime,$maxtime );
 
  
  $strftimedatetime = get_string("strftimedatetime");
  $nroPages = ceil($results['count']/(EXCELROWS-FIRSTUSEDEXCELROW+1));
  //Ustalenie nazwy pliku XLS
  //Set name of file
  $filename = 'logs_'.userdate(time(),get_string('backupnameformat'),99,false);
  $filename .= '.xls';
   
  $workbook = new MoodleExcelWorkbook('-');
  $workbook->send($filename);
  
  $worksheet = array();
  //Definicja nagłowków tabeli w pliku  XLS.
  //Define headers of table in XLS file
  $headers = array(get_string('th_name','block_timestat'),
             get_string('th_login','block_timestat'),
             get_string('th_course','block_timestat'),
             get_string('th_module','block_timestat'),
             get_string('th_time_in_seconds','block_timestat'),
	         get_string('th_time','block_timestat'));
 			
			//Jeżeli raport ma  być wygenerowany dla danego przedziału czasowego, następuje ustalenie komunikatu
			//opisującego przedział czasowy (komunikat ten będzie zapisany do pliku, i będzie określał ten przedział czasowy) 
			//If report is to be generated for a defined time interval, establish a message describing time interval
			if($mintime!=NULL and $maxtime!=NULL){
				$a->strmintime = userdate($mintime);
            	$a->strmaxtime = userdate($maxtime);
		    	$podpis=get_string('timeperiod','block_timestat',$a);
			}else $podpis=''; 
			 
		//Zapisanie nagłowków tabel oraz informacji o raporcie w pierwszych rekordach pliku
		//Save table headers and information about repord in first records of file
 		for($wsnumber = 1; $wsnumber <= $nroPages; $wsnumber++) {
              $sheettitle = get_string('logs').' '.$wsnumber.'-'.$nroPages;
              $worksheet[$wsnumber] =& $workbook->add_worksheet($sheettitle);
              $worksheet[$wsnumber]->set_column(1, 1, 30);
              $worksheet[$wsnumber]->write_string(0,0, get_string('savedate','block_timestat').userdate(time(), $strftimedatetime));
			  $worksheet[$wsnumber]->write_string(1,0,get_string('title','block_timestat').$podpis);
              $col = 0;
              foreach ($headers as $item) {
                  $worksheet[$wsnumber]->write(FIRSTUSEDEXCELROW-1,$col,$item,'');
                  $col++;
             }
          }
		  
		//Jeżeli raport jest pusty, operacja zapisywania do pliku jest zakończona
		//If report is empty, operation of saving data to file is closed
		if (empty($results['stats'])) {
              $workbook->close();
              return true;
         }
	
	//Zapisywanie poszczególnych rekordów raportu to pliku XLS
	//Saving particular records to XLS file
	$row = FIRSTUSEDEXCELROW;
	$wsnumber = 1;
    $myxls =& $worksheet[$wsnumber];
	foreach ($results['stats'] as $res) {
		 	if ($nroPages>1) {
               if ($row > EXCELROWS) {
               	$wsnumber++;
               	$myxls =& $worksheet[$wsnumber];
               	$row = FIRSTUSEDEXCELROW;
               }
           }
		   $myxls->write($row, 0,$res->lastname.' '.$res->firstname, '');
           $myxls->write($row, 1,$res->username ,'');
		   $myxls->write($row, 2,$res->fullname, '');
		   	//Funkcja get_modname zamienia identyfiaktor modułu na jego opis tekstowy
		   	//Function get_modname converts module id to its text description
		   $myxls->write($row, 3,get_modname($courseid,$res->cmid), '');
		   $myxls->write($row, 4,$res->sumtime, '');
		    //Funkcja seconds_to_stringtime zamienia ilosc sekund na czas przedstaiowny w formacie dni::godziny::minuty::sekundy
		    //Function seconds_to_stringtime converts amount of seconds to time in format DD:HH:MM:SS
		   $myxls->write($row, 5,seconds_to_stringtime($res->sumtime), '');
    
           $row++;
	}
	
	$workbook->close();
    return true;
}

//FUNKCJA odpowiedzialna za wyświetlanie raportu na stronie 
//This function is responsible for printing report on page
function print_to_html($courseid,$modid,$roleid,$userid,$lastinitial,$page,$mintime,$maxtime){
	  //Ilość wierwszy wyswietlana na jednej stronie raportu
	  //Number of rows displayed on a single page of report
	  $perpage=20;
	  //Pobranie rekordów raportu
	  //Get report records
	  $results=create_time_array($courseid,$modid,$roleid,$userid,$lastinitial,$page*$perpage,$perpage,$mintime,$maxtime);
		
	  //Wyświetlanie informacji o wszystkich znalezionych rekordach	
	  //Dispaly information about all records found
	  echo "<div class=\"info\" style=\"text-align:center;margin-top:20px;\">\n";
      	echo get_string('find_records','block_timestat').$results['count'];
	  echo "</div>\n";
   

	   //url_withoutinitial = adres url do strony rarpotu nie zawierający parametru pierwszej litery nazwiska
	   //url_withoutinitial = url to page of report without first initial of surname
	   $url_withoutinitial='counttime.php?param_course_id='.$courseid.'&param_instanceid='.$modid.'param_userid='.$userid;
	   $url_withoutinitial.='&param_roleid='.$roleid.'&param_userid='.$userid;	
	  
	  //Generowanie paska zawierającego wszystkie litery
	  //Generate bar which contains all capital letters
	  $strall = get_string('all');
	  $alpha  = explode(',', get_string('alphabet'));	
   	    echo '<div class="initialbar lastinitial">'.get_string('lastname','block_timestat').' : ';
		//Jezeli żadna litera nie została wybrana to podświetlony jest napis "wszystkie"
		//a jeżeli jakaś litera została wybrana to napisz wszystkie jest odnosnikiem
		//If none of letters was chosen, highlight text "ALL", if letter was chosen display all as hyperlink
      	if(!empty($lastinitial)) {
        	echo '<a href="'.$url_withoutinitial.'&amp;lastinitial=">'.$strall.'</a>';
        } else {
             echo '<strong>'.$strall.'</strong>';
        }
		//Wyswietlane sa wszystkie litery alfabetu, jeżeli któraś została wybrana to jest podswietlona
		//pozostałe są odnośnikami
		//Highlighted are all letters, if any was chosen then it is higlighted and others are hyperlinks
        foreach ($alpha as $letter) {
        	if ($letter == $lastinitial) {
            	echo ' <strong>'.$letter.'</strong>';
            } else {
                echo ' <a href="'.$url_withoutinitial.'&amp;lastinitial='.$letter.'">'.$letter.'</a>';
            }
        }
       echo '</div>';	
	
	  //Przygotowanie adresu zawierającego wszystkie parametry, oraz wygenerowanie paska stronicowania raportu	
	  //Preparation of address which contains all parameters, and generating paging bar of report
	  $url='counttime.php?param_course_id='.$courseid.'&param_instanceid='.$modid.'param_userid='.$userid.'&lastinitial='.$lastinitial;
	  $url.='&param_roleid='.$roleid.'&param_userid='.$userid;	
	  print_paging_bar($results['count'], $page, $perpage, "$url&amp;"); //funkcja biblioteczna Moodle	
		
	  //Wyswietlenie nagłówków tabeli przedstawiającej raport		
	  //Displaying table headers of report
	  echo '<table class="logtable generalbox boxaligncenter" summary="">'."\n";
      echo "<tr>";
      echo "<th class=\"c0 header\" scope=\"col\">".get_string('th_name','block_timestat')."</th>\n";
      echo "<th class=\"c1 header\" scope=\"col\">".get_string('th_login','block_timestat')."</th>\n";
      echo "<th class=\"c2 header\" scope=\"col\">".get_string('th_course','block_timestat')."</th>\n";
      echo "<th class=\"c3 header\" scope=\"col\">".get_string('th_module','block_timestat')."</th>\n";
      echo "<th class=\"c4 header\" scope=\"col\">".get_string('th_time_in_seconds','block_timestat')."</th>\n";
	  echo "<th class=\"c5 header\" scope=\"col\">".get_string('th_time','block_timestat')."</th>\n";
      echo "</tr>\n";
		
	  
	  //W pętli for wyświetlane są poszczególne rekordy raportu,
	  //zmienna $row steruję jaki styl zdefiniowany CSS ma zostać użyty, gdyż kolory wyswietlanych rekordów ustalane są na przemian	
	  //For loop is responsible for printing report records. $row variable defines CSS style of row, because rows are colored alternately
	  $row=1; //do stylu class 
	  if(count($results['stats'])>0 and $results['stats']!=NULL)
		foreach($results['stats'] as $res){	 
			 $row = ($row + 1) % 2;
			 echo '<tr class="r'.$row.'">';
          			echo "<td class=\"cell c0\">\n";
			  			echo $res->lastname.' '.$res->firstname;
			  		echo "</td>\n";
				
			  		echo "<td class=\"cell c1\">\n";
			  			echo $res->username;
			  		echo "</td>\n";	
			  
			  		echo "<td class=\"cell c1\">\n";
			  			echo $res->fullname;
			  		echo "</td>\n";
			  
			  		echo "<td class=\"cell c1\">\n";
			  			echo get_modname($courseid,$res->cmid);
			  		echo "</td>\n";
			  
			  		echo "<td class=\"cell c1\">\n";
			  			echo $res->sumtime;
			  		echo "</td>\n";
			
			  		echo "<td class=\"cell c1\">\n";
			  			echo seconds_to_stringtime($res->sumtime);
			  		echo "</td>\n";
			echo '</tr>';
		}
		echo "</table>\n";
	   
	   // Ponownie wyświetlany pasek stronicowania 
	   // Display paging bar one more time
	   print_paging_bar($results['count'], $page, $perpage, "$url&amp;");		
	   
}

//FUNKCJA pobierająca dane z bazy danych.
//This function gets data from database
function create_time_array($courseid,$modid,$roleid,$userid,$lastinitial,$limitfrom,$limitnum,$mintime=NULL,$maxtime=NULL){
	global $CFG;
	//Ustalany jest początek zapytania SQL,
	// l.time - czas dodania logu, sumtime obliczony czas aktywności, l.cmid - identyfikator modułu, c.fullname - nazwa kursu
	//Łączone są tabele: log,user,course oraz
	//dodatkowo tabela role_assignments zawierająca prawa użytkowników
	// Sql query details:
	// l.time - time of adding log, sumtime - counted activity time, l.cmid - module ID, c.fullname - course fullname
	// Tabels liked: log,user,course and table role_assignments
	$sql="SELECT DISTINCT l.id, l.time , sum(l.count) as sumtime,l.cmid,u.username,u.firstname,u.lastname,c.fullname
		FROM 
			{$CFG->prefix}log l
		 JOIN
            {$CFG->prefix}user u 
		ON l.userid = u.id 
	
		 JOIN
			{$CFG->prefix}course c
		ON l.course=c.id";	
		
		if($roleid!=0 and $userid==0){
			$sql=$sql. " JOIN 
					   {$CFG->prefix}role_assignments ra
		 				ON l.userid=ra.userid ";
		}	 
        $sql=$sql." WHERE u.username!='guest' and  ";
		
	//Zapytanie SQL zostało ustalone ale wyłącznie do klauzuli WHERE
	//dalsza część zapytania jest zależna od ustawień parametrów:
	//courseid - identyfikator kursu, userid= identyfikator uzytkownika, roleid - identyfikator roli, modid - identyfikator modułu	
	//SQL query was formatted but only to WHERE clause
		
	//Jeżeli został wybrany tylko kurs
	//If only one course was chosen to report
	if($courseid!=0 and $userid==0 and $roleid==0){
		$where=" l.course=$courseid ";
		$order=" order by u.lastname";
			
			//dla ściśle wybranego modułu identyfikator modułu musi być zgodny z wybranym
			//for single module chose, check module ID
		if($modid>0)$where.=" and l.cmid=$modid  ";
			
			//modid==-1 - oznacza wyszczególnienie raportu na wszystkei moduły
			//modid==-1 - specifies report for all modules
		if($modid==-1){
			$group=" group by l.userid,l.cmid";
			//Dodanie ponizszej lini do zapytania SQL, jest to zabezpieczenie przed wyświetlaniem modułów ktore kiedyś znajdowały się, 
			// w kursie, dane dotyczące ich są w logach lecz same moduly zostały juz usunięte i aktualnie nie istnieją.
			//	NP. w logach sa moduły o identyfikatorach : 1,2,3,4,5,6
			//ale faktycznie dla kursu w chwili obecnej istnieją tylko moduły 1,2,3
			//wiec zostanie dodany warunek do zapytania: l.cmid in (1,2,3)
			// Funkcja get_AllModulesToSQL zwraca string w postaci (1,2,3), czyli tablice zawierającą
			// wszystkie moduły które aktualnie znajdują się w kursie.
			//Adding the below line to SQL clause is a procetion against viewing which were in a course once, but there are not in course
			//now (but log table contains data about them)
			$where.=" and l.cmid in ".get_AllModulesToSQL($courseid);
		}else{
		   // w kazdym innym przypadku poza modid==-1, grupowanie występuję według użytkowników
		   // in any case beyond modid==-1 grouping occurs by users
			$where.=" and l.cmid in ".get_AllModulesToSQL($courseid);
			$group=" group by l.userid";
		}
	}
			
	//Jeżeli został wybrany kurs i rola (grupa użytkoników)
	//If course and role were specified
	if($courseid!=0 and $roleid!=0 and $userid==0){
			//Sprawdzenie ról wymaga pobranie CONTEXT dla kkursu
			//Ten sam mechanizm jest uzyty w Moodle (Course->Report->Participation)
			//gdzie użyty został w tym samym celu, czyli wybraniu uzytkowników należących do okreśłonej roli
			//Checkign roles requires geting course CONTEXT
			$context = get_context_instance(CONTEXT_COURSE, $courseid);
		  	$relatedcontexts = get_related_contexts_string($context);
			$order=" order by u.lastname";
					//Jeżeli wyszczególnienie raportu na wszystkie moduły lub łączna suma czasu w kursie
					//If sum time is counted, or if repord is specified on all modules
		if($modid==-1 or $modid==0){	
			$where="l.course=$courseid AND ra.contextid $relatedcontexts AND ra.roleid=$roleid and l.cmid in ".get_AllModulesToSQL($courseid);
			if($modid==-1){	
				$group=" group by l.userid, l.course, l.cmid, ra.contextid ";
			}else{
				$group=" group by l.userid, l.course,ra.contextid ";
			}
		}else{
				   //Jeżeli raport ma dotyczyć ściśle okreslonego modułu
				   //if report is specified on signle module
			if($modid>0){
			$where="l.course=$courseid AND ra.contextid $relatedcontexts AND ra.roleid=$roleid AND l.cmid=$modid";
			$group=" group by l.userid, l.course,ra.contextid";
			}
		}
	}
	
	//Jeżeli został wybrany KURS i konkretny UZYTKOWNIK
	//if single course and single user were specified
	if($courseid!=0 and $userid!=0){
		$order=" order by u.lastname ";
		//Raport dla konkretnego modułu
		//report for specified module
		if($modid>0){
		$where="l.course=$courseid AND u.id=$userid AND l.cmid=$modid";
		$group=" group by u.id, l.cmid ";
		}
		//Raport podsumuwujący czas w kursie
		//report summarizing time course
		if($modid==0){
			$where="l.course=$courseid AND u.id=$userid and l.cmid in ".get_AllModulesToSQL($courseid);
			$group=" group by u.id";
		}
		//Raport z wyszczególniniem na wszystkie moduły
		//a report detailing all modules
		if($modid==-1){
		$where="l.course=$courseid AND l.userid=$userid and l.cmid in ".get_AllModulesToSQL($courseid);
		$group=" group by l.cmid";
		}
	}
	
	//Wybranie użytkownika gdzie pierwsza litera nzwiska została wybrana prez ustawienie parametru $lastinitial.
	//Linia ta jest zawsze dodawana do zapytania,
	//Jesli litera została wybrana (np. Z) zapytanie przyjmie postać LIKE 'Z%',
	//natomiast jak nie została wybrana to zapytanie bedzie wyglądało : LIKE '%' 
	//Choosing user when firt initial of surname was set. This line is always added to query.
	$like=" and u.lastname LIKE '$lastinitial%' ";
	
	//Jeżeli został określony przedział czasowy to $mintime i $maxtime to parametry podane jako uniksowy znacznik czasu
	//I analizowane są logi mieszczące się w przedziale.
	//If time interval was set then $mintime and $maxtime are parameters given as unix timestamp, and analized logs are
	//from this interval
	$timelimit=" l.time>=$mintime AND l.time<=$maxtime AND ";
	if($mintime!=NULL and $maxtime!=NULL)$sql.=$timelimit;
	
	//Pobieranie logów do tablicy
	//Get logs to table
	$result['stats'] = get_records_sql($sql.$where.$like.$group.$order,$limitfrom,$limitnum);
		
		//Jeżeli raport ma dotyczyć sumy czasów w całym kursie to cmid wszystkich rekordów
		//przestawiamy na string 'all_modules', jest to operacja dodatkowoa
		//która spowoduje że przy wyswietlaniu nazw danego modułu przez funkcje get_modname
		//zwrocony zostanie ciąg "łącznie w całym kursie".	
		//If report should contain summary of total course time then cmid of all records is set to string 'all_modules'.
		//This operation will result that when displaying module using get_modname function "course summary time" will be returned
		if($modid==0 and !empty($result['stats']))
		foreach($result['stats'] as $record){
			$record->cmid='all_modules';
		}
	//echo $sql.$where.$like.$group.$order . 'limitfrom='.$limitfrom.' limitnum'.$limitnum;
	//echo $sql.$where.$like.$group;
		
		//Ponowne wykokanie zapytania, ale tym razem bez parametrów limitfrom,limitnum
		//po to aby obliczyć ile tak na prawde dany raport ma rekordów.
		//count number of records in report
		$rec=get_records_sql($sql.$where.$like.$group);
		if(is_array($rec))	$result['count'] = count($rec);
		else $result['count']=0;
					
	return $result;
}

//Funcka zwraca opis modułu w postaci tekstowej.
//Opis ten znajduję się w raporcie w kolumnie MODUŁ.
//w przypadky gdy jest to konkertny moduł pobierana jest jego nazwa,
//natomiast w przypadku storny głównej kursu - "Strona głowna kursu",
//łącznie w całym kursie - "Łącznie w całym kursie"
//This function returns module name
function get_modname($courseid,$modid){
		 if($modid=='all_modules')return get_string('summary','block_timestat');
		 if($modid==0)return get_string('course_main_page','block_timestat');
		 if($modid==-1)return get_string('all_modules','block_timestat');
		 
		 
		 //Mechanizm pobierania opisu tekstowego konkretnego modułu został zaczerpnięty
		 //z COURSE->REPORT->PARTICIPATION
	 	 $course = get_record('course','id', $courseid);
		 $modinfo = get_fast_modinfo($course);  
		 $modules = get_records_select('modules', "visible = 1 AND name <> 'label'", 'name ASC');
    	 $instanceoptions = array();
         foreach ($modules as $module) {
            if (empty($modinfo->instances[$module->name])) {
                continue;
            }
            $agroup = get_string('modulenameplural', $module->name);
            $instanceoptions[$agroup] = array();
            foreach ($modinfo->instances[$module->name] as $cm) {
                if($cm->id==$modid) return format_string($cm->name);
            }
        }
}

//Funkcja pomocnicza,uzywana w przypadky raportów z wyszczególnieniem na wszystkie moduły.
//Zwraca tablice np. (1,2,3) , która zawiera identyfiaktory modułów które aktualnie wchodzą w skład kursu.
//Zabezpiecza to przed uwzględnianiem w rapocie modułów które już zostały usunięte z kursu,
// ale w logach wpisy o nich nadal istnieją.
//This funcion returns table which contains ID of all modules which are currently in course. It prevents for counting modules which were
//in course but were deleted (while there are still log recors describing deleted modules)
function get_AllModulesToSQL($courseid){
		 $array="(0";
		 $course = get_record('course','id', $courseid);
		 //Pobierana jest informacja o modułach aktualnie isntiejacych w kursie
		 //get data about present course modules
		 $modinfo = get_fast_modinfo($course);  
		 $modules = get_records_select('modules', "visible = 1 AND name <> 'label'", 'name ASC');
    	
		 //W pętli sprawdzany jest każdy moduł, jeżeli istnieje w kursie to jego indetyfikator zapisywany jest do stringu 
		 //check all module if exist in course
         foreach ($modules as $module) {
            if (empty($modinfo->instances[$module->name])) {
                continue;
            }
            foreach ($modinfo->instances[$module->name] as $cm) {
                	$array.=','.$cm->id; 
            }
        }
		return $array.')';	
}

//Funkcja zamieniająca sekundy do postaci: dni::godziny::minuty::sekundy
//convert unix timestamp to format DD:HH:MM::SS
function seconds_to_stringtime($seconds){
	$CON_MIN=60;
	$CON_HOUR=$CON_MIN*60;
	$CON_DAY=$CON_HOUR*24;
	
	$temp_day=(int)((int)$seconds/(int)$CON_DAY);
		$seconds=$seconds-$temp_day*$CON_DAY;
	$temp_hour=(int)((int)$seconds/(int)$CON_HOUR);
		$seconds=$seconds-$temp_hour*$CON_HOUR;
	$temp_min=(int)((int)$seconds/(int)$CON_MIN);
		$seconds=$seconds-$temp_min*$CON_MIN;	
	
	$str='';
	if($temp_day!=0)$str=$str.$temp_day.get_string('days','block_timestat');		
	if($temp_hour!=0)$str=$str.$temp_hour.get_string('hours','block_timestat');
	if($temp_min!=0)$str=$str.$temp_min.get_string('minuts','block_timestat');
	$str=$str.$seconds.get_string('seconds','block_timestat');
	return $str;		
}


//Funkcja zwraca w postaci tekstowej - rolę zalogowanego użytkownika.
//This functions returns role of logged user
function getRoleString($courseid,$userid){
//shortname roleid
//admin 1
//coursecreator 2
//editingteacher 3
//teacher 4
//student 5
//guest 6
//user 7 
//teacher moze ogladać wszystko, natomiast 

$teachers=array(2,3,4);
$admin=array(1);
	$context = get_context_instance(CONTEXT_COURSE,$courseid);
	$roles= get_user_roles($context, $userid, true, $order='c.contextlevel DESC, r.sortorder ASC', $view=false);	
	$shortname='student';
	foreach($roles as $role){
		if(in_array($role->roleid,$teachers)){$shortname='teacher';}
	}
	foreach($roles as $role){
		if(in_array($role->roleid,$admin)){$shortname='admin';}	
	}	
	return $shortname;	
}			
?>