<?php
if (!class_exists('TSoapClientServicos')) {
    class TSoapClientServicos extends SoapClient
    {
        public function __construct()
        {
            global $DB;
            if (!function_exists('get_config')) {
                require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
            }
            $location  = get_config('local_pages', 'webservicelocation');
            $uri       = get_config('local_pages', 'webserviceuri');
            $trace     = get_config('local_pages', 'webservicetrace');
            $encoding  = get_config('local_pages', 'webserviceencoding');
            $this->key = get_config('local_pages', 'webservicekey');
            parent::__construct(null, array(
                'location' => "$location",
                'uri' => "$uri",
                'trace' => $trace,
                'encoding' => "$encoding",
                'keep_alive' => false,
            ));
        }

        public function __call($name, $arguments)
        {
            $arguments[] = $this->key;
            $result = parent::__soapCall($name, $arguments);
            $result = unserialize(base64_decode($result));
            if ($result instanceof Exception) {
                throw $result;
            } else {
                return $result;
            }
        }
    }
}
?>
