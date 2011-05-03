<?PHP
//PLIK który odpowiada za autouzupelnianie list formularza,
//z poziomu JavaScript nastepuje laczenie z tym plikiem, skrypt ten natomiast
//zwracasa nowe elementy list.	  	
//This file is responsible for filling lists of form
//JavasCript connects with this file, script return new lists elements

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
	  
	  
	  	// W zminnej 'action' znajduje sie informacja, elementy której listy nalezy pobrac.
		// Zmienna ta moze przybierac jedna z trzech wartosci: updateUsers,updateModules,updateRoles.
		// Okresla ona rodzaj zadania.
		//Parameter 'action' stores data about which list is about. This variable can have 3 values:
		//updateUsers,updateModules,updateRoles
	    switch(required_param('action', PARAM_ALPHA)){
	  		
			//Aktualizacja uzytkowników.
			//users actualization
	  case 'updateUsers':	
	  		//Do aktualizacji uzytkowników niezbedne sa dwa parametry:
			//identyfikator kursu i identyfikator roli
			//(zwracani sa  uzytkownicy zapisani na dany kurs i pelniacy odpowiednia role)
			//get parameters needed for users actualization
			$param_course_id=required_param('id', PARAM_INT);
			$param_roleid=required_param('roleid', PARAM_INT);
			
			//mechanizm pobierania uzytkoników jest identyczny jak w przypadku
			//uzupelniania listy w formularzu (counttime.php)
			//mechanism of getting users is the same as in actualization list in form (counttime.php)
			$context = get_context_instance(CONTEXT_COURSE, $param_course_id);
		  	$relatedcontexts = get_related_contexts_string($context);
		  	$sql = "SELECT ra.userid, u.firstname, u.lastname, u.idnumber 
                    FROM {$CFG->prefix}role_assignments ra
                          JOIN {$CFG->prefix}user u ON u.id = ra.userid
                    WHERE ra.contextid $relatedcontexts AND ra.roleid = $param_roleid ORDER BY u.lastname";
					
			$users = get_records_sql($sql);
			
			//Uzytkownicy znajduja sie w tabeli $users
			//lecz wartosc tablicy w PHP musi zostac przekazana do JavaScript.
			//Wymaga to zastosowania JSON,
			//w zwiazku z czym nastepuje konwersja tablicy do formatu JSON.
			//Conversion users table to JSON format
			$tab=array();
			foreach($users as $user){
				$shortmember=new StdClass;
				$shortmember->userid=$user->userid;
				$shortmember->name=$user->lastname.' '.$user->firstname;
				$tab[]=$shortmember;
			}
			//Wyslanie danych.
			//sending data
			echo json_encode($tab);
            die;
		break;
			
			//W przypadku aktualizacji listy modulów, oraz listy z rolami uzytkowników w danym kursie 
			//mechanizm jest identyczny jak powyzej.
			//Samo pobieranie danych z bazy danych opiera sie o te same instrukcje co aktualizacje tych list w pliku (counttime.php).
			//Jako dodatkowy element dochodzi zawsze konwersja tablic do formatu JSON.
			
			
		case 'updateModules':
			  $param_course_id=required_param('courseid', PARAM_INT);
			$instanceoptions = array();
			$course = get_record('course','id', $param_course_id);
		 	$modinfo = get_fast_modinfo($course);  
  		 	$modules = get_records_select('modules', "visible = 1 AND name <> 'label'", 'name ASC');
    		
			
				//$instanceoptions[' ']['all_modules']=get_string('all_modules','block_timestat');
				$shortmember=new StdClass;
            	$shortmember->groupname='';
				$shortmember->elements=array();
					$shortel=new StdClass;
					$shortel->value_id=-1;
					$shortel->name=get_string('all_modules','block_timestat');
					$shortmember->elements[]=$shortel;
				$instanceoptions[]=$shortmember;
			
         	foreach ($modules as $module) {
            	if (empty($modinfo->instances[$module->name])) {
                	continue;
            	}
				$shortmember=new StdClass;
            	$agroup = get_string('modulenameplural', $module->name);
            	$shortmember->groupname=$agroup;
				$shortmember->elements=array();
				foreach ($modinfo->instances[$module->name] as $cm) {
                	$shortel=new StdClass;
					$shortel->value_id=$cm->id;
					$shortel->name=$cm->name;
					$shortmember->elements[]=$shortel;
					
            	}
				
				$instanceoptions[]=$shortmember;
        	}
			echo json_encode($instanceoptions);
            die;
		break;	
		
		case 'updateRoles':
			  $param_course_id=required_param('courseid', PARAM_INT);
			  $userid=required_param('userid', PARAM_INT);
			$roleoptions=array();
			$roleoptions_students=array();
			$rolesAsString=getRoleString($param_course_id,$userid);
				$blockList=0;
				if($rolesAsString=='teacher')$blockList=1;
			
			$context = get_context_instance(CONTEXT_COURSE, $param_course_id);
 		   	$roles = get_roles_used_in_context($context);
 	       	foreach ($roles as $r) {
				  $shortmember=new StdClass;
				  $shortmember->value_id=$r->id;
				  $shortmember->name=$r->name;
				  $shortmember->block=$blockList;
				  
				  if($r->id==5)$roleoptions_students[] = $shortmember;
 	              $roleoptions[] = $shortmember;
		   	}
				  
			switch($rolesAsString){
			case 'admin':
				echo json_encode($roleoptions);
            break;
			default:
				echo json_encode($roleoptions_students);
			break;
			}
			die;
		break;
		
	}			
	
?>