<?PHP
//PLIK odpowiedzialny za okreslenie metod JavaScript które maja zostac wykonane po zmianie wartosci w elementach formularza.
//Funkcje te wchodza w sklad mechanizmu autouzupelniania list formularza zrealizowanego przy pomocy AJAX.


	//Funkcja zwracajaca kod JavaScript w przypadku zmiany wartosci listy z rolami usytkowników.
	//Function which return JavaScript code when value in list of users is changed
function onChange_Role(){
	//Jezeli AJAX jest wlaczony zwracany jest kod JavaScript
	//if ajax is enabled return javascript code
	if (ajaxenabled()) {	 
		$message_empty=get_string('choose','block_timestat');
		$message_wait=get_string('loading','block_timestat');
	
		//Obiekt UpdatableUsersSelect odpowiada za aktualizacje listy z uzytkownikami,
		//i zostaje wykrzystany po zmianie wartosci na liscie z rolami.
		//Implementacja obiektu UpdatableUsersSelect znajduje sie w pliku ajax.js (w katalogu timestat).
		//Object UpdatableUsersSelect is responsible for actualization of list of users, it is used when 
		//value of list of roles is changed.
 	 $onchange =" var uu=new UpdatableUsersSelect('$message_empty','$message_wait');
	 			  var select_field=document.getElementById('param_course_id');
				  var courseid=select_field.options[select_field.selectedIndex].value;
	  uu.refreshUsers(courseid,this.options[this.selectedIndex].value,'".$message_empty."','".$message_wait."');";
 	
	//Jezeli AJAX jest wylaczonoy to formularz zostaje automatycznie zatwierdzony.
	//If AJAX is disabled, form is instantly submited
	} else {
 		 $onchange=" document.form_counttime.submit();";
 	}
	return $onchange;
}
	
	//Funkcja zwraca kod JavaScript ktory ma zostac wykonany po zmianie wartosci na liscie z kursami.
	//This function return JavaScript code which is executed after change in course list
function onChange_Course($userid){
	if (ajaxenabled()){
		 	
		$message_empty=get_string('choose','block_timestat');
		$summary=get_string('summary','block_timestat');
		$message_wait=get_string('loading','block_timestat');
			
			//Jezeli zmieni sie kurs, to wymaga to trzech zmian:
			//zmiany dostepnych modulów w kursie (obiekt UpdatableModulesSelect)
			//zmiany zdefiniowanych ról dla kursu (obiekt UpdatableRolesSelect)
			//zmiany uzytkowników na liscie z uzytkownikami(obiekt UpdatableUsersSelect)
			
			//If course was changed, it requires three actions:
			//changing available modules of course (object updatableModulesSelect)
			//changing roles defined for course (object UpdatableRolesSelect)
			//changing users on list of users (object UpdatableUsersSelect)
		$onchange =" var um=new UpdatableModulesSelect('$summary','$message_wait');
		 um.refreshModules(this.options[this.selectedIndex].value,'".$message_empty."','".$message_wait."');
		 
		 var ur=new UpdatableRolesSelect('$message_empty','$message_wait');
		 ur.refreshRoles(this.options[this.selectedIndex].value,".$userid.",'".$message_empty."','".$message_wait."');
		 
		 var uu=new UpdatableUsersSelect('$message_empty','$message_wait');
	  	 uu.refreshUsers(this.options[this.selectedIndex].value,0,'".$message_empty."','".$message_wait."');";
		 
 	} else {
 		 $onchange=" document.form_counttime.submit();";
 	}
	return $onchange;
}


?>