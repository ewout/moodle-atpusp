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

//require_once('../../config.php' );
require_once('lib.php');


// Dados do Aluno para o BLOCKTERM
$termuser = get_record('block_term','user', $USER->id);
//Criar usuario no block_term
if ($termuser){ //ja respondeu
   //Buscar resposta
   break;
   //Possibilidade de expansao dos eventos
   //$respuser = $termuser->response; //resposta do aluno: 0(nao respondeu), 1(aceitou, sim), 2(nao aceitou, nao)
} else { //nao respondeu ainda
   $respuser=0; //aparece dialogo para responder
}

$courseid = $this->instance->pageid;
$course = get_record('course','id', $courseid);

// Variaveis do Modulo
$id = $this->instance->pageid;
$instanceid = $this->instance->id;
$titleterm = $this->config->titleterm;
$institution = $this->config->institution;
$city = $this->config->city;
$day = date('d');$month = date('F');$year = date('Y');

// Obtem todos os cursos e categorias, do aluno
$courses = get_my_courses($USER->id, 'category DESC, fullname ASC');
$courses = prepCourseCategories($courses);//informacoes de cursos e categorias



//FUNCTIONS *********************************************************

//Lista com Cursos e Categorias
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
	* Salva opcao selecionada pelo usuario via AJAX
**/
function updateterm(dlg,res) {
   // Salvar no BD: 1=yes e 2=no, possibilidade de controles sob outros eventos, default=0 o aluno nao leu ainda
   if (res==2){
      $dialogterm.dialog("close");
   }

}

var formhtml = "<div id=\"paragraphterm\"><p>Eu, <b>'.$USER->firstname .' '. $USER->lastname.'</b>, portador(a) do RG ou RNE n°<input type=\"text\" name=\"rg\" id=\"rg\" size=\"10\"> e do CPF n°<input type=\"text\" name=\"cpf\" id=\"cpf\" size=\"12\">, tutor do Curso <b>'.$courses[0]['categoryname'].' '.$institution.'</b>, declaro para os devidos fins concordar com a utilização, para fins acadêmicos, das informações contidas no ambiente virtual de aprendizagem. Tenho ciência que os responsáveis pela condução da pesquisa asseguram o anonimato dos alunos e dos tutores por meio da supressão do nome e/ou qualquer sinal identificador dos participantes. Declaro compreender que as informações obtidas só podem ser usadas para fins científicos, de acordo com a ética da academia e que a participação nessa pesquisa não comporta qualquer remuneração.</p><p><i>'.$city.', '.$day.' de '.$month.' de '.$year.'</i></p></div>";


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
			click: function() { updateterm(this,1); }
		}, 
		{	text: "'.$no.'",
			click: function() { updateterm(this,2); }
		}, 
		]		
	});


//EVENTOS do BlockTERM
// ABRE a JANELA caso o usuario nao tenha respondido
if ('.$respuser.' == 0){
   $dialogterm.dialog("open");
}


</script>
';


?>
