<?php
/**
 * Testa as funções de busca do plano de Ensino.
 *
 */

define('CLI_SCRIPT', true);

require_once('../../../config.php');
require_once('../classes/alfa.class.php');

if(!is_siteadmin()){
    // die('Você não tem permissão de executar este script.');
}

$courseinfo = Alfa::registrarAprovacaoEmExtensao('254799', '683697', 10, true);
