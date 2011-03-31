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
$moretermtitle = $this->config->moretermtitle;
$oktermtitle = $this->config->oktermtitle;

// Tirar espacos entre linhas dos campos que permitemo HTML-EDITOR
$order = array("\r\n", "\n", "\r");
$replace = '';
$bodyterm = $this->config->bodyterm;
$bodyterm = str_replace($order, $replace, $bodyterm);
$moretermbody = $this->config->moretermbody;
$moretermbody = str_replace($order, $replace, $moretermbody);
$oktermbody = $this->config->oktermbody;
$oktermbody = str_replace($order, $replace, $oktermbody);

//Obtem ip do usuario
$ip = $_SERVER['REMOTE_ADDR'];

//DEFINE PRINT STRINGS
$processing = get_string('processing', 'block_term');
$yes = get_string('yes', 'block_term');
$no = get_string('no', 'block_term');
$okterm = get_string('okterm', 'block_term');
$newentryerror = get_string('newentryerror', 'block_term');
$more = get_string('more', 'block_term');
$close = get_string('close', 'block_term');

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
	* Salva opcao selecionada pelo usuario via AJAX, cria um registro no BD
**/
function addterm(dlg,data_response) {
   // Salvar no BD: RESPONSE 1=yes e 2=no, possibilidade de controles sob outros eventos, default=0 o aluno nao leu ainda

   // Prepara URL para AJAX
   url="'.$CFG->wwwroot.'/blocks/term/ajax_libterm.php?func=addterm&id='.$id.'&instanceid='.$instanceid.'&response="+data_response+"&ip='.$ip.'";

	
   $waitdlg.dialog("open");
   // Invoca via AJAX a criação de uma nova entrada
   $.getJSON(url, function(j){
      if (j) {
	$waitdlg.dialog("close");
	$dialogterm.dialog("close");
	$dialogmore.dialog("close");
	$dialogok.dialog("open");
      } else {
	$waitdlg.dialog("close");
	alert("'.$newentryerror.'");
      }
   });			
}

var $dialogterm = $(\'<div></div>\')
	.html(\'<div>'.$bodyterm.'</div>\')
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
		{	text: "'.$more.'",
			click: function() { $dialogmore.dialog("open"); }
		},
		]
	});

var $dialogmore = $(\'<div></div>\')
	.html(\'<div>'.$moretermbody.'</div>\')
	.dialog({
		autoOpen: false,
		title: "'.$moretermtitle.'",
		resizable: false,
		width: 750,
		height: 600,
		buttons: [
		{	text: "'.$close.'",
			click: function() { $dialogmore.dialog("close"); }
		},
		]
	});

var $dialogok = $(\'<div></div>\')
	.html(\'<div>'.$oktermbody.'</div>\')
	.dialog({
		autoOpen: false,
		title: "'.$oktermtitle.'",
		modal: true, //habilitar fundo transparente
		resizable: false,
		width: 600,
		height: 180,
		buttons: [
		{	text: "'.$okterm.'",
			click: function() { $dialogok.dialog("close"); }
		},
		]
	});


//EVENTOS do BlockTERM
// ABRE a JANELA caso o usuario nao tenha respondido
$dialogterm.dialog("open");

</script>
';


?>
