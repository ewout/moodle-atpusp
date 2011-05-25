<?php
//	Sistema de Relat�rios USP para Moodle
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

// P�gina respons�vel pela apresenta��o do Relat�rio de Tutores

require_once('../../config.php' );
require_once($CFG->libdir.'/blocklib.php' );

// Cace�alho
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
<script language="javascript" src="http://www.google.com/jsapi"></script>

<?php
// Obtem o Id do curso e o Id de inst�ncia do bloco
$id = required_param('id', PARAM_INT);
$instanceid = required_param('instanceid', PARAM_INT);
?>
<!-- // Formul�rio para entrada da faixa de datas do relat�rio -->
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
<!-- // Formul�rio oculto que receber� os dados em CSV para exporta��o do arquivo CSV -->
<div align="right">
<span id="graphperiod"></span>
<form name="csv_form" id="csv_form" target="_blank" method="post" enctype="application/x-www-form-urlencoded;charset=UTF-8" action="<?php echo $CFG->wwwroot ?>/blocks/relusp/csv_processor.php">
<input type="hidden" name="csv" id="csv" value="">
<input type="hidden" name="name" id="name" value="">
<span id="export"></span>
</div>
<!-- // Divis�o na qual ser� apresentado o relat�rio -->
<div id="report">
</div>
<script>
/**
	* Formata o valor num�rico para uma casa decimal convertendo ponto para v�rgula.
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

/**
	* Limpa a string passada retirando aspas duplas
**/
function Clean(value) {
	return value.replace('"','');
}

/**
	* Gera um nome de arquivo para exporta��a do CSV baseado nas datas e tipo de relat�rio
**/
function Filename() {
	f = $('#from').datepicker("getDate");
	t = $('#to').datepicker("getDate");
	return 'reptutor_'+f.getDate()+'-'+(f.getMonth()+1)+'-'+f.getFullYear()+'_'+t.getDate()+'-'+(t.getMonth()+1)+'-'+t.getFullYear()+'.csv';
}

/**
	* Verifica se ser� apresentado OK ou PROBLEMA no alarme
**/
function AlFmt(value) {
	if (value)
		return "OK";
	else
		return "Problema";
}

/**
	* Gera o arquivo CSV numa string para exporta��o
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

// Cria numa vari�vel a caixa de di�logo de espera no processamento do relat�rio
var $waitdlg = $('<div></div>')
	.html('<div align="center"><img style="vertical-align:middle;" src="<?php echo $CFG->wwwroot ?>'+'/blocks/relusp/loading.gif"> <?php print_string('processing', 'block_relusp');?></div>')
	.dialog({
		autoOpen: false,
		modal: true,
		title: 'Processando...',
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
      if (maxx==null) //primeiro item
	 maxx=Number(g_res[i][0]) + Number(g_res[i][0]-g_res[i+1][0]); //eixo x, quantidade e distanciar do ultimo ponto
   }
if (i+1 == g_res.length) //ultimo item
   maxy=i+1; //eixo y, usuarios
   maxy=Number(maxy)+10; //distanciar do ultimo ponto
}

//Documentacao GoogleGRAPH
//chtt=Titulo do grafico - chxt: eixos, x,y - chs:dimensao da imagem - cht:tipo de grafico - chd: dados do grafico - chxr: max/min imagem dos eixos - chds: max/min dados do eixo - chg: habilitar grade - chxl:legendas - chxp:posicionamento das legendas

switch (opt){
 case 1:
   graphcode='<div id="chart"><img src="http://chart.apis.google.com/chart?&chtt='+escape("#")+'<?php print_string('titlegraphtutor', 'block_relusp');?><?php print_string('daccess', 'block_relusp');?> '+escape(">")+' X&chxt=x,y,x&chs=400x300&cht=s&chd=t:'+values+'|'+qtd+'|50&chxr=0,0,'+maxx+'|1,0,'+maxy+'&chds=0,'+maxx+',0,'+maxy+',0,100&chg=-1,-1,0,0&chxl=2:|<?php print_string('daccess', 'block_relusp');?>&chxp=2,50"></div>';
   $graphdlg1.html(graphcode);
   $graphdlg1.dialog("open");
   break;

 case 2:
   graphcode='<div id="chart"><img src="http://chart.apis.google.com/chart?&chtt='+escape("#")+'<?php print_string('titlegraphtutor', 'block_relusp');?><?php print_string('paccess', 'block_relusp');?> '+escape(">")+' X&chxt=x,y,x&chs=400x300&cht=s&chd=t:'+values+'|'+qtd+'|50&chxr=0,0,'+maxx+'|1,0,'+maxy+'&chds=0,'+maxx+',0,'+maxy+',0,100&chg=-1,-1,0,0&chxl=2:|<?php print_string('paccess', 'block_relusp');?>&chxp=2,50"></div>';
   $graphdlg2.html(graphcode);
   $graphdlg2.dialog("open");
   break;

 case 3:
   graphcode='<div id="chart"><img src="http://chart.apis.google.com/chart?&chtt='+escape("#")+'<?php print_string('titlegraphtutor', 'block_relusp');?><?php print_string('dpermanence', 'block_relusp');?> '+escape(">")+' X&chxt=x,y,x&chs=400x300&cht=s&chd=t:'+values+'|'+qtd+'|50&chxr=0,0,'+maxx+'|1,0,'+maxy+'&chds=0,'+maxx+',0,'+maxy+',0,100&chg=-1,-1,0,0&chxl=2:|<?php print_string('dpermanence', 'block_relusp');?>&chxp=2,50"></div>';
   $graphdlg3.html(graphcode);
   $graphdlg3.dialog("open");
   break;

 case 4:
   graphcode='<div id="chart"><img src="http://chart.apis.google.com/chart?&chtt='+escape("#")+'<?php print_string('titlegraphtutor', 'block_relusp');?><?php print_string('ppermanence', 'block_relusp');?> '+escape(">")+' X&chxt=x,y,x&chs=400x300&cht=s&chd=t:'+values+'|'+qtd+'|50&chxr=0,0,'+maxx+'|1,0,'+maxy+'&chds=0,'+maxx+',0,'+maxy+',0,100&chg=-1,-1,0,0&chxl=2:|<?php print_string('ppermanence', 'block_relusp');?>&chxp=2,50"></div>';
   $graphdlg4.html(graphcode);
   $graphdlg4.dialog("open");
   break;

 case 5:
   graphcode='<div id="chart"><img src="http://chart.apis.google.com/chart?&chtt='+escape("#")+'<?php print_string('titlegraphtutor', 'block_relusp');?><?php print_string('dactivity', 'block_relusp');?> '+escape(">")+' X&chxt=x,y,x&chs=400x300&cht=s&chd=t:'+values+'|'+qtd+'|50&chxr=0,0,'+maxx+'|1,0,'+maxy+'&chds=0,'+maxx+',0,'+maxy+',0,100&chg=-1,-1,0,0&chxl=2:|<?php print_string('dactivity', 'block_relusp');?>&chxp=2,50"></div>';
   $graphdlg5.html(graphcode);
   $graphdlg5.dialog("open");
   break;

 case 6:
   graphcode='<div id="chart"><img src="http://chart.apis.google.com/chart?&chtt='+escape("#")+'<?php print_string('titlegraphtutor', 'block_relusp');?><?php print_string('pactivity', 'block_relusp');?> '+escape(">")+' X&chxt=x,y,x&chs=400x300&cht=s&chd=t:'+values+'|'+qtd+'|50&chxr=0,0,'+maxx+'|1,0,'+maxy+'&chds=0,'+maxx+',0,'+maxy+',0,100&chg=-1,-1,0,0&chxl=2:|<?php print_string('pactivity', 'block_relusp');?>&chxp=2,50"></div>';
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

// Iniciliza vari�veis de datas
var t_from = null;
var t_to = null;

// Bloco de inicializa��o executado quando a p�gina � carregada
$(document).ready(function() {
	// Cria os objetos DatePicker e fixa as datas iniciais.
	$('#from').datepicker({  constrainInput: true, showOn: 'button', buttonImageOnly: true, 
				maxDate: 'today', buttonImage: '<?php print $CFG->wwwroot ?>/blocks/relusp/calendar.gif' });
	$('#to').datepicker({  constrainInput: true, showOn: 'button', buttonImageOnly: true, 
				maxDate: 'today', buttonImage: '<?php print $CFG->wwwroot ?>/blocks/relusp/calendar.gif' });
	$('#from').datepicker( "setDate" , "-7" );
	$('#to').datepicker( "setDate" , "today" );	
	// Cria o objeto tablesorter com um tipo da dado particularizado para ordena��o de n�meros com v�rgula
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
	// Adiciona bot�o para gerar relat�rio
	$('#generate').button({ icons: { primary: "ui-icon-calculator" } });
});

// Bloco executado quando o bot�o para gera��o do relat�rio � pressionado
$("#generate").click(function() {
	// Obtem datas
	t_from = $('#from').datepicker("getDate");
	t_to = $('#to').datepicker("getDate");
	// Faz checagem se datas s�o v�lidas
	if ((t_from == null) || (t_to == null)) {
		alert('<?php print_string('dateerror1', 'block_relusp');?>');
		return false;
	}
	// Ajusta datas para unix timestamp
	t_from = t_from.getTime()/1000;
	t_to = t_to.getTime()/1000;
	t_to += 86390;
	// Checagem de sanidade
	if (t_to <= t_from) {
		alert('<?php print_string('dateerror2', 'block_relusp');?>');
		return false;
	}
	// Prepara execu��o de AJAX
	url='<?php echo $CFG->wwwroot ?>'+'/blocks/relusp/ajax_reptutor.php?func=reptutor'+
		'&id=<?php print $id; ?>&instanceid=<?php print $instanceid; ?>'+
		'&from='+encodeURIComponent(t_from)+'&to='+encodeURIComponent(t_to);
	//$('#debug').html(url);
	$waitdlg.dialog('open');
	$("#report").ajaxError(function(event, request, settings){
		$(this).html("<li><?php print_string('errorprocessing', 'block_relusp');?> " + settings.url + "</li><br>"+request.responseText);
		$waitdlg.dialog('close');
	});
	// Executa AJAX para gera��o do relat�rio
	$.getJSON(url, function(j){
		if (j) {
			// Declara array com valores para o GoogleChart
			g_values1 = new Array();g_values2 = new Array();g_values3 = new Array();g_values4 = new Array();g_values5 = new Array();g_values6 = new Array();
			// Fecha janela de processamento
			$waitdlg.dialog('close');
			// Monta cabe�alhos da tabela do relat�rio
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

				//Guarda valores para GoogleChart
				g_values1.push(CstFmt(j.bytutorid[i].logins_d,2,"."));
				g_values2.push(CstFmt(j.bytutorid[i].logins_p,0));
				g_values3.push(CstFmt(j.bytutorid[i].accesstime_d/60.0,2,"."));
				g_values4.push(CstFmt(j.bytutorid[i].accesstime_p/3600.0,2,"."));
				g_values5.push(CstFmt(j.bytutorid[i].accesses_d,2,"."));
				g_values6.push(CstFmt(j.bytutorid[i].accesses_p,0));

				// Prepara URL para di�rios do tutor
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

			//hds-Cabe�alho flutuante da tabela
			reptable+='<div style="position:fixed;top:0;background:#fff4c8;border:1px solid #ffcc00;width:936px;"><table style="font-size:9pt;font-weight:bold;margin:5px 3px; width:100%;"><tr><td width="210px;"><?php print_string('tutor', 'block_relusp');?></td><td width="85px;"><?php print_string('daccess', 'block_relusp');?></td><td width="105px;"><?php print_string('paccess', 'block_relusp');?></td><td width="85px;"><?php print_string('dpermanence', 'block_relusp');?></td><td width="115px;"><?php print_string('ppermanence', 'block_relusp');?></td><td width="105px;"><?php print_string('dactivity', 'block_relusp');?></td><td width="105px"><?php print_string('pactivity', 'block_relusp');?></td><td><?php print_string('tutordiary', 'block_relusp');?></td></tr></table></div>';

			//hds-Linha com media dos valores
			//Confere se o parametro para calcular e MEAN ou MEDIAN, para inverter posicionamento dos valores
			if (j.totals2.modocalc == 'MEDIAN') //quando o resultado de TOTALS = MEAN
			  reptable+='<div style="position:fixed;top:92%;background:#fff4c8;border:1px solid #ffcc00;width:936px;"><table style="font-size:9pt;font-weight:bold;margin:5px 3px; width:100%"><tr><td width="205px;">M&eacute;dia / Mediana</td><td width="85px;">'+CstFmt(j.totals.logins_d)+' / '+CstFmt(j.totals2.logins_d)+'</td><td width="105px;">'+CstFmt(j.totals.logins_p,0)+' / '+CstFmt(j.totals2.logins_p,0)+'</td><td width="85px;">'+CstFmt(j.totals.accesstime_d/60.0)+' / '+CstFmt(j.totals2.accesstime_d/60.0)+'</td><td width="115px;">'+CstFmt(j.totals.accesstime_p/3600.0,1)+' / '+CstFmt(j.totals2.accesstime_p/3600.0,1)+'</td><td width="105px;">'+CstFmt(j.totals.accesses_d)+' / '+CstFmt(j.totals2.accesses_d)+'</td><td width="105px;">'+CstFmt(j.totals.accesses_p,0)+' / '+CstFmt(j.totals2.accesses_p,0)+'</td><td rowspan="2"><a href="#" onclick="javascript:closeallgraphs();"><?php print_string('closeallgraphs', 'block_relusp');?></a></td></tr><tr><td></td><td><a href="#" onclick="javascript:searchgraph(g_values1,1);" title="<?php print_string('gengraph', 'block_relusp'); print_string('daccess', 'block_relusp');?>"><img src="<?php echo $CFG->pixpath; ?>/i/stats.gif" style="border:1px solid #000;"></a></td><td><a href="#" onclick="javascript:searchgraph(g_values2,2);" title="<?php print_string('gengraph', 'block_relusp'); print_string('paccess', 'block_relusp');?>"><img src="<?php echo $CFG->pixpath; ?>/i/stats.gif" style="border:1px solid #000;"></a></td><td><a href="#" onclick="javascript:searchgraph(g_values3,3);" title="<?php print_string('gengraph', 'block_relusp'); print_string('dpermanence', 'block_relusp');?>"><img src="<?php echo $CFG->pixpath; ?>/i/stats.gif" style="border:1px solid #000;"></a></td><td><a href="#" onclick="javascript:searchgraph(g_values4,4);" title="<?php print_string('gengraph', 'block_relusp'); print_string('ppermanence', 'block_relusp');?>"><img src="<?php echo $CFG->pixpath; ?>/i/stats.gif" style="border:1px solid #000;"></a></td><td><a href="#" onclick="javascript:searchgraph(g_values5,5);" title="<?php print_string('gengraph', 'block_relusp'); print_string('dactivity', 'block_relusp');?>"><img src="<?php echo $CFG->pixpath; ?>/i/stats.gif" style="border:1px solid #000;"></a></td><td><a href="#" onclick="javascript:searchgraph(g_values6,6);" title="<?php print_string('gengraph', 'block_relusp'); print_string('pactivity', 'block_relusp');?>"><img src="<?php echo $CFG->pixpath; ?>/i/stats.gif" style="border:1px solid #000;"></a></td></tr></table></div>';
			else //quando o resultado de TOTALS = MEDIAN, inverter valores, pois TOTALS2 = MEAN
			reptable+='<div style="position:fixed;top:92%;background:#fff4c8;border:1px solid #ffcc00;width:936px;"><table style="font-size:9pt;font-weight:bold;margin:5px 3px; width:100%"><tr><td width="205px;">M&eacute;dia / Mediana</td><td width="85px;">'+CstFmt(j.totals2.logins_d)+' / '+CstFmt(j.totals.logins_d)+'</td><td width="105px;">'+CstFmt(j.totals2.logins_p,0)+' / '+CstFmt(j.totals.logins_p,0)+'</td><td width="85px;">'+CstFmt(j.totals2.accesstime_d/60.0)+' / '+CstFmt(j.totals.accesstime_d/60.0)+'</td><td width="115px;">'+CstFmt(j.totals2.accesstime_p/3600.0,1)+' / '+CstFmt(j.totals.accesstime_p/3600.0,1)+'</td><td width="105px;">'+CstFmt(j.totals2.accesses_d)+' / '+CstFmt(j.totals.accesses_d)+'</td><td width="105px;">'+CstFmt(j.totals2.accesses_p,0)+' / '+CstFmt(j.totals.accesses_p,0)+'</td><td rowspan="2"><a href="#" onclick="javascript:closeallgraphs();"><?php print_string('closeallgraphs', 'block_relusp');?></a></td></tr><tr><td></td><td><a href="#" onclick="javascript:searchgraph(g_values1,1);" title="<?php print_string('gengraph', 'block_relusp'); print_string('daccess', 'block_relusp');?>"><img src="<?php echo $CFG->pixpath; ?>/i/stats.gif" style="border:1px solid #000;"></a></td><td><a href="#" onclick="javascript:searchgraph(g_values2,2);" title="<?php print_string('gengraph', 'block_relusp'); print_string('paccess', 'block_relusp');?>"><img src="<?php echo $CFG->pixpath; ?>/i/stats.gif" style="border:1px solid #000;"></a></td><td><a href="#" onclick="javascript:searchgraph(g_values3,3);" title="<?php print_string('gengraph', 'block_relusp'); print_string('dpermanence', 'block_relusp');?>"><img src="<?php echo $CFG->pixpath; ?>/i/stats.gif" style="border:1px solid #000;"></a></td><td><a href="#" onclick="javascript:searchgraph(g_values4,4);" title="<?php print_string('gengraph', 'block_relusp'); print_string('ppermanence', 'block_relusp');?>"><img src="<?php echo $CFG->pixpath; ?>/i/stats.gif" style="border:1px solid #000;"></a></td><td><a href="#" onclick="javascript:searchgraph(g_values5,5);" title="<?php print_string('gengraph', 'block_relusp'); print_string('dactivity', 'block_relusp');?>"><img src="<?php echo $CFG->pixpath; ?>/i/stats.gif" style="border:1px solid #000;"></a></td><td><a href="#" onclick="javascript:searchgraph(g_values6,6);" title="<?php print_string('gengraph', 'block_relusp'); print_string('pactivity', 'block_relusp');?>"><img src="<?php echo $CFG->pixpath; ?>/i/stats.gif" style="border:1px solid #000;"></a></td></tr></table></div>';

			// Escreve tabela
			$('#report').html(reptable);
			// Ordena dados
			$("#results").tablesorter( {sortList: [[0,0]], widgets: ['zebra'],
							 headers: { 1: { sorter:'customfloat' },
										2: { sorter:'customfloat' }, 3: { sorter:'customfloat' },
										4: { sorter:'customfloat' }, 5: { sorter:'customfloat' },
										6: { sorter:'customfloat' } } } );
			// Apresenta bot�o para exportar dados
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
