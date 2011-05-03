// PLIK implementujacy polaczenie synchroniczne AJAX
//Funkcja createXMLHTTP odpowiada za utworzenei obiektu, odpowiedzialnego za nawiazanie polaczenia
//natomiast synchronousConnectToUrl odpowiada za polaczenie z adresem wskazanym przez parametr przekazany do tej metody
function createXMLHTTP(){
try{	
	if(window.XMLHttpRequest){
		var oRequest=new XMLHttpRequest();
		return oRequest;
	}else{
		var oRequest= new ActiveXObject ("Microsoft.XMLHTTP");
		return oRequest;
	}
}catch(exception){}
	return null;
}

function synchronousConnectToUrl(url){
	var oRequest=createXMLHTTP(); 
	if(oRequest!=null){
		oRequest.open("get",url,false);
		oRequest.send(null);
	}
}