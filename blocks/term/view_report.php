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
$close = get_string('close', 'block_term');
$titleexportgraph = get_string('titleexportgraph', 'block_term');
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
	return newdate.getDate()+"/"+(newdate.getMonth()+1)+"/"+newdate.getFullYear()+" - "+newdate.getHours()+":"+newdate.getMinutes()+":"+newdate.getSeconds();
}

/**
	* Formata o valor numérico para ndigits casas decimal convertendo ponto para vírgula.
**/
function CstFmt(value, ndigits) {
  if(ndigits==null) {
    ndigits=2;
  }
  return (""+value.toFixed(ndigits)).replace(\'.\',\',\');
}


/**
	* Gera o arquivo CSV numa string para exportação
**/
function GenerateCSV(data) {
	result = new Array();
	result.push(\''.$headercsv.'\');
	result.push("\n");
	for (var i=0; i<data.length; i++) {
		result.push(\'"\'+data[i].termid+\'";\');
		result.push(\'"\'+data[i].userid+\'";\');
		result.push(\'"\'+data[i].firstname+\'";\');
		result.push(\'"\'+data[i].lastname+\'";\');
		result.push(\'"\'+data[i].email+\'";\');
		result.push(\'"\'+data[i].courseid+\'";\');
		result.push(\'"\'+data[i].shortname+\'";\');
		result.push(\'"\'+data[i].response+\'";\');
		result.push(\'"\'+data[i].ip+\'";\');
		result.push(\'"\'+FormatDate(data[i].timemodified)+\'";\n\');
	}
	return result.join(\'\');
}


/**
	* Obtem respostas, opt= 1(consulta todas as disciplinas) ou 2(consulta somente disciplina instanciada)
			   opt2= 1(exporta em CSV) ou 2(visualizar o Grafico)
**/
function searchterm(opt,opt2) {
   // Prepara URL para AJAX
   url="'.$CFG->wwwroot.'/blocks/term/ajax_libterm.php?func=searchterm&id='.$id.'&instanceid='.$instanceid.'&optsearch="+opt+"";

   $waitdlg.dialog("open");
   // Invoca via AJAX a criacao de uma nova entrada
   $.getJSON(url, function(j){
      if (j) {
        if (opt2==1 && j.responses) { //Gerar CSV
  	   $("#csv").val(GenerateCSV(j.responses));
	   $("#name").val("report-blockterm.csv");
	   document.csv_form.submit();
        }
        if (opt2==2 && j.totals) { //Gerar GOOGLE GRAPH
	   total = j.totals.total;
	   yes = (j.totals.yes / total) * 100; no= (j.totals.no / total) * 100;

	   $graphdlg.html(\'<div align="center"><p><img style="vertical-align:middle;" src="https://chart.googleapis.com/chart?cht=p&chd=t:\'+yes+\',\'+no+\'&chs=300x150&chl='.$yes.'|'.$no.'"></p><p><b>Amostra: \'+j.totals.total+\' respostas <br><br> '.$yes.': \'+CstFmt(yes,2)+\' % | \'+j.totals.yes+\' respostas<br>'.$no.':  \'+CstFmt(no,2)+\' % | \'+j.totals.no+\' respostas</b></p></div>\')
	   $graphdlg.dialog("open");
        }
	$waitdlg.dialog("close");
      } else {
	$waitdlg.dialog("close");
	alert("'.$searcherror.'");
      }
   });		
}


var $graphdlg = $(\'<div></div>\')
	.dialog({
		autoOpen: false,
		modal: false,
		title: "'.$titleexportgraph.'",
		closeOnEscape: true,
		resizable: false,
		height: 400,
		width: 400,
		buttons: [
		{	text: "'.$close.'",
			click: function() { $graphdlg.dialog("close"); }
		},
		]
	});
</script>
';


?>
