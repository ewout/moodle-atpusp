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

// Funções para gerenciamento das parametrizações do diário do tutor

	require_once("../../config.php");
	require_once("$CFG->libdir/pear/HTML/AJAX/JSON.php");
	require_once( $CFG->libdir.'/blocklib.php' );

	// Obtem função
	$func   = required_param('func', PARAM_TEXT);
    
	// Verificações de segurança
	if (isloggedin()) {
		$context = get_context_instance(CONTEXT_SYSTEM);
    	require_capability('block/relusp:reptutorconfig', $context, NULL, false);
	
		// Executa função
		switch($func) {
			case 'list' : proc_list(); break;
			case 'delete' : proc_delete(); break;			
			case 'add' : proc_add(); break;			
		}
	}
	
/**
	* Lista as interações possíveis para o curso em questão
**/
	function proc_list() {
		// Obtem curso
		$courseid = required_param('courseid', PARAM_INT);
		$ajax = new HTML_AJAX_JSON();
		$a = array();
		// Obtem interações
		if ($interactions = get_records('tutordiary_interactions', 'course', $courseid, 'interaction ASC')) {
			foreach($interactions as $i) // Gera lista
				$a[]=array('id' => $i->id, 'interaction' => $i->interaction);
			echo($ajax->encode($a));
		}
	}
	
/**
	* Apaga uma interação
**/
	function proc_delete() {
		// Obtem Id da interação
		$id = required_param('id', PARAM_INT);
		$ajax = new HTML_AJAX_JSON();
		// Apaga interação
		delete_records("tutordiary_interactions", "id", "$id");
		echo($ajax->encode(true));
	}
	
/**
	* Adiciona uma nova interação
**/
	function proc_add() {
		// Obtem curso
		$courseid = required_param('courseid', PARAM_INT);
		// Obtem nome da interação
		$value = required_param('value', PARAM_TEXT);
		$ajax = new HTML_AJAX_JSON();
		// Gera nova interação
		$newinteraction = new object();
		$newinteraction->id=0;
		$newinteraction->course=$courseid;
		$newinteraction->interaction=$value;
		// Insere
		if (insert_record("tutordiary_interactions", $newinteraction))
			echo($ajax->encode(true));
		else
			echo($ajax->encode(false));
	}
	
?>