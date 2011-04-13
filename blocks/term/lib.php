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
	private $id = 0;		// Id do curso atual
	private $instanceid = 0;	// Id da instância do bloco no curso
	private $userid = 0;		// Id do usuário
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
			case 'searchterm' : $this->proc_searchterm(); break;
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
		$termentry->ip=required_param('ip', PARAM_TEXT);
		$termentry->timemodified=time();

		// Insere novo registro
		if (insert_record('block_term', $termentry))
			$result = true;
		else
			$result = false;
		// Codifica resultado em JSON
		echo($ajax->encode($result));
	}

/**
	* Consulta respostas no BD mdl_block_term
**/
	public function proc_searchterm() {
		global $CFG;
		$ajax = new HTML_AJAX_JSON();
		$result = array();
		$yes = 0;$no = 0; //ResponseCounter, response=1 (yes), response=2(no)
		$stryes = get_string('yes', 'block_term');$strno = get_string('no', 'block_term');

		$optsearch=required_param('optsearch', PARAM_INT);// Opcao de Busca: 1=Busca Global | 2=Busca no contexto do Curso

		// Obtem Respostas
		$query = 'SELECT t.id AS termid, u.id AS userid, u.firstname, u.lastname, u.email, c.id AS courseid, c.shortname, t.response, t.ip, t.timemodified ';
		$query .= 'FROM '.$CFG->prefix.'block_term t, '.$CFG->prefix.'user u, '.$CFG->prefix.'course c ';
		if ($optsearch==1)
		   $query .= 'WHERE t.user=u.id AND t.course=c.id ORDER BY t.timemodified ASC';
		elseif ($optsearch==2)
		   $query .= 'WHERE t.user=u.id AND t.course=c.id AND c.id='.$this->id.' ORDER BY t.timemodified ASC';

		if ($records = get_records_sql($query)) {
			foreach ($records as $record) {
			   if ($record->response==1){
				$yes++;
				$response = $stryes;
			   }elseif ($record->response==2){
				$no++;
				$response = $strno;
			   }
			   // Preenche entradas na variável de resultados
			   $result['responses'][]=array('termid' => $record->termid, 'userid' => $record->userid, 'firstname' => $record->firstname, 'lastname' => $record->lastname, 'email' => $record->email, 'courseid' => $record->courseid, 'shortname' => $record->shortname, 'response' => $response, 'ip' => $record->ip, 'timemodified' => $record->timemodified); 
			}
			$result['totals']=array('yes' => $yes, 'no' => $no, 'total' => $yes + $no);
		}

		// Codifica e retorna os resultados em JSON
		echo($ajax->encode($result));
	}
	

}

