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

//DEFINE PRINT STRINGS
$searcherror = get_string('searcherror', 'block_term');
$processing = get_string('processing', 'block_term');
$headercsv = get_string('headercsv', 'block_term');

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
	* Formata data na forma brasileira
**/
function FormatDate(ts) {
	var newdate=new Date(ts*1000);
	return newdate.getDate()+"/"+(newdate.getMonth()+1)+"/"+newdate.getFullYear();
}

/**
	* Gera o arquivo CSV numa string para exportação
**/
function GenerateCSV(data) {
	result = new Array();
	result.push(\''.$headercsv.'\');
	result.push("\n");
	for (var i=0; i<data.length; i++) {
		result.push(\'"\'+data[i].id+\'";\');
		result.push(\'"\'+data[i].user+\'";\');
		result.push(\'"\'+data[i].course+\'";\');
		result.push(\'"\'+data[i].response+\'";\');
		result.push(\'"\'+data[i].ip+\'";\');
		result.push(\'"\'+FormatDate(data[i].timemodified)+\'";\n\');
	}
	return result.join(\'\');
}


/**
	* Obtem respostas
**/
function searchterm(dlg) {
   // Prepara URL para AJAX
   url="'.$CFG->wwwroot.'/blocks/term/ajax_libterm.php?func=searchterm&id='.$id.'&instanceid='.$instanceid.'";

   $waitdlg.dialog("open");
   // Invoca via AJAX a criação de uma nova entrada
   $.getJSON(url, function(j){
      if (j) {
	$("#csv").val(GenerateCSV(j));
	$("#name").val("report-blockterm.csv");
	$waitdlg.dialog("close");
      } else {
	$waitdlg.dialog("close");
	alert("'.$searcherror.'");
      }
   });		
}


// Ao carregar a pagina, realizar a consulta de dados - somente usuarios que tem permissao para ver relatorios
searchterm();

</script>
';


?>
