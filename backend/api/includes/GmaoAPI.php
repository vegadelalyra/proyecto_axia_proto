<?php

// Autoload any requested class (all the classes should be in controllers folder)
spl_autoload_register('apiAutoload');
function apiAutoload($classname)
{
    if (preg_match('/[a-zA-Z]+Controller$/', $classname)) {
        @include __DIR__ . '/controllers/' . $classname . '.php';
        return true;
   }
}


function escape_string($value) {
    $return = '';
    for($i = 0; $i < strlen($value); ++$i) {
        $char = $value[$i];
        $ord = ord($char);
        if($char !== "'" && $char !== "\"" && $char !== '\\' && $ord >= 32 && $ord <= 126)
            $return .= $char;
        else
            $return .= '\\x' . dechex($ord);
    }
    return $return;
}

include_once 'utils/API.class.php';
include_once 'business/APIKey.php';
include_once 'business/UserAuthenticator.php';


// Handles al the requests
class GmaoAPI extends API
{

    private $APIKeyRequired = false;

    public function __construct($request, $files, $origin) {
        parent::__construct($request, $files);

        // Abstracted out for example
        $APIKey = new APIKey();
        $Authenticator = new UserAuthenticator();

        if( !isset($_SERVER['PHP_AUTH_USER']) )
        {
            if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION']) && (strlen($_SERVER['REDIRECT_HTTP_AUTHORIZATION']) > 0)){
                list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':', base64_decode(substr($_SERVER['REDIRECT_HTTP_AUTHORIZATION'], 6)));
                if( strlen($_SERVER['PHP_AUTH_USER']) == 0 || strlen($_SERVER['PHP_AUTH_PW']) == 0 )
                {
                    unset($_SERVER['PHP_AUTH_USER']);
                    unset($_SERVER['PHP_AUTH_PW']);
                }
            }
        }

        if ($this->APIKeyRequired && !array_key_exists('apiKey', $this->request)) {
            throw new Exception('No API Key provided');
        } else if ($this->APIKeyRequired && !$APIKey->verifyKey($this->request['apiKey'], $origin)) {
            throw new Exception('Invalid API Key');
        }  else if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']) ||
             (($this->callerInfo = $Authenticator->login($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW']))== null)) {
            if (array_key_exists('token', $this->request)) {
                if  (($this->callerInfo = $Authenticator->checkToken($this->request['token']))== null) {
                    header('HTTP/1.0 401 Unauthorized');
                    die ("");
                }
            } else if (array_key_exists('enctoken', $this->request)) { //url enc token authentication
                if  (($this->callerInfo = $Authenticator->checkEncToken($this->request['enctoken']))== null) {
                    header('HTTP/1.0 401 Unauthorized');
                    die ("");
                }
            } else {
                if ($this->endpoint == "users" && isset($this->verb) && ($this->verb == "retrievepassword")) { //Special scenario to retrieve lost password.
                }                    
                else {
                    header('HTTP/1.0 401 Unauthorized');
                    die ("");
                }
            }
        }
    }
 }

?>