<?php
//	Sistema de Relatórios USP para Moodle
//	Copyright (C) 2010 Neomundi Internet
//
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

// Página responsável pela apresentação do Relatório de Alunos

require_once('../../config.php' );
require_once('lib.php');
require_once($CFG->libdir.'/blocklib.php');

// Caceçalho
//print_header_simple(get_string('reptutor', 'block_relusp'));
//hds print Breadcrumb
$courseid = required_param('id', PARAM_INT);
$course = get_record('course','id', $courseid);
$navigation = array(
              array('name' => $course->shortname, 'link' => "{$CFG->wwwroot}/course/view.php?id=$course->id", 'type'=> 'title'),
              array('name' => get_string('repstudent', 'block_relusp'), 'link'=>'', 'type'=>'title'),
                );
print_header_simple(get_string('repstudent', 'block_relusp'),'', build_navigation($navigation));

?>
<!-- // Importa estilos e arquivos .js das bibliotecas JQUERY e TABLESORTER -->
<link href="<?php print $CFG->wwwroot ?>/blocks/relusp/css/jquery-ui.css" rel="stylesheet" type="text/css"/>
<link href="<?php print $CFG->wwwroot ?>/blocks/relusp/css/tablesorter.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="<?php print $CFG->wwwroot ?>/blocks/relusp/jquery.js"></script>
<script type="text/javascript" src="<?php print $CFG->wwwroot ?>/blocks/relusp/jquery-ui.js"></script>
<script type="text/javascript" src="<?php print $CFG->wwwroot ?>/blocks/relusp/jquery-ui-ptBR.js"></script>
<script type="text/javascript" src="<?php print $CFG->wwwroot ?>/blocks/relusp/jquery.tablesorter.min.js"></script>

<?php
// Obtem o Id do curso e o Id de instância do bloco
$id = required_param('id', PARAM_INT);
$instanceid = required_param('instanceid', PARAM_INT);
// Cria um objeto RepTutor para acesso a parâmetros
$tutordiary = new RepStudent($id, $instanceid, $USER->id);
// Obtem grupos
$tutordiary->getGroups();
// Obtem parâmetros de permissões
$tutordiary->canViewAll();
$tutordiary->canViewGroup();
?>
<!-- // Formulário para entrada da faixa de datas do relatório e escolha de grupo e atividades -->
<p align="center">
<?php print_string('reptutormsg1', 'block_relusp');?>
</p>
<p>
<table width="500" border="0" align="center">
<tr>
<td align="left">
<?php print_string('from', 'block_relusp');?>: <input type="text" name="from" id="from">
</td>
<td align="right">
<?php print_string('to', 'block_relusp');?>: <input type="text" name="to" id="to">
</td>
</tr>
<tr>
<td align="center">
<span id="grp"></span>
</td>
<!-- hds Desabilitar modo Experimental
<td align="center">Método: <select name="met" id="met"><option value="0">Normal</option><option value="1">Experimental 1</option><option value="2">Experimental 2</option><option value="3">Experimental 3</option></select>
</td>
-->
<input type="hidden" name="met" id="met" value="0">

</tr>
<tr>
<td colspan="2" align="center">
<?php print_string('includeact', 'block_relusp');?>:<br><br>
<input type="checkbox" id="act_forum" name="act_forum" <?php $tutordiary->selectActivities('forum'); ?>><?php print_string('forum', 'block_relusp');?> &nbsp;&nbsp;&nbsp;
<input type="checkbox" id="act_chat" name="act_chat" <?php $tutordiary->selectActivities('chat'); ?>><?php print_string('chat', 'block_relusp');?> &nbsp;&nbsp;&nbsp;
<input type="checkbox" id="act_assignment" name="act_assignment" <?php $tutordiary->selectActivities('assignment'); ?>><?php print_string('assignment', 'block_relusp');?> &nbsp;&nbsp;&nbsp;
<input type="checkbox" id="act_wiki" name="act_wiki" <?php $tutordiary->selectActivities('wiki'); ?>><?php print_string('wiki', 'block_relusp');?> &nbsp;&nbsp;&nbsp;
<input type="checkbox" id="act_quiz" name="act_quiz" <?php $tutordiary->selectActivities('quiz'); ?>><?php print_string('quiz', 'block_relusp');?>&nbsp;&nbsp;&nbsp;
<input type="checkbox" id="act_book" name="act_book" <?php $tutordiary->selectActivities('book'); ?>><?php print_string('book', 'block_relusp');?>&nbsp;&nbsp;&nbsp;
<input type="checkbox" id="act_lesson" name="act_lesson" <?php $tutordiary->selectActivities('lesson'); ?>><?php print_string('lesson', 'block_relusp');?>&nbsp;&nbsp;&nbsp;
<input type="checkbox" id="act_questionnaire" name="act_questionnaire" <?php $tutordiary->selectActivities('questionnaire'); ?>><?php print_string('questionnaire', 'block_relusp');?>
</td>
</tr>
<tr>
<td colspan="2" align="center" height="50">
<button id="generate"><?php print_string('generate', 'block_relusp');?></button>
<div id="debug"></div>
</td>
</tr>
</table>
</p>
<!-- // Formulário oculto que receberá os dados em CSV para exportação do arquivo CSV -->
<div align="right">
<form name="csv_form" id="csv_form" target="_blank" method="post" enctype="application/x-www-form-urlencoded;charset=UTF-8" action="<?php echo $CFG->wwwroot ?>/blocks/relusp/csv_processor.php">
<input type="hidden" name="csv" id="csv" value="">
<input type="hidden" name="name" id="name" value="">
<span id="export"></span>
</div>
<!-- // Divisão na qual será apresentado o relatório -->
<div id="report">
</div>
<script>
// Prepara bloco que irá apresentar os detalhes das atividades quando se passa o mouse
var container = $('<div id="personPopupContainer">'
	+ '<table width="" border="0" cellspacing="0" cellpadding="0" align="center" class="personPopupPopup">'
	+ '<tr>'
	+ '   <td class="corner topLeft"></td>'
	+ '   <td class="top"></td>'
	+ '   <td class="corner topRight"></td>'
	+ '</tr>'
	+ '<tr>'
	+ '   <td class="left">&nbsp;</td>'
	+ '   <td><div id="personPopupContent"></div></td>'
	+ '   <td class="right">&nbsp;</td>'
	+ '</tr>'
	+ '<tr>'
	+ '   <td class="corner bottomLeft">&nbsp;</td>'
	+ '   <td class="bottom">&nbsp;</td>'
	+ '   <td class="corner bottomRight"></td>'
	+ '</tr>'
	+ '</table>'
	+ '</div>');

/**
	* Limpa a string passada retirando aspas duplas
**/
function Clean(value) {
	return value.replace('"','');
}

/**
	* Gera um nome de arquivo para exportaçõa do CSV baseado nas datas e tipo de relatório
**/
function Filename() {
	f = $('#from').datepicker("getDate");
	t = $('#to').datepicker("getDate");
	return 'repstudent_'+f.getDate()+'-'+(f.getMonth()+1)+'-'+f.getFullYear()+'_'+t.getDate()+'-'+(t.getMonth()+1)+'-'+t.getFullYear()+'.csv';
}

/**
	* Apresenta os dados da atividade ou N/D caso não havia atividade disponível.
**/
function ActFmt(value) {
	if (value!=null)
		return value;
	else
		return 0;
}
/**
	* Verifica se será apresentado OK ou PROBLEMA no alarme
**/
function AlFmt(value) {
	if (value)
		return "<?php print_string('ok', 'block_relusp');?>";
	else
		return "<?php print_string('problem', 'block_relusp');?>";
}

/**
	* Gera o arquivo CSV numa string para exportação
**/
function GenerateCSV(data) {
	result = new Array();
	result.push('<?php print_string('headerrepstudent', 'block_relusp');?>');
	result.push("\n");
	for (var i in data.bystudentid){
		result.push('"'+Clean(data.bystudentid[i].name)+'";');
		result.push('"'+CstFmt(data.bystudentid[i].logins_d)+'";');
		result.push('"'+CstFmt(data.totals.logins_d)+'";');
		result.push('"'+CstFmt(data.bystudentid[i].logins_p)+'";');
		result.push('"'+CstFmt(data.totals.logins_p)+'";');
		result.push('"'+CstFmt(data.bystudentid[i].accesstime_d/3600.0)+'";');
		result.push('"'+CstFmt(data.totals.accesstime_d/3600.0)+'";');
		result.push('"'+CstFmt(data.bystudentid[i].accesstime_p/3600.0)+'";');
		result.push('"'+CstFmt(data.totals.accesstime_p/3600.0)+'";');
		result.push('"'+CstFmt(data.bystudentid[i].accesses_d)+'";');
		result.push('"'+CstFmt(data.totals.accesses_d)+'";');
		result.push('"'+CstFmt(data.bystudentid[i].accesses_p)+'";');
		result.push('"'+CstFmt(data.totals.accesses_p)+'";');
		result.push('"'+AlFmt(data.bystudentid[i].al_student)+'";');
		result.push('"'+ActFmt(data.bystudentid[i].act_forum_view)+'";');
		result.push('"'+ActFmt(data.bystudentid[i].act_forum_post)+'";');
		result.push('"'+ActFmt(data.bystudentid[i].act_forum_count)+'";');
		result.push('"'+ActFmt(data.bystudentid[i].act_chat_view)+'";');
		result.push('"'+ActFmt(data.bystudentid[i].act_chat_post)+'";');
		result.push('"'+ActFmt(data.bystudentid[i].act_chat_count)+'";');
		result.push('"'+ActFmt(data.bystudentid[i].act_assignment_view)+'";');
		result.push('"'+ActFmt(data.bystudentid[i].act_assignment_post)+'";');
		result.push('"'+ActFmt(data.bystudentid[i].act_assignment_count)+'";');
		result.push('"'+ActFmt(data.bystudentid[i].act_wiki_view)+'";');
		result.push('"'+ActFmt(data.bystudentid[i].act_wiki_post)+'";');
		result.push('"'+ActFmt(data.bystudentid[i].act_wiki_count)+'";');
		result.push('"'+ActFmt(data.bystudentid[i].act_quiz_view)+'";');
		result.push('"'+ActFmt(data.bystudentid[i].act_quiz_post)+'";');
		result.push('"'+ActFmt(data.bystudentid[i].act_quiz_count)+'";');
		result.push('"'+ActFmt(data.bystudentid[i].act_book_view)+'";');
		result.push('"'+ActFmt(data.bystudentid[i].act_book_count)+'";');
		result.push('"'+ActFmt(data.bystudentid[i].act_lesson_view)+'";');
		result.push('"'+ActFmt(data.bystudentid[i].act_lesson_post)+'";');
		result.push('"'+ActFmt(data.bystudentid[i].act_lesson_count)+'";');
		result.push('"'+ActFmt(data.bystudentid[i].act_questionnaire_view)+'";');
		result.push('"'+ActFmt(data.bystudentid[i].act_questionnaire_post)+'";');
		result.push('"'+ActFmt(data.bystudentid[i].act_questionnaire_count)+'"\n');
	}
	return result.join('');
}

// Gera e atualiza a lista de grupos na drop box
function UpdateGroups() {
	if (canViewAll)
		var options='<option value="0"><?php print_string('all', 'block_relusp');?> </option>';
	else
		var options='';
	if (canViewGroup) {
		for (var i=0; i<groups.length; i++) {
			options+='<option value="'+groups[i].id+'">'+groups[i].name+'</option>';
		}
	}	
	$('#grp').html('<br>Grupo: <select name="group" id="group">'+options+'</select><br><br>');
}

/**
	* Formata o valor numérico para ndigits casas decimal convertendo ponto para vírgula.
**/
function CstFmt(value, ndigits, digits) {
  if(ndigits==null) {
    ndigits=2;
  }
  if(digits==null) {
    digits=',';
  }
  return (''+value.toFixed(ndigits)).replace('.',digits);
}

/**
	* Verifica se deve retornar o estilo de alarme (vermelho)
**/
function CstAlert(value) {
	if (value)
		return ' style="color: red"';
	else
		return '';
}

// Criea numa variável a caixa de diálogo de espera no processamento do relatório
var $waitdialog = $('<div></div>')
	.html('<div align="center"><img style="vertical-align:middle;" src="<?php echo $CFG->wwwroot ?>'+'/blocks/relusp/loading.gif"> <?php print_string('processing', 'block_relusp');?></div>')
	.dialog({
		autoOpen: false,
		modal: true,
		title: '<?php print_string('processing', 'block_relusp');?>',
		resizable: false,
		height: 100,
		width: 250
	});

var $graphdlg1 = $('<div></div>')
      .dialog({
	autoOpen: false,
	modal: false,
	title: '<?php print_string('fda', 'block_relusp');print ' de ';print_string('daccess', 'block_relusp');?>',
	closeOnEscape: true,
	resizable: true,
	height: 390,
	width: 430
   });

var $graphdlg2 = $('<div></div>')
      .dialog({
	autoOpen: false,
	modal: false,
	title: '<?php print_string('fda', 'block_relusp');print ' de ';print_string('paccess', 'block_relusp');?>',
	closeOnEscape: true,
	resizable: true,
	height: 390,
	width: 430
   });

var $graphdlg3 = $('<div></div>')
      .dialog({
	autoOpen: false,
	modal: false,
	title: '<?php print_string('fda', 'block_relusp');print ' de ';print_string('dpermanence', 'block_relusp');?>',
	closeOnEscape: true,
	resizable: true,
	height: 390,
	width: 430
   });

var $graphdlg4 = $('<div></div>')
      .dialog({
	autoOpen: false,
	modal: false,
	title: '<?php print_string('fda', 'block_relusp');print ' de ';print_string('ppermanence', 'block_relusp');?>',
	closeOnEscape: true,
	resizable: true,
	height: 390,
	width: 430
   });

var $graphdlg5 = $('<div></div>')
      .dialog({
	autoOpen: false,
	modal: false,
	title: '<?php print_string('fda', 'block_relusp');print ' de ';print_string('dactivity', 'block_relusp');?>',
	closeOnEscape: true,
	resizable: true,
	height: 390,
	width: 430
   });

var $graphdlg6 = $('<div></div>')
      .dialog({
	autoOpen: false,
	modal: false,
	title: '<?php print_string('fda', 'block_relusp');print ' de ';print_string('pactivity', 'block_relusp');?>',
	closeOnEscape: true,
	resizable: true,
	height: 390,
	width: 430
   });

/**
	* Obtem GoogleChart
**/
function searchgraph(g_values,opt) {

g_values = g_values.sort(function(a,b){return b - a}); //ordena numeros em descendente

if (g_values.length > 200){
   alert("<?php print_string('errorurldata', 'block_relusp');?>");
} else {

g_res = new Array(); //[0] valoes [1]indice

for(var i = 0; i < g_values.length; i++){ //criar arrays
   g_res[i] = new Array();
   g_res[i][0]=g_values[i]; g_res[i][1]=i+1; //[0]:valor [1]:indice
}

qtd='';
values='';
maxx=null;maxy=null; //maximo dos eixos
for(var i = 0; i < g_res.length; i++){
if (g_res[i][0]) //verificar apenas itens com valor
   if ((g_res[i][1])==g_res.length){ //verifica se eh o ultimo item, nao colocar ','
      qtd+= (i+1);
      values+= g_res[i][0];
   } else {
      qtd+= (i+1)+',';
      values+= g_res[i][0]+',';
      if (maxx==null){ //primeiro item
	 maxx=Number(g_res[i][0]) + Number(g_res[i][0]-g_res[i+1][0]); //eixo x, quantidade e distanciar do ultimo ponto
	 maxx=CstFmt(maxx,2,".");
      }
   }
if (i+1 == g_res.length) //ultimo item
   maxy=i+1; //eixo y, usuarios
   maxy=Number(maxy)+10; //distanciar do ultimo ponto
   maxy=CstFmt(maxy,2,".");
}

//Documentacao GoogleGRAPH
//chtt=Titulo do grafico - chxt: eixos, x,y - chs:dimensao da imagem - cht:tipo de grafico - chd: dados do grafico - chxr: max/min imagem dos eixos - chds: max/min dados do eixo - chg: habilitar grade - chxl:legendas - chxp:posicionamento das legendas

switch (opt){
 case 1:
   graphcode='<div id="chart"><img src="http://chart.apis.google.com/chart?&chtt='+escape("#")+'<?php print_string('titlegraphstd', 'block_relusp');?><?php print_string('daccess', 'block_relusp');?> '+escape(">")+' X&chxt=x,y,x&chs=400x300&cht=s&chd=t:'+values+'|'+qtd+'|50&chxr=0,0,'+maxx+'|1,0,'+maxy+'&chds=0,'+maxx+',0,'+maxy+',0,100&chg=-1,-1,0,0&chxl=2:|<?php print_string('daccess', 'block_relusp');?>&chxp=2,50"></div>';
   $graphdlg1.html(graphcode);
   $graphdlg1.dialog("open");
   break;

 case 2:
   graphcode='<div id="chart"><img src="http://chart.apis.google.com/chart?&chtt='+escape("#")+'<?php print_string('titlegraphstd', 'block_relusp');?><?php print_string('paccess', 'block_relusp');?> '+escape(">")+' X&chxt=x,y,x&chs=400x300&cht=s&chd=t:'+values+'|'+qtd+'|50&chxr=0,0,'+maxx+'|1,0,'+maxy+'&chds=0,'+maxx+',0,'+maxy+',0,100&chg=-1,-1,0,0&chxl=2:|<?php print_string('paccess', 'block_relusp');?>&chxp=2,50"></div>';
   $graphdlg2.html(graphcode);
   $graphdlg2.dialog("open");
   break;

 case 3:
   graphcode='<div id="chart"><img src="http://chart.apis.google.com/chart?&chtt='+escape("#")+'<?php print_string('titlegraphstd', 'block_relusp');?><?php print_string('dpermanence', 'block_relusp');?> '+escape(">")+' X&chxt=x,y,x&chs=400x300&cht=s&chd=t:'+values+'|'+qtd+'|50&chxr=0,0,'+maxx+'|1,0,'+maxy+'&chds=0,'+maxx+',0,'+maxy+',0,100&chg=-1,-1,0,0&chxl=2:|<?php print_string('dpermanence', 'block_relusp');?>&chxp=2,50"></div>';
   $graphdlg3.html(graphcode);
   $graphdlg3.dialog("open");
   break;

 case 4:
   graphcode='<div id="chart"><img src="http://chart.apis.google.com/chart?&chtt='+escape("#")+'<?php print_string('titlegraphstd', 'block_relusp');?><?php print_string('ppermanence', 'block_relusp');?> '+escape(">")+' X&chxt=x,y,x&chs=400x300&cht=s&chd=t:'+values+'|'+qtd+'|50&chxr=0,0,'+maxx+'|1,0,'+maxy+'&chds=0,'+maxx+',0,'+maxy+',0,100&chg=-1,-1,0,0&chxl=2:|<?php print_string('ppermanence', 'block_relusp');?>&chxp=2,50"></div>';
   $graphdlg4.html(graphcode);
   $graphdlg4.dialog("open");
   break;

 case 5:
   graphcode='<div id="chart"><img src="http://chart.apis.google.com/chart?&chtt='+escape("#")+'<?php print_string('titlegraphstd', 'block_relusp');?><?php print_string('dactivity', 'block_relusp');?> '+escape(">")+' X&chxt=x,y,x&chs=400x300&cht=s&chd=t:'+values+'|'+qtd+'|50&chxr=0,0,'+maxx+'|1,0,'+maxy+'&chds=0,'+maxx+',0,'+maxy+',0,100&chg=-1,-1,0,0&chxl=2:|<?php print_string('dactivity', 'block_relusp');?>&chxp=2,50"></div>';
   $graphdlg5.html(graphcode);
   $graphdlg5.dialog("open");
   break;

 case 6:
   graphcode='<div id="chart"><img src="http://chart.apis.google.com/chart?&chtt='+escape("#")+'<?php print_string('titlegraphstd', 'block_relusp');?><?php print_string('pactivity', 'block_relusp');?> '+escape(">")+' X&chxt=x,y,x&chs=400x300&cht=s&chd=t:'+values+'|'+qtd+'|50&chxr=0,0,'+maxx+'|1,0,'+maxy+'&chds=0,'+maxx+',0,'+maxy+',0,100&chg=-1,-1,0,0&chxl=2:|<?php print_string('pactivity', 'block_relusp');?>&chxp=2,50"></div>';
   $graphdlg6.html(graphcode);
   $graphdlg6.dialog("open");
   break;
}  //fim if errorurldata
} //fim switch
}//fim function

function closeallgraphs() {
   $graphdlg1.dialog("close");
   $graphdlg2.dialog("close");
   $graphdlg3.dialog("close");
   $graphdlg4.dialog("close");
   $graphdlg5.dialog("close");
   $graphdlg6.dialog("close");
}

var t_from = null;
var t_to = null;

// Bloco de inicialização executado quando a página é carregada
$(document).ready(function() {
	// Cria os objetos DatePicker e fixa as datas iniciais.
	$('#from').datepicker({  constrainInput: true, showOn: 'button', buttonImageOnly: true, 
				maxDate: 'today', buttonImage: '<?php print $CFG->wwwroot ?>/blocks/relusp/calendar.gif' });
	$('#to').datepicker({  constrainInput: true, showOn: 'button', buttonImageOnly: true, 
				maxDate: 'today', buttonImage: '<?php print $CFG->wwwroot ?>/blocks/relusp/calendar.gif' });
	$('#from').datepicker( "setDate" , "-7" );
	$('#to').datepicker( "setDate" , "today" );
	// Cria o objeto tablesorter com um tipo da dado particularizado para ordenação de números com vírgula	
    $.tablesorter.addParser({ 
        id: 'customfloat', 
        is: function(s) { 
            return false; 
        }, 
        format: function(s) {
	  return s.replace(',', '.'); 
        }, 
        type: 'numeric' 
    });
	// Atualiza grupos
	UpdateGroups();
	// Adiciona botão para gerar relatório
	$('#generate').button({ icons: { primary: "ui-icon-calculator" } });	
	// Cria container para mensagens das atividades
	$('body').append(container);		
});

// Bloco executado quando o botão para geração do relatório é pressionado
$("#generate").click(function() {
	// Obtem datas
	t_from = $('#from').datepicker("getDate");
	t_to = $('#to').datepicker("getDate");
	// Faz checagem se datas são válidas	
	if ((t_from == null) || (t_to == null)) {
		alert('<?php print_string('dateerror1', 'block_relusp');?>');
		return false;
	}
	// Ajusta datas para unix timestamp
	t_from = t_from.getTime()/1000;
	t_to = t_to.getTime()/1000;
	t_to += 86400;
	// Checagem de sanidade	
	if (t_to <= t_from) {
		alert('<?php print_string('dateerror2', 'block_relusp');?>');
		return false;
	}
	// Verifica permissões
	if ((!canViewAll) && (!canViewGroup)) {
		alert("<?php print_string('noperm', 'block_relusp');?>");
		return;
	}
	// Obtem grupo de interesse
	var groupid = $('#group').val();
	// Prepara variáveis para requisição das atividades
	var act_forum=0; if ($('#act_forum').attr('checked')) act_forum=1;
	var act_chat=0; if ($('#act_chat').attr('checked')) act_chat=1;
	var act_assignment=0; if ($('#act_assignment').attr('checked')) act_assignment=1;
	var act_wiki=0; if ($('#act_wiki').attr('checked')) act_wiki=1;
	var act_quiz=0; if ($('#act_quiz').attr('checked')) act_quiz=1;
	var act_book=0; if ($('#act_book').attr('checked')) act_book=1;
	var act_lesson=0; if ($('#act_lesson').attr('checked')) act_lesson=1;
	var act_questionnaire=0; if ($('#act_questionnaire').attr('checked')) act_questionnaire=1;
	// Prepara execução de AJAX	
	url='<?php echo $CFG->wwwroot ?>'+'/blocks/relusp/ajax_repstudent.php?func=repstudent'+
		'&id=<?php print $id; ?>&instanceid=<?php print $instanceid; ?>'+'&groupid='+encodeURIComponent(groupid)+
		'&from='+encodeURIComponent(t_from)+'&to='+encodeURIComponent(t_to)+'&act_forum='+act_forum+'&act_chat='+act_chat+'&act_assignment='+
		act_assignment+'&act_wiki='+act_wiki+'&act_quiz='+act_quiz+'&act_book='+act_book+'&act_lesson='+act_lesson+'&act_questionnaire='+act_questionnaire+'&met='+encodeURIComponent($('#met').val());
	//$('#debug').html(url);
	$waitdialog.dialog('open');
	$("#report").ajaxError(function(event, request, settings){
		$(this).html("<li><?php print_string('errorprocessing', 'block_relusp');?> " + settings.url + "</li><br>"+request.responseText);
		$waitdialog.dialog('close');
	});
	// Executa AJAX para geração do relatório	
	$.getJSON(url, function(j){
		if (j) {
			// Declara array com valores para o GoogleChart
			g_values1 = new Array();g_values2 = new Array();g_values3 = new Array();g_values4 = new Array();g_values5 = new Array();g_values6 = new Array();
			// Fecha janela de processamento
			$waitdialog.dialog('close');
			// Monta cabeçalhos da tabela do relatório
			reptable='<table id="results" class="tablesorter"><thead><tr><th width="290px"><?php print_string('student', 'block_relusp');?></th>';
			reptable+='<th width="90px"><?php print_string('daccess', 'block_relusp');?></th><th width="110px"><?php print_string('paccess', 'block_relusp');?></th>';
			reptable+='<th width="90px"><?php print_string('dpermanence', 'block_relusp');?></th><th width="100px"><?php print_string('ppermanence', 'block_relusp');?></th>';
			reptable+='<th width="80px"><?php print_string('dactivity', 'block_relusp');?></th><th width="90px"><?php print_string('pactivity', 'block_relusp');?></th>';
			reptable+='<th><?php print_string('activities', 'block_relusp');?></th></tr></thead><tbody>';

			// Preenche os dados retornados
			for (var i in j.bystudentid){
				//hds- link para relatorios das atividades(default-moodle)
				reptable+='<tr><td><a href="../../course/user.php?&id='+j.bystudentid[i].course+'&user='+j.bystudentid[i].id+'&mode=alllogs" target="_blank">'+j.bystudentid[i].name+'</a></td>';
				reptable+='<td'+CstAlert(j.bystudentid[i].al_logins_d)+'>'+CstFmt(j.bystudentid[i].logins_d)+'</td>';
				reptable+='<td'+CstAlert(j.bystudentid[i].al_logins_p)+'>'+CstFmt(j.bystudentid[i].logins_p,0)+'</td>';
				reptable+='<td'+CstAlert(j.bystudentid[i].al_accesstime_d)+'>'+CstFmt(j.bystudentid[i].accesstime_d/60.0)+'</td>';
				reptable+='<td'+CstAlert(j.bystudentid[i].al_accesstime_p)+'>'+CstFmt(j.bystudentid[i].accesstime_p/3600.0,1)+'</td>';
				reptable+='<td'+CstAlert(j.bystudentid[i].al_accesses_d)+'>'+CstFmt(j.bystudentid[i].accesses_d)+'</td>';
				reptable+='<td'+CstAlert(j.bystudentid[i].al_accesses_p)+'>'+CstFmt(j.bystudentid[i].accesses_p,0)+'</td>';
				if (j.bystudentid[i].al_student)
					reptable+='<td><div style="display:block; cursor:pointer;text-align:center;background:#FFF;" class="acts" i="'+i+'"><?php print_string('ok', 'block_relusp');?></div></td>';
				else
					reptable+='<td style="color: red"><div style="display:block; padding:3px; cursor:pointer;text-align:center;background:#FFF;" class="acts" i="'+i+'"><?php print_string('problem', 'block_relusp');?></div></td>';
					
				reptable+='</tr>';

				//Guarda valores para GoogleChart
				g_values1.push(CstFmt(j.bystudentid[i].logins_d,2,"."));
				g_values2.push(CstFmt(j.bystudentid[i].logins_p,0));
				g_values3.push(CstFmt(j.bystudentid[i].accesstime_d/60.0,2,"."));
				g_values4.push(CstFmt(j.bystudentid[i].accesstime_p/3600.0,2,"."));
				g_values5.push(CstFmt(j.bystudentid[i].accesses_d,2,"."));
				g_values6.push(CstFmt(j.bystudentid[i].accesses_p,0));

			}
			reptable+='</tbody></table>';

			//hds-Cabeçalho flutuante da tabela
			reptable+='<div style="position:fixed;top:0;background:#fff4c8;border:1px solid #ffcc00;width:936px;"><table style="font-size:9pt;font-weight:bold;margin:5px 3px; width:100%;"><tr><td width="295px;"><?php print_string('student', 'block_relusp');?></td><td width="95px;"><?php print_string('daccess', 'block_relusp');?></td><td width="115px;"><?php print_string('paccess', 'block_relusp');?></td><td width="95px;"><?php print_string('dpermanence', 'block_relusp');?></td><td width="105px;"><?php print_string('ppermanence', 'block_relusp');?></td><td width="85px;"><?php print_string('dactivity', 'block_relusp');?></td><td width="95px"><?php print_string('pactivity', 'block_relusp');?></td><td><?php print_string('activities', 'block_relusp');?></td></tr></table></div>';

			//hds-Linha com media dos valores
			//Confere se o parametro para calcular e MEAN ou MEDIAN, para inverter posicionamento dos valores
			if (j.totals2.modocalc == 'MEDIAN') //quando o resultado de TOTALS = MEAN
			  reptable+='<div style="position:fixed;top:92%;background:#fff4c8;border:1px solid #ffcc00;width:936px;"><table style="font-size:9pt;font-weight:bold;margin:5px 3px; width:100%"><tr><td width="255px;">M&eacute;dia / Mediana</td><td width="105px;">'+CstFmt(j.totals.logins_d)+' / '+CstFmt(j.totals2.logins_d)+'</td><td width="115px;">'+CstFmt(j.totals.logins_p,0)+' / '+CstFmt(j.totals2.logins_p,0)+'</td><td width="95px;">'+CstFmt(j.totals.accesstime_d/60.0)+' / '+CstFmt(j.totals2.accesstime_d/60.0)+'</td><td width="105px;">'+CstFmt(j.totals.accesstime_p/3600.0,1)+' / '+CstFmt(j.totals2.accesstime_p/3600.0,1)+'</td><td width="85px;">'+CstFmt(j.totals.accesses_d)+' / '+CstFmt(j.totals2.accesses_d)+'</td><td width="45px;">'+CstFmt(j.totals.accesses_p,0)+' / '+CstFmt(j.totals2.accesses_p,0)+'</td><td rowspan="2"><a href="#" onclick="javascript:closeallgraphs();"><?php print_string('closeallgraphs', 'block_relusp');?></a></td></tr><tr><td></td><td><a href="#" onclick="javascript:searchgraph(g_values1,1);" title="<?php print_string('gengraph', 'block_relusp'); print_string('daccess', 'block_relusp');?>"><img src="<?php echo $CFG->pixpath; ?>/i/stats.gif" style="border:1px solid #000;"></a></td><td><a href="#" onclick="javascript:searchgraph(g_values2,2);" title="<?php print_string('gengraph', 'block_relusp'); print_string('paccess', 'block_relusp');?>"><img src="<?php echo $CFG->pixpath; ?>/i/stats.gif" style="border:1px solid #000;"></a></td><td><a href="#" onclick="javascript:searchgraph(g_values3,3);" title="<?php print_string('gengraph', 'block_relusp'); print_string('dpermanence', 'block_relusp');?>"><img src="<?php echo $CFG->pixpath; ?>/i/stats.gif" style="border:1px solid #000;"></a></td><td><a href="#" onclick="javascript:searchgraph(g_values4,4);" title="<?php print_string('gengraph', 'block_relusp'); print_string('ppermanence', 'block_relusp');?>"><img src="<?php echo $CFG->pixpath; ?>/i/stats.gif" style="border:1px solid #000;"></a></td><td><a href="#" onclick="javascript:searchgraph(g_values5,5);" title="<?php print_string('gengraph', 'block_relusp'); print_string('dactivity', 'block_relusp');?>"><img src="<?php echo $CFG->pixpath; ?>/i/stats.gif" style="border:1px solid #000;"></a></td><td><a href="#" onclick="javascript:searchgraph(g_values6,6);" title="<?php print_string('gengraph', 'block_relusp'); print_string('pactivity', 'block_relusp');?>"><img src="<?php echo $CFG->pixpath; ?>/i/stats.gif" style="border:1px solid #000;"></a></td></tr></table></div>';
			else //quando o resultado de TOTALS = MEDIAN, inverter valores, pois TOTALS2 = MEAN
			reptable+='<div style="position:fixed;top:92%;background:#fff4c8;border:1px solid #ffcc00;width:936px;"><table style="font-size:9pt;font-weight:bold;margin:5px 3px; width:100%"><tr><td width="255px;">M&eacute;dia / Mediana</td><td width="105px;">'+CstFmt(j.totals2.logins_d)+' / '+CstFmt(j.totals.logins_d)+'</td><td width="115px;">'+CstFmt(j.totals2.logins_p,0)+' / '+CstFmt(j.totals.logins_p,0)+'</td><td width="95px;">'+CstFmt(j.totals2.accesstime_d/60.0)+' / '+CstFmt(j.totals.accesstime_d/60.0)+'</td><td width="105px;">'+CstFmt(j.totals2.accesstime_p/3600.0,1)+' / '+CstFmt(j.totals.accesstime_p/3600.0,1)+'</td><td width="85px;">'+CstFmt(j.totals2.accesses_d)+' / '+CstFmt(j.totals.accesses_d)+'</td><td width="45px;">'+CstFmt(j.totals2.accesses_p,0)+' / '+CstFmt(j.totals.accesses_p,0)+'</td><td rowspan="2"><a href="#" onclick="javascript:closeallgraphs();"><?php print_string('closeallgraphs', 'block_relusp');?></a></td></tr><tr><td></td><td><a href="#" onclick="javascript:searchgraph(g_values1,1);" title="<?php print_string('gengraph', 'block_relusp'); print_string('daccess', 'block_relusp');?>"><img src="<?php echo $CFG->pixpath; ?>/i/stats.gif" style="border:1px solid #000;"></a></td><td><a href="#" onclick="javascript:searchgraph(g_values2,2);" title="<?php print_string('gengraph', 'block_relusp'); print_string('paccess', 'block_relusp');?>"><img src="<?php echo $CFG->pixpath; ?>/i/stats.gif" style="border:1px solid #000;"></a></td><td><a href="#" onclick="javascript:searchgraph(g_values3,3);" title="<?php print_string('gengraph', 'block_relusp'); print_string('dpermanence', 'block_relusp');?>"><img src="<?php echo $CFG->pixpath; ?>/i/stats.gif" style="border:1px solid #000;"></a></td><td><a href="#" onclick="javascript:searchgraph(g_values4,4);" title="<?php print_string('gengraph', 'block_relusp'); print_string('ppermanence', 'block_relusp');?>"><img src="<?php echo $CFG->pixpath; ?>/i/stats.gif" style="border:1px solid #000;"></a></td><td><a href="#" onclick="javascript:searchgraph(g_values5,5);" title="<?php print_string('gengraph', 'block_relusp'); print_string('dactivity', 'block_relusp');?>"><img src="<?php echo $CFG->pixpath; ?>/i/stats.gif" style="border:1px solid #000;"></a></td><td><a href="#" onclick="javascript:searchgraph(g_values6,6);" title="<?php print_string('gengraph', 'block_relusp'); print_string('pactivity', 'block_relusp');?>"><img src="<?php echo $CFG->pixpath; ?>/i/stats.gif" style="border:1px solid #000;"></a></td></tr></table></div>';


			// Escreve tabela
			$('#report').html(reptable);

			// Ordena dados
			$("#results").tablesorter( {sortList: [[0,0]], widgets: ['zebra'],
							 headers: { 1: { sorter:'customfloat' },
										2: { sorter:'customfloat' }, 3: { sorter:'customfloat' },
										4: { sorter:'customfloat' } } } );
			// Bloco executando quando usuario passa o mouse sobre as atividades. Apresenta detalhes das atividades.
			$('.acts').live('mouseover', function() {
				// Posiciona bloco
				var pos = $(this).offset();
				var width = $(this).width();
				container.css({
					left: (pos.left - 250) + 'px',
					top: pos.top - 190 + 'px'
				});
				// Formata atividades
				var i = $(this).attr('i');
				var res='';
				res+='<b>ATIVIDADE: VISUALIZAÇÕES / POSTAGENS</b><br>'; 
				if ($('#act_forum').attr('checked')) {
				   res+='Fórum: '+ActFmt(j.bystudentid[i].act_forum_view)+' / ';
				   if (j.bystudentid[i].act_forum_post)
				      res+= '<span>';
				   else
				      res+= '<span '+CstAlert('ok')+'>';
				   res+= ActFmt(j.bystudentid[i].act_forum_post)+' em '+ActFmt(j.bystudentid[i].act_forum_count)+' Fóruns </span><br>';
				}
				if ($('#act_chat').attr('checked')) {
				   res+='Chat: '+ActFmt(j.bystudentid[i].act_chat_view)+' / ';
				   if (j.bystudentid[i].act_chat_post)
				      res+= '<span>';
				   else
				      res+= '<span '+CstAlert('ok')+'>';
				   res+= ActFmt(j.bystudentid[i].act_chat_post)+' em '+ActFmt(j.bystudentid[i].act_chat_count)+' Chats </span><br>';
				}
				if ($('#act_assignment').attr('checked')) {
				   res+='Tarefa: '+ActFmt(j.bystudentid[i].act_assignment_view)+' / ';
				   if (j.bystudentid[i].act_assignment_post)
				      res+= '<span>';
				   else
				      res+= '<span '+CstAlert('ok')+'>';
				   res+= ActFmt(j.bystudentid[i].act_assignment_post)+' em '+ActFmt(j.bystudentid[i].act_assignment_count)+' Tarefas </span><br>';
				}
				if ($('#act_wiki').attr('checked')) {
				   res+='Wiki: '+ActFmt(j.bystudentid[i].act_wiki_view)+' / ';
				   if (j.bystudentid[i].act_wiki_post)
				      res+= '<span>';
				   else
				      res+= '<span '+CstAlert('ok')+'>';
				   res+= ActFmt(j.bystudentid[i].act_wiki_post)+' em '+ActFmt(j.bystudentid[i].act_wiki_count)+' Wikis </span><br>';
				}
				if ($('#act_quiz').attr('checked')) {
				   res+='Questionário: '+ActFmt(j.bystudentid[i].act_quiz_view)+' / ';
				   if (j.bystudentid[i].act_quiz_post)
				      res+= '<span>';
				   else
				      res+= '<span '+CstAlert('ok')+'>';
				   res+= ActFmt(j.bystudentid[i].act_quiz_post)+' em '+ActFmt(j.bystudentid[i].act_quiz_count)+' Questionários </span><br>';
				}
				if ($('#act_book').attr('checked')) {
				   res+='Livro(Views): '+ActFmt(j.bystudentid[i].act_book_view)+' em '+ActFmt(j.bystudentid[i].act_book_count)+' Livros <br>';
				}
				if ($('#act_lesson').attr('checked')) {
				   res+='Lição: '+ActFmt(j.bystudentid[i].act_lesson_view)+' / ';
				   if (j.bystudentid[i].act_lesson_post)
				      res+= '<span>';
				   else
				      res+= '<span '+CstAlert('ok')+'>';
				   res+= ActFmt(j.bystudentid[i].act_lesson_post)+' em '+ActFmt(j.bystudentid[i].act_lesson_count)+' Lições </span><br>';
				}
				if ($('#act_questionnaire').attr('checked')) {
				   res+='Enquete: '+ActFmt(j.bystudentid[i].act_questionnaire_view)+' / ';
				   if (j.bystudentid[i].act_questionnaire_post)
				      res+= '<span>';
				   else
				      res+= '<span '+CstAlert('ok')+'>';
				   res+= ActFmt(j.bystudentid[i].act_questionnaire_post)+' em '+ActFmt(j.bystudentid[i].act_questionnaire_count)+' Enquetes </span><br>';
				}
				$('#personPopupContent').html(res);
				container.css('display', 'block');
			});
			// Bloco executando quando usuário retira o mouse das atividades. Esconde detalhes das atividades.
			$('.acts').live('mouseout', function() {
				container.css('display', 'none');
			});
			// Apresenta botão para exportar dados
			$('#export').html('<button id="exportbtn">Exportar</button>');
			$('#exportbtn').button({ icons: { primary: "ui-icon-calculator" } });
			// Exporta dados em CSV
			$("#exportbtn").click(function() {
				$("#csv").val(GenerateCSV(j));
				$("#name").val(Filename());
			});			
		}
	});		
	return false;
});
</script>

<?php
print_footer(); 
?>
