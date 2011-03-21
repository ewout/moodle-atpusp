<?PHP
//PLIK okreslajacy prawa dostepu do aplikacji.
//Wylacznie administrator posiada dostep do uzytkowania o czym swiadczy wartosc: CAP_ALLOW
//Dla pozostalych uzytkowników ustawienie CAP_PREVENT - zabrania im korzystania z aplikacji.
$block_timestat_capabilities = array(
	'block/timestat:view' => array(
    'captype' => 'read',
          'contextlevel' => CONTEXT_BLOCK,
          'legacy' => array (
              'guest' => CAP_PREVENT,
              'user' => CAP_PREVENT,
              'student' => CAP_PREVENT,
              'teacher' => CAP_PREVENT,
              'editingteacher' => CAP_PREVENT,
              'coursecreator' => CAP_PREVENT,
	      'admin' => CAP_ALLOW
          )
      )
);


?>