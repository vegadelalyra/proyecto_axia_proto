<?php

class assetsController extends BaseController
{

    public function getAction($args, $callerInfo, $rawdata, $request, $verb) {
        if (isset($args[0]) && is_numeric($args[0]) && count($args) == 1 && empty($verb)) {
            $toReturn =  $this->proxy->getAssets("assets",$args[0],$callerInfo,null, BaseController::DEFAULT_LIMIT,null, null,null, null);
            if (empty($toReturn))
                return  new Exception("Unknown resource",404);
            else
                return $toReturn;
        }
        else if (isset($args[0]) && is_numeric($args[0]) && isset($args[1])) {
            if (strtolower($args[1]) == "alerts") {
                if (!empty($this->proxy->genericGetById("assets",$args[0],$callerInfo))) {
                    $filtersFields = array("Asset");
                    $filterValues = array("=".$args[0]);
                    if (isset($request["filterField"]) && is_array($request["filterField"]))
                        $filtersFields=array_merge($filtersFields,$request["filterField"]);
                    else if (isset($request["filterField"]))
                        $filtersFields=array_merge($filtersFields,[$request["filterField"]]);
                    if (isset($request["filterValue"]) && is_array($request["filterField"]))
                        $filterValues=array_merge($filterValues,$request["filterValue"]);
                    else if (isset($request["filterValue"]))
                        $filterValues=array_merge($filterValues,[$request["filterValue"]]);
                    $activeAlerts = $this->proxy->getActiveAlerts(isset($request["start"])?$request["start"]:null, isset($request["limit"])?$request["limit"]:BaseController::DEFAULT_LIMIT,
                    isset($request["sortField"])?$request["sortField"]:null, isset($request["sortAsc"])?$request["sortAsc"]:null,
                    $filtersFields, $filterValues, $callerInfo);
                    return $this->proxy->completeAlerts($activeAlerts, $callerInfo);
                }
                else
                    return new Exception("Forbidden",403);
            } else if (strtolower($args[1]) == "files") {
                if (!empty($this->proxy->genericGetById("assets",$args[0],$callerInfo))) {
                    return $this->proxy->getActiveFiles($args[0], isset($request["start"])?$request["start"]:null, isset($request["limit"])?$request["limit"]:BaseController::DEFAULT_LIMIT,
                    isset($request["sortField"])?$request["sortField"]:null, isset($request["sortAsc"])?$request["sortAsc"]:null,
                    isset($request["filterField"])?$request["filterField"]:null, isset($request["filterValue"])?$request["filterValue"]:null,$callerInfo);
                }
                else
                    return new Exception("Forbidden",403);
            } else if (strtolower($args[1]) == "allfiles") {
                if (!empty($this->proxy->genericGetById("assets",$args[0],$callerInfo))) {
                    return $this->proxy->getAssetFiles($args[0], isset($request["start"])?$request["start"]:null, isset($request["limit"])?$request["limit"]:BaseController::DEFAULT_LIMIT,
                    isset($request["sortField"])?$request["sortField"]:null, isset($request["sortAsc"])?$request["sortAsc"]:null,
                    isset($request["filterField"])?$request["filterField"]:null, isset($request["filterValue"])?$request["filterValue"]:null,$callerInfo);
                }
                else
                    return new Exception("Forbidden",403);
            } else if (strtolower($args[1]) == "images") {
                if (!empty($this->proxy->genericGetById("assets",$args[0],$callerInfo))) {
                    return $this->proxy->getActiveImages($args[0], isset($request["start"])?$request["start"]:null, isset($request["limit"])?$request["limit"]:BaseController::DEFAULT_LIMIT,
                    isset($request["sortField"])?$request["sortField"]:null, isset($request["sortAsc"])?$request["sortAsc"]:null,
                    isset($request["filterField"])?$request["filterField"]:null, isset($request["filterValue"])?$request["filterValue"]:null,$callerInfo);
                }
                else
                    return new Exception("Forbidden",403);
            } else if (strtolower($args[1]) == "symptoms") {
                if (!empty($this->proxy->genericGetById("assets",$args[0],$callerInfo))) {
                    return $this->proxy->getActiveSymptoms($args[0], isset($request["start"])?$request["start"]:null, isset($request["limit"])?$request["limit"]:BaseController::DEFAULT_LIMIT,
                    isset($request["sortField"])?$request["sortField"]:null, isset($request["sortAsc"])?$request["sortAsc"]:null,
                    isset($request["filterField"])?$request["filterField"]:null, isset($request["filterValue"])?$request["filterValue"]:null,$callerInfo);
                }
                else
                    return new Exception("Forbidden",403);
            } else if (strtolower($args[1]) == "orders") {
                $orders = $this->proxy->genericGetAllByKeyField("orders","Asset", $args[0], $callerInfo);
                if (!empty($orders) && count($orders) > 0) {
                    $completeOrders = [];
                    foreach ($orders as $order) {
                        array_push($completeOrders, $this->proxy->completeOrder($order));
                    }
                    return $completeOrders;
                } else
                    return new Exception("Unknown resource",404);
            } else if (strtolower($args[1]) == "family") {
                if (!empty($this->proxy->genericGetById("families",$args[0],$callerInfo))) {
                    if (isset($request["filterField"]) && isset($request["filterValue"])) {
                        array_push($request["filterField"] , 'Family');
                        array_push($request["filterValue"] , $args[0]);
                    } else {
                        $request["filterField"] = array('Family');
                        $request["filterValue"] = array($args[0]);
                    }
                    if (isset($request["version"])) {
                        array_push($request["filterField"] , 'Version');
                        array_push($request["filterValue"] ,$request["version"]);
                    }
                    
                    return $this->proxy->genericGetElements("assets", isset($request["start"])?$request["start"]:null, isset($request["limit"])?$request["limit"]:BaseController::DEFAULT_LIMIT,
                    isset($request["sortField"])?$request["sortField"]:null, isset($request["sortAsc"])?$request["sortAsc"]:null,
                    isset($request["filterField"])?$request["filterField"]:null, isset($request["filterValue"])?$request["filterValue"]:null,$callerInfo);
                }
                else
                    return new Exception("Forbidden",403);
            } else if (strtolower($args[1]) == "checklists") {
                if (!empty($this->proxy->genericGetById("assets",$args[0],$callerInfo))) {
                    return $this->proxy->getAssetChecklists($args[0], isset($request["start"])?$request["start"]:null, isset($request["limit"])?$request["limit"]:BaseController::DEFAULT_LIMIT,
                    isset($request["sortField"])?$request["sortField"]:null, isset($request["sortAsc"])?$request["sortAsc"]:null,
                    isset($request["filterField"])?$request["filterField"]:null, isset($request["filterValue"])?$request["filterValue"]:null,$callerInfo);
                }
                else
                    return new Exception("Forbidden",403);
            } else if (strtolower($args[1]) == "chkexecs") {
                $chkexecs = $this->proxy->getCheckExecsByAsset($args[0], $callerInfo, true);
                if (!empty($chkexecs)) {
                    return $chkexecs;
                } else
                    return new Exception("Does not exist",404);
            } else
                return  new Exception("Invalid URL parameters",405);
        } else if (count($args) == 0 && isset($verb) && substr($verb, 0, 1) == "K") {
            $key = substr(escape_string($verb), 1);
            $asset = $this->proxy->genericGetByKeyField("assets","Code", $key, $callerInfo);
            if (!empty($asset)) {
                return $asset;
            } else
                return new Exception("Does not exist",403);      
        } else
            /*if (count($args) == 0 && $verb == "manufacturer") {
                 return $this->proxy->getAssetsManufacturer("assets", null, $callerInfo, isset($request["start"])?$request["start"]:null, isset($request["limit"])?$request["limit"]:BaseController::DEFAULT_LIMIT,
                    isset($request["sortField"])?$request["sortField"]:null, isset($request["sortAsc"])?$request["sortAsc"]:null,
                    isset($request["filterField"])?$request["filterField"]:null, isset($request["filterValue"])?$request["filterValue"]:null);
            } else */if (count($args) == 0) {
                return $this->proxy->getAssets("assets", null, $callerInfo, isset($request["start"])?$request["start"]:null, isset($request["limit"])?$request["limit"]:BaseController::DEFAULT_LIMIT,
                   isset($request["sortField"])?$request["sortField"]:null, isset($request["sortAsc"])?$request["sortAsc"]:null,
                   isset($request["filterField"])?$request["filterField"]:null, isset($request["filterValue"])?$request["filterValue"]:null);
            }
            else
                return  new Exception("Unknown resource",404);

    }

    public function deleteAction($args, $callerInfo, $data, $request, $verb) {
        if (($callerInfo["IsSystemAdmin"] == 1 || isset($callerInfo["roles"]["SA"])) && !empty($this->proxy->genericGetById("assets",$args[0],$callerInfo))) {
            if (isset($args[0]) && is_numeric($args[0]) && count($args) == 1 && empty($verb))
                return $this->proxy->genericDelete("assets",$args[0]);
            else
                return  new Exception("Invalid URL parameters",405);
        } else
            return new Exception("Forbidden",403);
    }

    public function postAction($args, $callerInfo, $data, $request, $verb, $files) {
         if ($callerInfo["IsSystemAdmin"] == 1 || isset($callerInfo["roles"]["SA"]) || isset($callerInfo["roles"]["REP"]) || isset($callerInfo["roles"]["MAN"])) {
            if (count($args) == 0 && empty($verb)) {
                $dataObject = json_decode($data);
                $this->fixToCase($dataObject, 'SecurityGroup');
                if (isset($dataObject->SecurityGroup) && empty($this->proxy->genericGetById("SecurityGroups", $dataObject->SecurityGroup, $callerInfo)))
                    return new Exception("Invalid Default SecurityGroups",403);
                if (!isset($dataObject->SecurityGroup))
                    $dataObject->SecurityGroup = $callerInfo["DefaultSecurityGroup"];
                if (!isset($dataObject->Status))
                    $dataObject->Status = 5; //ACTIVE
                $this->fixToCase($dataObject, 'Family');
                if (isset($dataObject->Family))
                    $family = $this->proxy->genericGetById("families", $dataObject->Family, $callerInfo);
                if (isset($family)) {
                    if (!isset($dataObject->Version))
                        $dataObject->Version = $family["CurrentVersion"];
                    return $this->proxy->genericInsert("assets", $dataObject);
                } else
                    return new Exception("Invalid Family",403);
            } else if (isset($args[0]) && is_numeric($args[0])) {
                if (isset($args[1])) {
                    if ( !empty($this->proxy->genericGetById("assets",$args[0],$callerInfo))) {
                        if (strtolower($args[1]) == "files" && !empty($files)) { 
                            $result = array();
                            foreach($files as $file) {
                                    $insertData = new stdClass();
                                    $insertData->Name = $file["name"];
                                    $insertData->Size = $file["size"];
                                    $insertData->Type = $file["type"];
                                    $insertData->Asset = $args[0];
                                    $insertData->Owner = $callerInfo["Id"];
                                    if (isset($request["category"]))
                                        $insertData->Category = $request["category"];
                                    else
                                        $insertData->Category = 5;
                                    if (isset($request["created"]))
                                        $insertData->Created = $request["created"];
                                    else
                                        $insertData->Created = date("Y-m-d H:i:s");
                                    $current = $result[] =  $this->proxy->genericInsert("files", $insertData);
                            
                                    $index = strrpos($file["name"], '.'); 
                                    $extension = substr($file["name"], $index); 
                                    move_uploaded_file($file["tmp_name"], __DIR__."/../../dataFiles/gmaoFile".$current["Id"].$extension);
                            }
                            return $result;
                        }
                        else
                            return  new Exception("Unknown resource",404);
                    } else
                        return new Exception("Forbidden",403);
                }
                else
                    return  new Exception("Unknown resource",404);
            } else if ($verb =="POSTBYLOTS") { 
                $dataObject = json_decode($data);
                $family = $this->proxy->genericGetById("families",$dataObject->Family, $callerInfo);
                if (isset($family)) {
                    $quantity = $dataObject->Quantity;
                    //CREATE ASSETS
                    $result = array();
                    for ($i=0; $i < $quantity; $i++) { 
                        $nextCode = $this->proxy->getNextAsset($family["Id"]);
                        $insertData = new stdClass();
                        $insertData->Family = $family["Id"];
                        $insertData->Location = $callerInfo["Location"];
                        $insertData->Securitygroup = $family["Securitygroup"];
                        $insertData->Version = $family["CurrentVersion"];
                        $insertData->Code = $nextCode;
                        $insertData->Name = $nextCode;
                        $insertData->Status = 1;
                        $result[] =  $this->proxy->genericInsert("assets", $insertData);
                    }
                    return $result;
                } else 
                    return new Exception("Forbidden",403);
            } else
                return  new Exception("Invalid URL parameters",405);  
        }
        else
            return new Exception("Forbidden",403);
    }

    public function putAction($args, $callerInfo, $data, $request, $verb) {
        $dataObject = json_decode($data); 
        if (isset($args[0]) && is_numeric($args[0]) && count($args) == 1 && empty($verb)) {
            $this->fixToCase($dataObject, 'Id');
            $dataObject->Id = $args[0];
            $this->fixToCase($dataObject, 'SecurityGroup');
            if (isset($dataObject->SecurityGroup) && empty($this->proxy->genericGetById("SecurityGroups", $dataObject->SecurityGroup, $callerInfo)))
                return new Exception("Invalid Default SecurityGroups",403);
            $this->fixToCase($dataObject, 'Family');
            if (isset($dataObject->Family) && empty($this->proxy->genericGetById("families", $dataObject->Family, $callerInfo)))
                return new Exception("Invalid Family",403);
            if (($callerInfo["IsSystemAdmin"] == 1 || isset($callerInfo["roles"]["SA"])))  {
                $asset = $this->proxy->genericGetById("assets",$args[0],$callerInfo);
                if (!empty($asset)) {
                    //Add in status historic
                    if ($dataObject->Status != $asset["Status"])
                        $history = $this->proxy->insertStatusHistory($asset, $dataObject->Status ,$callerInfo);
                    return $this->proxy->genericUpdate("assets", $dataObject);
                } else
                    return  new Exception("Unknown resource",404);
            }
            else
                return new Exception("Forbidden",403);
        }
        else
            return  new Exception("Invalid URL parameters",405);
    }
}
