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

// Importa a class JSON a biblioteca de blocos do Moodle
require_once("$CFG->libdir/pear/HTML/AJAX/JSON.php");
require_once($CFG->libdir.'/blocklib.php');

/**
	* Esta classe implementa o Formulario do BLOCKTERM
**/	

class libTerm
{
	private $id = 0;			// Id do curso atual
	private $instanceid = 0;	// Id da instância do bloco no curso
	private $from = 0;			// Data de início do relatório
	private $to = 0;			// Data de fim do relatório
	private $userid = 0;		// Id to tutor para gerar o diário
	private $instance = null;	// Objeto instância do bloco
	private $block = null;		// Objeto bloco do bloco
	private $context = null;	// Objeto contexto do bloco

/**
	* Construtor do LIBTerm.
	* @param $id - Id do curso atual.
	* @param $instanceid - Id da instância do bloco atual.
	* @param $userid - Id do tutor para gerar o relatório
**/
	function __construct($id, $instanceid, $userid) {
		$this->id = $id;
		$this->instanceid = $instanceid;
		$this->userid = $userid;
		// Carrega os objetos instância, bloco e contexto de segurança
		$this->instance=get_record('block_instance', 'id', $this->instanceid);
		$this->block = block_instance('term', $this->instance);
		$this->context = get_context_instance(CONTEXT_BLOCK, $this->block->instance->id);
	}
	
/**
	* Método para processar uma requisição. Recebe a requisição e processa usando
	* o método específico.
	* @param $func - Nome da operação a processar.
**/
	public function process($func) {
		switch($func) {
			case 'addterm' : $this->proc_addterm(); break;
			//case 'updateterm' : $this->proc_updateterm(); break;
		}
	}

	
/**
	* Adiciona um novo registro no BD mdl_block_term
	* Retorna uma variável JSON indicando se foi inserido.
**/
	public function proc_addterm() {
		$ajax = new HTML_AJAX_JSON();
		// Cria um novo objeto entrada no diário
		$termentry=new object();
		// Prenche dados da entrada
		$termentry->id=0;
		$termentry->user=$this->userid;
		$termentry->course=$this->id;
		$termentry->response=required_param('response', PARAM_INT);
		$termentry->rg=required_param('rg', PARAM_INT);
		$termentry->cpf=required_param('cpf', PARAM_INT);
		$termentry->timemodified=time();

		// Insere novo registro
		if (insert_record('block_term', $termentry))
			$result = true;
		else
			$result = false;
		// Codifica resultado em JSON
		echo($ajax->encode($result));
	}
	

}

