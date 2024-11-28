<?php

class operationsController extends BaseController
{

    // Retrieves the operations
    public function getAction($args, $callerInfo, $rawdata, $request, $verb) {
        if (isset($args[0]) && is_numeric($args[0]) && count($args) == 1 && empty($verb)) {
            $toReturn =  $this->proxy->genericGetById("operations",$args[0],$callerInfo);
            if (empty($toReturn))
                return  new Exception("Unknown resource",404);
            else
                return $toReturn;
        } else if (isset($args[0]) && is_numeric($args[0]) && isset($args[1]) && strtolower($args[1]) == "files") {
            if (!empty($this->proxy->genericGetById("operations",$args[0],$callerInfo))) {
                return $this->proxy->getOperationFiles($args[0], isset($request["start"])?$request["start"]:null, isset($request["limit"])?$request["limit"]:BaseController::DEFAULT_LIMIT,
                isset($request["sortField"])?$request["sortField"]:null, isset($request["sortAsc"])?$request["sortAsc"]:null,
                isset($request["filterField"])?$request["filterField"]:null, isset($request["filterValue"])?$request["filterValue"]:null,$callerInfo);
            }
            else
                return new Exception("Forbidden",403);
        }
        else
            if (count($args) == 0) {
                 return $this->proxy->genericGetElements("operations", isset($request["start"])?$request["start"]:null, isset($request["limit"])?$request["limit"]:BaseController::DEFAULT_LIMIT,
                    isset($request["sortField"])?$request["sortField"]:null, isset($request["sortAsc"])?$request["sortAsc"]:null,
                    isset($request["filterField"])?$request["filterField"]:null, isset($request["filterValue"])?$request["filterValue"]:null,$callerInfo);
            }
            else
                return  new Exception("Unknown resource",404);

    }

    // Delete operations
    public function deleteAction($args, $callerInfo, $data, $request, $verb) {
        if (($callerInfo["IsSystemAdmin"] == 1 || isset($callerInfo["roles"]["SA"]) || isset($callerInfo["roles"]["SUP"])) && !empty($this->proxy->genericGetById("operations",$args[0],$callerInfo))) {
            if (isset($args[0]) && is_numeric($args[0]) && count($args) == 1 && empty($verb))
                return $this->proxy->genericDelete("operations",$args[0]);
            else
                return  new Exception("Invalid URL parameters",405);
        } else
            return new Exception("Forbidden",403);
    }

    // Create operations
    public function postAction($args, $callerInfo, $data, $request, $verb, $files) {
         if ($callerInfo["IsSystemAdmin"] == 1 || isset($callerInfo["roles"]["SA"]) || isset($callerInfo["roles"]["SUP"])) {
            if (count($args) == 0 && empty($verb) && empty($files)) {
                $dataObject = json_decode($data);
                $this->fixToCase($dataObject, 'SecurityGroup');
                if (isset($dataObject->SecurityGroup) && empty($this->proxy->genericGetById("SecurityGroups", $dataObject->SecurityGroup, $callerInfo)))
                    return new Exception("Invalid Default SecurityGroups",403);
                if (!isset($dataObject->SecurityGroup))
                    $dataObject->SecurityGroup = $callerInfo["DefaultSecurityGroup"];
                return $this->proxy->genericInsert("operations", $dataObject);
            } else if (isset($args[0]) && is_numeric($args[0]) && empty($files)) {
                if (isset($args[1])) {
                    if ( !empty($this->proxy->genericGetById("operations",$args[0],$callerInfo))) {
                        if (strtolower($args[1]) == "files" && !empty($files)) { 
                            $result = array();
                            foreach($files as $file) {
                                $insertData = new stdClass();
                                $insertData->Name = $file["name"];
                                $insertData->Size = $file["size"];
                                $insertData->Type = $file["type"];
                                $insertData->Operation = $args[0];
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
                        }
                        else
                            return  new Exception("Unknown resource",404);
                    } else
                        return new Exception("Forbidden",403);
                }
                else
                    return  new Exception("Unknown resource",404);
            } else if (!empty($files) && empty($args[0]) && $verb!="UPDATESFROMFILES") {
                set_time_limit(600);
                $this->delimiter = ";";
                $this->enclosure = '"';
                $result = array();
                $arrDevices = array();
                foreach ($files as $file) {
                    $file=$file['tmp_name'];
                    $csv= file_get_contents($file);
                    $array = array_map(function($row) {
                        return str_getcsv($row,$this->delimiter,$this->enclosure);
                    },  explode("\n", $csv));

                    if (count($array)>1) {
                        //CREATE OPERATIONS
                        for ($i=1; $i<count($array); $i++) {
                            $currentObject = new stdClass();
                            $j=0;
                            if (isset($array[$i]) && is_array($array[$i]) && count($array[$i]) > 1) {
                                foreach($array[0] as $field) {
                                    $exploded = explode(".", $field);
                                    if (count($exploded) == 2) {
                                        if (!isset($currentObject -> $exploded[0]))
                                            $currentObject -> $exploded[0] = new stdClass();
                                        $currentObject -> $exploded[0] -> $exploded[1] = $array[$i][$j];
                                    }
                                    else
                                        $currentObject -> $field = $array[$i][$j];
                                    $j++;
                                }
                                if (!isset($currentObject->Securitygroup))
                                    $currentObject->Securitygroup = $callerInfo["DefaultSecurityGroup"];
                                $arrDevices[] = $currentObject;
                                $result[] = $this->proxy->genericInsert("operations", $currentObject);
                            }
                        }
                    }
                }
                $mainResult=new stdClass();
                $mainResult->success = true;
                $mainResult->data = $result;
                return $mainResult;
            } else if (!empty($files) && $verb == "UPDATESFROMFILES") {
                return $this->proxy->updateByFile($files, "operations", $callerInfo);
            } else
                return  new Exception("Invalid URL parameters",405);  
        }
        else
            return new Exception("Forbidden",403);
    }

    // Update operations
    public function putAction($args, $callerInfo, $data, $request, $verb) {
        $dataObject = json_decode($data); 
        if (isset($args[0]) && is_numeric($args[0]) && count($args) == 1 && empty($verb)) {
            $this->fixToCase($dataObject, 'Id');
            $dataObject->Id = $args[0];
            $this->fixToCase($dataObject, 'SecurityGroup');
            if (isset($dataObject->SecurityGroup) && empty($this->proxy->genericGetById("SecurityGroups", $dataObject->SecurityGroup, $callerInfo)))
                return new Exception("Invalid Default SecurityGroups",403);
            if (($callerInfo["IsSystemAdmin"] == 1 || isset($callerInfo["roles"]["SA"]) || isset($callerInfo["roles"]["SUP"])) && !empty($this->proxy->genericGetById("operations",$args[0],$callerInfo)))  {
                return $this->proxy->genericUpdate("operations", $dataObject);
            }
            else
                return new Exception("Forbidden",403);
        }
        else
            return  new Exception("Invalid URL parameters",405);
    }
}
