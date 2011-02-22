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

// Funções para gerenciamento das operações AJAX do relatório de alunos

require_once("../../config.php");
require_once($CFG->libdir.'/blocklib.php');
require_once('lib.php');

// Obtem função, Id do curso e Id da instância do bloco
$func   = required_param('func', PARAM_TEXT);
$id = required_param('id', PARAM_INT);
$instanceid = required_param('instanceid', PARAM_INT);

// Segurança
if (isloggedin()) {
	// Cria um novo objeto RepStudent e processa a função desejada
	$reptutor = new RepStudent($id, $instanceid, $USER->id);
	$reptutor->process($func);
}

?>