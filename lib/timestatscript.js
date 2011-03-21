// Skrypt uruchamiany w oknie przegladarki uzytkowników portalu Moodle.
//Jego podstawowym zadaniem jest obliczanie czasu aktywnosci uzytkowników i zapisywanie go w bazie danych.

		var device_flag=true; //flaga korzystania z urzadzenia myszy i klawiatury
		var window_flag=true; //flaga aktywnosci okna
		var popup_window_active=false; //flaga informujaca czy okno pochodne z czat (ramki i JavaScriot) jest aktywne
		
		var time_without_device=30000; //dozwolony czas, w ktorym niewymagane jest korzystanie z myszy i klawiatury (w milisekundach)
		var timeout_flag; //zmienna pomocnicza
		var timeout_addMouse; //zmienna pomocnicza do sterowania zdarzeniem mousemove
		
		var active_time=0; //zliczony czas aktywnosci (w sekundach)
		setInterval(set_alltime,1000); //co jedna seuknde nastepuje wywolywanie metody set_alltime, która w zaleznosci od stanu flag
									  // inkrementuje czas aktywnosci 
		
		addMouse(); //dodanie obslugi zdarzenia mousemove
		
		//Dodanie obslugi zdarzen, w oparciu o mechanizm kompatybilny z roznymi wersjami przegladarek
		if(window.addEventListener){ //dla przegladarek zgodnych z DOM (Firefox,Opera,Chrome)
			window.addEventListener('focus',focus_setflag,false);
			window.addEventListener('blur',blur_setflag,false);
			window.addEventListener('keydown',setFlagsOnActive,true);
			if(!isPopup)window.addEventListener('unload',add_time,false);
		}else if(window.attachEvent){ //dla IE
			document.attachEvent('onfocusin',focus_setflag);
			document.attachEvent('onfocusout',blur_setflag);
			document.attachEvent('onkeydown',setFlagsOnActive);
			if(!isPopup)window.attachEvent('onunload',add_time);
		}else{ //dla pozostalych
			window['onfocus']=focus_setflag;
			window['onblur']=blur_setflag;
			window['onblur']=setFlagsOnActive;
			if(!isPopup)window['onunload']=add_time;
		}
		
		//Funkcja odpowiedzialna za dodwanie do bazy obliczonego czasu aktywnosci.
		//W momencie wywolania funkcji, nastepuje laczenie z serwerem,
		//przez wskazany adres sURL. W celu polaczenia  wykorzystane jest polaczenie synchroniczne AJAX
		//zaimplemetowane w pliku ajax_connection.js. Zmienna start_of_url zainicjowana zostala w pliku timestatlib.php
		function add_time(){
			if(active_time==0)return;
			var sUrl=start_of_url+active_time;
			active_time=0;
			synchronousConnectToUrl(sUrl);
		}
		
		
		//Funkcja ktora odpowiada za zliczanie czasu aktywnosci
		//wewnatrz funkcji znajduja sie dwie grupy intrukcji,
		//jedna z nich uruchamiana jest gdy skrypt zostal zaladowany w wersji standardowej
		//a druga w momencie pracy z oknem pochodnym ( czat uruchomionym czat w wersji z ramkami )
		function set_alltime(){
			//W przypadku gdy skrypt uruchamiany jest w wersji standardowej
			//sprawdzane sa dwie flagi: window_flag i device_flag
			//Jezeli obie sa ustawione na true to zwiekszany jest czas aktywnosci o 1 sekunde (bo metoda uruchamina jest co 1 sekunde)
			if(device_flag & window_flag){
				active_time++;
				popup_window_active=false;
			}else{
				//Jezeli w oknie nie ma ktywnosci to sprawdzane jest czy przypadkiem aktywnosc nie wystepuje
				//w oknie pochodnym z uruchomionym czat
				//jezeli okno pochodne czat jest aktywne, to okno glowne zlicza dla niego czas
				if(popup_window_active)active_time++;
			}
			
			//Druga grupa to instrukcje wykonywane w przypadku okna pochodnego ( czat w wersji z ramkami i JavaScript)
			if( (parent.parent.opener!=null && parent.parent.opener.closed==false) && isPopup){
					//Jezeli okno jest oknem pochodnym, to rowniez sprawdzane sa obie flagi
					//natomiast w przypadku stwierdzenia aktywnosci uzytkownika ,
					//nie jest inkrementowany czas akwtywnosci, lecz w oknie glownym,
					//tym z ktorego poziomu zostalo otwarte nowe okno, jest ustawiana flaga
					//popup_window_active - sygnalizujaca aktywnosc w oknie pochodnym.
					//Dodatkowo zaimnplementowany jest mechanizm, ktory odpowiada za zamkniecia okna pochodnego
					//w przypadku zamkniecia okna glownego. Gdyby istanilo okno pochodne, bez okna glownego, 
					//wowczas nie istanilby element odpowiedzialny za zliczanie dla niego czasu aktywnosci.
				try{
				if(window_flag & device_flag){
					parent.parent.opener.popup_window_active=true;
				}else{
					parent.parent.opener.popup_window_active=false;	
				}
				}catch(exception){parent.parent.close();}
			
			}else{
				if(isPopup && (parent.parent.opener==null || parent.parent.opener.closed) ){parent.parent.close();}
			}
				
		}
		
		//Funkcja wywokywana podczas obslugi zdarzenia focus, ustwia flage aktywnosci okna na true
		function focus_setflag(){
			window_flag=true;
		}
		
		//Funkcja wywolywana jest podczas obslugi zdarzenia blur, poza przestawieniem flagi aktywnosci okna na false,
		//zostaje dodana procedura obslugi zdarzenia mousemove, aby w momencie stwierdzenia nieaktywnosci uzytkownika
		//wszystkie zdarzenia, pozwalajace na nowo wykryc aktywnosc, byly nasluchiwane.
		function blur_setflag(evt){
			addMouse();
			window_flag=false;
		}
		
		//Funkcja odpowiadajaca za dodanie procedury obslugi zdarzenia mousemove
		//Zawiera ona takze mechanizm stwierdzania braku aktywnosci, jezeli w okreslonym czasie 
		//zdarzenie nie zostanie wykryte.
		function addMouse(){		
			if(window.addEventListener){
				window.addEventListener('mousemove',setFlagsOnActive,false);
			}else if(window.attachEvent){
				document.attachEvent('onmousemove',setFlagsOnActive);		
			}else{
				window['onmousemove']=setFlagsOnActive;
			}
			//Po dodaniu obslugi zdarzenie mousemove, jezeli w przeciagu okresu zdefiniowanego jako time_without_device
			//nie zostanie ono wykryte to flaga korzystania z urzadzen zostaje ustawiona na false.
			timeout_flag=window.setTimeout(function(){device_flag=false;},time_without_device);
		}
		
		//Usuwanie procedury obslugi zdarzenia mousemove
		function removeMouse(){
			if(window.removeEventListener){
				window.removeEventListener('mousemove',setFlagsOnActive,false);
			}else if(window.detachEvent){
				document.detachEvent('onmousemove',setFlagsOnActive);		
			}else{
				window['onmousemove']=null;
			}
		}
		
		//Jezeli zostalo zgloszone zdarzenie mousemove lub keydown, to zostaje wywolana ponizsza metoda.
		//Wówaczas obie flagi stanowiace o aktywnosci uzytkownika ustawiane sa na true, gdyz kazde ze zdarzen jednoznacznie wskazuje 
		//aktywnosc uzytkonika.Dodatkowo usuwana jest obsluga zdarzenie mousemve, oraz wykonana jest intrukcja odpowiedzialna za 
		//ponowne dodanie procedury obslugi dla zdarzenia mousemove ale dopiero po uplywie czasu (time_without_device),
		//w którym nie wymagane jest korzystanie z urzadzen myszy i klawiatury.
		function setFlagsOnActive(){
			if(timeout_flag)clearTimeout(timeout_flag);
			if(timeout_addMouse)clearTimeout(timeout_addMouse);
			device_flag=true;
			window_flag=true;
			removeMouse();
		    timeout_addMouse=window.setTimeout(addMouse,time_without_device);	
		}
		