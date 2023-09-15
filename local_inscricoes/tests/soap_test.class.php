<?php

/**
 * Classe base para os testes com soap deste plugin
 */
abstract class soap_test
{
    private $functionname = '';
    private $return = false;
    private $token = '';
    private $domainname = '';

    function __construct($functionname, $return = false)
    {
        require_once('../../../config.php');
        if( ! is_siteadmin()) {
            die('Você não tem permissão de executar este script.');
        }
        global $CFG;
        $this->functionname = $functionname;
        $this->return = $return;
        $this->domainname = $CFG->wwwroot;
        //$this->token = '4a92e0bb1fc31d685dafdc406f082758';
        $this->token = 'a4cdda6e667da5fa4ed1065b0577c1cd';
    }

    abstract function test();

    function execute_test($info)
    {
        /// SOAP CALL
        $serverurl = $this->domainname . '/webservice/soap/server.php?wsdl=1&wstoken=' . $this->token;
        echo $serverurl ;
        $client = new SoapClient($serverurl);

        try {
            $resp = $client->__soapCall($this->functionname, array($info));
        } catch (Exception $e) {
            var_dump('<pre>',$e,'</pre>');die;
        }
        if(isset($resp)) {
            if($this->return) {
                return $resp;
            } else {
                var_dump('<pre>',$resp,'</pre>');die;
            }
        }
    }
}

?>
