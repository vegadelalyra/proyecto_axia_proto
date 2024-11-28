<?php

class tasksController extends BaseController
{

    // Retrieves the tasks
    public function getAction($args, $callerInfo, $rawdata, $request, $verb) {
        if (isset($args[0]) && is_numeric($args[0]) && count($args) == 1 && empty($verb)) {
            $toReturn =  $this->proxy->genericGetById("tasks",$args[0],$callerInfo);
            if (empty($toReturn))
                return  new Exception("Unknown resource",404);
            else
                return $toReturn;
        } else if (isset($args[0]) && is_numeric($args[0]) && isset($args[1])) {
            if (strtolower($args[1]) == "files" && isset($request["version"])) {
                if (!empty($this->proxy->genericGetById("tasks",$args[0],$callerInfo))) {
                    return $this->proxy->getTasksFilesByVersion($args[0], $request["version"], isset($request["start"])?$request["start"]:null, isset($request["limit"])?$request["limit"]:BaseController::DEFAULT_LIMIT,
                    isset($request["sortField"])?$request["sortField"]:null, isset($request["sortAsc"])?$request["sortAsc"]:null,
                    isset($request["filterField"])?$request["filterField"]:null, isset($request["filterValue"])?$request["filterValue"]:null,$callerInfo);
                }
                else
                    return new Exception("Forbidden",403);
            } else
                return  new Exception("Unknown resource",404);
        }
        else
            if (count($args) == 0) {
                return $this->proxy->genericGetElements("tasks", isset($request["start"])?$request["start"]:null, isset($request["limit"])?$request["limit"]:BaseController::DEFAULT_LIMIT,
                    isset($request["sortField"])?$request["sortField"]:null, isset($request["sortAsc"])?$request["sortAsc"]:null,
                    isset($request["filterField"])?$request["filterField"]:null, isset($request["filterValue"])?$request["filterValue"]:null,$callerInfo);
            }
            else
                return  new Exception("Unknown resource",404);

    }

    // Delete tasks
    public function deleteAction($args, $callerInfo, $data, $request, $verb) {
        if (($callerInfo["IsSystemAdmin"] == 1 || isset($callerInfo["roles"]["SA"])) && !empty($this->proxy->genericGetById("locations",$args[0],$callerInfo))) {
            if (isset($args[0]) && is_numeric($args[0]) && count($args) == 1 && empty($verb))
                return $this->proxy->genericDelete("tasks",$args[0]);
            else
                return  new Exception("Invalid URL parameters",405);
        } else
            return new Exception("Forbidden",403);
    }

    // Create tasks
    public function postAction($args, $callerInfo, $data, $request, $verb, $files) {
         if ($callerInfo["IsSystemAdmin"] == 1 || isset($callerInfo["roles"]["SA"]) || isset($callerInfo["roles"]["MAN"])) {
            if (count($args) == 0 && empty($verb)) {
                $dataObject = json_decode($data);
                return $this->proxy->genericInsert("tasks", $dataObject);
            } else if (isset($args[0]) && is_numeric($args[0])) {
                if (isset($args[1])) {
                    $task = $this->proxy->genericGetById("tasks",$args[0],$callerInfo);
                    if (!empty($task)) {
                        if (strtolower($args[1]) == "files" && !empty($files)) { 
                            $result = array();
                            foreach($files as $file) {
                                    $insertData = new stdClass();
                                    $insertData->Name = $file["name"];
                                    $insertData->Size = $file["size"];
                                    $insertData->Type = $file["type"];
                                    $insertData->Family = $task["Family"];
                                    $insertData->Version = $task["Version"];
                                    $insertData->Owner = $callerInfo["Id"];
                                    $insertData->Task = $task["Id"];
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

    // Update tasks
    public function putAction($args, $callerInfo, $data, $request, $verb) {
        $dataObject = json_decode($data); 
        if (isset($args[0]) && is_numeric($args[0]) && count($args) == 1 && empty($verb)) {
            $this->fixToCase($dataObject, 'Id');
            $dataObject->Id = $args[0];
            if (($callerInfo["IsSystemAdmin"] == 1 || isset($callerInfo["roles"]["SA"]) || isset($callerInfo["roles"]["MAN"])) && !empty($this->proxy->genericGetById("tasks",$args[0],$callerInfo)))  {
                return $this->proxy->genericUpdate("tasks", $dataObject);
            }
            else
                return new Exception("Forbidden",403);
        }
        else
            return  new Exception("Invalid URL parameters",405);
    }
}