<?php

class orderdetailController extends BaseController
{

    // Retrieves the order detail
    public function getAction($args, $callerInfo, $rawdata, $request, $verb) {
        if (isset($args[0]) && is_numeric($args[0]) && $verb != "asset") {
            if (isset($args[1])) {
                if (strtolower($args[1]) == "files") {
                    if (!empty($this->proxy->genericGetById("orderdetail",$args[0],$callerInfo)))
                        return $this->proxy->getOrderDetailFiles($args[0], isset($request["start"])?$request["start"]:null, isset($request["limit"])?$request["limit"]:BaseController::DEFAULT_LIMIT,
                        isset($request["sortField"])?$request["sortField"]:null, isset($request["sortAsc"])?$request["sortAsc"]:null,
                        isset($request["filterField"])?$request["filterField"]:null, isset($request["filterValue"])?$request["filterValue"]:null,$callerInfo);
                    else
                        return new Exception("Forbidden",403);
                } else if (strtolower($args[1]) == "images") {
                    if (!empty($this->proxy->genericGetById("orderdetail",$args[0],$callerInfo))) {
                        return $this->proxy->getImages("files","OrderDetail",$args[0], $callerInfo);
                    }
                    else
                        return new Exception("Forbidden",403);
                }
            } else
                return  new Exception("Unknown resource",404);
        }
        else
            return  new Exception("Unknown resource",404);
    }

    // Delete order detail
    public function deleteAction($args, $callerInfo, $data, $request, $verb) {
        if (($callerInfo["IsSystemAdmin"] == 1 || isset($callerInfo["roles"]["RO"])) && !empty($this->proxy->genericGetById("orderdetail",$args[0],$callerInfo))) {
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
                    $children = $this->proxy->genericGetChildren("files","OrderDetail",$args[0],$callerInfo);
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

    // Create order detail
    public function postAction($args, $callerInfo, $data, $request, $verb, $files) {
        if ($callerInfo["IsSystemAdmin"] == 1 || isset($callerInfo["roles"]["CURA"]) || isset($callerInfo["roles"]["RP"])) {
            if (isset($args[0]) && is_numeric($args[0]) && is_numeric($args[2])) {
                if (isset($args[1])) {
                    if ( !empty($this->proxy->genericGetById("orderdetail",$args[0],$callerInfo))) {
                        if (strtolower($args[1]) == "files" && !empty($files)) { 
                            $result = array();
                            foreach($files as $file) {
                                    $insertData = new stdClass();
                                    $insertData->Name = $file["name"];
                                    $insertData->Size = $file["size"];
                                    $insertData->Type = $file["type"];
                                    $insertData->OrderDetail = $args[0];
                                    $insertData->OriginOrder = $args[2];
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
                                    if($extension == ".jpg" || $extension == ".jpeg" || $extension == ".png")
                                        $extension = ".bin";
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
            } else
                return  new Exception("Invalid URL parameters",405); 
        }
        else
            return new Exception("Forbidden",403);
    }

}