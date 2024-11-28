<?php
require_once __DIR__."/../data/db.php";
class BaseController
{
    const DEFAULT_LIMIT = 100;
	protected $proxy;

    public function __construct() {
    	$this->proxy = DbProxyBuilder::getProxy();
    }

    protected function fixToCase($object, $tag){
        $asigned = false;
        $firtValue = null;
        if ($object != null) {
            foreach ($object as $a => $b) {
            if (strtoupper($a) == strtoupper($tag)) {
                    if (!$asigned) {
                        $asigned = true;
                        $firstValue = $b;
                    }
                    unset($object->$a);
            }
            }
            if ($asigned)
                $object->$tag = $firstValue;
            return $object;
        }
    } 

    public function optionsAction($args, $callerInfo) {
    	$supported = "OPTIONS";
    	if (method_exists($this, "getAction"))
    		$supported.=",GET";
    	if (method_exists($this, "deleteAction"))
    		$supported.=",DELETE";
    	if (method_exists($this, "postAction"))
    		$supported.=",POST"; 
    	if (method_exists($this, "putAction"))
    		$supported.=",PUT";
    	header("Allow: ".$supported);
    	return ' ';
    }
	
}

?>