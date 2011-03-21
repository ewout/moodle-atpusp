//OBIEKT odpowiedzialny za aktualizacje ról uzytkowników dla danego kursu.
//Dla obiektu zdefiniowany jest konrsuktor wraz z funkcja connectCallback która jest wykonywana po otrzymaniu odpowiedzi od 
//serwera, w której zawaerte sa nowe elementy listy.
function UpdatableRolesSelect(message_empty,message_wait){
       this.connectCallback = {
		   		//jezeli zostala otrzymana odpowiedz od serwera wykonywana sa ponizsze operacje,
				//które odpowiadaja za uzupelnienie listy z rolami uzytkowników otrzymanymi danymi
            success: function(o) {
    
                if (o.responseText !== undefined) {
                    var selectEl = document.getElementById("param_roleid");
                    if (selectEl && o.responseText) {
                        	//otrzymany tekst z serwera w formacie JSON zamieniany jest na dane
							//które moga zostac jednoznacznie zinterpretowane przez JavaScript
						var roles = eval("("+o.responseText+")");
                       		
							//elementy aktualnie znajdujace sie na liscie zostaja usuniete
                        if (selectEl) {
                            while (selectEl.firstChild) {
                                selectEl.removeChild(selectEl.firstChild);
                            }
                        }
							
						//dodanie pierwszego elementu na liste, z informacja o mozliwosci wyboru ("wybierz")
						addOptionToSelect(selectEl,0,message_empty); 
							//w petli for analizowane sa dane otrzymane od serwera
							//oraz kolejno, nowe elementy sa dodawane do listy
							for(var j=0;j<roles.length;j++){
								var optionEl2=document.createElement("option");
								optionEl2.setAttribute("value",roles[j].value_id);
								optionEl2.innerHTML=roles[j].name;
								selectEl.appendChild(optionEl2);
							}
                   }
               }
           },
   
           failure: function(o) {
               //removeLoaderImgs("membersloader", "memberslabel");
           }
   
       };
   }
   
   //Funkcja która odpowiada za wyslanie do serwera zadania nowych elementów listy,
   //w odpowiedzi na które zostana zwrócone nowe elementy listy z rolami w danym kursie.
   UpdatableRolesSelect.prototype.refreshRoles = function (courseid,userid,message_empty,message_wait) {
       
	   //Przed wyslaniem zadania do serwera,
	   //elementy aktualnie znajdujece sie na liscie sa usuwane
       var selectEl = document.getElementById("param_roleid");
       if (selectEl) {
           while (selectEl.firstChild) {
               selectEl.removeChild(selectEl.firstChild);
           }
       }
	   //a dodany zostaje wylacznie jeden element, który jest tylko ifnormacja ("trwa wczytywanie...")
	   //ktora nakazuje czekac na aktualizacje listy
	   createLoaderOption(message_wait,"param_roleid");
	   //Okreslenie adresu URL do pliku na serwerze i zawarcie w nim parametrów,
	   //które jednoznacznie identyfikuja rodzaj zadania (action=updateRoles)
	   //oraz parametrów niezbednych do jego realizacji 
       var sUrl ="ajax_selectmembers.php?courseid="+courseid+"&action=updateRoles&userid="+userid;
	   //Laczenie z plikiem przy pomocy AJAX, jako trzeci prametr podana funkja która wykona sie po otrzymaniu
	   //odpowiedzi o serwera, i zaktualizuje elementy zawarte na liscie.
       YAHOO.util.Connect.asyncRequest("GET", sUrl, this.connectCallback, null);
   };
   
   
//Pozostale dwa obiekty, zawarte ponizej, zrealizowane sa na tej samej zasadzie co UpdatableRolesSelect
//Zglaszaja do serewra inny rodzaj zadan i uzupelniaje inne listy, natomaist caly mechanizm dzialania
//wyglada identycznie.
   
//Obiekt odpowiedzialny za aktualizacje listy modulów zawartych w kursie.
function UpdatableModulesSelect(message_empty,message_wait){
       this.connectCallback = {
            success: function(o) {
    
                if (o.responseText !== undefined) {
                    var selectEl = document.getElementById("menuparam_instanceid");
                    if (selectEl && o.responseText) {
						//alert(o.responseText);
                        var roles = eval("("+o.responseText+")");
    
                        // Clear the members list box.
                        if (selectEl) {
                            while (selectEl.firstChild) {
                                selectEl.removeChild(selectEl.firstChild);
                            }
                        }
						
						addOptionToSelect(selectEl,0,message_empty);
						for(var i=0;i<roles.length;i++){
							var optionEl = document.createElement("optgroup");	
							optionEl.setAttribute("label", roles[i].groupname);
							selectEl.appendChild(optionEl);  
							for(var j=0;j<roles[i].elements.length;j++){
								var optionEl2=document.createElement("option");
								optionEl2.setAttribute("value",roles[i].elements[j].value_id);
								optionEl2.innerHTML=roles[i].elements[j].name;
								optionEl.appendChild(optionEl2);
							
							}
						}
                   }
               }
           },
   
           failure: function(o) {
               //removeLoaderImgs("membersloader", "memberslabel");
           }
   
       };
   }
   
   UpdatableModulesSelect.prototype.refreshModules = function (courseid,message_empty,message_wait) {
       // Clear the members list box.
       var selectEl = document.getElementById("menuparam_instanceid");
       if (selectEl) {
           while (selectEl.firstChild) {
               selectEl.removeChild(selectEl.firstChild);
           }
       }
	   createLoaderOption(message_wait,"menuparam_instanceid");
       var sUrl ="ajax_selectmembers.php?courseid="+courseid+"&action=updateModules";
       YAHOO.util.Connect.asyncRequest("GET", sUrl, this.connectCallback, null);
   };



//Obiekt odpowiedzialny za aktualizacje uzytkowników zawartych na liscie.
function UpdatableUsersSelect(message_empty,message_wait){
       this.connectCallback = {
            success: function(o) {
    
                if (o.responseText !== undefined) {
                    var selectEl = document.getElementById("param_userid");
                    if (selectEl && o.responseText) {
                        var users = eval("("+o.responseText+")");
    
                        // Clear the members list box.
                        if (selectEl) {
                            while (selectEl.firstChild) {
                                selectEl.removeChild(selectEl.firstChild);
                            }
                        }
						
						addOptionToSelect(selectEl,0,message_empty)
                        // Populate the members list box.
                        for (var i=0; i<users.length; i++) {
							var optionEl = document.createElement("option");
                                optionEl.setAttribute("value", users[i].userid);
                                //optionEl.title = roles[i].users[j].name;
                                optionEl.innerHTML = users[i].name;
                                selectEl.appendChild(optionEl);   		
                       }
                   }
               }
           },
   
           failure: function(o) {
               //removeLoaderImgs("membersloader", "memberslabel");
           }
   
       };
   }
   
   UpdatableUsersSelect.prototype.refreshUsers = function (courseid,roleid,message_empty,message_wait) {
       // Clear the members list box.
       var selectEl = document.getElementById("param_userid");
       if (selectEl) {
           while (selectEl.firstChild) {
               selectEl.removeChild(selectEl.firstChild);
           }
       }
		if(roleid==0){
			addOptionToSelect(selectEl,0,message_empty);
			return true;	
		}
	   createLoaderOption(message_wait,"param_userid");
       var sUrl ="ajax_selectmembers.php?courseid="+courseid+"&roleid="+roleid+"&action=updateUsers";
       YAHOO.util.Connect.asyncRequest("GET", sUrl, this.connectCallback, null);
	   
   };
   
   
   
   
   // Dwie funkcje pomocnicze. 
   var createLoaderOption = function (message_wait,object_id) {
       var parentEl = document.getElementById(object_id);
       if (!parentEl) {
           return false;
       }
       
	   addOptionToSelect(parentEl,0,message_wait);
       return true;
   };
	
	//Dodawanie elementy do listy rozwijanej
   function addOptionToSelect(selectObj,optionValue,optionName){
	   var optionEl = document.createElement("option");
           optionEl.setAttribute("value", optionValue);
           optionEl.innerHTML = optionName;
           selectObj.appendChild(optionEl);
	  return true;
   }
   
