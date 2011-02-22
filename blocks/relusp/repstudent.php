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
function CstFmt(value, ndigits) {
  if(ndigits==null) {
    ndigits=2;
  }
  return (''+value.toFixed(ndigits)).replace('.',',');
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
var $dialog = $('<div></div>')
	.html('<div align="center"><img style="vertical-align:middle;" src="<?php echo $CFG->wwwroot ?>'+'/blocks/relusp/loading.gif"> <?php print_string('processing', 'block_relusp');?></div>')
	.dialog({
		autoOpen: false,
		modal: true,
		title: '<?php print_string('processing', 'block_relusp');?>',
		resizable: false,
		height: 100,
		width: 250
	});
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
	$dialog.dialog('open');
	$("#report").ajaxError(function(event, request, settings){
		$(this).html("<li><?php print_string('errorprocessing', 'block_relusp');?> " + settings.url + "</li><br>"+request.responseText);
		$dialog.dialog('close');
	});
	// Executa AJAX para geração do relatório	
	$.getJSON(url, function(j){
		if (j) {
			// Fecha janela de processamento
			$dialog.dialog('close');
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
			}
			reptable+='</tbody></table>';

			//hds-Cabeçalho flutuante da tabela
			reptable+='<div style="position:fixed;top:0;background:#fff4c8;border:1px solid #ffcc00;width:936px;"><table style="font-size:9pt;font-weight:bold;margin:5px 3px; width:100%;"><tr><td width="295px;"><?php print_string('student', 'block_relusp');?></td><td width="95px;"><?php print_string('daccess', 'block_relusp');?></td><td width="115px;"><?php print_string('paccess', 'block_relusp');?></td><td width="95px;"><?php print_string('dpermanence', 'block_relusp');?></td><td width="105px;"><?php print_string('ppermanence', 'block_relusp');?></td><td width="85px;"><?php print_string('dactivity', 'block_relusp');?></td><td width="95px"><?php print_string('pactivity', 'block_relusp');?></td><td><?php print_string('activities', 'block_relusp');?></td></tr></table></div>';

			//hds-Linha com media dos valores
			//Confere se o parametro para calcular e MEAN ou MEDIAN, para inverter posicionamento dos valores
			if (j.totals2.modocalc == 'MEDIAN') //quando o resultado de TOTALS = MEAN
			  reptable+='<div style="position:fixed;top:95%;background:#fff4c8;border:1px solid #ffcc00;width:936px;"><table style="font-size:9pt;font-weight:bold;margin:5px 3px; width:100%"><tr><td width="275px;">M&eacute;dia / Mediana</td><td width="95px;">'+CstFmt(j.totals.logins_d)+' / '+CstFmt(j.totals2.logins_d)+'</td><td width="115px;">'+CstFmt(j.totals.logins_p,0)+' / '+CstFmt(j.totals2.logins_p,0)+'</td><td width="95px;">'+CstFmt(j.totals.accesstime_d/60.0)+' / '+CstFmt(j.totals2.accesstime_d/60.0)+'</td><td width="105px;">'+CstFmt(j.totals.accesstime_p/3600.0,1)+' / '+CstFmt(j.totals2.accesstime_p/3600.0,1)+'</td><td width="85px;">'+CstFmt(j.totals.accesses_d)+' / '+CstFmt(j.totals2.accesses_d)+'</td><td width="95px;">'+CstFmt(j.totals.accesses_p,0)+' / '+CstFmt(j.totals2.accesses_p,0)+'</td><td></td></tr></table></div>';
			else //quando o resultado de TOTALS = MEDIAN, inverter valores, pois TOTALS2 = MEAN
			reptable+='<div style="position:fixed;top:95%;background:#fff4c8;border:1px solid #ffcc00;width:936px;"><table style="font-size:9pt;font-weight:bold;margin:5px 3px; width:100%"><tr><td width="275px;">M&eacute;dia / Mediana</td><td width="95px;">'+CstFmt(j.totals2.logins_d)+' / '+CstFmt(j.totals.logins_d)+'</td><td width="115px;">'+CstFmt(j.totals2.logins_p,0)+' / '+CstFmt(j.totals.logins_p,0)+'</td><td width="95px;">'+CstFmt(j.totals2.accesstime_d/60.0)+' / '+CstFmt(j.totals.accesstime_d/60.0)+'</td><td width="105px;">'+CstFmt(j.totals2.accesstime_p/3600.0,1)+' / '+CstFmt(j.totals.accesstime_p/3600.0,1)+'</td><td width="85px;">'+CstFmt(j.totals2.accesses_d)+' / '+CstFmt(j.totals.accesses_d)+'</td><td width="95px;">'+CstFmt(j.totals2.accesses_p,0)+' / '+CstFmt(j.totals.accesses_p,0)+'</td><td></td></tr></table></div>';


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
