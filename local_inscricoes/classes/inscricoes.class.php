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
class Inscricoes{

    /**
     * Busca as informações de um determinado curso no alfa.
     * É utilizada para sincronizar as informaçõe de um determinado currículo(
     * @param $idoffer idnumber da tabela mdl_course
     * @return $course_info Object retorna um objeto igual ao documentado na função local_alfa_create_course
     */
    public static function sendAttendenceSchedule($schedule, $users)
    {
        $soapClient = new TSoapClientInscricoes();
        if(!$soapClient){
            throw new Exception('No Soap Class');
        }
        foreach ($users as $user){
            try {
                $soapClient->executaMetodoModel('PresencaService', 'savePresenca',  [$user, $schedule]);
                error_log("Presença registrada para $user no horário $schedule");
            } catch (Exception $e) {
                $msg = unserialize( base64_decode ($e->getMessage()));
                error_log("LOCAL_INSCRICOES_ERROR:::: TSoapClientInscricoes Inscricoes Soap Error: " . $msg);
            }
        }
        return 1;
    }

    public static function getCertificatePDF($eventid, $user){
        $soapClient = new TSoapClientInscricoes();
        if(!$soapClient){
            throw new Exception('No Soap Class');
        }
        try {
            return $soapClient->executaMetodoModel( 'InscricoesWebService','getCertificadoPessoaProcesso', array(  $user, $eventid ) );
        } catch (Exception $e) {
            $msg = unserialize( base64_decode ($e->getMessage()));
            error_log("LOCAL_INSCRICOES_ERROR:::: getCertificatePDF Inscricoes Soap Error: " . $msg);
        }
    }

}
