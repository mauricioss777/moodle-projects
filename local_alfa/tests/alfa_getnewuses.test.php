<?php
/**
 * Testa as funções de busca do plano de Ensino.
 *
 */

require_once('../classes/alfa.class.php');
require_once('../../../config.php');
if(!is_siteadmin()){
    die('Você não tem permissão de executar este script.');
}

echo print_r(Alfa::getNewUsers(),true);
?>
