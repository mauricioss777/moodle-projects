<?php

/**
 * Testa a função de alterar o link EAD no alfa 
 *
 */

define('CLI_SCRIPT', true);

require_once('../../../config.php');
require_once('../classes/alfa.class.php');

//$courseinfo = Alfa::updateLinkEaD(array(''), 'https://www.univates.br/virtual/course/view.php?id=');
$courseinfo = Alfa::updateLinkEaD(array('345158'), 'https://www.univates.br/virtual/course/view.php?id=40864');
//$courseinfo = Alfa::updateLinkEaD(array('345158'), '');

echo "<pre>\n";
echo print_r($courseinfo);
echo "</pre>\n";
