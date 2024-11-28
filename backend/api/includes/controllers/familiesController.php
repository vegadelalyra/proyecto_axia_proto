<?php

class familiesController extends BaseController
{

    // Retrieves the families
    public function getAction($args, $callerInfo, $rawdata, $request, $verb) {
        if (isset($args[0]) && is_numeric($args[0]) && count($args) == 1 && empty($verb)) {
            $toReturn =  $this->proxy->getFamilyById($args[0],$callerInfo);
            if (empty($toReturn))
                return  new Exception("Unknown resource",404);
            else
                return $toReturn;
        } else if (count($args) == 0 && isset($verb) && substr($verb, 0, 1) == "N") {
            $key = substr(escape_string($verb), 1);
            $family = $this->proxy->genericGetByKeyField("families","Key", $key, $callerInfo);
            if (!empty($family)) {
                return $family;
            } else
                return new Exception("Does not exist",404);      
        } else if (isset($args[0]) && is_numeric($args[0]) && isset($args[1]) && strtolower($args[1]) == "versions") {
            $toReturn = $this->proxy->getFamilyVersions("versions",$args[0],$callerInfo);
            if (!empty($toReturn)) {
                return $toReturn;
            }
            else
                return new Exception("Forbidden",403);
        } else if (count($args) == 0 && isset($verb) && $verb == "status") {
            return $this->proxy->recoveryFamilyStatus($callerInfo, isset($request["family"])?$request["family"]:null);
        } else if (isset($verb) && $verb == "globalstatus") {
            return $this->proxy->getGlobalFamilyStatus($callerInfo, isset($args[0])?$args[0]:null);
        } else if (isset($verb) && $verb == "globalquantity") {
            return $this->proxy->getGlobalFamilyQuantity($callerInfo, isset($args[0])?$args[0]:null);
        } else if (isset($args[0]) && is_numeric($args[0]) && isset($args[1]) && strtolower($args[1]) == "nextasset") {
            return $this->proxy->getNextAsset($args[0]);
        } else if (isset($args[0]) && is_numeric($args[0]) && isset($args[1]) && strtolower($args[1]) == "files") {
            $family = $this->proxy->genericGetById("families",$args[0],$callerInfo);
            if (!empty($family) && empty($request["all"])) {
                return $this->proxy->getFamilyFilesByVersion(isset($request["version"])?$request["version"]:null, isset($request["start"])?$request["start"]:null, isset($request["limit"])?$request["limit"]:BaseController::DEFAULT_LIMIT,
                isset($request["sortField"])?$request["sortField"]:null, isset($request["sortAsc"])?$request["sortAsc"]:null,
                isset($request["filterField"])?$request["filterField"]:null, isset($request["filterValue"])?$request["filterValue"]:null,$callerInfo);
            } else if (!empty($family) && isset($request["version"])) {
                return $this->proxy->getFamilyAllFilesByVersion($family["Id"], isset($request["version"])?$request["version"]:null, isset($request["start"])?$request["start"]:null, isset($request["limit"])?$request["limit"]:BaseController::DEFAULT_LIMIT,
                isset($request["sortField"])?$request["sortField"]:null, isset($request["sortAsc"])?$request["sortAsc"]:null,
                isset($request["filterField"])?$request["filterField"]:null, isset($request["filterValue"])?$request["filterValue"]:null,$callerInfo);
            }
            else
                return new Exception("Forbidden",403);
        } else if (isset($args[0]) && is_numeric($args[0]) && isset($args[1]) && strtolower($args[1]) == "tasks") {
            return $this->proxy->getFamilyTasksByVersion($args[0], isset($request["version"])?$request["version"]:null, isset($request["start"])?$request["start"]:null, isset($request["limit"])?$request["limit"]:BaseController::DEFAULT_LIMIT,
                isset($request["sortField"])?$request["sortField"]:null, isset($request["sortAsc"])?$request["sortAsc"]:null,
                isset($request["filterField"])?$request["filterField"]:null, isset($request["filterValue"])?$request["filterValue"]:null,$callerInfo);
        } else if (isset($args[0]) && is_numeric($args[0]) && isset($args[1]) && strtolower($args[1]) == "upgradetask") {
            return $this->proxy->getFamilyUpgradeTaskByVersion($args[0], isset($request["version"])?$request["version"]:null,$callerInfo);
        } else if (isset($args[0]) && is_numeric($args[0]) && isset($args[1]) && strtolower($args[1]) == "checklists") {
            $family = $this->proxy->genericGetById("families",$args[0],$callerInfo);
            if (!empty($family) && empty($request["all"]) && isset($request["version"])) {
                return $this->proxy->getFamilyCheckListsByVersion($args[0], isset($request["version"])?$request["version"]:null, isset($request["start"])?$request["start"]:null, isset($request["limit"])?$request["limit"]:BaseController::DEFAULT_LIMIT,
                    isset($request["sortField"])?$request["sortField"]:null, isset($request["sortAsc"])?$request["sortAsc"]:null,
                    isset($request["filterField"])?$request["filterField"]:null, isset($request["filterValue"])?$request["filterValue"]:null,$callerInfo);
            }
            else
                return new Exception("Forbidden",403);
        } else
            if (count($args) == 0) {
                return $this->proxy->genericGetElements("families", isset($request["start"])?$request["start"]:null, isset($request["limit"])?$request["limit"]:BaseController::DEFAULT_LIMIT,
                    isset($request["sortField"])?$request["sortField"]:null, isset($request["sortAsc"])?$request["sortAsc"]:null,
                    isset($request["filterField"])?$request["filterField"]:null, isset($request["filterValue"])?$request["filterValue"]:null,$callerInfo);
            }
            else
                return  new Exception("Unknown resource",404);
    }

    // Delete families
    public function deleteAction($args, $callerInfo, $data, $request, $verb) {
        if (($callerInfo["IsSystemAdmin"] == 1 || isset($callerInfo["roles"]["SA"])) && !empty($this->proxy->genericGetById("families",$args[0],$callerInfo))) {
            if (isset($args[0]) && is_numeric($args[0]) && count($args) == 1 && empty($verb))
                return $this->proxy->genericDelete("families",$args[0]);
            else
                return  new Exception("Invalid URL parameters",405);
        } else
            return new Exception("Forbidden",403);
    }

    // Create families
    public function postAction($args, $callerInfo, $data, $request, $verb, $files) {
        if ($callerInfo["IsSystemAdmin"] == 1 || isset($callerInfo["roles"]["SA"]) || isset($callerInfo["roles"]["DES"])) {
            if (count($args) == 0 && empty($verb)) {
                $dataObject = json_decode($data);
                $this->fixToCase($dataObject, 'SecurityGroup');
                if (isset($dataObject->SecurityGroup) && empty($this->proxy->genericGetById("SecurityGroups", $dataObject->SecurityGroup, $callerInfo)))
                    return new Exception("Invalid Default SecurityGroups",403);
                if (!isset($dataObject->SecurityGroup))
                    $dataObject->SecurityGroup = $callerInfo["DefaultSecurityGroup"];
                if (!isset($dataObject->CurrentVersion))
                    $dataObject->CurrentVersion = 1;
                //CREATE FAMILY
                $family = $this->proxy->genericInsert("families", $dataObject);
                if(isset($family) && isset($family["Id"])) {
                    //CREATE VERSION 1
                    $newVersion = new stdClass();
                    $newVersion->Family = $family["Id"];
                    $newVersion->Status = 1;
                    $newVersion->Description = "Versión 1";
                    $newVersion->Number = 1;
                    $version = $this->proxy->genericInsert("versions", $newVersion);

                    if(isset($version)) {
                        //UDPATE FAMILY
                        $updateFamily = new stdClass();
                        $updateFamily->Id = $family["Id"];
                        $updateFamily->CurrentVersion = $version["Id"];
                        $updateFamily = $this->proxy->genericUpdate("families", $updateFamily);

                        if(isset($updateFamily))
                            return $updateFamily;
                        else
                            return  new Exception("Unknown resource",404);
                    } else 
                        return  new Exception("Unknown resource",404);
                } else
                    return new Exception("Unknown resource",404);
            } else if (isset($args[0]) && is_numeric($args[0])) {
                if (isset($args[1])) {
                    $family = $this->proxy->genericGetById("families",$args[0],$callerInfo);
                    if (!empty($family)) {
                        if (strtolower($args[1]) == "files" && !empty($files)) { 
                            $result = array();
                            foreach($files as $file) {
                                    $insertData = new stdClass();
                                    $insertData->Name = $file["name"];
                                    $insertData->Size = $file["size"];
                                    $insertData->Type = $file["type"];
                                    $insertData->Family = $args[0];
                                    $insertData->Version = $family["CurrentVersion"];
                                    $insertData->Owner = $callerInfo["Id"];
                                    if (isset($request["category"]))
                                        $insertData->Category = $request["category"];
                                    else
                                        $insertData->Category = 5;
                                    if (isset($request["created"]))
                                        $insertData->Created = $request["created"];
                                    else
                                        $insertData->Created = date("Y-m-d H:i:s");

                                    $current = $result[] = $this->proxy->genericInsert("files", $insertData);
                            
                                    $index = strrpos($file["name"], '.'); 
                                    $extension = substr($file["name"], $index); 
                                    move_uploaded_file($file["tmp_name"], __DIR__."/../../dataFiles/gmaoFile".$current["Id"].$extension);
                            }
                            return $result;
                        } else if (strtolower($args[1]) == "operationcodes") {
                            $result = array();
                            foreach(explode(",",$data) as $currentElement) {
                                if (!empty($this->proxy->genericGetById("operationcodes",$currentElement,$callerInfo))) {
                                    $insertData = new stdClass();
                                    $insertData->orders_id = $args[0];
                                    $insertData->operationcodes_id = $currentElement;
                                    $result[] =  $this->proxy->genericInsert("orders_has_operationcodes", $insertData);
                                }
                            }
                            return (count($result)==1)?$result[0]:$result;
                        }
                        else
                            return  new Exception("Unknown resource",404);
                    } else
                        return new Exception("Forbidden",403);
                }
                else
                    return  new Exception("Unknown resource",404);
            }
            else
                return  new Exception("Invalid URL parameters",405);  
        } 
        else
            return new Exception("Forbidden",403);
    }

    // Update families
    public function putAction($args, $callerInfo, $data, $request, $verb) {
        $dataObject = json_decode($data); 
        if (isset($args[0]) && is_numeric($args[0]) && count($args) == 1 && empty($verb)) {
            $this->fixToCase($dataObject, 'Id');
            $dataObject->Id = $args[0];
            $this->fixToCase($dataObject, 'SecurityGroup');
            if (isset($dataObject->SecurityGroup) && empty($this->proxy->genericGetById("SecurityGroups", $dataObject->SecurityGroup, $callerInfo)))
                return new Exception("Invalid Default SecurityGroups",403);
            if (($callerInfo["IsSystemAdmin"] == 1 || isset($callerInfo["roles"]["SA"]) || isset($callerInfo["roles"]["MAN"]) || isset($callerInfo["roles"]["DES"])) && !empty($this->proxy->genericGetById("families",$args[0],$callerInfo)))  {
                return $this->proxy->genericUpdate("families", $dataObject);
            }
            else
                return new Exception("Forbidden",403);
        }
        else
            return  new Exception("Invalid URL parameters",405);
    }
}
