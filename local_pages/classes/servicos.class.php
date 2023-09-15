<?php
require_once('tsoapclient.class.php');

/**
 * Camada de integração com o alfa.
 *
 * @since  set/2014
 * @author Maurício S. Silva mss@univates.br
 * @author Artur H. Welp ahwelp@univates.br
 * @author Alexandre S. Wolf awolf@univates.br
 */
class Servicos 
{

    /**
     * @param $username  Nome de usuário 
     * @param $avaliacao Id da avaliação no Alfa
     * @return Se usuário respondeu a avaliação
     */
    public static function respondeuAvaliacaoAtiva($username, $avaliacao)
    {
        $soapClient = new TSoapClientServicos();
        try {
            if($soapClient) {
                $c = (array) $soapClient->executaMetodoModel('AvaliacaoService', 'respondeuAvaliacaoAtiva', array( $username, $avaliacao, null ) );
                return $c[0];
            }
        } catch (Exception $e) {
            error_log('SERVICOS WS --- ' . unserialize(base64_decode($e->getMessage()))->getMessage());
            return false;
        }
    }


}
