<?php
//	This program is free software: you can redistribute it and/or modify
//	it under the terms of the GNU General Public License as published by
//	the Free Software Foundation, either version 3 of the License, or
//	(at your option) any later version.
//
//	This program is distributed in the hope that it will be useful,
//	but WITHOUT ANY WARRANTY; without even the implied warranty of
//	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//	GNU General Public License for more details.
//
//	You should have received a copy of the GNU General Public License
//	along with this program.  If not, see <http://www.gnu.org/licenses/>.

// Página responsável pela apresentação do TERMO

require_once('lib.php');

$courseid = $this->instance->pageid;
$course = get_record('course','id', $courseid);

// Variaveis do Modulo
$id = $this->instance->pageid;
$instanceid = $this->instance->id;
$titleterm = $this->config->titleterm;
$bodyterm = $this->config->bodyterm;
$institution = $this->config->institution;
$city = $this->config->city;
$day = date('d');$month = date('F');$year = date('Y');

// Obtem todos os cursos e categorias, do aluno
$courses = get_my_courses($USER->id, 'category DESC, fullname ASC');
$courses = prepCourseCategories($courses);//informacoes de cursos e categorias



//FUNCTIONS *********************************************************

//Lista com Cursos e Categorias do usuario
function prepCourseCategories($arCourses) {
	$cats = get_records('course_categories');
	$arCats= array();
	foreach ($cats as $cat) {
		$arCats[$cat->id] = array($cat->name, $cat->depth, $cat->sortorder);
	}
	foreach ($arCourses as $course) {
		$arListing['id'] = $course->id;
		$arListing['visible'] = $course->visible;
		$arListing['shortname'] = $course->shortname;
		$arListing['fullname'] = $course->fullname;
		$arListing['category'] = $course->category;
		$arListing['categoryname'] = $arCats[$course->category][0];
		$arListing['categorydepth'] = $arCats[$course->category][1];
		$arListing['categorysortorder'] = $arCats[$course->category][2];
		
		$arListings[] = $arListing;
	}

	usort($arListing, array(&$this, "listingCmp"));

	return $arListings;
}

//DEFINE PRINT STRINGS
$processing = get_string('processing', 'block_term');
$yes = get_string('yes', 'block_term');
$no = get_string('no', 'block_term');
$newentryerror = get_string('newentryerror', 'block_term');

// JAVASCRIPT
$this->content->text .='
<script>
/**

 Importa estilos e arquivos .js das bibliotecas JQUERY
 - $->jquery.js
 - .DIALOG->jquery-ui.js
 - Mascara de campos ->jquery.maskedinput

**/
</script>
<link href="'.$CFG->wwwroot.'/blocks/term/css/jquery-ui.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="'.$CFG->wwwroot.'/blocks/term/jquery.js"></script>
<script type="text/javascript" src="'.$CFG->wwwroot.'/blocks/term/jquery-ui.js"></script>
<script src="'.$CFG->wwwroot.'/blocks/term/jquery.maskedinput-1.2.2.min.js" type="text/javascript"></script>

<script>

/**
	* Estende a JQUERY com uma função para obter as variáveis passadas na URL
**/
$.extend({
  getUrlVars: function(){
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf(\'?\') + 1).split(\'&\');
    for(var i = 0; i < hashes.length; i++)
    {
      hash = hashes[i].split(\'=\');
      vars.push(hash[0]);
      vars[hash[0]] = hash[1];
    }
    return vars;
  },
  getUrlVar: function(name){
    return $.getUrlVars()[name];
  }
});

var $waitdlg = $(\'<div></div>\')
	.html(\'<div align="center"><img style="vertical-align:middle;" src="'.$CFG->wwwroot.'/blocks/term/loading.gif">'.$processing.'</div>\')
	.dialog({
		autoOpen: false,
		modal: true,
		title: "'.$processing.'",
		resizable: false,
		height: 100,
		width: 250
	});


/**
	* Mascarar CAMPO
**/
jQuery(function($){
   $("#cpf").mask("999.999.999-99",{placeholder:"0"});
   $("#rg").mask("99.999.999-9",{placeholder:"0"});
});




/**
	* VALIDAR O CPF
**/

function validarCPF(cpf){
   var filtro = /^\d{3}.\d{3}.\d{3}-\d{2}$/i;
   if(!filtro.test(cpf)){
     window.alert("CPF inválido. Tente novamente.");
	 return false;
   }
   
   cpf = remove(cpf, ".");
   cpf = remove(cpf, "-");
    
   if(cpf.length != 11 || cpf == "00000000000" || cpf == "11111111111" ||
	  cpf == "22222222222" || cpf == "33333333333" || cpf == "44444444444" ||
	  cpf == "55555555555" || cpf == "66666666666" || cpf == "77777777777" ||
	  cpf == "88888888888" || cpf == "99999999999"){
	  window.alert("CPF inválido. Tente novamente.");
	  return false;
   }

   soma = 0;
   for(i = 0; i < 9; i++)
   	 soma += parseInt(cpf.charAt(i)) * (10 - i);
   resto = 11 - (soma % 11);
   if(resto == 10 || resto == 11)
	 resto = 0;
   if(resto != parseInt(cpf.charAt(9))){
	 window.alert("CPF inválido. Tente novamente.");
	 return false;
   }
   soma = 0;
   for(i = 0; i < 10; i ++)
	 soma += parseInt(cpf.charAt(i)) * (11 - i);
   resto = 11 - (soma % 11);
   if(resto == 10 || resto == 11)
	 resto = 0;
   if(resto != parseInt(cpf.charAt(10))){
     window.alert("CPF inválido. Tente novamente.");
	 return false;
   }
   return true;
 }
 
 function remove(str, sub) {
   i = str.indexOf(sub);
   r = "";
   if (i == -1) return str;
   r += str.substring(0,i) + remove(str.substring(i + sub.length), sub);
   return r;
 }



/**
	* Salva opcao selecionada pelo usuario via AJAX, cria um registro no BD
**/
function addterm(dlg,data_response) {
   // Salvar no BD: RESPONSE 1=yes e 2=no, possibilidade de controles sob outros eventos, default=0 o aluno nao leu ainda
   // Obtem dados
   data_rg = $("#rg").val();
   data_cpf = $("#cpf").val();

   if (validarCPF(data_cpf)){
	//Tira "-" e "." dos campos RG e CPF
	data_rg = data_rg.replace(/[^0-9]/g, "");
	data_cpf = data_cpf.replace(/[^0-9]/g, "");

	// Prepara URL para AJAX
	url="'.$CFG->wwwroot.'/blocks/term/ajax_libterm.php?func=addterm&id='.$id.'&instanceid='.$instanceid.'&response="+data_response+"&rg="+data_rg+"&cpf="+data_cpf;

	$waitdlg.dialog("open");
	// Invoca via AJAX a criação de uma nova entrada
	$.getJSON(url, function(j){
		if (j) {
			$waitdlg.dialog("close");
			$dialogterm.dialog("close");
		} else {
			$waitdlg.dialog("close");
			alert("'.$newentryerror.'");
		}
	});			
   }
}

var formhtml = "<div id=\"paragraphterm\"><p>Eu, <b>'.$USER->firstname .' '. $USER->lastname.'</b>, portador(a) do RG ou RNE n°<input type=\"text\" name=\"rg\" id=\"rg\" size=\"13\"> e do CPF n°<input type=\"text\" name=\"cpf\" id=\"cpf\" size=\"15\">, tutor do Curso <b>'.$courses[0]['categoryname'].' '.$institution.'</b>, '.$bodyterm.'</p><p><i>'.$city.', '.$day.' de '.$month.' de '.$year.'</i></p></div>";


var $dialogterm = $(formhtml)
	.dialog({
		autoOpen: false,  // Oculto inicialmente
		title: "'.$titleterm.'",
		modal: true, //habilitar fundo transparente
		closeOnEscape: false, //nao fechar janela ao pressionar a tecla ESC
		open: function(event, ui) { $(".ui-dialog-titlebar-close").hide(); }, //desabiliar o bota fechar
		width: 800,
		height: 500,
		buttons: [
		{	text: "'.$yes.'",
			click: function() { addterm(this,1); }
		}, 
		{	text: "'.$no.'",
			click: function() { addterm(this,2); }
		}, 
		]		
	});


//EVENTOS do BlockTERM
// ABRE a JANELA caso o usuario nao tenha respondido
$dialogterm.dialog("open");

</script>
';


?>
