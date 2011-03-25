<?php
class block_term extends block_base {
    function init() {
	$this->title = get_string('titleblock', 'block_term');
	$this->version = 2011032500;
    }

    function get_content() {
        global $CFG, $COURSE, $USER;

        $context = get_context_instance(CONTEXT_SYSTEM);

        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;

	$context = get_context_instance(CONTEXT_BLOCK, $this->instance->id);
	
	$this->content->text .='<a href></a>';
	include('view_term.php'); //Formulario do TERMO (AJAX)

        return $this->content;
    }

    function applicable_formats() {
        return array('site' => true, 'course' => true);
    }

    //disponibiliza opcao editar bloco
    function instance_allow_config() {
       return true;
    }

}   // Here's the closing curly bracket for the class definition
    // and here's the closing PHP tag from the section above.
?>
