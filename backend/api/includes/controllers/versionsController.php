<?php

class versionsController extends BaseController
{
    // Retrieves the versions
    public function getAction($args, $callerInfo, $rawdata, $request, $verb) {
        if (isset($args[0]) && is_numeric($args[0]) && count($args) == 1 && empty($verb)) {
            $toReturn = $this->proxy->genericGetById("versions",$args[0],$callerInfo);
            if (empty($toReturn))
                return  new Exception("Unknown resource",404);
            else
                return $toReturn;
        } else if (isset($args[0]) && is_numeric($args[0]) && isset($args[1]) && strtolower($args[1]) == "family") {
            $toReturn = $this->proxy->getFamilyVersions("versions",$args[0],$callerInfo);
            if (!empty($toReturn)) {
                return $toReturn;
            }
            else
                return new Exception("Forbidden",403);
        } if (isset($args[0]) && is_numeric($args[0]) && isset($args[1]) && strtolower($args[1]) == "files") {
            if (!empty($this->proxy->genericGetById("versions",$args[0],$callerInfo))) {
                return $this->proxy->getFamilyFilesByVersion($args[0], isset($request["start"])?$request["start"]:null, isset($request["limit"])?$request["limit"]:BaseController::DEFAULT_LIMIT,
                isset($request["sortField"])?$request["sortField"]:null, isset($request["sortAsc"])?$request["sortAsc"]:null,
                isset($request["filterField"])?$request["filterField"]:null, isset($request["filterValue"])?$request["filterValue"]:null,$callerInfo);
            }
            else
                return new Exception("Forbidden",403);
        } else
            if (count($args) == 0) {
                 return $this->proxy->genericGetElements("versions", isset($request["start"])?$request["start"]:null, isset($request["limit"])?$request["limit"]:BaseController::DEFAULT_LIMIT,
                    isset($request["sortField"])?$request["sortField"]:null, isset($request["sortAsc"])?$request["sortAsc"]:null,
                    isset($request["filterField"])?$request["filterField"]:null, isset($request["filterValue"])?$request["filterValue"]:null,$callerInfo);
            }
            else
                return  new Exception("Unknown resource",404);

    }

    // Update versions
    public function putAction($args, $callerInfo, $data, $request, $verb) {
        $dataObject = json_decode($data); 
        if (isset($args[0]) && is_numeric($args[0]) && count($args) == 1 && empty($verb)) {
            $this->fixToCase($dataObject, 'Id');
            $dataObject->Id = $args[0];
            if (($callerInfo["IsSystemAdmin"] == 1 || isset($callerInfo["roles"]["SA"]) || isset($callerInfo["roles"]["DES"]) || isset($callerInfo["roles"]["MAN"])) && !empty($this->proxy->genericGetById("versions",$args[0],$callerInfo)))  {
                return $this->proxy->genericUpdate("versions", $dataObject);
            }
            else
                return new Exception("Forbidden",403);
        }
        else
            return  new Exception("Invalid URL parameters",405);
    }

    // Create versions
    public function postAction($args, $callerInfo, $data, $request, $verb) {
        if ($callerInfo["IsSystemAdmin"] == 1 || isset($callerInfo["roles"]["SA"]) || isset($callerInfo["roles"]["DES"]) || isset($callerInfo["roles"]["MAN"])) {
            if (count($args) == 0 && empty($verb)) {
                $dataObject = json_decode($data);
                $lastVersion = $this->proxy->getLastVersionFamily($dataObject->Family, $callerInfo);
                if (isset($lastVersion)) {
                    //update last version
                    $updateOldVersion = new stdClass();
                    $updateOldVersion->Id = $lastVersion["Id"];
                    $updateOldVersion->Status = 7;
                    $oldVersion = $this->proxy->genericUpdate("versions", $updateOldVersion);

                    //insert new version
                    $version = $this->proxy->genericInsert("versions", $dataObject);
                    if (isset($version)) {
                        //Create task
                        $newTask = new stdClass();
                        $newTask->Family = $version["Family"];
                        $newTask->Version = $version["Id"];
                        $newTask->Description = 'Actualizar versión';
                        $task = $this->proxy->genericInsert("tasks", $newTask);
                        //Update family
                        $updateFamily = new stdClass();
                        $updateFamily->Id = $version["Family"];
                        $updateFamily->CurrentVersion = $version["Id"];
                        $updateFamily = $this->proxy->genericUpdate("families", $updateFamily);
                        //Update version
                        if (isset($task)) {
                            $updateVersion = new stdClass();
                            $updateVersion->Id = $version["Id"];
                            $updateVersion->UpgradeTask = $task["Id"];
                            $updateVersion = $this->proxy->genericUpdate("versions", $updateVersion);
                        }
                        return $updateVersion;
                    } else
                        return new Exception("Unknown resource",404);
                } else
                    return new Exception("Unknown resource",404);
            }
            else
               return  new Exception("Invalid URL parameters",405);  
        }
        else
            return new Exception("Forbidden",403);
    }
}
