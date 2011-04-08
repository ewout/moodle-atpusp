<?php
class block_term extends block_base {
    function init() {
	$this->title = get_string('titleblock', 'block_term');
	$this->version = 2011040502;
	$this->config->titleterm = 'TERMO DE CONSENTIMENTO LIVRE E ESCLARECIDO – ALUNO REDEFOR';
	$this->config->moretermtitle = 'SAIBA MAIS';
	$this->config->oktermtitle = 'Congratulações';
	$this->config->bodyterm = 'Na condição de integrante do PROGRAMA REDE SÃO PAULO DE FORMAÇÃO DOCENTE – REDEFOR, declaro concordar com a utilização, para fins acadêmicos, das informações contidas no ambiente virtual de aprendizagem. Tenho ciência que os responsáveis pela condução da pesquisa asseguram o anonimato dos alunos e dos tutores por meio da supressão do nome e/ou qualquer sinal identificador dos participantes. Declaro compreender que as informações obtidas só podem ser usadas para fins científicos, de acordo com a ética da academia e que a participação nessa pesquisa não comporta qualquer remuneração. ';
	$this->config->moretermbody = 'Caros cursistas:<br /><br />Na próxima semana, iniciaremos o Módulo III do nosso curso. A sua participação tem sido fundamental para o desenvolvimento dele.<br />No momento, vimos solicitar-lhes a assinatura do Termo de Consentimento Livre e Esclarecido que você encontrará no acesso ao AVA.<br />Tal pedido deve-se ao fato de que, com as suas participações, na forma de postagens, vem sendo gerados dados bastante importantes que podem ser utilizados para muitas pesquisas.<br />Sabemos que em nosso país pesquisas em educação ainda são escassas em comparação com outros. E, a nossa Universidade, historicamente local de pesquisa e produção de conhecimento portanto, não pode deixar passar essa oportunidade de realizá-la. <br />A pesquisa, uma das funções primordiais da nossa Universidade, neste caso articulada à extensão, assume um caráter especial, pois tem como foco a escola básica, o gestor e o professor que nela atuam, com suas necessidades formativas.<br />Os temas dos projetos de pesquisa acerca do REDEFOR-USP são muitos e variados, de maneira que os resultados, indiscutivelmente, trarão benefícios ao professor, ao gestor e à escola como um todo. <br />Todos os dados e informações pessoais serão resguardados no sentido de preservar a sua identidade. Apesar de sua adesão ao Termo de Consentimento Livre e Esclarecido não ser obrigatória para continuidade no curso, contamos com sua participação enquanto agente de nossas pesquisas.<br />Em caso de dúvidas, entre em contato com o Help Desk.<br /><br /><br />Gil da Costa Marques.<br />Coordenador Geral do REDEFOR-USP.';
	$this->config->oktermbody = 'Obrigado por colaborar com a Universidade de São Paulo';
    }

    function get_content() {
        global $CFG, $COURSE, $USER;

        $this->content = new stdClass;
	$context = get_context_instance(CONTEXT_BLOCK, $this->instance->id);

	$this->content->text .='';
	$this->content->footer .= '';

        if (!isloggedin() or isguestuser() or !has_capability('block/term:viewblock', $context, NULL, false)) {
            $this->content = NULL;
            return $this->content;
        }
	
	// Link para relatorio
	if (has_capability('block/term:viewreport', $context, NULL, false)) {
		$this->content->footer .= '
<form name="csv_form" id="csv_form" target="_blank" method="post" enctype="application/x-www-form-urlencoded;charset=UTF-8" action="'.$CFG->wwwroot.'/blocks/term/csv_processor.php">
<input type="hidden" name="csv" id="csv" value="" />
<input type="hidden" name="name" id="name" value="" />
<input type="submit" value="'.get_string('export', 'block_term').'" />
</form>
';
		include('view_report.php');

	}

	// Verificar se usuario respondeu
	$termuser = get_record('block_term','user', $USER->id);
	if (!$termuser && has_capability('block/term:enableterm', $context, NULL, false)){ //nao respondeu ainda e tem permissao, exibe termo
	   include('view_term.php'); //Formulario do TERMO (AJAX)
	}

        return $this->content;
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
	

}
?>
