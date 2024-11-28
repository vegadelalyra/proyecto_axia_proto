<?php

class chkexecdetailsController extends BaseController
{

    // Retrieves the checklist details executions
    public function getAction($args, $callerInfo, $rawdata, $request, $verb) {
        if (isset($args[0]) && is_numeric($args[0]) && count($args) == 1 && empty($verb)) {
            $toReturn =  $this->proxy->genericGetById("chkexecdetails",$args[0],$callerInfo);
            if (empty($toReturn))
                return  new Exception("Unknown resource",404);
            else
                return $toReturn;
        } else if (isset($args[0]) && is_numeric($args[0]) && isset($args[1]) && strtolower($args[1]) == "files") {
            if (!empty($this->proxy->genericGetById("chkexecdetails",$args[0],$callerInfo))) {
                return $this->proxy->getChkExecDetailFile($args[0], $callerInfo);
            }
            else
                return new Exception("Forbidden",403);
        }
        else
            if (count($args) == 0) {
                 return $this->proxy->genericGetElements("chkexecdetails", isset($request["start"])?$request["start"]:null, isset($request["limit"])?$request["limit"]:BaseController::DEFAULT_LIMIT,
                    isset($request["sortField"])?$request["sortField"]:null, isset($request["sortAsc"])?$request["sortAsc"]:null,
                    isset($request["filterField"])?$request["filterField"]:null, isset($request["filterValue"])?$request["filterValue"]:null,$callerInfo,
                    isset($request["location"])?$request["location"]:null, isset($request["assetName"])?$request["assetName"]:null);
            }
            else
                return  new Exception("Unknown resource",404);
    }

    // Delete checklist details executions
    public function deleteAction($args, $callerInfo, $data, $request, $verb) {
        if (($callerInfo["IsSystemAdmin"] == 1 || isset($callerInfo["roles"]["SA"]) || isset($callerInfo["roles"]["MANU"]) || isset($callerInfo["roles"]["QA"])) && !empty($this->proxy->genericGetById("chkexecdetails",$args[0],$callerInfo))) {
            if (isset($args[0]) && is_numeric($args[0]) && count($args) == 1 && empty($verb))
                return $this->proxy->genericDelete("chkexecdetails",$args[0]);
            else
                return  new Exception("Invalid URL parameters",405);
        } else
            return new Exception("Forbidden",403);
    }

    // Create checklist details executions
    public function postAction($args, $callerInfo, $data, $request, $verb, $files) {
        if ($callerInfo["IsSystemAdmin"] == 1 || isset($callerInfo["roles"]["SA"]) || isset($callerInfo["roles"]["MANU"]) || isset($callerInfo["roles"]["QA"])) {
            if (count($args) == 0 && empty($verb) && $data != "" && empty($files)) {
                $dataObject = json_decode($data);
                $this->fixToCase($dataObject, 'chkexec');
                if (isset($dataObject->SecurityGroup) && empty($this->proxy->genericGetById("chkexecs", $dataObject->chkexec, $callerInfo)))
                    return new Exception("Invalid chkexec",403);
                return $this->proxy->genericInsert("chkexecdetails", $dataObject);
            } else if (count($args) == 0 && empty($verb) && !empty($files)) {
                $detailData = ['chkexec', 'chkdetail', 'result', 'value'];
                $newDetail = [];
                foreach ($detailData as $val) {
                    if (isset($request[$val])) {
                        $newDetail[$val] = $request[$val];
                    }
                }
                $detail = $this->proxy->genericInsert("chkexecdetails", $newDetail);

                if (isset($detail)) {
                    $result = array();
                    foreach($files as $file) {
                        $insertData = new stdClass();
                        $insertData->Name = $file["name"];
                        $insertData->Size = $file["size"];
                        $insertData->Type = $file["type"];
                        $insertData->ChkExecDetail = $detail["Id"];
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
                    return $detail;
                } else
                    return new Exception("Forbidden",403);
            }
            else
                return  new Exception("Invalid URL parameters",405);  
        }
        else
            return new Exception("Forbidden",403);
    }

    // Update checklist details executions
    public function putAction($args, $callerInfo, $data, $request, $verb) {
        $dataObject = json_decode($data); 
        if (isset($args[0]) && is_numeric($args[0]) && count($args) == 1 && empty($verb)) {
            $this->fixToCase($dataObject, 'Id');
            $dataObject->Id = $args[0];
            if (isset($dataObject->chkexec) && empty($this->proxy->genericGetById("chkexecs", $dataObject->chkexec, $callerInfo)))
                return new Exception("Invalid chkexec",403);
            if (($callerInfo["IsSystemAdmin"] == 1 || isset($callerInfo["roles"]["SA"]) || isset($callerInfo["roles"]["MANU"])) && !empty($this->proxy->genericGetById("chkexecdetails",$args[0],$callerInfo)))  {
                return $this->proxy->genericUpdate("chkexecdetails", $dataObject);
            }
            else
                return new Exception("Forbidden",403);
        }
        else
            return  new Exception("Invalid URL parameters",405);
    }
    
}