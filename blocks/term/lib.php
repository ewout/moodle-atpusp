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
			case 'updateterm' : $this->proc_updateterm(); break;
		}
	}

/**
	* Verifica se o usuário pode ver o Block.
	* Gera uma variável Javascript para representar essa condição.
**/
	public function canViewBlock() {
		if (has_capability('block/term:viewblock', $this->context, NULL, false))
			echo('<script>var canViewBlock=true;</script>');
		else
			echo('<script>var canViewBlock=false;</script>');
	}

/**
	* Obtem a lista de estudantes de cada grupo que o tutor pertence.
	* Gera uma variável Javascript para representar essa condição.
**/
	public function getStudents() {
		$ajax = new HTML_AJAX_JSON();
		$result = array();
		// Obtem os grupos do tutor
		if ($groups = groups_get_all_groups($this->id, $this->userid, 0, 'g.id, g.name')) {
			// Obtem usuários que podem ser objeto de postagens no diário
			$subjects = get_users_by_capability($this->context, 'block/relusp:tutordiarysubject', 'u.id', '', '', '', '', '', false);
			foreach($groups as $group) {
				$rstudents = array();
				// Obtem estudantes do grupo
				if ($students = groups_get_members($group->id, 'u.id, CONCAT(u.firstname, \' \', u.lastname) as name', 'firstname,lastname ASC')) {
					foreach($students as $student) {
						// Se estudante não é objeto de postagens, pula.
						if ($subjects)
							if (!array_key_exists($student->id, $subjects))
								continue;
						// Preenche estudantes do grupo
						$rstudents[] = array('id' => $student->id, 'name' => $student->name);
					}
				}
				// Armazena estudantes do grupo
				$result[]=array('name' => $group->name, 'students'=> $rstudents);
			}
		} else {  // Tutor fora de grupo. 
			$rstudents = array();
			// Obtem estudantes objeto do diário do tutor
			if ($students = get_users_by_capability($this->context, 'block/relusp:tutordiarysubject', 'u.id, CONCAT(u.firstname, \' \', u.lastname) as name', 'firstname,lastname ASC', '', '', '', '', false)) {
				foreach($students as $student)
					$rstudents[] = array('id' => $student->id, 'name' => $student->name);
			}
			// Preenche estudantes
			$result[]=array('name' => get_string('none'), 'students'=> $rstudents);
		}
		// Cria variável Javascript
		echo('<script>var groups = '.$ajax->encode($result).';</script>');
	}
	
/**
	* Obtem a lista de interações possíveis para tutores no curso atual.
	* Gera uma variável Javascript para representar essas interações.
**/
	public function getInteractions() {
		$ajax = new HTML_AJAX_JSON();
		$result = array();
		// Obtem interações
		if ($interactions = get_records('tutordiary_interactions', 'course', $this->id, $sort='interaction ASC')) {
			foreach($interactions as $interaction) // Preenche
				$result[]=array('id' => $interaction->id, 'interaction' => $interaction->interaction);
		}
		// Cria variável Javascript
		echo('<script>var interactions = '.$ajax->encode($result).';</script>');
	}

/**
	* Obtem a lista de tutores que podem postar.
	* Gera uma variável Javascript para representar essa lista.
**/
	public function getTutors() {
		$ajax = new HTML_AJAX_JSON();
		$result = array();
		// Obtem tutores
		if ($tutors = get_users_by_capability($this->context, 'block/relusp:reptutorpost', '', 'firstname,lastname ASC', '', '', '', '', false)) {
			foreach($tutors as $tutor)  // Preenche
				$result[]=array('id' => $tutor->id, 'name' => $tutor->firstname.' '.$tutor->lastname);
		}
		// Cria variável Javascript
		echo('<script>var tutors = '.$ajax->encode($result).';</script>');
	}
	
/**
	* Adiciona uma nova entrada no diário do tutor.
	* Retorna uma variável JSON indicando se foi inserida.
**/
	public function proc_adddiaryentry() {
		$ajax = new HTML_AJAX_JSON();
		// Cria um novo objeto entrada no diário
		$diaryentry=new object();
		// Prenche dados da entrada
		$diaryentry->id=0;
		$diaryentry->timemodified=time();
		$diaryentry->tutorid=$this->userid;
		$diaryentry->courseid=$this->id;
		$diaryentry->interactionid=required_param('interac', PARAM_INT);
		$diaryentry->timedevoted=required_param('timedevoted', PARAM_INT);
		$diaryentry->studentid=required_param('student', PARAM_INT);
		$diaryentry->requestdate=required_param('reqdate', PARAM_INT);
		$diaryentry->responsedate=required_param('respdate', PARAM_INT);
		$diaryentry->notes=required_param('obs', PARAM_TEXT);
		// Insere novo registro
		if (insert_record('tutordiary', $diaryentry))
			$result = true;
		else
			$result = false;
		// Codifica resultado em JSON
		echo($ajax->encode($result));
	}
	
/**
	* Gera as informações do relatório diário de tutores. Os dados gerados são armazenados
	* numa estrutura de dados que depois é convertida em JSON para processamento
	* dentro da página do relatório.
**/
	public function proc_repdiary() {
		global $CFG;
		$ajax = new HTML_AJAX_JSON();
		// Obtem faixa de datas e tutor
		$from = required_param('from', PARAM_INT);
		$to = required_param('to', PARAM_INT);
		$tutorid = required_param('tutorid', PARAM_INT);
		if ($tutorid == 0) {
			// Diário de todos os tutores
			if ($tutors = get_users_by_capability($this->context, 'block/relusp:reptutorpost')) {
				$tutorsid='';
				// Constroi Ids dos tutores
				foreach($tutors as $tutor) {
					if (empty($tutorsid))
						$tutorsid.=$tutor->id;
					else
						$tutorsid.=', '.$tutor->id;
				}
			}
		} else // Diário de apenas um tutor
			$tutorsid=$tutorid;
		$result = array();
		// Obtem entradas do diário de tutores
		$query='SELECT d . * , CONCAT( s.firstname,  \' \', s.lastname ) AS student, CONCAT( t.firstname,  \' \', t.lastname ) AS tutor';
		$query.=', i.interaction FROM '.$CFG->prefix.'tutordiary d, '.$CFG->prefix.'user s, '.$CFG->prefix.'user t, '.$CFG->prefix.'tutordiary_interactions i WHERE d.courseid='.$this->id;
		$query.=' AND d.tutorid IN ( '.$tutorsid.' ) AND d.timemodified > '.$from.' AND d.timemodified < '.$to.' AND t.id = d.tutorid AND s.id = d.studentid AND i.id = d.interactionid ORDER BY d.timemodified ASC';
		if ($records = get_records_sql($query)) {
			foreach ($records as $record) {
				// Checa se foi respondida dentro do prazo
				$limit = $record->requestdate+(86400*($this->block->config->t_daystoreply+1));
				// Preenche entradas na variável de resultados
				$result[]=array('id' => $record->id, 'tutorid' => $record->tutorid, 'interactionid' => $record->interactionid, 'studentid' => $record->studentid, 'requestdate' => $record->requestdate, 'responsedate' => $record->responsedate, 'timemodified' => $record->timemodified, 'notes' => $record->notes, 'student' => $record->student, 'tutor' => $record->tutor,'interaction' => $record->interaction,'timedevoted' => $record->timedevoted,  'ok' => ($record->responsedate<$limit)); 
			}
		}
		// Codifica e retorna os resultados em JSON
		echo($ajax->encode($result));
	}

	
/**
	* Fornece dados para editar uma entrada no diário do tutor.
	* Retorna uma variável JSON indicando se foi editada.
**/
	public function proc_editdiaryentry1() {
		global $CFG;
		$ajax = new HTML_AJAX_JSON();
		// ***Fazer select com o parametro 
		$identry=required_param('identry', PARAM_INT);

		$result = array();
		// Obtem a entrada a partir do ID instanciado
		$query='SELECT * FROM '.$CFG->prefix.'tutordiary WHERE id='.$identry;
		if ($records = get_records_sql($query)) {
 		   foreach ($records as $record) {
			// Preenche entradas na variável de resultados
			$result[]=array('id' => $record->id, 'tutorid' => $record->tutorid, 'interactionid' => $record->interactionid, 'studentid' => $record->studentid, 'requestdate' => $record->requestdate, 'responsedate' => $record->responsedate, 'timemodified' => $record->timemodified, 'notes' => $record->notes,'timedevoted' => $record->timedevoted); 
			}
		}
		// Codifica resultado em JSON
		echo($ajax->encode($result));
	}


	
/**
	* Salva edicao de uma entrada no diário do tutor.
	* Retorna uma variável JSON indicando se foi salvo.
**/
	public function proc_editdiaryentry2() {
		global $CFG;
		$ajax = new HTML_AJAX_JSON();
		//Cria um novo objeto entrada no diário
		$diaryentry=new object();
		// Prenche dados da entrada
		$diaryentry->id=required_param('identry', PARAM_INT);;
		$diaryentry->timemodified=time();
		$diaryentry->tutorid=$this->userid;
		$diaryentry->courseid=$this->id;
		$diaryentry->interactionid=required_param('interac', PARAM_INT);
		$diaryentry->timedevoted=required_param('timedevoted', PARAM_INT);
		$diaryentry->studentid=required_param('student', PARAM_INT);
		$diaryentry->requestdate=required_param('reqdate', PARAM_INT);
		$diaryentry->responsedate=required_param('respdate', PARAM_INT);
		$diaryentry->notes=required_param('obs', PARAM_TEXT);
		// Atualiza o registro
		if (update_record('tutordiary', $diaryentry))
			$result = true;
		else
			$result = false;
		// Codifica resultado em JSON
		echo($ajax->encode($result));
	}



	
/**
	* Exclui uma entrada no diário do tutor.
	* Retorna uma variável JSON indicando se foi inserida.
**/
	public function proc_deletediaryentry() {
		$ajax = new HTML_AJAX_JSON();
		// Cria um novo objeto entrada no diário
		$diaryentry=new object();
		$identry=required_param('identry', PARAM_INT);;
		// Exclui o registro
		if (delete_records_select('tutordiary', 'id='.$identry))
			$result = true;
		else
			$result = false;
		// Codifica resultado em JSON
		echo($ajax->encode($result));
	}

}

