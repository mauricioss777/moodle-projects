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
        /*if( ! is_siteadmin()) {
            die('Você não tem permissão de executar este script.');
	}*/
        global $CFG;
        $this->functionname = $functionname;
        $this->return = $return;
        $this->domainname = $CFG->wwwroot;
	#$this->token = '335cf532c6e6b33eda1355ff278a7687';
	$this->token = '7ce3dc36a168b6bb8410edeba78238c9';
    }

    abstract function test();

    function execute_test($info)
    {
        /// SOAP CALL
        $serverurl = $this->domainname . '/webservice/soap/server.php?wsdl=1&wstoken=' . $this->token;
        $client = new SoapClient($serverurl, array('cache_wsdl' => WSDL_CACHE_NONE) );

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
