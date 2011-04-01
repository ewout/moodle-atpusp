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


/**
	* Esta classe implementa o bloco de relatórios USP
**/	
class block_relusp extends block_base {

/**
	* Inicializa bloco. Sobreescreve método da classe base.
**/
	function init() {
		// Informa no e versão do bloco.
		$this->title = get_string('relusp', 'block_relusp');
		$this->version = 2011030115;
		// Inicializa variáveis necessárias ao bloco.
		// Cria paramemetrizações padrão para o relatório de tutores
		$this->config->t_func = 'MEAN';
		$this->config->t_maxtime = 30;
		$this->config->t_d_access_min = 10;
		$this->config->t_d_access_min_per = 80;
		$this->config->t_p_access_min = 20;
		$this->config->t_p_access_min_per = 80;
		$this->config->t_d_permancence_min = 1.5;
		$this->config->t_d_permancence_min_per = 80;
		$this->config->t_p_permancence_min = 3.0;
		$this->config->t_p_permancence_min_per = 80;
		$this->config->t_d_activity_min = 20;
		$this->config->t_d_activity_min_per = 80;
		$this->config->t_p_activity_min = 50;
		$this->config->t_p_activity_min_per = 80;
		$this->config->t_reqsonschedule_perc = 90;
		$this->config->t_daystoreply = 1;
		// Cria paramemetrizações padrão para o relatório de alunos
		$this->config->s_func = 'MEAN';
		$this->config->s_maxtime = 30;
		$this->config->s_d_access_min = 10;
		$this->config->s_d_access_min_per = 80;
		$this->config->s_p_access_min = 20;
		$this->config->s_p_access_min_per = 80;
		$this->config->s_d_permancence_min = 1.5;
		$this->config->s_d_permancence_min_per = 80;
		$this->config->s_p_permancence_min = 3.0;
		$this->config->s_p_permancence_min_per = 80;
		$this->config->s_d_activity_min = 20;
		$this->config->s_d_activity_min_per = 80;
		$this->config->s_p_activity_min = 50;
		$this->config->s_p_activity_min_per = 80;
		//hds- retirado alerta de porcentagem de atividades postadas /facilitar interpretacao de tutores
		//$this->config->s_minactivitycompl_perc = 90;
	       
	}
	
/**
	* Informa onde o bloco pode aparecer. Sobreescreve método da classe base.
**/
	function applicable_formats() {
		// Pode aparecer em qualquer lugar.
		return array('all' => true);
	}
   
/**
	* Informa que o bloco possui configuração de instância. Sobreescreve método da classe base.
**/
	function instance_allow_config() {
		return true;
	}	
	
/**
	* Retorna o corpo do bloco. Sobreescreve método da classe base.
**/
	function get_content () {
		global $USER, $CFG;
		
		$this->content->footer = '';
		// Obtem contexto de segurança
		$context = get_context_instance(CONTEXT_BLOCK, $this->instance->id);
		if (has_capability('block/relusp:reptutorallview', $context, NULL, false)) {
			// Tutor pode ver todos os relatórios de tutores
			$this->content->text .= '<a href=\''.$CFG->wwwroot.'/blocks/relusp/reptutor.php?id='.$this->instance->pageid.'&instanceid='.$this->instance->id.'\'>'.get_string('reptutor', 'block_relusp').'<br></a>';
			// O trecho abaixo verifica se já existem interações cadastradas para o diário do tutor do curso em questão.
			// Caso não haja, ele cria as interações padrão. Isso é um hack, pois não existe nenhum gancho que é chamado
			// quando uma nova instância do bloco é criada.
			if (count_records('tutordiary_interactions', 'course', $this->instance->pageid)==0) {
				$new_interaction = new object();
				$new_interaction->course = $this->instance->pageid;
				$new_interaction->id = 0;
				for ($i=1; $i<=3; $i++) {
					$new_interaction->interaction = get_string('interaction'.$i, 'block_relusp');
					insert_record('tutordiary_interactions', $new_interaction); 
				}
			}
		}	
		if (has_capability('block/relusp:reptutorallview', $context, NULL, false) || has_capability('block/relusp:reptutorselfview', $context, NULL, false)
							|| has_capability('block/relusp:reptutorpost', $context, NULL, false))
			// Pode ver diário do tutor
			$this->content->text .= '<a href=\''.$CFG->wwwroot.'/blocks/relusp/tutordiary.php?id='.$this->instance->pageid.'&instanceid='.$this->instance->id.'\'>'.get_string('tutordiary', 'block_relusp').'<br></a>';		
		if (has_capability('block/relusp:repstudentsallview', $context, NULL, false) || has_capability('block/relusp:repstudentsgroupview', $context, NULL, false))
			// Pode ver relatório de alunos
			$this->content->text .= '<a href=\''.$CFG->wwwroot.'/blocks/relusp/repstudent.php?id='.$this->instance->pageid.'&instanceid='.$this->instance->id.'\'>'.get_string('repstudent', 'block_relusp').'<br></a>';		
		
		return $this->content;
	}
}

?>
