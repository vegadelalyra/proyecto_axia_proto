<?php

class destinationsController extends BaseController
{

    // Retrieves the destinations
    public function getAction($args, $callerInfo, $rawdata, $request, $verb) {
        if (isset($args[0]) && is_numeric($args[0]) && count($args) == 1 && empty($verb)) {
            $toReturn =  $this->proxy->genericGetById("assetdestinations",$args[0],$callerInfo);
            if (empty($toReturn))
                return  new Exception("Unknown resource",404);
            else
                return $toReturn;
        } else if (isset($args[0]) && is_numeric($args[0]) && isset($args[1])) {
            if (strtolower($args[1]) == "repairlocations") {
                if ($callerInfo["IsSystemAdmin"] == 1 || !empty($this->proxy->genericGetById("locations",$args[0],$callerInfo)))
                    return $this->proxy->genericGetSubelements($args[0], "assetdestinations_Id", "repairlocations_Id", "Id", "destinations_has_locations", "locations", isset($request["start"])?$request["start"]:null, isset($request["limit"])?$request["limit"]:BaseController::DEFAULT_LIMIT,
                        isset($request["sortField"])?$request["sortField"]:null, isset($request["sortAsc"])?$request["sortAsc"]:null,
                        isset($request["filterField"])?$request["filterField"]:null, isset($request["filterValue"])?$request["filterValue"]:null);
                else
                    return new Exception("Forbidden",403);
            }
        }
        else
            if (count($args) == 0) {
                 return $this->proxy->genericGetElements("assetdestinations", isset($request["start"])?$request["start"]:null, isset($request["limit"])?$request["limit"]:BaseController::DEFAULT_LIMIT,
                    isset($request["sortField"])?$request["sortField"]:null, isset($request["sortAsc"])?$request["sortAsc"]:null,
                    isset($request["filterField"])?$request["filterField"]:null, isset($request["filterValue"])?$request["filterValue"]:null,$callerInfo);
            }
            else
                return  new Exception("Unknown resource",404);

    }

    // Delete destinations
    public function deleteAction($args, $callerInfo, $data, $request, $verb) {
        if (($callerInfo["IsSystemAdmin"] == 1 || isset($callerInfo["roles"]["SA"])) && !empty($this->proxy->genericGetById("locations",$args[0],$callerInfo))) {
            if (isset($args[0]) && is_numeric($args[0]) && count($args) == 1 && empty($verb))
                return $this->proxy->genericDelete("assetdestinations",$args[0]);
            else if (isset($args[0]) && is_numeric($args[0]) && isset($args[1]) && isset($args[2]) && is_numeric($args[2])) {
                return $this->proxy->genericRelationDelete("destinations_has_locations","assetdestinations_Id",$args[0],"repairlocations_Id",$args[2],$callerInfo);
            } else
                return  new Exception("Invalid URL parameters",405);
        } else
            return new Exception("Forbidden",403);
    }

    // Create destinations
    public function postAction($args, $callerInfo, $data, $request, $verb) {
         if ($callerInfo["IsSystemAdmin"] == 1 || isset($callerInfo["roles"]["SA"])) {
            if (count($args) == 0 && empty($verb)) {
                $dataObject = json_decode($data);
                $this->fixToCase($dataObject, 'SecurityGroup');
                if (isset($dataObject->SecurityGroup) && empty($this->proxy->genericGetById("SecurityGroups", $dataObject->SecurityGroup, $callerInfo)))
                    return new Exception("Invalid Default SecurityGroups",403);
                if (!isset($dataObject->SecurityGroup))
                    $dataObject->SecurityGroup = $callerInfo["DefaultSecurityGroup"];
                return $this->proxy->genericInsert("assetdestinations", $dataObject);
            } else if (isset($args[0]) && is_numeric($args[0])) {
                if (isset($args[1])) {
                    if (($callerInfo["IsSystemAdmin"] == 1 || isset($callerInfo["roles"]["SA"])) && !empty($this->proxy->genericGetById("locations",$args[0],$callerInfo))) {
                        if (strtolower($args[1]) == "repairlocations") {
                            $result = array();
                            foreach(explode(",",$data) as $currentElement) {
                                if (!empty($this->proxy->genericGetById("locations",$currentElement,$callerInfo))) {
                                    $insertData = new stdClass();
                                    $insertData->assetdestinations_Id = $args[0];
                                    $insertData->repairlocations_Id = $currentElement;
                                    $result[] =  $this->proxy->genericInsert("destinations_has_locations", $insertData);
                                }
                            }
                            return (count($result)==1)?$result[0]:$result;
                        }
                        else
                            return  new Exception("Unknown resource",404);
                    } else
                        return new Exception("Forbidden",403);
                } else
                    return  new Exception("Unknown resource",404);
            } else
                return  new Exception("Invalid URL parameters",405);  
        }
        else
            return new Exception("Forbidden",403);
    }

    // Update destinations
    public function putAction($args, $callerInfo, $data, $request, $verb) {
        $dataObject = json_decode($data); 
        if (isset($args[0]) && is_numeric($args[0]) && count($args) == 1 && empty($verb)) {
            $this->fixToCase($dataObject, 'Id');
            $dataObject->Id = $args[0];
            $this->fixToCase($dataObject, 'SecurityGroup');
            if (isset($dataObject->SecurityGroup) && empty($this->proxy->genericGetById("SecurityGroups", $dataObject->SecurityGroup, $callerInfo)))
                return new Exception("Invalid Default SecurityGroups",403);
            if (($callerInfo["IsSystemAdmin"] == 1 || isset($callerInfo["roles"]["SA"])) && !empty($this->proxy->genericGetById("assetdestinations",$args[0],$callerInfo)))  {
                return $this->proxy->genericUpdate("assetdestinations", $dataObject);
            }
            else
                return new Exception("Forbidden",403);
        }
        else
            return  new Exception("Invalid URL parameters",405);
    }
}
