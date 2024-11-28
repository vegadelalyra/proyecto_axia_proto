<?php

class checklistsController extends BaseController
{

    // Retrieves the checklist
    public function getAction($args, $callerInfo, $rawdata, $request, $verb) {
        if (isset($args[0]) && is_numeric($args[0]) && count($args) == 1 && empty($verb)) {
            $toReturn =  $this->proxy->genericGetById("checklists",$args[0],$callerInfo);
            if (empty($toReturn))
                return  new Exception("Unknown resource",404);
            else
                return $toReturn;
        } else if (isset($args[0]) && is_numeric($args[0]) && isset($args[1])) {
            if (strtolower($args[1]) == "chkdetails") {
                if (!empty($this->proxy->genericGetById("checklists",$args[0],$callerInfo))) {
                    return $this->proxy->getChkDetails($args[0], $callerInfo);
                }
                else
                    return new Exception("Forbidden",403);
            } else if (isset($args[0]) && is_numeric($args[0]) && strtolower($args[1]) == "familyversions" && isset($args[2]) && is_numeric($args[2]) && isset($request["category"])) {
                return $this->proxy->getAllCheckListsByFamilyVersion($args[0], $args[2], $request["category"], isset($request["start"])?$request["start"]:null, isset($request["limit"])?$request["limit"]:BaseController::DEFAULT_LIMIT,
                isset($request["sortField"])?$request["sortField"]:null, isset($request["sortAsc"])?$request["sortAsc"]:null,
                isset($request["filterField"])?$request["filterField"]:null, isset($request["filterValue"])?$request["filterValue"]:null, $callerInfo);
            } else
                return  new Exception("Unknown resource",404);
        } else
            if (count($args) == 0) {
                 return $this->proxy->genericGetElements("checklists", isset($request["start"])?$request["start"]:null, isset($request["limit"])?$request["limit"]:BaseController::DEFAULT_LIMIT,
                    isset($request["sortField"])?$request["sortField"]:null, isset($request["sortAsc"])?$request["sortAsc"]:null,
                    isset($request["filterField"])?$request["filterField"]:null, isset($request["filterValue"])?$request["filterValue"]:null,$callerInfo,
                    isset($request["location"])?$request["location"]:null, isset($request["assetName"])?$request["assetName"]:null);
            }
            else
                return  new Exception("Unknown resource",404);
    }

    // Delete checklist
    public function deleteAction($args, $callerInfo, $data, $request, $verb) {
        if (($callerInfo["IsSystemAdmin"] == 1 || isset($callerInfo["roles"]["SA"]) || isset($callerInfo["roles"]["DES"]) || isset($callerInfo["roles"]["MAN"]) || isset($callerInfo["roles"]["QA"]))) {
            $checklist = $this->proxy->genericGetById("checklists",$args[0],$callerInfo);
            if (isset($checklist)) {
                //
                if (isset($args[0]) && is_numeric($args[0]) && count($args) == 1 && empty($verb))
                    return $this->proxy->checklistDelete($args[0]);
                else
                    return  new Exception("Invalid URL parameters",405);
            } else
                return  new Exception("Unknown resource",404);
        } else
            return new Exception("Forbidden",403);
    }

    // Create checklist
    public function postAction($args, $callerInfo, $data, $request, $verb, $files) {
        if ($callerInfo["IsSystemAdmin"] == 1 || isset($callerInfo["roles"]["SA"]) || isset($callerInfo["roles"]["DES"]) || isset($callerInfo["roles"]["MAN"]) || isset($callerInfo["roles"]["QA"])) {
            if (empty($files) && $verb == "copy") {
                $checklist = $this->proxy->getChecklists($request["checklist"], null. null, null, null, null, null, $callerInfo);
                if (isset($checklist) && is_array($checklist)) {
                    $copiedVersion = $this->proxy->copyChecklistAndDetails($checklist, $callerInfo);
                    if(isset($copiedVersion))
                        return $copiedVersion;
                    else
                        return new Exception("Forbidden",403);
                } else
                    return  new Exception("Unknown resource",404);
            } else if (!empty($files) && $verb!="UPDATESFROMFILES") {
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
                        //CREATE CHECKLIST
                        $insertData = new stdClass();
                        $insertData->category = intval($request["category"]);
                        $insertData->created = date("Y-m-d H:i:s");
                        $insertData->family = $family["Id"];
                        $insertData->owner = $callerInfo["Id"];
                        $insertData->familyversion = $family["CurrentVersion"];
                        $newChecklist = $this->proxy->genericInsert("checklists", $insertData);
                        if (isset($newChecklist)) {
                            //CREATE CHECKLIST DETAILS
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
                                    if (!isset($currentObject->checklist))
                                        $currentObject->checklist = $newChecklist["Id"];
                                    $arrDevices[] = $currentObject;
                                    $result[] = $this->proxy->genericInsert("chkdetails", $currentObject);
                                }
                            }
                        }
                    }
                }
                $mainResult=new stdClass();
                $mainResult->success = true;
                $mainResult->data = $result;
                return $mainResult;
            } else if (count($args) == 0 && empty($verb)) {
                $dataObject = json_decode($data);
                $this->fixToCase($dataObject, 'family');
                if (isset($dataObject->SecurityGroup) && empty($this->proxy->genericGetById("families", $dataObject->SecurityGroup, $callerInfo)))
                    return new Exception("Invalid Default Family",403);
                if (!isset($dataObject->owner))
                    $dataObject->owner = $callerInfo["Id"];
                return $this->proxy->genericInsert("checklists", $dataObject);
            }
            else
                return  new Exception("Invalid URL parameters",405);  
        }
        else
            return new Exception("Forbidden",403);
    }

    // Update checklist
    public function putAction($args, $callerInfo, $data, $request, $verb) {
        $dataObject = json_decode($data); 
        if (isset($args[0]) && is_numeric($args[0]) && count($args) == 1 && empty($verb)) {
            $this->fixToCase($dataObject, 'Id');
            $dataObject->Id = $args[0];
            if (isset($dataObject->family) && empty($this->proxy->genericGetById("families", $dataObject->family, $callerInfo)))
                return new Exception("Invalid Default Family",403);
            if (($callerInfo["IsSystemAdmin"] == 1 || isset($callerInfo["roles"]["SA"]) || isset($callerInfo["roles"]["DES"]) || isset($callerInfo["roles"]["MAN"]) || isset($callerInfo["roles"]["QA"])) && !empty($this->proxy->genericGetById("checklists",$args[0],$callerInfo)))  {
                return $this->proxy->genericUpdate("checklists", $dataObject);
            }
            else
                return new Exception("Forbidden",403);
        }
        else
            return  new Exception("Invalid URL parameters",405);
    }

}
