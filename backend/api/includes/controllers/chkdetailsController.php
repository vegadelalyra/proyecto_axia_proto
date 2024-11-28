<?php

class chkdetailsController extends BaseController
{

    // Retrieves the checklist details
    public function getAction($args, $callerInfo, $rawdata, $request, $verb) {
        if (isset($args[0]) && is_numeric($args[0]) && count($args) == 1 && empty($verb)) {
            $toReturn =  $this->proxy->genericGetById("chkdetails",$args[0],$callerInfo);
            if (empty($toReturn))
                return  new Exception("Unknown resource",404);
            else
                return $toReturn;
        }
        else
            if (count($args) == 0) {
                 return $this->proxy->genericGetElements("chkdetails", isset($request["start"])?$request["start"]:null, isset($request["limit"])?$request["limit"]:BaseController::DEFAULT_LIMIT,
                    isset($request["sortField"])?$request["sortField"]:null, isset($request["sortAsc"])?$request["sortAsc"]:null,
                    isset($request["filterField"])?$request["filterField"]:null, isset($request["filterValue"])?$request["filterValue"]:null,$callerInfo,
                    isset($request["location"])?$request["location"]:null, isset($request["assetName"])?$request["assetName"]:null);
            }
            else
                return  new Exception("Unknown resource",404);
    }

    // Delete checklist details
    public function deleteAction($args, $callerInfo, $data, $request, $verb) {
        if (($callerInfo["IsSystemAdmin"] == 1 || isset($callerInfo["roles"]["SA"]) || isset($callerInfo["roles"]["DES"]) || isset($callerInfo["roles"]["MAN"]) || isset($callerInfo["roles"]["QA"])) && !empty($this->proxy->genericGetById("chkdetails",$args[0],$callerInfo))) {
            if (isset($args[0]) && is_numeric($args[0]) && count($args) == 1 && empty($verb))
                return $this->proxy->genericDelete("chkdetails",$args[0]);
            else if (isset($args[0]) && is_numeric($args[0]) && isset($args[1]) && isset($args[2]) && is_numeric($args[2])) {
                return $this->proxy->genericRelationDelete("destinations_has_locations","assetdestinations_Id",$args[0],"repairlocations_Id",$args[2],$callerInfo);
            } else
                return  new Exception("Invalid URL parameters",405);
        } else
            return new Exception("Forbidden",403);
    }

    // Create checklist details
    public function postAction($args, $callerInfo, $data, $request, $verb) {
        if ($callerInfo["IsSystemAdmin"] == 1 || isset($callerInfo["roles"]["SA"]) || isset($callerInfo["roles"]["DES"]) || isset($callerInfo["roles"]["MAN"]) || isset($callerInfo["roles"]["QA"])) {
            if (count($args) == 0 && empty($verb)) {
                $dataObject = json_decode($data);
                $this->fixToCase($dataObject, 'family');
                if (isset($dataObject->SecurityGroup) && empty($this->proxy->genericGetById("families", $dataObject->family, $callerInfo)))
                    return new Exception("Invalid Default Family",403);
                return $this->proxy->genericInsert("chkdetails", $dataObject);
            }
            else
                return  new Exception("Invalid URL parameters",405);  
        }
        else
            return new Exception("Forbidden",403);
    }

    // Update checklist details
    public function putAction($args, $callerInfo, $data, $request, $verb) {
        $dataObject = json_decode($data); 
        if (isset($args[0]) && is_numeric($args[0]) && count($args) == 1 && empty($verb)) {
            $this->fixToCase($dataObject, 'Id');
            $dataObject->Id = $args[0];
            if (isset($dataObject->family) && empty($this->proxy->genericGetById("families", $dataObject->family, $callerInfo)))
                return new Exception("Invalid Default Family",403);
            if (($callerInfo["IsSystemAdmin"] == 1 || isset($callerInfo["roles"]["SA"]) || isset($callerInfo["roles"]["DES"]) || isset($callerInfo["roles"]["MAN"]) || isset($callerInfo["roles"]["QA"])) && !empty($this->proxy->genericGetById("chkdetails",$args[0],$callerInfo)))  {
                return $this->proxy->genericUpdate("chkdetails", $dataObject);
            }
            else
                return new Exception("Forbidden",403);
        }
        else
            return  new Exception("Invalid URL parameters",405);
    }

}
