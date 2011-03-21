<?PHP
	  require_once('../../config.php');
	  global $CFG;
	  require_login();
	  require_once($CFG->libdir.'/ddllib.php');
	  require_once($CFG->libdir.'/dmllib.php');
	     
		//Pobierany jest rekord o przeslanym ID oraz dodany do niego zostaje obliczony czas, przeslany w zmiennej TIME. 
		//Get record with sent ID and add to it counted time, sended in variable TIME
	      $record=get_record('log','id',required_param('id', PARAM_INT));
	      
	      
		  $record->count=$record->count+required_param('time', PARAM_INT);
		update_record('log',$record);
		
?>