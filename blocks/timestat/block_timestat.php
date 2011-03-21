<?PHP
//DEFINICJA KLASY realizowanego bloku TIMESTAT
class block_timestat extends block_list {
	
	//KONSTRUKTOR(ustawienie tytulu bloku,usrawienie wersji)
    function init() {
        $this->title = get_string('blocktitle','block_timestat');
        $this->version = 20140924635;
    }
	
	//AUTOMATYCZNA INSTALACJA bazy danych(funkcja rozszerza baze danych o pole COUNT)
	function after_install(){
	  global $CFG;
	  require_once($CFG->libdir.'/ddllib.php');
	  require_once($CFG->libdir.'/xmldb/classes/XMLDBTable.class.php');
	  require_once($CFG->libdir.'/xmldb/classes/XMLDBField.class.php');
	  require_once($CFG->libdir.'/dmllib.php');
	   
	  //DODAWANIE do tabeli LOG pola o nazwie COUNT (not null, unsigned integer, wartosc domyslna 0, dodanie go po polu info)
	  $table = new XMLDBTable('log');
      $field = new XMLDBField('count');
      $field->setAttributes(XMLDB_TYPE_INTEGER, '0', null, XMLDB_NOTNULL, null, null, null, 0, 'info');
	  add_field($table, $field);
	}
	
	//AUTOMATYCZNE PRZYWRACANIE bazy danych do stanu pierwitnego (usuniecie pola COUNT z tabeli LOG)
	//Metoda ta uruchamiana jest podczas usuwania bloku timestat
	function before_delete(){
	  global $CFG;
	  require_once($CFG->libdir.'/ddllib.php');
	  require_once($CFG->libdir.'/xmldb/classes/XMLDBTable.class.php');
	  require_once($CFG->libdir.'/xmldb/classes/XMLDBField.class.php');
	  require_once($CFG->libdir.'/dmllib.php');
	  $table = new XMLDBTable('log');
      $field = new XMLDBField('count');
	  drop_field($table,$field,true,true);
	  	
	}
	
	//DEFINICJA TRESCI bloku
	function get_content() {
    	global $CFG,$COURSE, $USER;	
	
		
    //Pobierany jest CONTEXT instancji aktualnie wyswietlanego bloku
	$context = get_context_instance(CONTEXT_BLOCK, $this->instance->id);
	//$context = get_context_instance(CONTEXT_COURSE, $COURSE->id);
	
	//Jezeli uzytkownik nie posiada uprawnienia block/timestat:view do danej instancji bloku
	//to tresc bloku jest pusta (NULL)
 	 
 		if (!has_capability('block/timestat:view', $context)){
		$this->content = NULL;
			return $this->content;
		}
	
	//W przeciwnym wypadku, jezeli uzytkownik posiada uprawnienia to generowana jest zawartosc bloku
	if (has_capability('block/timestat:view', $context) ) {
    		$this->content=new stdClass;
    		$this->content->items=array();
  			$this->content->icons=array();
    		
			//odnosnik do aplikacji wlasciwej z interfejsem graficznym
			$url= $CFG->wwwroot.'/blocks/timestat/counttime.php?param_course_id='.$COURSE->id;
			//Tresc bloku sklada sie z odnosnika oraz ikony zegara
			$this->content->items[] = '<a href="'.$url.'">'.get_string('link','block_timestat').'</a>';
  			$this->content->icons[] = '<img src="'.$CFG->wwwroot.'/blocks/timestat/images/zegar.gif" class="icon" alt="brak" />';
		
		//Brak stopki w tresci bloku
		$this->content->footer = NULL;
		
    	return $this->content;
		}
  	}
	  
	
	//Okreselenie formatów, w których blok ma byc wyswietlany
	function applicable_formats() {
 		 return array(
           'site-index' => false,
           'course-view' => true, 
   	   'course-view-social' => false,
           'mod' => false, 
           'mod-quiz' => false,
		   'course' => true
           );
	}
	
	function instance_allow_multiple() {
  		return false;
	}
	
}

?>
