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

// Página responsável pela apresentação do Diário do Tutor

require_once('../../config.php' );
require_once('lib.php');

// Caceçalho
//print_header_simple(get_string('tutordiary', 'block_relusp'));
//hds-print Breadcrumb
$courseid = required_param('id', PARAM_INT);
$course = get_record('course','id', $courseid);
$navigation = array(
              array('name' => $course->shortname, 'link' => "{$CFG->wwwroot}/course/view.php?id=$course->id", 'type'=> 'title'),
              array('name' => get_string('tutordiary', 'block_relusp'), 'link'=>'', 'type'=>'title'),
                );
print_header_simple(get_string('tutordiary', 'block_relusp'),'', build_navigation($navigation));

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
// Cria um objeto TutorDiary para acesso a parâmetros
$tutordiary = new TutorDiary($id, $instanceid, $USER->id);
// Obtem lista de interações
$tutordiary->getInteractions();
// Obtem lista de alunos
$tutordiary->getStudents();
// Obtem lista de tutores
$tutordiary->getTutors();
// Obtem permissões de segurança
$tutordiary->canViewAll();
$tutordiary->canPost();
$tutordiary->canEdit();
$tutordiary->canDelete();
?>
<p>
<!-- // Bloco para botão de adicionar entrada no diário -->
<span id="addpr"></span>
</p>
<p align="center">
<!-- // Formulário para entrada da faixa de datas do relatório -->
<?php print_string('tutordiarymsg1', 'block_relusp');?>
</p>
<div id="adddialog"></div>
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
<td colspan="2" align="center">
<span id="tut"></span>
</td>
</tr>
<tr>
<td colspan="2" align="center">
<button id="generate"><?php print_string('showdiary', 'block_relusp');?></button>
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

<!--#hds-permitir somente numeros inteiros em TIMEDEVOTED-->
<script language=Javascript>
function isNumberKey(evt)
{
 var charCode = (evt.which) ? evt.which : event.keyCode
 if (charCode > 31 && (charCode < 48 || charCode > 57))
    return false;
 return true;
}
</script>



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

// Formulário para adicionar/editar entradas no diário do tutor
var formhtml = '<div><table id="addreporttutor">';
formhtml+='<tr><td><label><?php print_string('group', 'block_relusp');?>:</label><span id="grp"></span></td>';
formhtml+='<td align="right"><label><?php print_string('student', 'block_relusp');?>:</label><span id="std"></span></td></tr>';
formhtml+='<tr><td><label><?php print_string('interaction', 'block_relusp');?>:</label><span id="ite"></span></td>';
formhtml+='<td align="right"><label><?php print_string('timedevotedadd', 'block_relusp');?>:</label><input type="text" name="timedevoted" id="timedevoted" size="5" onkeypress="return isNumberKey(event)"></span></td></tr>';
formhtml+='<tr><td><label><?php print_string('requestdate', 'block_relusp');?>: </label><input type="text" name="reqdate" id="reqdate" size="10"></td>';
formhtml+='<td align="right"><label><?php print_string('responsedate', 'block_relusp');?>: </label><input type="text" name="respdate" id="respdate" size="10"></td></tr>';
formhtml+='<tr></tr><tr><td colspan="2"><label><?php print_string('obs', 'block_relusp');?>:</label><br><textarea id="obs" rows="4" cols="80" ></textarea></td></tr>';
formhtml+='</table></div>';

// Formulário para adicionar/editar entradas no diário do tutor
var formhtml2 = '<div><table id="editreporttutor">';
formhtml2+='<tr><td><label><?php print_string('tutor', 'block_relusp');?>:</label><span id="tutoredit"></span></td></tr>';
formhtml2+='<tr><td><label><?php print_string('group', 'block_relusp');?>:</label><span id="grp2"></span></td>';
formhtml2+='<td align="right"><label><?php print_string('student', 'block_relusp');?>:</label><span id="std2"></span></td></tr>';
formhtml2+='<tr><td><label><?php print_string('interaction', 'block_relusp');?>:</label><span id="ite2"></span></td>';
formhtml2+='<td align="right"><label><?php print_string('timedevotedadd', 'block_relusp');?>:</label><input type="text" name="timedevoted2" id="timedevoted2" size="5" onkeypress="return isNumberKey(event)"></td></tr>';
formhtml2+='<tr><td><label><?php print_string('requestdate', 'block_relusp');?>: </label><input type="text" name="reqdate2" id="reqdate2" size="10"></td>';
formhtml2+='<td align="right"><label><?php print_string('responsedate', 'block_relusp');?>: </label><input type="text" name="respdate2" id="respdate2" size="10"></td></tr>';
formhtml2+='<tr></tr><tr><td colspan="2"><label><?php print_string('obs', 'block_relusp');?>:</label><br><textarea id="obs2" rows="4" cols="80" ></textarea></td></tr>';
formhtml2+='</table></div>';

// Formulário para excluir uma nova entrada no diário
var formhtml3 = '<div><table id="deletereporttutor">';
formhtml3+='<tr><td>Deseja realmente excluir o ítem <span id="identry"></span></td></tr>';
formhtml3+='</table></div>';

/**
	* Adiciona uma nova entrada no diário via AJAX
**/
function AddTutorDiaryEntry(dlg) {
	// Obtem dados
	t_reqdate = $('#reqdate').datepicker("getDate");
	t_respdate = $('#respdate').datepicker("getDate");
	if (t_reqdate != null) {
		t_reqdate = encodeURIComponent(t_reqdate.getTime()/1000);
		t_respdate = encodeURIComponent(t_respdate.getTime()/1000);
		t_student = encodeURIComponent($('#student').val());
		t_interac = encodeURIComponent($('#interac').val());
		t_timedevoted = encodeURIComponent($('#timedevoted').val());
		t_obs = encodeURIComponent($('#obs').val());
		// Prepara URL para AJAX
		url='<?php echo $CFG->wwwroot ?>'+'/blocks/relusp/ajax_tutordiary.php?func=adddiaryentry'+
		'&id=<?php print $id; ?>&instanceid=<?php print $instanceid; ?>'+
		'&reqdate='+t_reqdate+'&respdate='+t_respdate+'&student='+t_student+'&interac='+t_interac+'&timedevoted='+t_timedevoted+'&obs='+t_obs;
		$waitdlg.dialog('open');
		// Invoca via AJAX a criação de uma nova entrada
		$.getJSON(url, function(j){
			if (j) {
				$("#generate").click();
				$('#timedevoted').val('');
				$('#reqdate').datepicker( "setDate" , "-1" );
				$('#respdate').datepicker( "setDate" , "-1" );
				$('#obs').val('');
				$waitdlg.dialog('close');

				var r=confirm("O registro foi incluído com sucesso! \n Deseja incluir outro registro?");
				if (r==false)
				   $dialog.dialog('close');
			} else
				alert('<?php print_string('newentryerror', 'block_relusp');?>');	
		});			
	} else {
		alert('<?php print_string('choosereqdate', 'block_relusp');?>');
	}
}

/**
	* Editar uma nova entrada no diário via AJAX
**/
function EditTutorDiaryEntry(dlg) {
	// Obtem dados
	t_reqdate = $('#reqdate2').datepicker("getDate");
	t_respdate = $('#respdate2').datepicker("getDate");
	if (t_reqdate != null) {
		t_reqdate = encodeURIComponent(t_reqdate.getTime()/1000);
		t_respdate = encodeURIComponent(t_respdate.getTime()/1000);
		t_tutor = encodeURIComponent($('#tutor2').val());
		t_student = encodeURIComponent($('#student2').val());
		t_interac = encodeURIComponent($('#interac2').val());
		t_timedevoted = encodeURIComponent($('#timedevoted2').val());
		t_obs = encodeURIComponent($('#obs2').val());
		// Prepara URL para AJAX
		url='<?php echo $CFG->wwwroot ?>'+'/blocks/relusp/ajax_tutordiary.php?func=editdiaryentry2'+
		'&id=<?php print $id; ?>&instanceid=<?php print $instanceid; ?>'+
		'&reqdate='+t_reqdate+'&respdate='+t_respdate+'&tutor='+t_tutor+'&student='+t_student+'&interac='+t_interac+'&timedevoted='+t_timedevoted+'&obs='+t_obs+'&identry='+$identry;

		$waitdlg.dialog('open');
		// Invoca via AJAX a edicao de uma nova entrada
		$.getJSON(url, function(j){
			if (j) {
			   $waitdlg.dialog('close');
			   $("#generate").click(); //atualiza lista
			} else
			   alert('<?php print_string('newentryerror', 'block_relusp');?>');	
		});			
	} else {
		alert('<?php print_string('choosereqdate', 'block_relusp');?>');
	}
}

/**
	* Exclui uma nova entrada no diário via AJAX
**/
function DeleteTutorDiaryEntry(dlg) {

	// Prepara URL para AJAX
	url='<?php echo $CFG->wwwroot ?>'+'/blocks/relusp/ajax_tutordiary.php?func=deletediaryentry'+
	'&id=<?php print $id; ?>&instanceid=<?php print $instanceid; ?>'+
	'&identry='+$identry;

	$waitdlg.dialog('open');
	// Invoca via AJAX a exclusao de uma nova entrada
	$.getJSON(url, function(j){
		if (j) {
		   $waitdlg.dialog('close');
		   $dialog3.dialog('close');
		   closedialog('personPopupContainer'); //fecha box onmouseover
		   $("#generate").click(); //atualiza lista
		} else
		   alert('<?php print_string('newentryerror', 'block_relusp');?>');	
	});			
}

/**
	* Verifica se deve retornar o estilo de alarme (vermelho)
**/
function DateOK(value) {
	if (!value)
		return ' style="color: red"';
	else
		return '';
}

/**
	* Estende a JQUERY com uma função para obter as variáveis passadas na URL
**/
$.extend({
  getUrlVars: function(){
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for(var i = 0; i < hashes.length; i++)
    {
      hash = hashes[i].split('=');
      vars.push(hash[0]);
      vars[hash[0]] = hash[1];
    }
    return vars;
  },
  getUrlVar: function(name){
    return $.getUrlVars()[name];
  }
});

// Cria numa variável com a caixa de diálogo do formulário de nova entrada no diário
var $dialog = $(formhtml)
	.dialog({
		autoOpen: false,  // Oculto inicialmente
		title: '<?php print_string('insertnewentry', 'block_relusp');?>',
		width: 650,
		height: 380,
		buttons: [
		{	text: "<?php print_string('insert', 'block_relusp');?>",
			click: function() { AddTutorDiaryEntry(this); }
		}, 
		{	text: "<?php print_string('cancel', 'block_relusp');?>",
			click: function() { $(this).dialog("close"); }
		}, 
		]		
	});

// Cria numa variável com a caixa de diálogo do formulário para editar entrada no diário
var $dialog2 = $(formhtml2)
	.dialog({
		autoOpen: false,  // Oculto inicialmente
		title: '<?php print_string('editentry', 'block_relusp');?>',
		width: 650,
		height: 380,
		buttons: [
		{	text: "<?php print_string('edit', 'block_relusp');?>",
			click: function() { EditTutorDiaryEntry(this); }
		}, 
		{	text: "<?php print_string('cancel', 'block_relusp');?>",
			click: function() { $(this).dialog("close"); }
		}, 
		]		
	});

// Cria numa variável com a caixa de diálogo do formulário para excluir entrada no diário
var $dialog3 = $(formhtml3)
	.dialog({
		autoOpen: false,  // Oculto inicialmente
		title: '<?php print_string('deleteentry', 'block_relusp');?>',
		width: 350,
		height: 120,
		buttons: [
		{	text: "<?php print_string('delete', 'block_relusp');?>",
			click: function() { DeleteTutorDiaryEntry(this); }
		}, 
		{	text: "<?php print_string('cancel', 'block_relusp');?>",
			click: function() { $(this).dialog("close"); }
		}, 
		]		
	});

// Criea numa variável a caixa de diálogo de espera no processamento do relatório
var $waitdlg = $('<div></div>')
	.html('<div align="center"><img style="vertical-align:middle;" src="<?php echo $CFG->wwwroot ?>'+'/blocks/relusp/loading.gif"> <?php print_string('processing', 'block_relusp');?></div>')
	.dialog({
		autoOpen: false,
		modal: true,
		title: '<?php print_string('processing', 'block_relusp');?>',
		resizable: false,
		height: 100,
		width: 250
	});
	
var lastgroup=0;	// último grupo selecionado
var lastgroup2=0;	// último grupo selecionado(edit)
var laststudent=0;	// último aluno selecionado

// Verifica se já foram passadas datas para geração automática do relatório
var from = $.getUrlVar('from');
var to = $.getUrlVar('to');
var tutorid = $.getUrlVar('tutorid');

/**
	* Limpa a string passada retirando aspas duplas
**/
function Clean(value) {
	return value.replace('"','').replace("\n", '');
}
/**
	* Gera um nome de arquivo para exportaçõa do CSV baseado nas datas e tipo de relatório
**/
function Filename() {
	f = $('#from').datepicker("getDate");
	t = $('#to').datepicker("getDate");
	return 'tutordiary_'+f.getDate()+'-'+(f.getMonth()+1)+'-'+f.getFullYear()+'_'+t.getDate()+'-'+(t.getMonth()+1)+'-'+t.getFullYear()+'.csv';
}
/**
	* Verifica se será apresentado OK ou PROBLEMA no alarme
**/
function AlFmt(value) {
	if (value)
		return "OK";
	else
		return "Problema";
}

/**
	* Gera o arquivo CSV numa string para exportação
**/
function GenerateCSV(data) {
	result = new Array();
	result.push('<?php print_string('headertutordiary', 'block_relusp');?>');
	result.push("\n");
	for (var i=0; i<data.length; i++) {
		result.push('"'+Clean(data[i].tutor)+'";');
		//hds - opcao de nenhum estudante : se for Guest User=nenhum (declarado linha 317)
		if (data[i].studentid==1)
		   result.push('"------";');
		else
		   result.push('"'+Clean(data[i].student)+'";');
		result.push('"'+FormatDate(data[i].timemodified)+'";');
		result.push('"'+FormatDate(data[i].requestdate)+'";');
		result.push('"'+FormatDate(data[i].responsedate)+'";');
		result.push('"'+Clean(data[i].interaction)+'";');
		result.push('"'+Clean(data[i].timedevoted)+'";');
		result.push('"'+Clean(data[i].notes)+'";');
		result.push('"'+AlFmt(data[i].ok)+'";\n');
	}
	return result.join('');
}

// Bloco de inicialização executado quando a página é carregada
$(document).ready(function() {
	// Cria os objetos DatePicker e fixa as datas iniciais.
	$('#from').datepicker({  constrainInput: true, showOn: 'button', buttonImageOnly: true, 
				maxDate: 'today', buttonImage: '<?php print $CFG->wwwroot ?>/blocks/relusp/calendar.gif' });
	$('#to').datepicker({  constrainInput: true, showOn: 'button', buttonImageOnly: true, 
				maxDate: 'today', buttonImage: '<?php print $CFG->wwwroot ?>/blocks/relusp/calendar.gif' });
	$('#reqdate').datepicker({  constrainInput: true, showOn: 'button', buttonImageOnly: true, 
				maxDate: 'today', buttonImage: '<?php print $CFG->wwwroot ?>/blocks/relusp/calendar.gif' });
	$('#respdate').datepicker({  constrainInput: true, showOn: 'button', buttonImageOnly: true, 
				maxDate: 'today', buttonImage: '<?php print $CFG->wwwroot ?>/blocks/relusp/calendar.gif' });
	$('#reqdate2').datepicker({  constrainInput: true, showOn: 'button', buttonImageOnly: true, 
				maxDate: 'today', buttonImage: '<?php print $CFG->wwwroot ?>/blocks/relusp/calendar.gif' });
	$('#respdate2').datepicker({  constrainInput: true, showOn: 'button', buttonImageOnly: true, 
				maxDate: 'today', buttonImage: '<?php print $CFG->wwwroot ?>/blocks/relusp/calendar.gif' });
	$('#from').datepicker( "setDate" , "-7" );
	$('#to').datepicker( "setDate" , "today" );				
	$('#reqdate').datepicker( "setDate" , "-1" );
	$('#respdate').datepicker( "setDate" , "-1" );
	// Cria o objeto tablesorter com um tipo da dado particularizado para ordenação de datas no formato brasileiro
	$.tablesorter.addParser({ 
		id: 'customdate', 
		is: function(s) { 
			return false; 
		}, 
		format: function(s) {
			var fs=s.indexOf("/");
			var ss=s.indexOf("/", fs+1);
			var newDate=new Date(parseInt(s.substring(ss+1)), parseInt(s.substring(fs+1, ss))-1, parseInt(s.substring(0, fs)), 0, 0, 0, 0);
			return (newDate.getTime()/1000); 
		}, 
		type: 'numeric' 
	}); 				
	// Adiciona botão para gerar relatório
	$('#generate').button({ icons: { primary: "ui-icon-calculator" } });

	// Cria container para mensagens das atividades
	$('body').append(container);	

	// Mostra lista de tutores se tem permissão
	if (canViewAll)
		UpdateTutors();
	// Se tem permissão de postar, prepara formulário e ativa botão
	if (canPost) {
		$('#addpr').html('<button id="add"><?php print_string('insertnewentry', 'block_relusp');?></button>');
		$('#add').button({ icons: { primary: "ui-icon-document" } });
		$("#add").click(function() {
			UpdateGroups();
			UpdateStudents();
			UpdateInteractions();
			$('#group').attr("selectedIndex", lastgroup);
			$('#obs').val('');
			$('#group').change(function() {
				lastgroup=$(this).attr("selectedIndex");
				UpdateStudents();
			});
			$dialog.dialog('open');
			return false;
		});
	}
	// Se parâmetros para geração do relatório já foram passados, acione a geração
	if (from && to && tutorid) {
		n_from=new Date(from*1000);
		n_to=new Date(to*1000);
		$('#from').datepicker("setDate", n_from);
		$('#to').datepicker("setDate", n_to);
		for (i=0; i<$('#tutor').attr('options').length; i++) {
			if (parseInt(($('#tutor').attr('options')[i]).value)==parseInt(tutorid))
				$('#tutor').attr('selectedIndex', i);
		}
		$("#generate").trigger(new jQuery.Event("click"));
	}
});

/**
	* Gera ou atualiza a lista de alunos - ADD
**/
function UpdateStudents() {
	//hds - opcao de nenhum estudante : http://redmine.atp.usp.br/issues/272
	var options='<option value="1">--Nenhum--</option>'; //valor=1 eh o id Guest User default do moodle.Necessario user no mdl_user(lib.php:490)
	for (var i=0; i<groups[lastgroup].students.length; i++) {
		options+='<option value="'+groups[lastgroup].students[i].id+'">'+groups[lastgroup].students[i].name+'</option>';
	}
	$('#std').html('<select name="student" id="student">'+options+'</select>');
}
/**
	* Gera ou atualiza a lista de alunos - EDIT (replicado para nao interferir no boxADDentries)
**/
function UpdateStudents2() {
	var options2='<option value="1">--Nenhum--</option>'; //valor=1 eh o id Guest User default do moodle.Necessario user no mdl_user(lib.php:490)
	for (var i=0; i<groups[lastgroup2].students.length; i++) {
		options2+='<option value="'+groups[lastgroup2].students[i].id+'">'+groups[lastgroup2].students[i].name+'</option>';
	}
	$('#std2').html('<select name="student2" id="student2">'+options2+'</select>');

}
/**
	* Gera ou atualiza a lista de grupos - ADD
**/
function UpdateGroups() {
	var options='';
	for (var i=0; i<groups.length; i++) {
		options+='<option>'+groups[i].name+'</option>';
	}
	$('#grp').html('<select name="group" id="group">'+options+'</select>');
}
/**
	* Gera ou atualiza a lista de grupos - EDIT (replicado para nao interferir no boxADDentries)
**/
function UpdateGroups2() {
	var options2='';
	for (var i=0; i<groups.length; i++) {
		options2+='<option>'+groups[i].name+'</option>';
	}
	$('#grp2').html('<select name="group2" id="group2">'+options2+'</select>');
}
/**
	* Gera ou atualiza a lista de tutores
**/
function UpdateTutors() {
	var options='<option value="0">--Todos--</option>';
	for (var i=0; i<tutors.length; i++) {
		options+='<option value="'+tutors[i].id+'">'+tutors[i].name+'</option>';
	}
	$('#tut').html('<br>Tutor: <select name="tutor" id="tutor">'+options+'</select><br><br>');
}
/**
	* Gera ou atualiza a lista de tutores para caixa de edicao das Entradas
**/
function UpdateTutors2() {
	var options='';
	for (var i=0; i<tutors.length; i++) {
		options+='<option value="'+tutors[i].id+'">'+tutors[i].name+'</option>';
	}
	$('#tutoredit').html('<select name="tutor2" id="tutor2" <?php $tutordiary->canChangeTutor(); ?>>'+options+'</select>');
}
/**
	* Gera ou atualiza a lista de interações
**/
function UpdateInteractions() {
	var options='';
	for (var i=0; i<interactions.length; i++) {
		options+='<option value="'+interactions[i].id+'">'+interactions[i].interaction+'</option>';
	}
	$('#ite').html('<select name="interac" id="interac">'+options+'</select>');
}

/**
	* Editar entradas dos tutores
**/
function EditEntry(identry) {

	//IDentry instanciado
	$identry=identry; 

	closedialog('personPopupContainer'); //fecha box onmouseover
	UpdateTutors2();
	UpdateGroups2();
	UpdateStudents2();
	$('#group2').attr("selectedIndex", lastgroup2);
	$('#group2').change(function() {
		     lastgroup2=$(this).attr("selectedIndex");
		     UpdateStudents2();
	});
	$dialog2.dialog('open');

	//buscar informacoes da entrada
	// Prepara URL para AJAX
	url='<?php echo $CFG->wwwroot ?>'+'/blocks/relusp/ajax_tutordiary.php?func=editdiaryentry1'+'&id=<?php print $id; ?>&instanceid=<?php print $instanceid; ?>'+'&identry='+identry;
	$waitdlg.dialog('open');

	// Invoca via AJAX para editar entrada-getData
	$.getJSON(url, function(j){
		if (j) {
		   // Fecha janela de processamento
		   $waitdlg.dialog('close');

		   //Plotar no formulario resultado deste IDentry
		   for (var i=0; i<j.length; i++) {

			//Tutor que incluiu a entrada, manter o tutor na edicao
			$('#tutor2').attr("value",j[i].tutorid);

			//GROUP
			for (var z=0; z<groups.length; z++) {
			  for (var k=0; k<groups[z].students.length; k++) {
			    if (groups[z].students[k].id==j[i].studentid){
				$('#group2').attr("selectedIndex", z);
				lastgroup2=z;
				UpdateStudents2();
				$('#student2').attr("selectedIndex", k+1);
				break;break;}

			  }
			}

			//INTERACTIONS
			var options='';
			for (var k=0; k<interactions.length; k++) {
			   if (interactions[k].id==j[i].interactionid)
			      options+='<option selected value="'+interactions[k].id+'">'+interactions[k].interaction+'</option>';
			   else
			      options+='<option value="'+interactions[k].id+'">'+interactions[k].interaction+'</option>';
			}
			$('#ite2').html('<select name="interac2" id="interac2">'+options+'</select>');

			//RequestDate
			$('#reqdate2').attr("value",FormatDate(j[i].requestdate));
			//ResponseDate
			$('#respdate2').attr("value",FormatDate(j[i].responsedate));

			$('#timedevoted2').attr("value",j[i].timedevoted);
			$('#obs2').attr("value",j[i].notes);
		   }
		} else
		   alert('<?php print_string('newentryerror', 'block_relusp');?>');	
	});
}


/**
	* Deleta entradas dos tutores
**/
function DeleteEntry(identry) {

	//IDentry instanciado
	$identry=identry; 

	$dialog3.dialog('open'); //abre box para confirmar exclusao
	$('#identry').html('#'+identry+'');

}


/**
	* Formata data na forma brasileira
**/
function FormatDate(ts) {
	var newdate=new Date(ts*1000);
	return newdate.getDate()+'/'+(newdate.getMonth()+1)+'/'+newdate.getFullYear();
}
/* Close Dialog Onmouseover*/
function closedialog(objName){
	var obj = document.getElementById(objName);
	obj.style.display = "none"; 
	return;
}
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
	// Se tem permissão de ver outros tutores, seleciona o tutor
	if (canViewAll)
		var tutorid = $('#tutor').val();
	else
		var tutorid=<?php echo $USER->id; ?>;
	// Prepara execução de AJAX
	url='<?php echo $CFG->wwwroot ?>'+'/blocks/relusp/ajax_tutordiary.php?func=repdiary'+
		'&id=<?php print $id; ?>&instanceid=<?php print $instanceid; ?>'+
		'&from='+encodeURIComponent(t_from)+'&to='+encodeURIComponent(t_to)+'&tutorid='+encodeURIComponent(tutorid);
//	$('#debug').html(url);
	$waitdlg.dialog('open');
	$("#report").ajaxError(function(event, request, settings){
		$(this).html("<li><?php print_string('errorprocessing', 'block_relusp');?> " + settings.url + "</li><br>"+request.responseText);
		$waitdlg.dialog('close');
	});
	// Executa AJAX para geração do relatório
	$.getJSON(url, function(j){
		if (j) {
			$Z = j;
			// Fecha janela de processamento
			$waitdlg.dialog('close');
			// Monta cabeçalhos da tabela do relatório
			reptable='<table id="results" class="tablesorter"><thead><tr>';
			reptable+='<th><?php print_string('tutor', 'block_relusp');?></th>';
			reptable+='<th><?php print_string('student', 'block_relusp');?></th>';
			reptable+='<th><?php print_string('timemodified', 'block_relusp');?></th>';
			reptable+='<th><?php print_string('requestdate', 'block_relusp');?></th>';
			reptable+='<th><?php print_string('responsedate', 'block_relusp');?></th>';
			reptable+='<th><?php print_string('interaction', 'block_relusp');?></th>';
			reptable+='<th><?php print_string('timedevotedview', 'block_relusp');?></th>';
			reptable+='<th><?php print_string('notes', 'block_relusp');?></th>';
			if (canEdit || canDelete)
 			   reptable+='<th><?php print_string('actions', 'block_relusp');?></th>';
			reptable+='</tr></thead><tbody>';

			// Totalizador de tempo dedicado
			var counter=0;
			var courseid='<?php echo $courseid; ?>';
			// Preenche os dados retornados
			for (var i=0; i<j.length; i++) {
				reptable+='<tr>';
				reptable+='<td><a href="../../course/user.php?&id='+courseid+'&user='+j[i].tutorid+'&mode=alllogs" target="_blank">'+j[i].tutor+'</a></td>';
				//hds - opcao de nenhum estudante : se for Guest User=nenhum (declarado linha 317)
				if (j[i].studentid==1)
					reptable+='<td>-----</td>';
				else
					reptable+='<td><a href="../../course/user.php?&id='+courseid+'&user='+j[i].studentid+'&mode=alllogs" target="_blank">'+j[i].student+'</a></td>';
				reptable+='<td>'+FormatDate(j[i].timemodified)+'</td>';
				reptable+='<td>'+FormatDate(j[i].requestdate)+'</td>';
				reptable+='<td'+DateOK(j[i].ok)+'>'+FormatDate(j[i].responsedate)+'</td>';
				reptable+='<td>'+j[i].interaction+'</td>';
				reptable+='<td>'+j[i].timedevoted+'</td>';
				reptable+='<td class="notes">'+j[i].notes+'</td>';
				if (canEdit || canDelete)
				   reptable+='<td><div id="actionbox" style="display:block; cursor:pointer;text-align:center;background:#FFF;" class="acts" i="'+i+'"><img src="<?php echo $CFG->pixpath; ?>/t/preview.gif"></div></td>';
				reptable+='</tr>';
				timedevotedcount= parseFloat(j[i].timedevoted);
				counter= parseFloat(counter + timedevotedcount);
			}
			reptable+='</tbody><tfooter><tr><td></td><td></td><td></td><td></td><td></td><td></td><td>';
			reptable+='<b>'+counter+'(min)</b>'; //totalizador de tempo dedicado
			reptable+='</td><td></td></tr></tfooter></table>';
			// Escreve tabela
			$('#report').html(reptable);
			// Ordena dados
			$("#results").tablesorter( {sortList: [[0,0]], widgets: ['zebra'],
							headers: { 2: { sorter:'customdate' }, 3: { sorter:'customdate' } } } ); 
			// Editar/excluir entrar diario do tutor ao passar o mouse
			$('.acts').live('mouseover', function() {
				// Posiciona bloco
				var pos = $(this).offset();
				var width = $(this).width();
				container.css({
					left: (pos.left - 350) + 'px',
					top: pos.top - 190 + 'px'
				});
				// Formata atividades
				var i = $(this).attr('i');
				var res='';
				res+='<b><?php print_string('onmouseentry', 'block_relusp');?> #'+$Z[i].id+' ?</b><br><br>';
				res+='<?php print_string('postedby', 'block_relusp');?>: <b>'+$Z[i].tutor+'</b><br>';
				res+='<?php print_string('forstudent', 'block_relusp');?>: <b>';
				if ($Z[i].studentid==1)
				   res+='-----';
				else
				   res+= $Z[i].student;
				res+='</b><br>';

				res+='<?php print_string('interaction', 'block_relusp');?>: <b>'+$Z[i].interaction+'</b><br>';
				res+='<?php print_string('lastmodified', 'moodle');?>: <b>'+FormatDate($Z[i].timemodified)+'</b><br>';
				//actions
				res+='<table id="tableactions">';
				res+='<tr>';
				if (canEdit)
				   res+='<td><a style="cursor:pointer" onclick="javascript:EditEntry('+$Z[i].id+');"><img src="<?php echo $CFG->pixpath; ?>/t/edit.gif"> <?php print_string('edit', 'moodle');?></a></td>';
				if (canDelete)
				   res+='<td><a style="cursor:pointer" onclick="javascript:DeleteEntry('+$Z[i].id+');"><img src="<?php echo $CFG->pixpath; ?>/t/delete.gif"> <?php print_string('delete', 'moodle');?></a></td>';
				res+='</tr></table>';
				res+='<div id="closeactions"><a style="cursor:pointer" onclick="javascript:closedialog(\'personPopupContainer\');">Fechar</a></div>';

				$('#personPopupContent').html(res);
				container.css('display', 'block');
			});
// Bloco executando quando usuário retira o mouse das atividades. Esconde detalhes das atividades.
//			$('.acts').live('mouseout', function() {
//				container.css('display', 'none');
//				setTimeout("closedialog('personPopupContainer')",5000); // 5 seconds after user (re)load the page
//			});	
	
			// Apresenta botão para exportar dados
			$('#export').html('<button id="exportbtn">Exportar</button>');
			$('#exportbtn').button({ icons: { primary: "ui-icon-calculator" } });
			// Exporta dados em CSV
			$("#exportbtn").click(function() {
				$("#csv").val(GenerateCSV(j));
				$("#name").val(Filename());
			});							
		} else
			$waitdlg.dialog('close');		
	});		
	return false;
});
</script>

<?php
print_footer(); 
?>
