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

// Importa a class JSON a biblioteca de blocos do Moodle
require_once("$CFG->libdir/pear/HTML/AJAX/JSON.php");
require_once($CFG->libdir.'/blocklib.php');

/**
	* Esta classe implementa o Relatório de tutores
**/	
class RepTutor
{
	private $id = 0;			// Id do curso atual
	private $instanceid = 0;	// Id da instância do bloco no curso
	private $from = 0;			// Data de início do relatório
	private $to = 0;			// Data de fim do relatório
	private $instance = null;	// Objeto instância do bloco
	private $block = null;		// Objeto bloco do bloco
	private $context = null;	// Objeto contexto do bloco

/**
	* Construtor do Relatório de Tutores.
	* @param $id - Id do curso atual.
	* @param $instanceid - Id da instância do bloco atual.
**/
	function __construct($id, $instanceid) {
		$this->id = $id;
		$this->instanceid = $instanceid;
		// Carrega os objetos instância, bloco e contexto de segurança
		$this->instance=get_record('block_instance', 'id', $this->instanceid);
		$this->block = block_instance('relusp', $this->instance);
		$this->context = get_context_instance(CONTEXT_BLOCK, $this->block->instance->id);
	}
	
/**
	* Método para processar uma requisição. Recebe a requisição e processa usando
	* o método específico.
	* @param $func - Nome da operação a processar.
**/
	public function process($func) {
		//Deve ter permissão de visualização do relatório de todos os tutores
		require_capability('block/relusp:reptutorallview', $this->context, NULL, false);
		switch($func) {
			case 'reptutor' : $this->proc_reptutor(); break;
		}
	}
	
/**
	* Calcula a mediana de uma série de valores pela definição.
	* @param $values - array com os valores.
	* @return float (mediana calculada).
**/
	private function calcMedian($values) {
		sort($values);
		$n = count($values);
		$h = intval($n / 2);
		if($n % 2 == 0) { 
			$median = ($values[$h] + $values[$h-1]) / 2; 
		} else { 
			$median = $values[$h]; 
		}
		return $median;
	}

/**
	* Calcula a média de uma série de valores.
	* @param $values - array com os valores.
	* @return float (média calculada).
**/
	private function calcAverage($values) {
		$total = array_sum($values);
		return $total/floatval(count($values));
	}
	
/**
	* Verifica se o diário de um tutor em específico no período apresenta
	* a porcentagem mínima de respotas às interações dentro do prazo máximo.
	* @param $tutorid - Id do tutor.
	* @return boolean (apresenta a porcentagem mínima).
**/
	private function tutorDiaryCheck($tutorid) {
		// Objtem registros do diário no período
		if ($records=get_records_select('tutordiary', "courseid=$this->id AND tutorid=$tutorid AND timemodified > $this->from AND timemodified < $this->to")) {
			$contok=0;
			$total=0;
			// Conta o número de respostas dentro do prazo
			foreach($records as $record) {
				$limit = $record->requestdate+(86400*($this->block->config->t_daystoreply+1));
				if ($record->timemodified<$limit)
					$contok++;
				$total++;
			}
			// Compara com a parametrização
			return (($contok/$total)>=($this->block->config->t_reqsonschedule_perc/100.0));
		}
		return true;
	}
	
/**
	* Gera as informações do relatório de tutores. Os dados gerados são armazenados
	* numa estrutura de dados que depois é convertida em JSON para processamento
	* dentro da página do relatório.
**/
	public function proc_reptutor() {
		//$maxtime = 15*60; // Tempo máximo de inatividade = 15 minutos
		$maxtime = $this->block->config->t_maxtime * 60; //hds - configurar tempo maximo de inatividade em configuracoes do bloco

		// Obtem faixa de datas
		$this->from = required_param('from', PARAM_INT);
		$this->to = required_param('to', PARAM_INT);
		$periodo = ($this->to - $this->from)/(3600*24.0); // dias
		$ajax = new HTML_AJAX_JSON();
		// Obtem lista de tutores
		$tutors = get_users_by_capability($this->context, 'block/relusp:reptutorlist', 'u.id, u.firstname, u.lastname', 'firstname,lastname ASC', '', '', '', '', false);
		if ($tutors) {
			// Determina o número de dias no intervalo da análise
			$days = intval(($this->to-$this->from)/(24*3600));
			$tutorsid = '';
			$lastlog = array();			// Tempo do último log do tutor
			$t_totaltime = array();		// Tempos de permanência do tutor
			$t_totallogins = array();	// Números de logins do tutor
			$t_totalaccess = array();	// Números de acessos (atividade) do tutor
			foreach($tutors as $tutor) {
				// Inicializa arrays que vão armazenar os logins, permanência, etc.
				$lastlog[$tutor->id] = 0;
				$t_totaltime[$tutor->id]=array();
				$t_totallogins[$tutor->id]=array();
				$t_totalaccess[$tutor->id]=array();
				// Os números de logins, permanência e atividade são armazenados por dia
				for($i=0; $i<$days; $i++) {
					$t_totaltime[$tutor->id][$i] = 0;
					$t_totallogins[$tutor->id][$i] = 0;
					$t_totalaccess[$tutor->id][$i] = 0;
				}
				// Gera uma string com os Ids dos tutores
				if ($tutorsid == '')
					$tutorsid.=$tutor->id;
				else
					$tutorsid.=', '.$tutor->id;
			}
			// Obtem os logs dos tutores no período especificado do curso em questão
			// É utilizado recordset pela quantidade de dados retornados
			$logs = get_recordset_select('log' , "course=$this->id AND userid IN ($tutorsid) AND time > $this->from AND time < $this->to", 'time ASC', 'id, userid, time');
			while(!$logs->EOF) {
				// Obtem dados do registro atual
				$userid = $logs->fields['userid'];
				$thistime = $logs->fields['time'];
				// Determina o dia do registro
				$day=intval(($thistime-$this->from)/(24*3600));
				// Contabiliza atividade
				$t_totalaccess[$userid][$day]++;
				// Contabiliza tempo de permanência de acessos (logins)
				if ($lastlog[$userid]==0) {
					// Novo acesso
					$lastlog[$userid]=$thistime;
					$t_totallogins[$userid][$day]++;
				} else {
					// Incrementa permanência
					$difftime = $thistime-$lastlog[$userid];
					if ($difftime <= $maxtime) {
						// Mesma sessão
						if ($difftime>0)
							$t_totaltime[$userid][$day]+=$difftime;
					} else {
						// Tempo maior que a sessão. Contabiliza novo acesso.
						$t_totallogins[$userid][$day]++;
					}
					$lastlog[$userid]=$thistime;
				}
				$logs->MoveNext();
			}
			// Inicializa array associativo que irá conter dados por tutor e totalizações.
			$result=array('bytutorid' => array(), 'totals' =>array(), 'totals2' =>array());
			
			// Inicializa arrays
			$accesstime_acc_d=array();
			$logins_acc_d=array();
			$accesses_acc_d=array();
			$accesstime_acc_p=array();
			$logins_acc_p=array();
			$accesses_acc_p=array();
			
			// Itera para cada tutor preenchendo resultados de tempos e calculando e preenchendo totalizações.
			foreach($tutors as $tutor) {
				// Calcula tempos médios ou medianos
				//if ($this->block->config->t_func == 'MEAN') {
				//	$accesstime_d=$this->calcAverage(array_values($t_totaltime[$tutor->id]));
				//	$logins_d=$this->calcAverage(array_values($t_totallogins[$tutor->id]));
				//	$accesses_d=$this->calcAverage(array_values($t_totalaccess[$tutor->id]));
				//} else {
				//	$accesstime_d=$this->calcMedian($t_totaltime[$tutor->id]);
				//	$logins_d=$this->calcMedian($t_totallogins[$tutor->id]);
				//	$accesses_d=$this->calcMedian($t_totalaccess[$tutor->id]);
				//}
				// Totaliza para o período
				$accesstime_p=array_sum($t_totaltime[$tutor->id]);
				$logins_p=array_sum($t_totallogins[$tutor->id]);
				$accesses_p=array_sum($t_totalaccess[$tutor->id]);
				// permanencia e logins por dia (nao faz sentido calcular medianas aqui)
                                $accesstime_d = $accesstime_p/$periodo; //media de tempo no sistema diariamente em SEGUNDOS
                                $logins_d = $logins_p/$periodo; //media de logins no sistema diariamente em LOGINS
				$accesses_d = $accesses_p/$periodo; //media de logins no sistema diariamente em LOGINS


				// Constroi arrays que acumulam todos os resultados diários para gerar média ou mediana
				// de totalização no final
				//$accesstime_acc_d=array_merge($accesstime_acc_d, array_values($t_totaltime[$tutor->id]));
				//$logins_acc_d=array_merge($logins_acc_d, array_values($t_totallogins[$tutor->id]));
				//$accesses_acc_d=array_merge($accesses_acc_d, array_values($t_totalaccess[$tutor->id]));
				$accesstime_acc_d[]=$accesstime_d;
				$logins_acc_d[]=$logins_d;
				$accesses_acc_d[]=$accesses_d;

				$accesstime_acc_p[]=$accesstime_p;
				$logins_acc_p[]=$logins_p;
				$accesses_acc_p[]=$accesses_p;
	
				// Preenche resultados 
				$result['bytutorid'][$tutor->id]=array(
				'id' => "$tutor->id",
				'course' => "$this->id",
				'name' => "$tutor->firstname $tutor->lastname",
				'accesstime_d' => $accesstime_d, 'al_accesstime_d' => false,
				'accesstime_p' => $accesstime_p, 'al_accesstime_p' => false, 
				'logins_d' => $logins_d, 'al_logins_d' => false, 
				'logins_p' => $logins_p, 'al_logins_p' => false, 
				'accesses_d' => $accesses_d, 'al_accesses_d' => false, 
				'accesses_p' => $accesses_p , 'al_accesses_p' => false, 'al_tutordiary' => false );
			}

			// Calcula média ou mediana das totalizações e preenche resultados
			if ($this->block->config->t_func == 'MEAN') {
				$result['totals']=array(	'accesstime_d' => $this->calcAverage($accesstime_acc_d),
								'accesstime_p' => $this->calcAverage($accesstime_acc_p),
								'logins_d' => $this->calcAverage($logins_acc_d),
								'logins_p' => $this->calcAverage($logins_acc_p),
								'accesses_d' => $this->calcAverage($accesses_acc_d),
								'accesses_p' => $this->calcAverage($accesses_acc_p) );
				//hds-totals2 imprimir MEDIANA no rodape,e colocar valores de medianas referenciados em valores medios diarios
				$result['totals2']=array(	'accesstime_d' => $this->calcMedian($accesstime_acc_d),
								'accesstime_p' => $this->calcMedian($accesstime_acc_p),
								'logins_d' => $this->calcMedian($logins_acc_d),
								'logins_p' => $this->calcMedian($logins_acc_p),
								'accesses_d' => $this->calcMedian($accesses_acc_d),
								'accesses_p' => $this->calcMedian($accesses_acc_p),
			                                        'modocalc' => 'MEDIAN');

		  	}else if ($this->block->config->t_func == 'MEDIAN'){
				$result['totals']=array(	'accesstime_d' => $this->calcMedian($accesstime_acc_d),
								'accesstime_p' => $this->calcMedian($accesstime_acc_p),
								'logins_d' => $this->calcMedian($logins_acc_d),
								'logins_p' => $this->calcMedian($logins_acc_p),
								'accesses_d' => $this->calcMedian($accesses_acc_d),
								'accesses_p' => $this->calcMedian($accesses_acc_p) );
				//hds-totals2 imprimir MEDIANA no rodape,e colocar valores de medianas referenciados em valores medios diarios
				$result['totals2']=array(	'accesstime_d' => $this->calcAverage($accesstime_acc_d),
								'accesstime_p' => $this->calcAverage($accesstime_acc_p),
								'logins_d' => $this->calcAverage($logins_acc_d),
								'logins_p' => $this->calcAverage($logins_acc_p),
								'accesses_d' => $this->calcAverage($accesses_acc_d),
								'accesses_p' => $this->calcAverage($accesses_acc_p),
			                                        'modocalc' => 'MEAN');
			}
			foreach($tutors as $tutor) {
				// Calcula e preenche os alarmes baseado nas parametrizações do relatório
				$result['bytutorid'][$tutor->id]['al_accesstime_d'] = (($result['bytutorid'][$tutor->id]['accesstime_d'] < ($this->block->config->t_d_permancence_min*3600)) || 
					($result['bytutorid'][$tutor->id]['accesstime_d'] < $result['totals']['accesstime_d']*($this->block->config->t_d_permancence_min_per/100.0)));
				$result['bytutorid'][$tutor->id]['al_accesstime_p'] = (($result['bytutorid'][$tutor->id]['accesstime_p'] < ($this->block->config->t_p_permancence_min*3600)) || 
					($result['bytutorid'][$tutor->id]['accesstime_p'] < $result['totals']['accesstime_p']*($this->block->config->t_p_permancence_min_per/100.0)));
				$result['bytutorid'][$tutor->id]['al_logins_d'] = (($result['bytutorid'][$tutor->id]['logins_d'] < $this->block->config->t_d_access_min) || 
					($result['bytutorid'][$tutor->id]['logins_d'] < $result['totals']['logins_d']*($this->block->config->t_d_access_min_per/100.0)));
				$result['bytutorid'][$tutor->id]['al_logins_p'] = (($result['bytutorid'][$tutor->id]['logins_p'] < $this->block->config->t_p_access_min) || 
					($result['bytutorid'][$tutor->id]['logins_p'] < $result['totals']['logins_p']*($this->block->config->t_p_access_min_per/100.0)));
				$result['bytutorid'][$tutor->id]['al_accesses_d'] = (($result['bytutorid'][$tutor->id]['accesses_d'] < $this->block->config->t_d_activity_min) || 
					($result['bytutorid'][$tutor->id]['accesses_d'] < $result['totals']['accesses_d']*($this->block->config->t_d_activity_min_per/100.0)));
				$result['bytutorid'][$tutor->id]['al_accesses_p'] = (($result['bytutorid'][$tutor->id]['accesses_p'] < $this->block->config->t_p_activity_min) || 
					($result['bytutorid'][$tutor->id]['accesses_p'] < $result['totals']['accesses_p']*($this->block->config->t_p_activity_min_per/100.0)));
				$result['bytutorid'][$tutor->id]['al_tutordiary'] = $this->tutorDiaryCheck($tutor->id);
			}
		}
		echo($ajax->encode($result));
	}	
}

// Esta classe implementa o diário do tutor
class TutorDiary
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
	* Construtor do diário do tutor.
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
		$this->block = block_instance('relusp', $this->instance);
		$this->context = get_context_instance(CONTEXT_BLOCK, $this->block->instance->id);
	}
	
/**
	* Método para processar uma requisição. Recebe a requisição e processa usando
	* o método específico.
	* @param $func - Nome da operação a processar.
**/
	public function process($func) {
		switch($func) {
			case 'adddiaryentry' : $this->proc_adddiaryentry(); break;
			case 'repdiary' : $this->proc_repdiary(); break;
			case 'editdiaryentry1' : $this->proc_editdiaryentry1(); break;
			case 'editdiaryentry2' : $this->proc_editdiaryentry2(); break;
			case 'deletediaryentry' : $this->proc_deletediaryentry(); break;
		}
	}

/**
	* Verifica se o usuário pode ver os diários de todos os tutores.
	* Gera uma variável Javascript para representar essa condição.
**/
	public function canViewAll() {
		if (has_capability('block/relusp:reptutorallview', $this->context, NULL, false))
			echo('<script>var canViewAll=true;</script>');
		else
			echo('<script>var canViewAll=false;</script>');
	}

/**
	* Verifica se o usuário pode postar no diário do tutor.
	* Gera uma variável Javascript para representar essa condição.
**/
	public function canPost() {
		if (has_capability('block/relusp:reptutorpost', $this->context, NULL, false))
			echo('<script>var canPost=true;</script>');
		else
			echo('<script>var canPost=false;</script>');
	}

/**
	* Verifica se o usuário pode Editar as entradas do diário do tutor.
	* Gera uma variável Javascript para representar essa condição.
**/
	public function canEdit() {
		if (has_capability('block/relusp:reptutoredit', $this->context, NULL, false))
			echo('<script>var canEdit=true;</script>');
		else
			echo('<script>var canEdit=false;</script>');
	}

/**
	* Verifica se o usuário pode trocar o tutor das entradas no diário do tutor.
	* Gera uma variável Javascript para representar essa condição.
**/
	public function canChangeTutor() {
		if (has_capability('block/relusp:reptutorchangetutor', $this->context, NULL, false))
		   print 'checked';
		else
		   print 'disabled';
	}

/**
	* Verifica se o usuário pode Excluir as entradas do diário do tutor.
	* Gera uma variável Javascript para representar essa condição.
**/
	public function canDelete() {
		if (has_capability('block/relusp:reptutordelete', $this->context, NULL, false))
			echo('<script>var canDelete=true;</script>');
		else
			echo('<script>var canDelete=false;</script>');
	}

/**
	* Obtem a lista de estudantes de cada grupo que o tutor pertence.
	* Gera uma variável Javascript para representar essa condição.
**/
	public function getStudents() {
		$ajax = new HTML_AJAX_JSON();
		$result = array();

		// Grupo TODOS OS PARTICIPANTES 
		$rstudents = array();
		// Obtem estudantes objeto do diário do tutor
		if ($students = get_users_by_capability($this->context, 'block/relusp:tutordiarysubject', 'u.id, CONCAT(u.firstname, \' \', u.lastname) as name', 'firstname,lastname ASC', '', '', '', '', false)) {
		   foreach($students as $student)
			$rstudents[] = array('id' => $student->id, 'name' => $student->name);
		}
		// Preenche estudantes
		$result[]=array('name' => get_string('allparticipants'), 'students'=> $rstudents);

		// Obtem os grupos do tutor
		if ($groups = groups_get_all_groups($this->id, $this->userid, 0, 'g.id, g.name')) {
			// Obtem usuários que podem ser objeto de postagens no diário
			$subjects = get_users_by_capability($this->context, 'block/relusp:tutordiarysubject', 'u.id', '', '', '', '', '', false);
			foreach($groups as $group) {
				$rstudents2 = array();
				// Obtem estudantes do grupo
				if ($students = groups_get_members($group->id, 'u.id, CONCAT(u.firstname, \' \', u.lastname) as name', 'firstname,lastname ASC')) {
					foreach($students as $student) {
						// Se estudante não é objeto de postagens, pula.
						if ($subjects)
							if (!array_key_exists($student->id, $subjects))
								continue;
						// Preenche estudantes do grupo
						$rstudents2[] = array('id' => $student->id, 'name' => $student->name);
					}
				}
				// Armazena estudantes do grupo
				$result[]=array('name' => $group->name, 'students'=> $rstudents2);
			}
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
		if (insert_record('tutordiary', $diaryentry)){
			$result = true;
		        add_to_log($this->id, 'BlockRELUSP', 'add', "blocks/relusp/tutordiary.php?id=$this->id",'ADD entry in TUTORDIARY');
		} else
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
		$diaryentry->tutorid=required_param('tutor', PARAM_INT);
		$diaryentry->courseid=$this->id;
		$diaryentry->interactionid=required_param('interac', PARAM_INT);
		$diaryentry->timedevoted=required_param('timedevoted', PARAM_INT);
		$diaryentry->studentid=required_param('student', PARAM_INT);
		$diaryentry->requestdate=required_param('reqdate', PARAM_INT);
		$diaryentry->responsedate=required_param('respdate', PARAM_INT);
		$diaryentry->notes=required_param('obs', PARAM_TEXT);
		// Atualiza o registro
		if (update_record('tutordiary', $diaryentry)){
			$result = true;
		        add_to_log($this->id, 'BlockRELUSP', 'edit', "blocks/relusp/tutordiary.php?id=$this->id",'EDIT entry(#'.$diaryentry->id.') in TUTORDIARY',$diaryentry->id);
		} else
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
		if (delete_records_select('tutordiary', 'id='.$identry)){
			$result = true;
		        add_to_log($this->id, 'BlockRELUSP', 'delete', "blocks/relusp/tutordiary.php?id=$this->id",'DELETE entry(#'.$identry.') in TUTORDIARY',$identry);
		} else
			$result = false;
		// Codifica resultado em JSON
		echo($ajax->encode($result));
	}









}

// Esta classe implementa o Relatório de alunos
class RepStudent
{
	private $id = 0;			// Id do curso atual
	private $instanceid = 0;	// Id da instância do bloco no curso
	private $from = 0;			// Data de início do relatório
	private $to = 0;			// Data de fim do relatório
	private $userid = 0;		// Id do usuário
	private $groupid = 0;		// Id do grupo do relatório
	private $instance = null;	// Objeto instância do bloco
	private $block = null;		// Objeto bloco do bloco
	private $context = null;	// Objeto contexto do bloco
	private $studentsarray = null;	// Armazena array de estudantes válidos
	private $studentsid = '';	// Armazena string com Ids de estudantes válidos

//Guardar resultados de ocorrencias em Atividades
	private $act_forum_view = null;	// Array associativo com resultados dos fóruns	
	private $act_forum_post = null;
	private $act_forum_count = null;

	private $act_assignment_view = null; // Array associativo com resultados das tarefas
	private $act_assignment_post = null;
	private $act_assignment_post_max = null;// Array associativo com número máximo de tarefas possíveis
	private $act_assignment_count = null;

	private $act_chat_view = null; // Array associativo com resultados dos chats
	private $act_chat_post = null;	
	private $act_chat_count = null;	

	private $act_wiki_view = null; // Array associativo com resultados das wikis
	private $act_wiki_post = null;	
	private $act_wiki_count = null;	

	private $act_quiz_view = null; // Array associativo com resultados dos questionários
	private $act_quiz_post = null;
	private $act_quiz_post_max = null; // Array associativo com número máximo de questionários possíveis
	private $act_quiz_count = null;

	private $act_book_view = null;	// Array associativo com resultados dos livros
	private $act_book_count = null;

	private $act_lesson_view = null; // Array associativo com resultados das lições
	private $act_lesson_post = null;
	private $act_lesson_count = null;

	private $act_questionnaire_view = null;	// Array associativo com resultados das enquetes
	private $act_questionnaire_post = null;
	private $act_questionnaire_count = null;


	private $inc_forum = 0;		// Sinaliza se vai incluir fórum no processamento
	private $inc_assignment = 0;		// Sinaliza se vai incluir tarefas no processamento
	private $inc_chat = 0;		// Sinaliza se vai incluir chat no processamento
	private $inc_wiki = 0;		// Sinaliza se vai incluir wiki no processamento
	private $inc_quiz = 0;		// Sinaliza se vai incluir questionário no processamento
	private $inc_book = 0;		// Sinaliza se vai incluir Book no processamento
	private $inc_lesson = 0;		// Sinaliza se vai incluir lição no processamento
	private $inc_questionnaire = 0;		// Sinaliza se vai incluir enquete no processamento

	private $met = 0;
	

/**
	* Construtor do Relatório de Alunos.
	* @param $id - Id do curso atual.
	* @param $instanceid - Id da instância do bloco atual.
	* @param $userid - Id do tutor gerando o relatório.
**/
	function __construct($id, $instanceid, $userid) {
		$this->id = $id;
		$this->instanceid = $instanceid;
		$this->userid = $userid;
		// Carrega os objetos instância, bloco e contexto de segurança
		$this->instance=get_record('block_instance', 'id', $this->instanceid);
		$this->block = block_instance('relusp', $this->instance);
		$this->context = get_context_instance(CONTEXT_BLOCK, $this->block->instance->id);
	}
	
/**
	* Verifica se o tutor pode ver o relatório de todos os alunos.
	* Gera uma variável Javascript para representar essa condição.
**/
	public function canViewAll() {
		if (has_capability('block/relusp:repstudentsallview', $this->context, NULL, false))
			echo('<script>var canViewAll=true;</script>');
		else
			echo('<script>var canViewAll=false;</script>');	
	}
	
/**
	* Verifica se o tutor pode ver o relatório do seu grupo.
	* Gera uma variável Javascript para representar essa condição.
**/
	public function canViewGroup() {
		if (has_capability('block/relusp:repstudentsgroupview', $this->context, NULL, false))
			echo('<script>var canViewGroup=true;</script>');
		else
			echo('<script>var canViewGroup=false;</script>');
	}
	
/**
	* Obtem a lista de grupos para geração do relatório de alunos.
	* Gera uma variável Javascript para representar essa condição.
**/
	public function getGroups() {
		$ajax = new HTML_AJAX_JSON();
		$result = array();
		// Se pode ver todos, vai retornar todos os grupos
		if (has_capability('block/relusp:repstudentsallview', $this->context, NULL, false))
			$u=0;
		else  // Senão, apenas os seus grupos
			$u=$this->userid;
		// Obtem grupos
		if ($groups = groups_get_all_groups($this->id, $u, 0, 'g.id, g.name')) {
			foreach($groups as $group)
				// Preenche grupos
				$result[]=array('name' => $group->name, 'id'=> $group->id);
		}
		// Cria variável Javascript
		echo('<script>var groups = '.$ajax->encode($result).';</script>');	
	}
	
/**
	* Método para processar uma requisição. Recebe a requisição e processa usando
	* o método específico.
	* @param $func - Nome da operação a processar.
**/
	public function process($func) {
		// Precisa de no mínimo poder ver o relatório dos seu grupo
		require_capability('block/relusp:repstudentsgroupview', $this->context, NULL, false);
		switch($func) {
			case 'repstudent' : $this->proc_repstudent(); break;
		}
	}

/**
	* Funcao permite consultar se existe atividades daquele tipo na disciplina, caso tenha na pagina de exibicao do relatorio de alunos, as opcoes de atividades ficarao selecionadas

**/
	public function selectActivities($type) {
		if ($this->getActivities($type))
		   print 'checked';
		else
		   print 'disabled';
	}
	
/**
	* Calcula a mediana de uma série de valores pela definição.
	* @param $values - array com os valores.
	* @return float (mediana calculada).
**/
	private function calcMedian($values) {
		sort($values);
		$n = count($values);
		$h = intval($n / 2);
		if($n % 2 == 0) { 
			$median = ($values[$h] + $values[$h-1]) / 2; 
		} else { 
			$median = $values[$h]; 
		}
		return $median;
	}


/**
	* Calcula a média de uma série de valores.
	* @param $values - array com os valores.
	* @return float (média calculada).
**/
	private function calcAverage($values) {
		$total = array_sum($values);
		return $total/floatval(count($values));
	}
	
/**
	* Obtem a lista de atividades do tipo especificado.
	* @param $type - tipo da atividade.
	* @return array (array com os objetos das atividades).
**/
	function getActivities($type) {
        global $CFG;
		// Obtem todas as seções do curso
        $query="SELECT id,sequence FROM {$CFG->prefix}course_sections WHERE course={$this->id}";
        $sections=get_records_sql($query);
        if ($sections) {
		// Gera a lista das atividades na seção
                $activities='';
                foreach ($sections as $section) {
		    if ($section->sequence)
			if ($activities=='')
			   $activities.=$section->sequence;
			else
			   $activities.=','.$section->sequence;
                }
		// Obtem as instâncias das atividades
                $query="SELECT {$CFG->prefix}course_modules.id as id, {$CFG->prefix}course_modules.instance as instance FROM {$CFG->prefix}course_modules, {$CFG->prefix}modules WHERE {$CFG->prefix}course_modules.visible=1 AND {$CFG->prefix}course_modules.module={$CFG->prefix}modules.id AND  {$CFG->prefix}modules.name='$type' AND {$CFG->prefix}course_modules.id IN ($activities)";
                $instances=get_records_sql($query);
		// Gera lista de Ids das instâncias
                $ids='';
		if ($instances)
                  foreach ($instances as $instance) {
		    if ($ids=='')
			$ids.=$instance->instance;
		    else
			$ids.=','.$instance->instance;
                  }
		// Obtem as atividades
		if ($ids)
		   return get_records_select($type, "id IN ($ids)");
		else
		   return null;
        }
        return null;
	}
	
/**
	Processamento MOD_FORUM (Forum)
**/
	function processForum() {
		//VIEWS
		// Inicializa a Array para somatoria
		foreach($this->studentsarray as $student)
		       $this->act_forum_view[$student]=0;
		// Obtem views - apenas da leitura dos topicos de foruns
		if ($views = get_records_select('log', "course={$this->id} AND module='forum' AND action='view discussion' AND userid IN ({$this->studentsid}) AND time > {$this->from} AND time < {$this->to}", '', 'id, userid')) {
		   foreach ($views as $view)
			// Contabiliza VIEWS
		        $this->act_forum_view[$view->userid]++;
		}

		//POSTS - add discussion e add posts
		foreach($this->studentsarray as $student)
		       $this->act_forum_post[$student]=0;
		if ($views = get_records_select('log', "course={$this->id} AND module='forum' AND action IN ('add discussion','add post') AND userid IN ({$this->studentsid}) AND time > {$this->from} AND time < {$this->to}", '', 'id, userid')) {
		   foreach ($views as $view)
		        $this->act_forum_post[$view->userid]++;
		}

		//Quantidade de ATIVIDADES na disciplina
		if ($atividades = $this->getActivities('forum')) {
		   $this->act_forum_count = count($atividades) - 1; //-1 pois o fórum de noticias não é computado
		}
		


		//POSTS - retirado por complexidade
//		$foundvalidactivity = false;  // Marca se há atividade válida
//		$this->act_forum_post = array();
//		// Obtem atividades fórum do curso
//		if ($forums = $this->getActivities('forum')) {
//			foreach ($forums as $forum) {
//				// Ignora fóruns de notícias
//				if ($forum->type != 'news') {
//					if (!$foundvalidactivity) {
//						// Encontrei primeiro fórum válido
//						$foundvalidactivity=true;
//						$this->act_forum_post = array();
//						// Inicializa com zero as postagens dos alunos
//						foreach($this->studentsarray as $student)
//							$this->act_forum_post[$student]=0;
//					}
//					// Obtem a lista de todas as discussões no fórum
//					$ids='';
//					$discussions=get_records('forum_discussions', 'forum', $forum->id, '', 'id');
//					foreach ($discussions as $discussion) {
//						if ($ids)
//							$ids.=','.$discussion->id;
//						else
//							$ids=$discussion->id;
//					}
//					// Obtem postagens de todos os alunos
//					if ($posts = get_records_select('forum_posts', "discussion IN ($ids) AND userid IN ({$this->studentsid})", '', 'id, userid, modified')) {
//						foreach ($posts as $post) {
//							if ((intval($post->modified) > $this->from )&& (intval($post->modified) < $this->to)) {
//								// Postagem dentro do período. Contabiliza.
//								$this->act_forum_post[$post->userid]++;
//							}
//						}
//					}
//				}
//			}
//		}
	}

/**
	Processamento do MOD_CHAT (Chat)
**/
	function processChat() {
		//VIEWS
		foreach($this->studentsarray as $student)
		       $this->act_chat_view[$student]=0;
		if ($views = get_records_select('log', "course={$this->id} AND module='chat' AND action='view' AND userid IN ({$this->studentsid}) AND time > {$this->from} AND time < {$this->to}", '', 'id, userid')) {
		   foreach ($views as $view)
		        $this->act_chat_view[$view->userid]++;
		}

		//POSTS
		foreach($this->studentsarray as $student)
		       $this->act_chat_post[$student]=0;
		if ($views = get_records_select('log', "course={$this->id} AND module='chat' AND action='talk' AND userid IN ({$this->studentsid}) AND time > {$this->from} AND time < {$this->to}", '', 'id, userid')) {
		   foreach ($views as $view)
		        $this->act_chat_post[$view->userid]++;
		}

		//Quantidade de ATIVIDADES na disciplina
		if ($atividades = $this->getActivities('chat')) {
		   $this->act_chat_count = count($atividades);
		}

		//POSTS - retirado por complexidade
//		$foundvalidactivity = false;  // Marca se há atividade válida
//		$this->act_chat_post = null;
//		// Obtem atividades chat do curso
//		if ($chats = $this->getActivities('chat')) {
//			foreach ($chats as $chat) {
//				// Verifica se chat está disponível
//				if (intval($chat->chattime) > $this->from) {
//					if (!$foundvalidactivity) {
//						// Encontrei primeiro chat válido
//						$foundvalidactivity=true;
//						$this->act_chat_post = array();
//						// Inicializa com zero as participações dos alunos
//						foreach($this->studentsarray as $student)
//							$this->act_chat_post[$student]=0;
//					}
//					// Obtem as mensagens do chat
//					if ($messages = get_records_select('chat_messages', "chatid={$chat->id} AND userid IN ({$this->studentsid}) AND timestamp > {$this->from} AND timestamp < {$this->to}", '', 'id, userid, system')) {
//						foreach ($messages as $message)
//							// Se for postagem, contabiliza
//							if ($message->system==0)
//								$this->act_chat_post[$message->userid]++;
//					}
//				}
//			}
//		}
	}

/**
	Processamento das MOD_ASSIGNMENT (Tarefas: Modalidade Avançada de carregamento de arquivos, texto online, envio de arquivo unico ...)
**/
	function processAssignment() {
		//VIEWS
		foreach($this->studentsarray as $student)
		       $this->act_assignment_view[$student]=0;
		if ($views = get_records_select('log', "course={$this->id} AND module='assignment' AND action='view' AND userid IN ({$this->studentsid}) AND time > {$this->from} AND time < {$this->to}", '', 'id, userid')) {
		   foreach ($views as $view)
		        $this->act_assignment_view[$view->userid]++;
		}

		//POSTS
		foreach($this->studentsarray as $student)
		       $this->act_assignment_post[$student]=0;
		if ($views = get_records_select('log', "course={$this->id} AND module='assignment' AND action IN ('upload') AND userid IN ({$this->studentsid}) AND time > {$this->from} AND time < {$this->to}", '', 'id, userid')) {
		   foreach ($views as $view)
		        $this->act_assignment_post[$view->userid]++;
		}

		//Quantidade de ATIVIDADES na disciplina
		if ($atividades = $this->getActivities('assignment')) {
		   $this->act_assignment_count = count($atividades);
		}

		//POSTS - retirado por complexidade
//		$foundvalidactivity = false;
//		$this->act_assignment_post = null;
//		$this->act_assignment_post_max = null;
//		// Obtem atividades tarefa do curso
//		if ($assignments = $this->getActivities('assignment')) {
//			foreach ($assignments as $assignment) {
//				// Verifica se está dentro da faixa de datas
//				if ((intval($assignment->timedue) > $this->from) && (intval($assignment->timeavailable) < $this->to)) {
//					if (!$foundvalidactivity) {
//						// Encontrei primeira tarefa válida
//						$foundvalidactivity=true;
//						$this->act_assignment_post = array();
//						$this->act_assignment_post_max = array();
//						// Inicializa com zero os envios dos alunos
//						foreach($this->studentsarray as $student) {
//							$this->act_assignment_post[$student]=0;
//							$this->act_assignment_post_max[$student]=0;
//						}
//					}
//					// Incrimenta o número de tarefas válidas totais
//					foreach($this->studentsarray as $student)
//						$this->act_assignment_post_max[$student]++;
//					// Obtem envios para essa tarefa
//					if ($submissions = get_records_select('assignment_submissions', "assignment={$assignment->id} AND userid IN ({$this->studentsid})", '', 'id, userid, timemodified')) {
//						foreach ($submissions as $submission)
//							if ((intval($submission->timemodified) > $this->from )&& (intval($submission->timemodified) < $this->to)) {
//								// Se envio está dentro da data, contabiliza
//								$this->act_assignment_post[$submission->userid]++;
//							}
//					}
//				}
//			}
//		}
	}


/**
	Processamento do MOD_WIKI (Wiki)
**/
	function processWiki() {
		//VIEWS
		foreach($this->studentsarray as $student)
		       $this->act_wiki_view[$student]=0;
		if ($views = get_records_select('log', "course={$this->id} AND module='wiki' AND action='view' AND userid IN ({$this->studentsid}) AND time > {$this->from} AND time < {$this->to}", '', 'id, userid')) {
		   foreach ($views as $view)
		        $this->act_wiki_view[$view->userid]++;
		}

		//POSTS
		foreach($this->studentsarray as $student)
		       $this->act_wiki_post[$student]=0;
		if ($views = get_records_select('log', "course={$this->id} AND module='wiki' AND action='edit' AND userid IN ({$this->studentsid}) AND time > {$this->from} AND time < {$this->to}", '', 'id, userid')) {
		   foreach ($views as $view)
		        $this->act_wiki_post[$view->userid]++;
		}

		//Quantidade de ATIVIDADES na disciplina
		if ($atividades = $this->getActivities('wiki')) {
		   $this->act_wiki_count = count($atividades);
		}

		//POSTS - retirado, pois dado importante eh edicao do wiki e nao somente criar paginas
//		$foundvalidactivity = false;	// Marca se há atividade válida
//		$this->act_wiki_post = null;
//		// Obtem atividades wiki do curso
//		if ($wikis = $this->getActivities('wiki')) {
//			foreach ($wikis as $wiki) {
//				if (!$foundvalidactivity) {
//					// Encontrei primeira tarefa válida
//					$foundvalidactivity=true;
//					$this->act_wiki_post = array();
//					// Inicializa com zero as postagens dos alunos
//					foreach($this->studentsarray as $student)
//						$this->act_wiki_post[$student]=0;
//				}
//				// Obtem postagens no wiki
//				if ($pages = get_records_select('wiki_pages', "wiki={$wiki->id} AND userid IN ({$this->studentsid}) AND lastmodified > {$this->from} AND lastmodified < {$this->to}", '', 'id, userid')) {
//					foreach ($pages as $page)
//						// Contabiliza postagem
//						$this->act_wiki_post[$page->userid]++;
//				}
//			}
//		}
	}

/**
	Processamento do MOD_QUIZ(Questionário)
**/
	function processQuiz() {
		//VIEWS
		foreach($this->studentsarray as $student)
		       $this->act_quiz_view[$student]=0;
		if ($views = get_records_select('log', "course={$this->id} AND module='quiz' AND action='view' AND userid IN ({$this->studentsid}) AND time > {$this->from} AND time < {$this->to}", '', 'id, userid')) {
		   foreach ($views as $view)
		        $this->act_quiz_view[$view->userid]++;
		}

		//POSTS
		foreach($this->studentsarray as $student)
		       $this->act_quiz_post[$student]=0;
		if ($views = get_records_select('log', "course={$this->id} AND module='quiz' AND action='close attempt' AND userid IN ({$this->studentsid}) AND time > {$this->from} AND time < {$this->to}", '', 'id, userid')) {
		   foreach ($views as $view)
		        $this->act_quiz_post[$view->userid]++;
		}

		//Quantidade de ATIVIDADES na disciplina
		if ($atividades = $this->getActivities('quiz')) {
		   $this->act_quiz_count = count($atividades);
		}

		//POSTS - esta dando erro na contagem, este script conta tentativas semelhante ao acima
//		$foundvalidactivity = false;	// Marca se há atividade válida
//		$this->act_quiz_post = null;
//		$this->act_quiz_post_max = null;
//		// Obtem atividades questionário do curso
//		if ($quizes = $this->getActivities('quiz')) {
//			foreach ($quizes as $quiz) {
//				// Verifica se está dentro da faixa de datas
//				if ((intval($quiz->timeclose) > $this->from) && (intval($quiz->timeopen) < $this->to)) {
//					if (!$foundvalidactivity) {
//						// Encontrei primeiro questionário válido
//						$foundvalidactivity=true;
//						$this->act_quiz_post = array();
//						$this->act_quiz_post_max = array();
//						// Inicializa com zero os envios dos alunos
//						foreach($this->studentsarray as $student) {
//							$this->act_quiz_post[$student]=0;
//							$this->act_quiz_post_max[$student]=0;
//						}
//					}
//					// Incrimenta o número de questionários válidos totais
//					foreach($this->studentsarray as $student)
//						$this->act_quiz_post_max[$student]++;
//					// Obtem envios para esse questionário
//					if ($attempts = get_records_select('quiz_attempts', "quiz={$quiz->id} AND userid IN ({$this->studentsid})", '', 'id, userid, timemodified')) {
//						foreach ($attempts as $attempt)
//							if ((intval($attempt->timemodified) > $this->from )&& (intval($attempt->timemodified) < $this->to)) {							
//								// Se envio está dentro da data, contabiliza
//								$this->act_quiz_post[$attempt->userid]++;
//							}
//					}
//				}
//			}
//		}
	}
	
/**
	Processamento do MOD_BOOK (Livro)
**/
	function processBook() {
		//VIEWS - somente, o recurso LIVRO nao tem post
		// Inicializa com zero as participações dos alunos
		foreach($this->studentsarray as $student)
		       $this->act_book_view[$student]=0;
		// Obtem views do Livro
		if ($views = get_records_select('log', "course={$this->id} AND module='book' AND action='view' AND userid IN ({$this->studentsid}) AND time > {$this->from} AND time < {$this->to}", '', 'id, userid')) {
		   foreach ($views as $view)
			// Contabiliza VIEWS
		        $this->act_book_view[$view->userid]++;
		}

		//Quantidade de ATIVIDADES na disciplina
		if ($atividades = $this->getActivities('book')) {
		   $this->act_book_count = count($atividades);
		}

	}

/**
	Processamento do MOD_LESSON (Lição)
**/
	function processLesson() {
		//VIEWS
		foreach($this->studentsarray as $student)
		       $this->act_lesson_view[$student]=0;
		if ($views = get_records_select('log', "course={$this->id} AND module='lesson' AND action='view' AND userid IN ({$this->studentsid}) AND time > {$this->from} AND time < {$this->to}", '', 'id, userid')) {
		   foreach ($views as $view)
		        $this->act_lesson_view[$view->userid]++;
		}

		//POSTS
		foreach($this->studentsarray as $student)
		       $this->act_lesson_post[$student]=0;
		if ($views = get_records_select('log', "course={$this->id} AND module='lesson' AND action='end' AND userid IN ({$this->studentsid}) AND time > {$this->from} AND time < {$this->to}", '', 'id, userid')) {
		   foreach ($views as $view)
		        $this->act_lesson_post[$view->userid]++;
		}

		//Quantidade de ATIVIDADES na disciplina
		if ($atividades = $this->getActivities('lesson')) {
		   $this->act_lesson_count = count($atividades);
		}
	}

/**
	Processamento do MOD_QUESTIONNAIRE (Enquete)
**/
	function processQuestionnaire() {
		//VIEWS
		foreach($this->studentsarray as $student)
		       $this->act_questionnaire_view[$student]=0;
		if ($views = get_records_select('log', "course={$this->id} AND module='questionnaire' AND action='view' AND userid IN ({$this->studentsid}) AND time > {$this->from} AND time < {$this->to}", '', 'id, userid')) {
		   foreach ($views as $view)
		        $this->act_questionnaire_view[$view->userid]++;
		}

		//POSTS
		foreach($this->studentsarray as $student)
		       $this->act_questionnaire_post[$student]=0;
		if ($views = get_records_select('log', "course={$this->id} AND module='questionnaire' AND action='submit' AND userid IN ({$this->studentsid}) AND time > {$this->from} AND time < {$this->to}", '', 'id, userid')) {
		   foreach ($views as $view)
		        $this->act_questionnaire_post[$view->userid]++;
		}

		//Quantidade de ATIVIDADES na disciplina
		if ($atividades = $this->getActivities('questionnaire')) {
		   $this->act_questionnaire_count = count($atividades);
		}
	}

/**
	* Faz processamento de todas as atividades marcadas para processamento no relatório
**/
	function processActivities() {
		if ($this->inc_forum) $this->processForum();
		if ($this->inc_chat) $this->processChat();
		if ($this->inc_assignment) $this->processAssignment();
		if ($this->inc_wiki) $this->processWiki();
		if ($this->inc_quiz) $this->processQuiz();
		if ($this->inc_book) $this->processBook();
		if ($this->inc_lesson) $this->processLesson();
		if ($this->inc_questionnaire) $this->processQuestionnaire();
	}
	
/**
	* Gera as informações do relatório de alunos. Os dados gerados são armazenados
	* numa estrutura de dados que depois é convertida em JSON para processamento
	* dentro da página do relatório.
**/
	public function proc_repstudent() {
		//$maxtime = 15*60; // Tempo máximo de inatividade = 15 minutos
		$maxtime = $this->block->config->s_maxtime * 60; //hds - configurar tempo maximo de inatividade em configuracoes do bloco

		// Obtem faixa de datas
		$this->from = required_param('from', PARAM_INT);
		$this->to = required_param('to', PARAM_INT);
		$periodo = ($this->to - $this->from)/(3600*24.0); // dias
		// Obtem grupo a processar
		$this->groupid = required_param('groupid', PARAM_INT);
		// Obtem atividades a processar
		$this->inc_forum = required_param('act_forum', PARAM_INT);
		$this->inc_chat = required_param('act_chat', PARAM_INT);
		$this->inc_assignment = required_param('act_assignment', PARAM_INT);
		$this->inc_wiki = required_param('act_wiki', PARAM_INT);
		$this->inc_quiz = required_param('act_quiz', PARAM_INT);
		$this->inc_book = required_param('act_book', PARAM_INT);
		$this->inc_lesson = required_param('act_lesson', PARAM_INT);
		$this->inc_questionnaire = required_param('act_questionnaire', PARAM_INT);
		$this->met = required_param('met', PARAM_INT);
		
		$ajax = new HTML_AJAX_JSON();
		$groupstudents = null;
		// Obtem todos os alunos que podem ser objeto do relatório
		if ($students = get_users_by_capability($this->context, 'block/relusp:tutordiarysubject', 'u.id, CONCAT(u.firstname, \' \', u.lastname) as name', 'firstname,lastname ASC', '', '', '', '', false)) {
			// Verifica se quer apenas um grupo
			if ($this->groupid)
				// Obtem alunos do grupo
				$groupstudents = groups_get_members($this->groupid, 'u.id');
			// Determina o número de dias no intervalo da análise
			$days = intval(($this->to-$this->from)/(24*3600));
			$this->studentsid = '';
			$lastlog = array();		// Tempo do último log do aluno
			$t_totaltime = array();	// Tempos de permanência do aluno
			$t_totallogins = array();	// Números de logins do aluno
			$t_totalaccess = array();	// Números de acessos (atividade) do aluno
			foreach($students as $student) {
				// Em caso de grupo, verifica se aluno está no grupo
				if (($groupstudents) && (!array_key_exists($student->id, $groupstudents)))
					continue;
				// Inicializa arrays que vão armazenar os logins, permanência, etc.
				$lastlog[$student->id] = 0;
				$t_totaltime[$student->id]=array();
				$t_totallogins[$student->id]=array();
				$t_totalaccess[$student->id]=array();
				// Os números de logins, permanência e atividade são armazenados por dia
				for($i=0; $i<$days; $i++) {
					$t_totaltime[$student->id][$i] = 0;
					$t_totallogins[$student->id][$i] = 0;
					$t_totalaccess[$student->id][$i] = 0;
				}
				// Gera uma string com os Ids dos alunos
				if ($this->studentsid == '')
					$this->studentsid.=$student->id;
				else
					$this->studentsid.=', '.$student->id;
			}
			// Armazena lista de alunos
			$this->studentsarray = array_keys($lastlog);
			if ($this->met==1) {
				foreach($students as $student) {
					$userid = $student->id;
					$logs = get_recordset_select('log' , "course={$this->id} AND userid={$userid} AND time > {$this->from} AND time < {$this->to}", 'time ASC', 'id, userid, time, course');
					while(!$logs->EOF) {
						$thistime = $logs->fields['time'];
						$day=intval(($thistime-$this->from)/(24*3600));
						$t_totalaccess[$userid][$day]++;
						if ($lastlog[$userid]==0) {
							$lastlog[$userid]=$thistime;
							$t_totallogins[$userid][$day]++;
						} else {
							$difftime = $thistime-$lastlog[$userid];
							if ($difftime <= $maxtime) {
								// Same session
								if ($difftime>0)
									$t_totaltime[$userid][$day]+=$difftime;
							} else {
								$t_totallogins[$userid][$day]++;
							}
							$lastlog[$userid]=$thistime;
						}
						$logs->MoveNext();
					}
				}
			} else if ($this->met==2) {
				$logs = get_recordset_select('log' , "course={$this->id} AND time > {$this->from} AND time < {$this->to}", 'time ASC', 'id, userid, time, course');
				while(!$logs->EOF) {
					if (!array_key_exists($logs->fields['userid'], $lastlog)){
						$logs->MoveNext();
						continue;
					}
					$userid = $logs->fields['userid'];
					$thistime = $logs->fields['time'];
					$day=intval(($thistime-$this->from)/(24*3600));
					$t_totalaccess[$userid][$day]++;
					if ($lastlog[$userid]==0) {
						$lastlog[$userid]=$thistime;
						$t_totallogins[$userid][$day]++;
					} else {
						$difftime = $thistime-$lastlog[$userid];
						if ($difftime <= $maxtime) {
							// Same session
							if ($difftime>0)
								$t_totaltime[$userid][$day]+=$difftime;
						} else {
							$t_totallogins[$userid][$day]++;
						}
						$lastlog[$userid]=$thistime;
					}
					$logs->MoveNext();
				}
			} else if ($this->met==3) {
				$logs = get_recordset_select('log' , "time > {$this->from} AND time < {$this->to}", 'time ASC', 'id, userid, time, course');
				while(!$logs->EOF) {
					if (($logs->fields['course'] != $this->id) || (!array_key_exists($logs->fields['userid'], $lastlog))){
						$logs->MoveNext();
						continue;
					}
					$userid = $logs->fields['userid'];
					$thistime = $logs->fields['time'];
					$day=intval(($thistime-$this->from)/(24*3600));
					$t_totalaccess[$userid][$day]++;
					if ($lastlog[$userid]==0) {
						$lastlog[$userid]=$thistime;
						$t_totallogins[$userid][$day]++;
					} else {
						$difftime = $thistime-$lastlog[$userid];
						if ($difftime <= $maxtime) {
							// Same session
							if ($difftime>0)
								$t_totaltime[$userid][$day]+=$difftime;
						} else {
							$t_totallogins[$userid][$day]++;
						}
						$lastlog[$userid]=$thistime;
					}
					$logs->MoveNext();
				}			
			} else {
				$logs = get_recordset_select('log' , "course={$this->id} AND userid IN ({$this->studentsid}) AND time > {$this->from} AND time < {$this->to}", 'time ASC', 'id, userid, time');
				while(!$logs->EOF) {
					$userid = $logs->fields['userid'];
					$thistime = $logs->fields['time'];
					$day=intval(($thistime-$this->from)/(24*3600));//guarda dia do log corrente para indices de arrays
					$t_totalaccess[$userid][$day]++; //guarda entradas de logs
					if ($lastlog[$userid]==0) { //primeiro log do aluno
						$lastlog[$userid]=$thistime; //guarda primeiro log
						$t_totallogins[$userid][$day]++; //1o login no espaco de tempo selecionado
					} else {
						$difftime = $thistime-$lastlog[$userid]; //diferenca de tempo com o ultimo log em segundos
						if ($difftime <= $maxtime) { //avalia tempo maximo de inatividade (mesma secao)
							// Same session
							if ($difftime>0)
								$t_totaltime[$userid][$day]+=$difftime; //soma tempo de atividade no sistema
						} else {
							$t_totallogins[$userid][$day]++; //se passou tempo maximo de inatividade, conta outro login
						}
						$lastlog[$userid]=$thistime; //guarda tempo do ultimo log
					}
					$logs->MoveNext();
				}			
			}
			// Inicializa array associativo que irá conter dados por aluno e totalizações.
			$result=array('bystudentid' => array(), 'totals' =>array(), 'totals2' =>array()); //hds-totals2 para incluir valores de medias/medianas e imprimir na barra flutuante do rodape. criado para nao atrapalhar o restante do modulo.
			
			// Processa tividades
			$activities = $this->processActivities();
			
			// Inicializa arrays
			$accesstime_acc_d=array();
			$logins_acc_d=array();
			$accesses_acc_d=array();
			$accesstime_acc_p=array();
			$logins_acc_p=array();
			$accesses_acc_p=array();
			
			// Itera para cada aluno preenchendo resultados de tempos e calculando e preenchendo totalizações.
			foreach($students as $student) {
				// Em caso de grupo, verifica se aluno está no grupo
				if (($groupstudents) && (!array_key_exists($student->id, $groupstudents)))
					continue;
				// Calcula tempos médios ou medianos
				//if ($this->block->config->s_func == 'MEAN') {
				//	$accesstime_d=$this->calcAverage(array_values($t_totaltime[$student->id]));
				//	$logins_d=$this->calcAverage(array_values($t_totallogins[$student->id]));
				//} else {
				//	$accesstime_d=$this->calcMedian($t_totaltime[$student->id]);
				//	$logins_d=$this->calcMedian($t_totallogins[$student->id]);
				//}
				// Totaliza para o período
				$accesstime_p=array_sum($t_totaltime[$student->id]);
				$logins_p=array_sum($t_totallogins[$student->id]);
				$accesses_p=array_sum($t_totalaccess[$student->id]);

				// permanencia e logins por dia (nao faz sentido calcular medianas aqui)
				$accesstime_d = $accesstime_p/$periodo; //media de tempo no sistema diariamente em SEGUNDOS
				$logins_d = $logins_p/$periodo; //media de logins no sistema diariamente em LOGINS
				$accesses_d = $accesses_p/$periodo; //media de logins no sistema diariamente em LOGINS

				// Constroi arrays que acumulam todos os resultados diários para gerar média ou mediana
				// de totalização no final
				//$accesstime_acc_d=array_merge($accesstime_acc_d, array_values($t_totaltime[$student->id]));
				//$logins_acc_d=array_merge($logins_acc_d, array_values($t_totallogins[$student->id]));
				$accesstime_acc_d[]=$accesstime_d;
				$logins_acc_d[]=$logins_d;
				$accesses_acc_d[]=$accesses_d;

				$accesstime_acc_p[]=$accesstime_p;
				$logins_acc_p[]=$logins_p;
				$accesses_acc_p[]=$accesses_p;
				
				// Preenche resultados 
				$result['bystudentid'][$student->id]=array(
				    'id' => "$student->id",
				    'course' => "$this->id",
				    'name' => "$student->name",
				    'accesstime_d' => $accesstime_d, 'al_accesstime_d' => false, 
				    'accesstime_p' => $accesstime_p, 'al_accesstime_p' => false, 
				    'logins_d' => $logins_d, 'al_logins_d' => false, 
				    'logins_p' => $logins_p, 'al_logins_p' => false, 
				    'accesses_d' => $accesses_d, 'al_accesses_d' => false,
  				    'accesses_p' => $accesses_p , 'al_accesses_p' => false,
				    'al_student' => false,

				    'act_forum_view' => ($this->act_forum_view)? $this->act_forum_view[$student->id] : 0,
				    'act_forum_post' => ($this->act_forum_post)? $this->act_forum_post[$student->id] : 0,
				    'act_forum_count' => ($this->act_forum_count)? $this->act_forum_count : 0,

				    'act_chat_view' => ($this->act_chat_view)? $this->act_chat_view[$student->id] : 0,
				    'act_chat_post' => ($this->act_chat_post)? $this->act_chat_post[$student->id] : 0,
				    'act_chat_count' => ($this->act_chat_count)? $this->act_chat_count : 0,

				    'act_assignment_view' => ($this->act_assignment_view)? $this->act_assignment_view[$student->id] : 0,
				    'act_assignment_post' => ($this->act_assignment_post)? $this->act_assignment_post[$student->id] : 0,
				    'act_assignment_post_max' => ($this->act_assignment_post_max)? $this->act_assignment_post_max[$student->id] : 0,
				    'act_assignment_count' => ($this->act_assignment_count)? $this->act_assignment_count : 0,

				    'act_wiki_view' => ($this->act_wiki_view)? $this->act_wiki_view[$student->id] : 0,
				    'act_wiki_post' => ($this->act_wiki_post)? $this->act_wiki_post[$student->id] : 0,
				    'act_wiki_count' => ($this->act_wiki_count)? $this->act_wiki_count : 0,

				    'act_quiz_view' => ($this->act_quiz_view)? $this->act_quiz_view[$student->id] : 0,
				    'act_quiz_post' => ($this->act_quiz_post)? $this->act_quiz_post[$student->id] : 0,
				    'act_quiz_post_max' => ($this->act_quiz_post_max)? $this->act_quiz_post_max[$student->id] : 0,
				    'act_quiz_count' => ($this->act_quiz_count)? $this->act_quiz_count : 0,

				    'act_book_view' => ($this->act_book_view)? $this->act_book_view[$student->id] : 0,
				    'act_book_count' => ($this->act_book_count)? $this->act_book_count : 0,

				    'act_lesson_view' => ($this->act_lesson_view)? $this->act_lesson_view[$student->id] : 0,
				    'act_lesson_post' => ($this->act_lesson_post)? $this->act_lesson_post[$student->id] : 0,
				    'act_lesson_count' => ($this->act_lesson_count)? $this->act_lesson_count : 0,

				    'act_questionnaire_view' => ($this->act_questionnaire_view)? $this->act_questionnaire_view[$student->id] : 0,
				    'act_questionnaire_post' => ($this->act_questionnaire_post)? $this->act_questionnaire_post[$student->id] : 0,
				    'act_questionnaire_count' => ($this->act_questionnaire_count)? $this->act_questionnaire_count : 0
				    );
				
			}
			// Calcula média ou mediana das totalizações e preenche resultados
			if ($this->block->config->s_func == 'MEAN'){
				$result['totals']=array(	
				    'accesstime_d' => $this->calcAverage($accesstime_acc_d),
				    'accesstime_p' => $this->calcAverage($accesstime_acc_p),
				    'logins_d' => $this->calcAverage($logins_acc_d),
				    'logins_p' => $this->calcAverage($logins_acc_p),
			  	    'accesses_d' => $this->calcAverage($accesses_acc_d),
			 	    'accesses_p' => $this->calcAverage($accesses_acc_p));
				//hds-totals2 imprimir MEDIANA no rodape
				$result['totals2']=array(	
				     'accesstime_d' => $this->calcMedian($accesstime_acc_d),
				     'accesstime_p' => $this->calcMedian($accesstime_acc_p),
				     'logins_d' => $this->calcMedian($logins_acc_d),
				     'logins_p' => $this->calcMedian($logins_acc_p),
				     'accesses_d' => $this->calcMedian($accesses_acc_d),
				     'accesses_p' => $this->calcMedian($accesses_acc_p),
				     'modocalc' => 'MEDIAN');
			}else if ($this->block->config->s_func == 'MEDIAN'){ 
				$result['totals']=array(	
				     'accesstime_d' => $this->calcMedian($accesstime_acc_d),
				     'accesstime_p' => $this->calcMedian($accesstime_acc_p),
				     'logins_d' => $this->calcMedian($logins_acc_d),
				     'logins_p' => $this->calcMedian($logins_acc_p),
				     'accesses_d' => $this->calcMedian($accesses_acc_d),
				     'accesses_p' => $this->calcMedian($accesses_acc_p));
				//hds-totals2 imprimir MEDIA no rodape
				$result['totals2']=array(
				      'accesstime_d' => $this->calcAverage($accesstime_acc_d),
				      'accesstime_p' => $this->calcAverage($accesstime_acc_p),
				      'logins_d' => $this->calcAverage($logins_acc_d),
				      'logins_p' => $this->calcAverage($logins_acc_p),
				      'accesses_d' => $this->calcAverage($accesses_acc_d),
				      'accesses_p' => $this->calcAverage($accesses_acc_p),
				      'modocalc' => 'MEAN');
			}
			
			$validacts=$this->inc_forum+$this->inc_assignment+$this->inc_chat+$this->inc_quiz+$this->inc_wiki+$this->inc_lesson+$this->inc_questionnaire;
			if ($students)
			  foreach($students as $student) {
				// Em caso de grupo, verifica se aluno está no grupo
				if (($groupstudents) && (!array_key_exists($student->id, $groupstudents)))
					continue;
				// Calcula e preenche os alarmes baseado nas parametrizações do relatório
				$result['bystudentid'][$student->id]['al_accesstime_d'] = (($result['bystudentid'][$student->id]['accesstime_d'] < ($this->block->config->s_d_permancence_min*3600)) || 
					($result['bystudentid'][$student->id]['accesstime_d'] < $result['totals']['accesstime_d']*($this->block->config->s_d_permancence_min_per/100.0)));
				$result['bystudentid'][$student->id]['al_accesstime_p'] = (($result['bystudentid'][$student->id]['accesstime_p'] < ($this->block->config->s_p_permancence_min*3600)) || 
					($result['bystudentid'][$student->id]['accesstime_p'] < $result['totals']['accesstime_p']*($this->block->config->s_p_permancence_min_per/100.0)));
				$result['bystudentid'][$student->id]['al_logins_d'] = (($result['bystudentid'][$student->id]['logins_d'] < $this->block->config->s_d_access_min) || 
					($result['bystudentid'][$student->id]['logins_d'] < $result['totals']['logins_d']*($this->block->config->s_d_access_min_per/100.0)));
				$result['bystudentid'][$student->id]['al_logins_p'] = (($result['bystudentid'][$student->id]['logins_p'] < $this->block->config->s_p_access_min) || 
					($result['bystudentid'][$student->id]['logins_p'] < $result['totals']['logins_p']*($this->block->config->s_p_access_min_per/100.0)));
				$result['bystudentid'][$student->id]['al_accesses_d'] = (($result['bystudentid'][$student->id]['accesses_d'] < $this->block->config->s_d_activity_min) || 
					($result['bystudentid'][$student->id]['accesses_d'] < $result['totals']['accesses_d']*($this->block->config->s_d_activity_min_per/100.0)));
				$result['bystudentid'][$student->id]['al_accesses_p'] = (($result['bystudentid'][$student->id]['accesses_p'] < $this->block->config->s_p_activity_min) || 
					($result['bystudentid'][$student->id]['accesses_p'] < $result['totals']['accesses_p']*($this->block->config->s_p_activity_min_per/100.0)));

				//Alarme das atividades: nas opcoes de atividades selecionadas verifica-se se existe pelo menos 1post(suficiente para acionar o alarme)
				if ($this->inc_forum)
                                   if ($this->act_forum_post)
                                      $a_forum=($this->act_forum_post[$student->id]>0)? 1 : 0;
                                   else
                                      $a_forum=1;
                                else
                                   $a_forum=0;

				if ($this->inc_assignment) if ($this->act_assignment_post) $a_assignment=($this->act_assignment_post[$student->id]>0)? 1 : 0; else $a_assignment=1; else $a_assignment=0;
				if ($this->inc_chat) if ($this->act_chat_post) $a_chat=($this->act_chat_post[$student->id]>0)? 1 : 0; else $a_chat=1; else $a_chat=0;
				if ($this->inc_wiki) if ($this->act_wiki_post) $a_wiki=($this->act_wiki_post[$student->id]>0)? 1 : 0; else $a_wiki=1; else $a_wiki=0;
				if ($this->inc_quiz) if ($this->act_quiz_post) $a_quiz=($this->act_quiz_post[$student->id]>0)? 1 : 0; else $a_quiz=1; else $a_quiz=0;
				if ($this->inc_lesson) if ($this->act_lesson_post) $a_lesson=($this->act_lesson_post[$student->id]>0)? 1 : 0; else $a_lesson=1; else $a_lesson=0;
				if ($this->inc_questionnaire) if ($this->act_questionnaire_post) $a_questionnaire=($this->act_questionnaire_post[$student->id]>0)? 1 : 0; else $a_questionnaire=1; else $a_questionnaire=0;

				//Aciona o alarme
				if ($validacts)
				   $result['bystudentid'][$student->id]['al_student'] = ((($a_forum+$a_chat+$a_assignment+$a_wiki+$a_quiz+$a_lesson+$a_questionnaire)/floatval($validacts)) >= 1);
				else
				   $result['bystudentid'][$student->id]['al_student'] = true;
			}
		echo($ajax->encode($result));			
		}
	}	
}


?>
