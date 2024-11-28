<?php

class alertsController extends BaseController
{

    public function getAction($args, $callerInfo, $rawdata, $request, $verb) {
        if (isset($args[0]) && is_numeric($args[0])) {
            if (isset($args[1])) {
                if (strtolower($args[1]) == "files") {
                    if (!empty($this->proxy->genericGetById("alerts",$args[0],$callerInfo)))
                        return $this->proxy->genericGetChildren("files","OriginAlert",$args[0],$callerInfo);
                    else
                        return new Exception("Forbidden",403);
                }
            } else {
                $alert = $this->proxy->getAlertById($args[0],$callerInfo);
                $toReturn =  $this->proxy->completeAlert($alert);
                if (empty($toReturn))
                    return  new Exception("Unknown resource",404);
                else {
                    return $toReturn;
                }   
            }
        } else if (isset($verb) && $verb == "asset" && isset($args[0])) {
            $toReturn = $this->proxy->genericGetElements("alerts", isset($request["start"])?$request["start"]:null, isset($request["limit"])?$request["limit"]:BaseController::DEFAULT_LIMIT,
            isset($request["sortField"])?$request["sortField"]:null, isset($request["sortAsc"])?$request["sortAsc"]:null,
            ["AssetCode"], [$args[0]],$callerInfo);
            if (empty($toReturn))
                return  new Exception("Unknown resource",404);
            else
                return $toReturn;
        }
        else
            if (count($args) == 0) {
                if ($request["request"] == "alerts/pending") {
                    //SOLO LOS QUE TIENEN WARNINGS ACTIVAS
                    $allAlerts = $this->proxy->getAlerts("alerts",$callerInfo, false, isset($request["limit"])?$request["limit"]:BaseController::DEFAULT_LIMIT, isset($request["start"])?$request["start"]:null,
                    isset($request["sortField"])?$request["sortField"]:null, isset($request["sortAsc"])?$request["sortAsc"]:null,
                    isset($request["filterField"])?$request["filterField"]:null, isset($request["filterValue"])?$request["filterValue"]:null);
                    if (isset($allAlerts))
                        return $allAlerts;
                    else
                        return  new Exception("Unknown resource",404);
                } else {
                    $alerts = $this->proxy->getAlerts("alerts",$callerInfo, true, isset($request["limit"])?$request["limit"]:BaseController::DEFAULT_LIMIT, isset($request["start"])?$request["start"]:null,
                            isset($request["sortField"])?$request["sortField"]:null, isset($request["sortAsc"])?$request["sortAsc"]:null,
                            isset($request["filterField"])?$request["filterField"]:null, isset($request["filterValue"])?$request["filterValue"]:null);
                    if (isset($alerts))
                        return $alerts;
                    else
                        return  new Exception("Unknown resource",404);
                }
            }
            else
                return  new Exception("Unknown resource",404);

    }

    public function deleteAction($args, $callerInfo, $data, $request, $verb) {
        if (($callerInfo["IsSystemAdmin"] == 1 || isset($callerInfo["roles"]["SA"]) || isset($callerInfo["roles"]["REP"])) 
        && !empty($this->proxy->genericGetById("alerts",$args[0],$callerInfo))) {
            if (isset($args[0]) && is_numeric($args[0])) {
                if (isset($args[1])) { 
                    if (strtolower($args[1]) == "files") {
                        if (isset($args[2]) && is_numeric($args[2])) {
                            return $this->proxy->fileDelete($args[2],$callerInfo);
                        }
                        else
                            return  new Exception("Unknown resource",404);
                    }
                    else
                        return  new Exception("Unknown resource",404);
                }
                else {
                    $children = $this->proxy->genericGetChildren("files","OriginAlert",$args[0],$callerInfo);
                    foreach($children as $child)
                        $this->proxy->fileDelete($child["Id"],$callerInfo);
                    return $this->proxy->alertDelete($args[0]);
                }
            }
            else
                return  new Exception("Invalid URL parameters",405);
        } else
            return new Exception("Forbidden",403);
    }

    public function postAction($args, $callerInfo, $data, $request, $verb, $files) {
        if ($callerInfo["IsSystemAdmin"] == 1 || isset($callerInfo["roles"]["REP"]) || isset($callerInfo["roles"]["CURA"]) || isset($callerInfo["roles"]["QA"])) {
            if (count($args) == 0 && empty($verb)) {
                $details = array();
                $dataObject = json_decode($data);
                $this->fixToCase($dataObject, 'Owner');
                $dataObject->Owner = $callerInfo["Id"];
                $this->fixToCase($dataObject, 'Asset');
                if (isset($dataObject->Asset) && empty($this->proxy->genericGetById("assets", $dataObject->Asset, $callerInfo)))
                    return new Exception("Invalid Asset",403);
                $this->fixToCase($dataObject, 'details');
                if (isset($dataObject -> details)) {
                    $details = $dataObject -> details;
                    unset($dataObject -> details);
                }
                $result = $this->proxy->genericInsert("alerts", $dataObject);
                if (empty($result))
                    return null;
                $result["details"] = array();
                $i = 0;
                foreach ($details as $currentdetail) {
                    $insertData = new stdClass();
                    $insertData->Alert = $result["Id"];
                    $this->fixToCase($currentdetail, 'Status');
                    $insertData->Status = (isset($currentdetail->Status))?$currentdetail->Status:0;
                    $this->fixToCase($currentdetail, 'Symptom');
                    $insertData->Symptom = $currentdetail->Symptom;
                    $this->fixToCase($currentdetail, 'Element');
                    if (isset($currentdetail->Element))
                        $insertData->Element = $currentdetail->Element;
                    $result["details"][$i++] = $this->proxy->genericInsert("alertdetail", $insertData);
                }
                return $result;
            }
            else if (isset($args[0]) && is_numeric($args[0])) {
                if (isset($args[1])) {
                    if ( !empty($this->proxy->genericGetById("alerts",$args[0],$callerInfo))) {
                        if (strtolower($args[1]) == "files" && !empty($files)) {
                            $result = array();
                            foreach($files as $file) {
                                    $insertData = new stdClass();
                                    $insertData->Name = $file["name"];
                                    $insertData->Size = $file["size"];
                                    $insertData->Type = $file["type"];
                                    $insertData->OriginAlert = $args[0];
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
                                    move_uploaded_file($file["tmp_name"], __DIR__."/../../dataFiles/gmaoFile".$current["Id"].".bin");
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
            $this->fixToCase($dataObject, 'Asset');
            if (isset($dataObject->Family) && empty($this->proxy->genericGetById("assets", $dataObject->Asset, $callerInfo)))
                return new Exception("Invalid Default SecurityGroups",403);
            $original = $this->proxy->completeAlert($this->proxy->genericGetById("alerts",$args[0],$callerInfo));
            if (($callerInfo["IsSystemAdmin"] == 1 || isset($callerInfo["roles"]["REP"]) || isset($callerInfo["roles"]["CURA"])) && !empty($original))  {
                $this->fixToCase($dataObject, 'details');
                if (isset($dataObject -> details)) {
                    $details = $dataObject -> details;
                    unset($dataObject -> details);
                
                    $result["details"] = array();
                    $i = 0;
                    $ids = array();
                    foreach ($original["details"] as $detail) {
                        $ids[$detail["Id"]] = $detail;
                    }
                    foreach ($details as $currentdetail) {
                        $this->fixToCase($currentdetail, 'Id');
                        if (!isset($currentdetail->Id)) {
                            $insertData = new stdClass();
                            $insertData->Alert = $args[0];
                            $this->fixToCase($currentdetail, 'Status');
                            $insertData->Status = (isset($currentdetail->Status))?$currentdetail->Status:0;
                            $this->fixToCase($currentdetail, 'Symptom');
                            $insertData->Symptom = $currentdetail->Symptom;
                                $this->fixToCase($currentdetail, 'Element');
                            if (isset($currentdetail->Element))
                                $insertData->Element = $currentdetail->Element;
                            $result["details"][$i++] = $this->proxy->genericInsert("alertdetail", $insertData);
                        } else if (isset($currentdetail->Id) && isset($ids[$currentdetail->Id])) {
                            $this->proxy->genericUpdate("alertdetail", $currentdetail, $callerInfo, true);
                            unset($ids[$currentdetail->Id]);
                        }
                    }
                    foreach ($ids as $id=>$value){
                        $this->proxy->genericDelete("alertdetail",$id);
                    }                
                }
                return $this->proxy->completeAlert($this->proxy->genericUpdate("alerts", $dataObject));
            }
            else
                return new Exception("Forbidden",403);
        } else if (isset($verb) && $verb == "asset" && isset($args[0]) && is_numeric($args[0])) {
            $alerts = $this->proxy->genericGetAllByKeyField("alerts","Asset", $args[0], $callerInfo);
            if (!empty($alerts)) {
                $activeOrders = [];
                foreach ($alerts as $alert) {
                    if ($alert) {
                        $alert =  $this->proxy->completeAlert($alert);
                        $updateAlert = new stdClass();
                        $updateAlert->Id = $alert["Id"];
                        date_default_timezone_set('UTC');
                        $actualDate = gmdate('Y-m-d H:i:s');
                        $updateAlert->Closed = $actualDate;

                        foreach ($alert["details"] as $currentdetail) {
                            if ($currentdetail["Status"] < 3) {
                                $updateData = new stdClass();
                                $updateData->Id = $currentdetail["Id"];
                                $updateData->Status = 8;
                                $this->proxy->genericUpdate("alertdetail", $updateData, $callerInfo, true);
                            }
                        }
                        $this->proxy->genericUpdate("alerts", $updateAlert, $callerInfo, true);
                    }
                }
            }
            return [];
        }
        else
            return  new Exception("Invalid URL parameters",405);
    }
}
