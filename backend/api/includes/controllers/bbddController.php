<?php

class bbddController extends BaseController
{
    // Retrieves schema database
    public function getAction($args, $callerInfo, $rawdata, $request, $verb) {
        if (count($args) == 0 && $verb != null) {
            if ($verb != null && $verb != "") {
                $schema = $this->proxy->getSchemaDatabase($verb); 
                return $schema;
            } else
                return new Exception("Unknown resource",404);
        }
    }
}

?>