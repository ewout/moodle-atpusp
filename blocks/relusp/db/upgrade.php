<?php

// This file keeps track of upgrades to 
// the activity_modules block
//
// Sometimes, changes between versions involve
// alterations to database structures and other
// major things that may break installations.
//
// The upgrade function in this file will attempt
// to perform all the necessary actions to upgrade
// your older installtion to the current version.
//
// If there's something it cannot do itself, it
// will tell you what you need to do.
//
// The commands in here will all be database-neutral,
// using the functions defined in lib/ddllib.php


//Processo para fazer UPGRADE
//1) incluir instrucoes nesta classe "xmldb_block_relusp_upgrade"
//2) incluir instrucoes no /moodle/blocks/relusp/db/install.xml
//3) alterar a versao do bloco em /moodle/blocks/relusp/block_relusp.php (para o moodle identificar que precisa fazer upgrade)

function xmldb_block_relusp_upgrade($oldversion = 0) {

    $result = true;

    /// Novo atributo na tabela MDL_tutordiary, campo RESPONSEDATE para guardar data da resposta do tutor (BLOCKRELUSP:diario do tutor)
    if ($result && $oldversion < 2011011704) {
        //http://docs.moodle.org/en/Development:Installing_and_upgrading_plugin_database_tables
	$table = new XMLDBTable('tutordiary');
        $field = new XMLDBField('responsedate');
        if (!field_exists($field)) {
           $field->setAttributes(XMLDB_TYPE_INTEGER, '12', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'requestdate');
           $field->comment = 'Este campo guardara a data de resposta do tutor';
           /// Incluir campo na tabela
           $result = $result && add_field($table, $field);
        }
    }

    /// Novo atributo na tabela MDL_tutordiary, campo TIMEDEVOTED para guardar tempo dedicado a tarefa (BLOCKRELUSP:diario do tutor)
    if ($result && $oldversion < 2011020901) {
	$table = new XMLDBTable('tutordiary');
        $field = new XMLDBField('timedevoted');
        if (!field_exists($field)) {
           $field->setAttributes(XMLDB_TYPE_INTEGER, '12',  XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'interactionid');
           $field->comment = 'Este campo guardara o tempo dedicado pelo tutor a tarefa';
           /// Incluir campo na tabela
           $result = $result && add_field($table, $field);
        }
    }

    return $result;
}


