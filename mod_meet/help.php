<?php

require_once('../../config.php');

echo $OUTPUT->header();
?>

<style>

img{
    max-width: 100%;
}

h4{
    margin-top: 20px;
}

</style>

<h2> Problemas de visualização </h2>

<p> Essa página abordará alguns problemas que você pode enfrentar durante a utilização dessa ferramenta do sistema. </p>

<article>
    <h3> Não há uma mensagem de erro no vídeo, mas aparece apenas uma tela preta com o botão de play. </h3>
    <p> Esse problema provavelmente está relacionado com as configurações de compartilhamento de cookies do navegador. </p>
    <p> Cada navegador terá alguma peculiaridade para essa liberação. </p>
    <br />

    <h4> Google Crome </h4>
    <p> Na extremidade direita da barra de endereço, próximo ao canto superior direito da tela, um simbolo de olho riscado deve estar presente. Nesse menu, você terá a opção para habilitar os cookies para o Ambiente Virtual </p>
    <img src='<?=$CFG->wwwroot ?>/mod/meet/pix/chrome_1.png' />
    <img src='<?=$CFG->wwwroot ?>/mod/meet/pix/chrome_2.png' />
    <img src='<?=$CFG->wwwroot ?>/mod/meet/pix/chrome_3.png' />
    <br />

    <h4> Firefox  </h4>
    <p> Na extremidade esquerda da barra de endereço, um escudo roxo deve estar presente. Nesse menu você terá a opção de liberar os cookies para o Ambiente Virtual </p>
    <img src='<?=$CFG->wwwroot ?>/mod/meet/pix/firefox_1.png' />
    <img src='<?=$CFG->wwwroot ?>/mod/meet/pix/firefox_2.png' />
    <br />

    <h4> Microsoft Edge </h4>
    <p> Na extremidade esquerda da barra de endereço, um cadeado deve estar presente. Nesse menu você terá a opção de liberar os cookies para o Ambiente Virtual </p>
    <img src='<?=$CFG->wwwroot ?>/mod/meet/pix/edge_1.png' />
    <img src='<?=$CFG->wwwroot ?>/mod/meet/pix/edge_2.png' />

</article>

<hr />

<article>
    <h3> Mensagem: "Esse formato de vídeo não é suportado." </h3>
    <p> Essa mensagem só foi detectada no navegador Opera. Esse caso não pode ser solucionado, pois devido a decisoes internas, alguns codecs de vídeo não foram integrados no navegador. </p>
</article>

<?php
echo $OUTPUT->footer();
