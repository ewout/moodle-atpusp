<?PHP
	require_once('../../config.php');
	global $CFG;
	require_login();
	require_once($CFG->libdir.'/ddllib.php');
	require_once($CFG->libdir.'/dmllib.php');

	//Guarda tempo da acao da entrada no mdl_log na tabela do bloco mdl_block_timestat
	$result=get_record('log','id',required_param('id', PARAM_INT));

	//Valores que serao inseridos no BD
	$record = new object();
	$record->log = $result->id;
	$record->count = $result->count+required_param('time', PARAM_INT);
	//Insere o registro
	insert_record('block_timestat', $record)
?>
