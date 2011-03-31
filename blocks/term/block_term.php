<?php
class block_term extends block_base {
    function init() {
	$this->title = get_string('titleblock', 'block_term');
	$this->version = 2011033000;
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
		$this->content->footer .=  '<a href="#">'.get_string('export', 'block_term').'<br></a>';
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
