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

// Página responsável pela apresentação do Relatório de Tutores

require_once('../../config.php' );
require_once($CFG->libdir.'/blocklib.php' );

// Caceçalho
//print_header_simple(get_string('reptutor', 'block_relusp'))
//hds-print Breadcrumb
$courseid = required_param('id', PARAM_INT);
$course = get_record('course','id', $courseid);
$navigation = array(
              array('name' => $course->shortname, 'link' => "{$CFG->wwwroot}/course/view.php?id=$course->id", 'type'=> 'title'),
              array('name' => get_string('reptutor', 'block_relusp'), 'link'=>'', 'type'=>'title'),
                );
print_header_simple(get_string('reptutor', 'block_relusp'),'', build_navigation($navigation));

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
?>
<!-- // Formulário para entrada da faixa de datas do relatório -->
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
/**
	* Formata o valor numérico para uma casa decimal convertendo ponto para vírgula.
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
	return 'reptutor_'+f.getDate()+'-'+(f.getMonth()+1)+'-'+f.getFullYear()+'_'+t.getDate()+'-'+(t.getMonth()+1)+'-'+t.getFullYear()+'.csv';
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
	result.push('<?php print_string('headerreptutor', 'block_relusp');?>');
	result.push("\n");
	for (var i in data.bytutorid){
		result.push('"'+Clean(data.bytutorid[i].name)+'";');
		result.push('"'+CstFmt(data.bytutorid[i].logins_d)+'";');
		result.push('"'+CstFmt(data.totals.logins_d)+'";');
		result.push('"'+CstFmt(data.bytutorid[i].logins_p)+'";');
		result.push('"'+CstFmt(data.totals.logins_p)+'";');
		result.push('"'+CstFmt(data.bytutorid[i].accesstime_d/3600.0)+'";');
		result.push('"'+CstFmt(data.totals.accesstime_d/3600.0)+'";');
		result.push('"'+CstFmt(data.bytutorid[i].accesstime_p/3600.0)+'";');
		result.push('"'+CstFmt(data.totals.accesstime_p/3600.0)+'";');
		result.push('"'+CstFmt(data.bytutorid[i].accesses_d)+'";');
		result.push('"'+CstFmt(data.totals.accesses_d)+'";');
		result.push('"'+CstFmt(data.bytutorid[i].accesses_p)+'";');
		result.push('"'+CstFmt(data.totals.accesses_p)+'";');
		result.push('"'+AlFmt(data.bytutorid[i].al_tutordiary)+'";');
		result.push('"'+AlFmt(!data.bytutorid[i].al_logins_d)+'";');
		result.push('"'+AlFmt(!data.bytutorid[i].al_logins_p)+'";');
		result.push('"'+AlFmt(!data.bytutorid[i].al_accesstime_d)+'";');
		result.push('"'+AlFmt(!data.bytutorid[i].al_accesstime_p)+'";');
		result.push('"'+AlFmt(!data.bytutorid[i].al_accesses_d)+'";');
		result.push('"'+AlFmt(!data.bytutorid[i].al_accesses_p)+'"\n');
	}
	return result.join('');
}

// Cria numa variável a caixa de diálogo de espera no processamento do relatório
var $dialog = $('<div></div>')
	.html('<div align="center"><img style="vertical-align:middle;" src="<?php echo $CFG->wwwroot ?>'+'/blocks/relusp/loading.gif"> <?php print_string('processing', 'block_relusp');?></div>')
	.dialog({
		autoOpen: false,
		modal: true,
		title: 'Processando...',
		resizable: false,
		height: 100,
		width: 250
	});
// Iniciliza variáveis de datas
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
          //return (s.substr(0, s.indexOf("/"))).replace(',', '.'); 
	  return s.replace(',', '.'); 
        }, 
        type: 'numeric' 
    });
	// Adiciona botão para gerar relatório
	$('#generate').button({ icons: { primary: "ui-icon-calculator" } });
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
	// Prepara execução de AJAX
	url='<?php echo $CFG->wwwroot ?>'+'/blocks/relusp/ajax_reptutor.php?func=reptutor'+
		'&id=<?php print $id; ?>&instanceid=<?php print $instanceid; ?>'+
		'&from='+encodeURIComponent(t_from)+'&to='+encodeURIComponent(t_to);
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
			reptable='<table id="results" class="tablesorter"><thead><tr><th width="200px"><?php print_string('tutor', 'block_relusp');?></th>';
			reptable+='<th width="80px"><?php print_string('daccess', 'block_relusp');?></th><th width="100px"><?php print_string('paccess', 'block_relusp');?></th>';
			reptable+='<th width="80px"><?php print_string('dpermanence', 'block_relusp');?></th><th width="110px"><?php print_string('ppermanence', 'block_relusp');?></th>';
			reptable+='<th width="100px"><?php print_string('dactivity', 'block_relusp');?></th><th width="100px"><?php print_string('pactivity', 'block_relusp');?></th>';
			reptable+='<th><?php print_string('tutordiary', 'block_relusp');?></th></tr></thead><tbody>';
			// Preenche os dados retornados
			for (var i in j.bytutorid){
				reptable+='<tr><td><a href="../../course/user.php?&id='+j.bytutorid[i].course+'&user='+j.bytutorid[i].id+'&mode=alllogs" target="_blank">'+j.bytutorid[i].name+'</a></td>';
				reptable+='<td'+CstAlert(j.bytutorid[i].al_logins_d)+'>'+CstFmt(j.bytutorid[i].logins_d)+'</td>';
				reptable+='<td'+CstAlert(j.bytutorid[i].al_logins_p)+'>'+CstFmt(j.bytutorid[i].logins_p,0)+'</td>';
				reptable+='<td'+CstAlert(j.bytutorid[i].al_accesstime_d)+'>'+CstFmt(j.bytutorid[i].accesstime_d/60.0)+'</td>';
				reptable+='<td'+CstAlert(j.bytutorid[i].al_accesstime_p)+'>'+CstFmt(j.bytutorid[i].accesstime_p/3600.0,1)+'</td>';
				reptable+='<td'+CstAlert(j.bytutorid[i].al_accesses_d)+'>'+CstFmt(j.bytutorid[i].accesses_d)+'</td>';
				reptable+='<td'+CstAlert(j.bytutorid[i].al_accesses_p)+'>'+CstFmt(j.bytutorid[i].accesses_p,0)+'</td>';
				// Prepara URL para diários do tutor
				url= '<?php echo $CFG->wwwroot ?>'+'/blocks/relusp/tutordiary.php?'+'tutorid='+i+
				'&id=<?php print $id; ?>&instanceid=<?php print $instanceid; ?>'+
				'&from='+encodeURIComponent(t_from)+'&to='+encodeURIComponent(t_to);				
				if (j.bytutorid[i].al_tutordiary)
					reptable+='<td><a class="oklink" href="'+url+'" target="_blank"><?php print_string('ok', 'block_relusp');?></a></td>';
				else
					reptable+='<td><a class="errorlink" href="'+url+'" target="_blank"><?php print_string('problem', 'block_relusp');?></a></td>';
					
				reptable+='</tr>';
			}
			reptable+='</tbody></table>';

			//hds-Cabeçalho flutuante da tabela
			reptable+='<div style="position:fixed;top:0;background:#fff4c8;border:1px solid #ffcc00;width:936px;"><table style="font-size:9pt;font-weight:bold;margin:5px 3px; width:100%;"><tr><td width="210px;"><?php print_string('tutor', 'block_relusp');?></td><td width="85px;"><?php print_string('daccess', 'block_relusp');?></td><td width="105px;"><?php print_string('paccess', 'block_relusp');?></td><td width="85px;"><?php print_string('dpermanence', 'block_relusp');?></td><td width="115px;"><?php print_string('ppermanence', 'block_relusp');?></td><td width="105px;"><?php print_string('dactivity', 'block_relusp');?></td><td width="105px"><?php print_string('pactivity', 'block_relusp');?></td><td><?php print_string('tutordiary', 'block_relusp');?></td></tr></table></div>';

			//hds-Linha com media dos valores
			//Confere se o parametro para calcular e MEAN ou MEDIAN, para inverter posicionamento dos valores
			if (j.totals2.modocalc == 'MEDIAN') //quando o resultado de TOTALS = MEAN
			  reptable+='<div style="position:fixed;top:95%;background:#fff4c8;border:1px solid #ffcc00;width:936px;"><table style="font-size:9pt;font-weight:bold;margin:5px 3px; width:100%"><tr><td width="205px;">M&eacute;dia / Mediana</td><td width="85px;">'+CstFmt(j.totals.logins_d)+' / '+CstFmt(j.totals2.logins_d)+'</td><td width="105px;">'+CstFmt(j.totals.logins_p,0)+' / '+CstFmt(j.totals2.logins_p,0)+'</td><td width="85px;">'+CstFmt(j.totals.accesstime_d/60.0)+' / '+CstFmt(j.totals2.accesstime_d/60.0)+'</td><td width="115px;">'+CstFmt(j.totals.accesstime_p/3600.0,1)+' / '+CstFmt(j.totals2.accesstime_p/3600.0,1)+'</td><td width="105px;">'+CstFmt(j.totals.accesses_d)+' / '+CstFmt(j.totals2.accesses_d)+'</td><td width="105px;">'+CstFmt(j.totals.accesses_p,0)+' / '+CstFmt(j.totals2.accesses_p,0)+'</td></tr></table></div>';
			else //quando o resultado de TOTALS = MEDIAN, inverter valores, pois TOTALS2 = MEAN
			reptable+='<div style="position:fixed;top:95%;background:#fff4c8;border:1px solid #ffcc00;width:936px;"><table style="font-size:9pt;font-weight:bold;margin:5px 3px; width:100%"><tr><td width="205px;">M&eacute;dia / Mediana</td><td width="85px;">'+CstFmt(j.totals2.logins_d)+' / '+CstFmt(j.totals.logins_d)+'</td><td width="105px;">'+CstFmt(j.totals2.logins_p,0)+' / '+CstFmt(j.totals.logins_p,0)+'</td><td width="85px;">'+CstFmt(j.totals2.accesstime_d/60.0)+' / '+CstFmt(j.totals.accesstime_d/60.0)+'</td><td width="115px;">'+CstFmt(j.totals2.accesstime_p/3600.0,1)+' / '+CstFmt(j.totals.accesstime_p/3600.0,1)+'</td><td width="105px;">'+CstFmt(j.totals2.accesses_d)+' / '+CstFmt(j.totals.accesses_d)+'</td><td width="105px;">'+CstFmt(j.totals2.accesses_p,0)+' / '+CstFmt(j.totals.accesses_p,0)+'</td><td></td></tr></table></div>';

			// Escreve tabela
			$('#report').html(reptable);
			// Ordena dados
			$("#results").tablesorter( {sortList: [[0,0]], widgets: ['zebra'],
							 headers: { 1: { sorter:'customfloat' },
										2: { sorter:'customfloat' }, 3: { sorter:'customfloat' },
										4: { sorter:'customfloat' }, 5: { sorter:'customfloat' },
										6: { sorter:'customfloat' } } } );
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
