<?php

class operationcodesController extends BaseController
{

    // Retrieves the operation codes
    public function getAction($args, $callerInfo, $rawdata, $request, $verb) {
        if (isset($args[0]) && is_numeric($args[0]) && count($args) == 1 && empty($verb)) {
            $toReturn =  $this->proxy->genericGetById("operationcodes",$args[0],$callerInfo);
            if (empty($toReturn))
                return  new Exception("Unknown resource",404);
            else
                return $toReturn;
        }
        else
            if (count($args) == 0) {
                 return $this->proxy->genericGetElements("operationcodes", isset($request["start"])?$request["start"]:null, isset($request["limit"])?$request["limit"]:BaseController::DEFAULT_LIMIT,
                    isset($request["sortField"])?$request["sortField"]:null, isset($request["sortAsc"])?$request["sortAsc"]:null,
                    isset($request["filterField"])?$request["filterField"]:null, isset($request["filterValue"])?$request["filterValue"]:null,$callerInfo);
            }
            else
                return  new Exception("Unknown resource",404);

    }

    // Delete operation codes
    public function deleteAction($args, $callerInfo, $data, $request, $verb) {
        if (($callerInfo["IsSystemAdmin"] == 1 || isset($callerInfo["roles"]["SA"]) || isset($callerInfo["roles"]["SUP"])) && !empty($this->proxy->genericGetById("operationcodes",$args[0],$callerInfo))) {
            if (isset($args[0]) && is_numeric($args[0]) && count($args) == 1 && empty($verb))
                return $this->proxy->genericDelete("operationcodes",$args[0]);
            else
                return  new Exception("Invalid URL parameters",405);
        } else
            return new Exception("Forbidden",403);
    }

    // Create operation codes
    public function postAction($args, $callerInfo, $data, $request, $verb) {
         if ($callerInfo["IsSystemAdmin"] == 1 || isset($callerInfo["roles"]["SA"]) || isset($callerInfo["roles"]["SUP"])) {
            if (count($args) == 0 && empty($verb)) {
                $dataObject = json_decode($data);
                return $this->proxy->genericInsert("operationcodes", $dataObject);
            }
            else
                return  new Exception("Invalid URL parameters",405);  
        }
        else
            return new Exception("Forbidden",403);
    }

    // Update operation codes
    public function putAction($args, $callerInfo, $data, $request, $verb) {
        $dataObject = json_decode($data); 
        if (isset($args[0]) && is_numeric($args[0]) && count($args) == 1 && empty($verb)) {
            $this->fixToCase($dataObject, 'Id');
            $dataObject->Id = $args[0];
            if (($callerInfo["IsSystemAdmin"] == 1 || isset($callerInfo["roles"]["SA"]) || isset($callerInfo["roles"]["SUP"])) && !empty($this->proxy->genericGetById("operationcodes",$args[0],$callerInfo)))  {
                return $this->proxy->genericUpdate("operationcodes", $dataObject);
            }
            else
                return new Exception("Forbidden",403);
        }
        else
            return  new Exception("Invalid URL parameters",405);
    }

}