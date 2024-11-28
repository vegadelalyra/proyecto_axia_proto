<?php

class ordersController extends BaseController
{

    // Retrieves the orders
    public function getAction($args, $callerInfo, $rawdata, $request, $verb) {
        if (count($args) == 0 && isset($verb) && substr($verb, 0, 1) == "K") {
            $key = substr(escape_string($verb), 1);
            $assetwithCode = $this->proxy->genericGetByKeyField("assets","Code", $key, $callerInfo);
            if (!empty($assetwithCode)) {
                $order = $this->proxy->genericGetAllByKeyField("orders","Asset", $assetwithCode["Id"], $callerInfo);
                if (!empty($order))
                    return $this->proxy->completeOrder($order[0]);
            }
        } else if (isset($args[0]) && is_numeric($args[0]) && $verb != "asset") {
            if (isset($args[1])) {
                if (strtolower($args[1]) == "files") {
                    if (!empty($this->proxy->genericGetById("orders",$args[0],$callerInfo)))
                        return $this->proxy->getFiles("files","OriginOrder",$args[0],$callerInfo);
                    else
                        return new Exception("Forbidden",403);
                } else if (strtolower($args[1]) == "operationcodes") {
                    if (!empty($this->proxy->genericGetById("orders",$args[0],$callerInfo)))
                        return $this->proxy->genericGetSubelements($args[0], "Orders_Id", "Operationcodes_Id", "Id", "orders_has_operationcodes", "OperationCodes", null, 100,null, null,null, null);
                    else
                        return new Exception("Forbidden",403);
                } else if (strtolower($args[1]) == "images") {
                    if (!empty($this->proxy->genericGetById("orders",$args[0],$callerInfo))) {
                        return $this->proxy->getImages("files","OriginOrder",$args[0], $callerInfo);
                    }
                    else
                        return new Exception("Forbidden",403);
                }
            } else {
                $toReturn =  $this->proxy->completeOrder($this->proxy->genericGetById("orders",$args[0],$callerInfo));
                if (empty($toReturn))
                    return  new Exception("Unknown resource",404);
                else {
                    return $toReturn;
                }   
            }
        } else if (isset($verb) && $verb == "asset" && isset($args[0]) && is_numeric($args[0])) {
            $orders = $this->proxy->genericGetAllByKeyField("orders","Asset", $args[0], $callerInfo);
            if (!empty($orders)) {
                $activeOrders = [];
                foreach ($orders as $order) {
                    if ($order["Status"] < 5) {
                        array_push($activeOrders, $this->proxy->completeOrder($order));
                    }
                }
            }
            return $activeOrders;
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
                return $this->proxy->completeOrders($this->proxy->genericGetElements("orders", isset($request["start"])?$request["start"]:null, isset($request["limit"])?$request["limit"]:BaseController::DEFAULT_LIMIT,
                    isset($request["sortField"])?$request["sortField"]:null, isset($request["sortAsc"])?$request["sortAsc"]:null,
                    isset($request["filterField"])?$request["filterField"]:null, isset($request["filterValue"])?$request["filterValue"]:null,$callerInfo));
            }
            else
                return  new Exception("Unknown resource",404);
    }

    // Delete orders
    public function deleteAction($args, $callerInfo, $data, $request, $verb) {
        if (($callerInfo["IsSystemAdmin"] == 1 || isset($callerInfo["roles"]["CURA"])) && !empty($this->proxy->genericGetById("orders",$args[0],$callerInfo))) {
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
                    $children = $this->proxy->genericGetChildren("files","OriginOrder",$args[0],$callerInfo);
                    foreach($children as $child)
                        $this->proxy->fileDelete($child["Id"],$callerInfo);
                    return $this->proxy->orderDelete($args[0]);
                }
            }
            else
                return  new Exception("Invalid URL parameters",405);
        } else
            return new Exception("Forbidden",403);
    }

    // Create orders
    public function postAction($args, $callerInfo, $data, $request, $verb, $files) {
        if ($callerInfo["IsSystemAdmin"] == 1 || isset($callerInfo["roles"]["CURA"]) || isset($callerInfo["roles"]["RP"])) {
            if (count($args) == 0 && empty($verb)) {
                $details = array();
                $dataObject = json_decode($data);
                $this->fixToCase($dataObject, 'Owner');
                $dataObject->Owner = $callerInfo["Id"];
                $this->fixToCase($dataObject, 'Asset');
                if (isset($dataObject->Asset) && empty($this->proxy->genericGetById("assets", $dataObject->Asset, $callerInfo)))
                    return new Exception("Invalid Asset",403);
                $this->fixToCase($dataObject, 'Created');
                unset($dataObject -> Created);
                $this->fixToCase($dataObject, 'details');
                if (isset($dataObject -> details)) {
                    $details = $dataObject -> details;
                    unset($dataObject -> details);
                }
                $dataObject->Status = 0;
                $result = $this->proxy->genericInsert("orders", $dataObject);
                if (empty($result))
                    return null;
                $result["details"] = array();
                $i = 0;
                foreach ($details as $currentdetail) {
                    $insertData = new stdClass();
                    $this->fixToCase($currentdetail, 'Order');
                    $insertData->Order = $result["Id"];
                    $this->fixToCase($currentdetail, 'AlertDetail');
                    if (isset($currentdetail->AlertDetail))
                        $insertData->AlertDetail = $currentdetail->AlertDetail;
                    $this->fixToCase($currentdetail, 'Status');
                    $insertData->Status = (isset($currentdetail->Status))?$currentdetail->Status:0;
                    $this->fixToCase($currentdetail, 'Symptom');
                    $insertData->Symptom = $currentdetail->Symptom;
                    $this->fixToCase($currentdetail, 'Element');
                    if (isset($currentdetail->Element))
                        $insertData->Element = $currentdetail->Element;
                    $this->fixToCase($currentdetail, 'Cause');
                    if (isset($currentdetail->Cause))
                        $insertData->Cause = $currentdetail->Cause;
                    $this->fixToCase($currentdetail, 'Location');
                    if (isset($currentdetail->Location))
                        $insertData->Location = $currentdetail->Location;
                    $this->fixToCase($currentdetail, 'Solver');
                    if (isset($currentdetail->Solver))
                        $insertData->Solver = $currentdetail->Solver;   
                    $this->fixToCase($currentdetail, 'Operation');    
                    if (isset($currentdetail->Operation))
                        $insertData->Operation = $currentdetail->Operation;                 
                    $result["details"][$i++] = $this->proxy->genericInsert("orderdetail", $insertData);
                    if (isset($currentdetail->AlertDetail) && isset($currentdetail->Status)) {
                        $updateData = new stdClass();
                        $updateData->Id = $currentdetail->AlertDetail;
                        $updateData->Status = $currentdetail->Status;
                        $this->proxy->genericUpdate("alertdetail", $updateData, $callerInfo, true);
                    }
                }
                return $result;
            }
            else if (isset($args[0]) && is_numeric($args[0])) {
                if (isset($args[1])) {
                    if ( !empty($this->proxy->genericGetById("orders",$args[0],$callerInfo))) {
                        if (strtolower($args[1]) == "files" && !empty($files)) { 
                            $result = array();
                            foreach($files as $file) {
                                    $insertData = new stdClass();
                                    $insertData->Name = $file["name"];
                                    $insertData->Size = $file["size"];
                                    $insertData->Type = $file["type"];
                                    $insertData->OriginOrder = $args[0];
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
                        } else if (strtolower($args[1]) == "filesorder" && !empty($files)) { 
                            $result = array();
                            foreach($files as $file) {
                                    $insertData = new stdClass();
                                    $insertData->Name = $file["name"];
                                    $insertData->Size = $file["size"];
                                    $insertData->Type = $file["type"];
                                    $insertData->OriginOrder = $args[0];
                                    $insertData->Owner = $callerInfo["Id"];
                                    if (isset($request["category"]))
                                        $insertData->Category = $request["category"];
                                    else
                                        $insertData->Category = 6;
                                    if (isset($request["created"]))
                                        $insertData->Created = $request["created"];
                                    $current = $result[] =  $this->proxy->genericInsert("files", $insertData);

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
            } else
                return  new Exception("Invalid URL parameters",405); 
        }
        else
            return new Exception("Forbidden",403);
    }

    // Update orders
    public function putAction($args, $callerInfo, $data, $request, $verb) {
         $dataObject = json_decode($data); 
        if (isset($args[0]) && is_numeric($args[0]) && count($args) == 1 && empty($verb)) {
            $this->fixToCase($dataObject, 'Id');
            $dataObject->Id = $args[0];
            $this->fixToCase($dataObject, 'Asset');
            if (isset($dataObject->Family) && empty($this->proxy->genericGetById("assets", $dataObject->Asset, $callerInfo)))
                return new Exception("Invalid Default SecurityGroups",403);
            $original = $this->proxy->completeOrder($this->proxy->genericGetById("orders",$args[0],$callerInfo));
            if (($callerInfo["IsSystemAdmin"] == 1 || isset($callerInfo["roles"]["RP"]) || isset($callerInfo["roles"]["QA"])) && !empty($original))  {
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
                            $insertData->Order = $args[0];
                            $this->fixToCase($currentdetail, 'AlertDetail');
                            if (isset($currentdetail->AlertDetail))
                                $insertData->AlertDetail = $currentdetail->AlertDetail;
                            $this->fixToCase($currentdetail, 'Status');
                            $insertData->Status = (isset($currentdetail->Status))?$currentdetail->Status:0;
                            $this->fixToCase($currentdetail, 'Symptom');
                            $insertData->Symptom = $currentdetail->Symptom;
                            $this->fixToCase($currentdetail, 'Element');
                            if (isset($currentdetail->Element))
                                $insertData->Element = $currentdetail->Element;
                            $this->fixToCase($currentdetail, 'Cause');
                            if (isset($currentdetail->Cause))
                                $insertData->Cause = $currentdetail->Cause;
                            $this->fixToCase($currentdetail, 'Location');
                            if (isset($currentdetail->Location))
                                $insertData->Location = $currentdetail->Location;
                            $this->fixToCase($currentdetail, 'Solver');
                            if (isset($currentdetail->Solver))
                                $insertData->Solver = $currentdetail->Solver;   
                            $this->fixToCase($currentdetail, 'Operation');    
                            if (isset($currentdetail->Operation))
                                $insertData->Operation = $currentdetail->Operation;
                            $result["details"][$i++] = $this->proxy->genericInsert("orderdetail", $insertData);
                            if (isset($currentdetail->AlertDetail) && isset($currentdetail->Status)) {
                                $updateData = new stdClass();
                                $updateData->Id = $currentdetail->AlertDetail;
                                $updateData->Status = $currentdetail->Status;
                                $this->proxy->genericUpdate("alertdetail", $updateData, $callerInfo, true);
                            }
                        } else if (isset($currentdetail->Id) && isset($ids[$currentdetail->Id])) {
                            $this->proxy->genericUpdate("orderdetail", $currentdetail, $callerInfo, true);
                            if (isset($currentdetail->AlertDetail) && isset($currentdetail->Status)) {
                                $updateData = new stdClass();
                                $updateData->Id = $currentdetail->AlertDetail;
                                //Alert detail validate is 3
                                if ($currentdetail->Status == 5)
                                    $updateData->Status = 3;
                                else
                                    $updateData->Status = $currentdetail->Status;
                                $this->proxy->genericUpdate("alertdetail", $updateData, $callerInfo, true);
                            }
                            unset($ids[$currentdetail->Id]);
                        }
                    }
                    foreach ($ids as $id=>$value){
                        $this->proxy->genericDelete("orderdetail",$id);
                        if (isset($value->AlertDetail)) {
                            $updateData = new stdClass();
                            $updateData->Id = $currentdetail->AlertDetail;
                            $updateData->Status = 0;
                            $this->proxy->genericUpdate("alertdetail", $updateData, $callerInfo, true);
                        }
                    }                
                }
                return $this->proxy->completeOrder($this->proxy->genericUpdate("orders", $dataObject));
            } else if (($callerInfo["IsSystemAdmin"] == 1 || isset($callerInfo["roles"]["RO"])) && !empty($original))  {
                return $this->proxy->genericUpdate("orders", $dataObject);
            } else
                return new Exception("Forbidden",403);
        } else if (isset($verb) && $verb == "asset" && isset($args[0]) && is_numeric($args[0])) {
            $orders = $this->proxy->genericGetAllByKeyField("orders","Asset", $args[0], $callerInfo);
            if (!empty($orders)) {
                $activeOrders = [];
                foreach ($orders as $order) {
                    if ($order["Status"] < 5) {
                        $order =  $this->proxy->completeOrder($order);
                        $updateOrder = new stdClass();
                        $updateOrder->Status = 8;
                        $updateOrder->Id = $order["Id"];
                        date_default_timezone_set('UTC');
                        $actualDate = gmdate('Y-m-d H:i:s');
                        $updateOrder->Closed = $actualDate;

                        foreach ($order["details"] as $currentdetail) {
                            $updateData = new stdClass();
                            $updateData->Id = $currentdetail["Id"];
                            $updateData->Status = 8;
                            $this->proxy->genericUpdate("orderdetail", $updateData, $callerInfo, true);
                        }
                        $this->proxy->genericUpdate("orders", $updateOrder, $callerInfo, true);
                    }
                }
            }
            return [];
        } else
            return  new Exception("Invalid URL parameters",405);
    }
}