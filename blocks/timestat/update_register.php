<?PHP
//CONFERIR QUEM ESTA SOLICITANDO ESTA PAGINA
$refering=parse_url($_SERVER['HTTP_REFERER']); //host que esta solicitando
if ($refering['host']==$_SERVER['HTTP_HOST']){ //se for o servidor OK
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
	insert_record('block_timestat', $record);

} else { //caso nao seja o servidor, banir acesso
   echo "Não é permitido acessar essa URL diretamente!";
}

?>
