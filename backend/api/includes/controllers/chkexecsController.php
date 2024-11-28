<?php

class chkexecsController extends BaseController
{

    // Retrieves the checklist executions
    public function getAction($args, $callerInfo, $rawdata, $request, $verb) {
        if (isset($args[0]) && is_numeric($args[0]) && count($args) == 1 && empty($verb)) {
            $toReturn =  $this->proxy->genericGetById("chkexecs",$args[0],$callerInfo);
            if (empty($toReturn))
                return  new Exception("Unknown resource",404);
            else
                return $toReturn;
        }
        else
            if (count($args) == 0) {
                 return $this->proxy->genericGetElements("chkexecs", isset($request["start"])?$request["start"]:null, isset($request["limit"])?$request["limit"]:BaseController::DEFAULT_LIMIT,
                    isset($request["sortField"])?$request["sortField"]:null, isset($request["sortAsc"])?$request["sortAsc"]:null,
                    isset($request["filterField"])?$request["filterField"]:null, isset($request["filterValue"])?$request["filterValue"]:null,$callerInfo,
                    isset($request["location"])?$request["location"]:null, isset($request["assetName"])?$request["assetName"]:null);
            }
            else
                return  new Exception("Unknown resource",404);
    }

    // Delete checklist executions
    public function deleteAction($args, $callerInfo, $data, $request, $verb) {
        if (($callerInfo["IsSystemAdmin"] == 1 || isset($callerInfo["roles"]["SA"]) || isset($callerInfo["roles"]["MANU"]) || isset($callerInfo["roles"]["QA"])) && !empty($this->proxy->genericGetById("chkexecs",$args[0],$callerInfo))) {
            if (isset($args[0]) && is_numeric($args[0]) && count($args) == 1 && empty($verb))
                return $this->proxy->genericDelete("chkexecs",$args[0]);
            else
                return  new Exception("Invalid URL parameters",405);
        } else
            return new Exception("Forbidden",403);
    }

    // Create checklist executions
    public function postAction($args, $callerInfo, $data, $request, $verb) {
        if ($callerInfo["IsSystemAdmin"] == 1 || isset($callerInfo["roles"]["SA"]) || isset($callerInfo["roles"]["MANU"]) || isset($callerInfo["roles"]["QA"])) {
            if (count($args) == 0 && empty($verb)) {
                $dataObject = json_decode($data);
                $this->fixToCase($dataObject, 'checklist');
                if (isset($dataObject->SecurityGroup) && empty($this->proxy->genericGetById("checklists", $dataObject->checklist, $callerInfo)))
                    return new Exception("Invalid checklist",403);
                return $this->proxy->genericInsert("chkexecs", $dataObject);
            }
            else
                return  new Exception("Invalid URL parameters",405);  
        }
        else
            return new Exception("Forbidden",403);
    }

    // Update checklist executions
    public function putAction($args, $callerInfo, $data, $request, $verb) {
        $dataObject = json_decode($data); 
        if (isset($args[0]) && is_numeric($args[0]) && count($args) == 1 && empty($verb)) {
            $this->fixToCase($dataObject, 'Id');
            $dataObject->Id = $args[0];
            if (isset($dataObject->checklist) && empty($this->proxy->genericGetById("checklists", $dataObject->checklist, $callerInfo)))
                return new Exception("Invalid checklist",403);
            if (($callerInfo["IsSystemAdmin"] == 1 || isset($callerInfo["roles"]["SA"]) || isset($callerInfo["roles"]["MANU"]) || isset($callerInfo["roles"]["QA"])) && !empty($this->proxy->genericGetById("chkexecs",$args[0],$callerInfo)))  {
                return $this->proxy->genericUpdate("chkexecs", $dataObject);
            }
            else
                return new Exception("Forbidden",403);
        }
        else
            return  new Exception("Invalid URL parameters",405);
    }

}