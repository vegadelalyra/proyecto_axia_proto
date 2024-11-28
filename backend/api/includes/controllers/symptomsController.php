<?php

class symptomsController extends BaseController
{
    // Retrieves the symptoms
    public function getAction($args, $callerInfo, $rawdata, $request, $verb) {
        if (isset($args[0]) && is_numeric($args[0])) {
            if (isset($args[1])) {
                if (strtolower($args[1]) == "elements") {
                    if ($callerInfo["IsSystemAdmin"] == 1 || !empty($this->proxy->genericGetById("symptoms",$args[0],$callerInfo)))
                        return $this->proxy->genericGetSubelements($args[0], "symptoms_Id", "elements_Id", "Id", "symptoms_has_elements", "elements", isset($request["start"])?$request["start"]:null, isset($request["limit"])?$request["limit"]:BaseController::DEFAULT_LIMIT,
                            isset($request["sortField"])?$request["sortField"]:null, isset($request["sortAsc"])?$request["sortAsc"]:null,
                            isset($request["filterField"])?$request["filterField"]:null, isset($request["filterValue"])?$request["filterValue"]:null);
                    else
                        return new Exception("Forbidden",403);
                }
            } else {
            $toReturn =  $this->proxy->genericGetById("symptoms",$args[0],$callerInfo);
            if (empty($toReturn))
                return  new Exception("Unknown resource",404);
            else
                return $toReturn;
            }
        }
        else
            if (count($args) == 0) {
                return $this->proxy->genericGetElements("symptoms", isset($request["start"])?$request["start"]:null, isset($request["limit"])?$request["limit"]:BaseController::DEFAULT_LIMIT,
                    isset($request["sortField"])?$request["sortField"]:null, isset($request["sortAsc"])?$request["sortAsc"]:null,
                    isset($request["filterField"])?$request["filterField"]:null, isset($request["filterValue"])?$request["filterValue"]:null,$callerInfo);
            }
            else
                return  new Exception("Unknown resource",404);

    }

    // Delete symptoms
    public function deleteAction($args, $callerInfo, $data, $request, $verb) {
        if (($callerInfo["IsSystemAdmin"] == 1 || isset($callerInfo["roles"]["SA"]) || isset($callerInfo["roles"]["SUP"])) && !empty($this->proxy->genericGetById("symptoms",$args[0],$callerInfo))) {
            if (isset($args[0]) && is_numeric($args[0])) {
                if (isset($args[1])) { 
                    if (strtolower($args[1]) == "elements") {
                        if (isset($args[2]) && is_numeric($args[2])) {
                            return $this->proxy->genericRelationDelete("symptoms_has_elements","Symptoms_Id",$args[0],"Elements_Id",$args[2],$callerInfo);
                        }
                        else
                            return  new Exception("Unknown resource",404);
                    }
                    else
                        return  new Exception("Unknown resource",404);
                }
                else
                    return $this->proxy->genericDelete("symptoms",$args[0]);
            }
            else
                return  new Exception("Invalid URL parameters",405);
        } else
            return new Exception("Forbidden",403);
    }

    // Create symptoms
    public function postAction($args, $callerInfo, $data, $request, $verb, $files) {
        if ($callerInfo["IsSystemAdmin"] == 1 || isset($callerInfo["roles"]["SA"]) || isset($callerInfo["roles"]["SUP"])) {
            if (count($args) == 0 && empty($verb) && empty($files)) {
                $dataObject = json_decode($data);
                $this->fixToCase($dataObject, 'Family');
                if (isset($dataObject->Family) && empty($this->proxy->genericGetById("families", $dataObject->Family, $callerInfo)))
                    return new Exception("Invalid Default SecurityGroups",403);
                return $this->proxy->genericInsert("symptoms", $dataObject);
            }
            else if (isset($args[0]) && is_numeric($args[0]) && empty($files)) {
                if (isset($args[1])) {
                    if (($callerInfo["IsSystemAdmin"] == 1 || isset($callerInfo["roles"]["SA"])) && !empty($this->proxy->genericGetById("symptoms",$args[0],$callerInfo))) {
                        if (strtolower($args[1]) == "elements") {
                            $result = array();
                            foreach(explode(",",$data) as $currentElement) {
                                if (!empty($this->proxy->genericGetById("elements",$currentElement,$callerInfo))) {
                                    $insertData = new stdClass();
                                    $insertData->Symptoms_Id = $args[0];
                                    $insertData->Elements_Id = $currentElement;
                                    $result[] =  $this->proxy->genericInsert("symptoms_has_elements", $insertData);
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
            } else if (!empty($files) && $verb != "UPDATESFROMFILES") {
                $family = $this->proxy->genericGetById("families", $request["family"], $callerInfo);
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

                    if (count($array)>1 && isset($family)) {
                        //CREATE SYMPTOMS
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
                                if (!isset($currentObject->Family))
                                    $currentObject->Family = $request["family"];
                                $arrDevices[] = $currentObject;
                                $result[] = $this->proxy->genericInsert("symptoms", $currentObject);
                            }
                        }
                    }
                }
                $mainResult=new stdClass();
                $mainResult->success = true;
                $mainResult->data = $result;
                return $mainResult;
            } else if (!empty($files) && $verb == "UPDATESFROMFILES") {
                return $this->proxy->updateByFile($files, "symptoms", $callerInfo);
            } else
                return  new Exception("Invalid URL parameters",405);  
        }
        else
            return new Exception("Forbidden",403);
    }

    // Update symptoms
    public function putAction($args, $callerInfo, $data, $request, $verb) {
        $dataObject = json_decode($data); 
        if (isset($args[0]) && is_numeric($args[0]) && count($args) == 1 && empty($verb)) {
            $this->fixToCase($dataObject, 'Id');
            $dataObject->Id = $args[0];
            $this->fixToCase($dataObject, 'Family');
            if (isset($dataObject->Family) && empty($this->proxy->genericGetById("families", $dataObject->Family, $callerInfo)))
                return new Exception("Invalid Default SecurityGroups",403);
            if (($callerInfo["IsSystemAdmin"] == 1 || isset($callerInfo["roles"]["SA"]) || isset($callerInfo["roles"]["SUP"])) && !empty($this->proxy->genericGetById("symptoms",$args[0],$callerInfo)))  {
                return $this->proxy->genericUpdate("symptoms", $dataObject);
            }
            else
                return new Exception("Forbidden",403);
        }
        else
            return  new Exception("Invalid URL parameters",405);
    }
}
