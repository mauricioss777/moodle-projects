<?php

require_once ('../../../config.php');

if(!is_siteadmin()){
    redirect('/');
}

$PAGE->set_title(get_string('pluginname', 'local_alfa'));
$PAGE->set_heading(get_string('pluginname', 'local_alfa'));

echo $OUTPUT->header();

echo "<h3> Processos </h3>";
echo "<a href='../tests/nead_adicionar_usuario_lote.php'> Adicionar usuários em lote </a> <br />";
echo "<a href='reset_conclusion.php'> Resetar progresso de usuário em ambiente virtual </a> <br />";
echo "<a href='vinculate_offer.php'> Vincular ambiente virtual a oferta do alfa </a> <br />";
echo "<a href='vinculate_curriculum.php'> Vincular ambiente virtual a currículo </a> <br />";
echo "<a href='#'> Alterar LinkEad </a> <br />";
echo "<a href='/virtual/local/pages/messages.php'> Gerenciar PopUps </a> <br />";

echo "<br / ><hr /><br />";

echo "<h3> Consultas </h3>";
echo "<a href='#'> Consultar oferta </a> <br />";
echo "<a href='#'> Consultar currículo </a> <br />";
echo "<a href='#'> Consultar situação financeira </a> <br />";
echo "<a href='#'> Consultar documentos faltantes </a> <br />";
echo $OUTPUT->footer();
