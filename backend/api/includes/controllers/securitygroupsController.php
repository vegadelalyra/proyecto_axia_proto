<?php

class securitygroupsController extends BaseController
{

    // Retrieves the security groups
    public function getAction($args, $callerInfo, $rawdata, $request, $verb) {
        if (isset($args[0]) && is_numeric($args[0]) && count($args) == 1 && empty($verb)) {
            $readed = $this->proxy->genericGetById("SecurityGroups",$args[0], $callerInfo);
            if (empty($readed))
                return new Exception("Forbidden",403);
            else
                return $readed;
        }
        else
            if (count($args) == 0 && empty($verb)) {
                return $this->proxy->genericGetElements("SecurityGroups", isset($request["start"])?$request["start"]:null, isset($request["limit"])?$request["limit"]:BaseController::DEFAULT_LIMIT,
                    isset($request["sortField"])?$request["sortField"]:null, isset($request["sortAsc"])?$request["sortAsc"]:null,
                    isset($request["filterField"])?$request["filterField"]:null, isset($request["filterValue"])?$request["filterValue"]:null, $callerInfo);
            } else
                return  new Exception("Unknown resource",404);

    }

    // Delete security groups
    public function deleteAction($args, $callerInfo, $data, $request, $verb) {
        if (isset($args[0]) && is_numeric($args[0]) && count($args) == 1 && empty($verb)) {
            if ($callerInfo["IsSystemAdmin"] == 1)
                return $this->proxy->genericDelete("SecurityGroups",$args[0]);
            else
                return new Exception("Forbidden",403);
        } else
            return  new Exception("Invalid URL parameters",405);
    }

    // Create security groups
    public function postAction($args, $callerInfo, $data, $request, $verb) {
        if (count($args) == 0 && empty($verb)) {
            if ($callerInfo["IsSystemAdmin"] == 1) {
                $dataObject = json_decode($data);
                $this->fixToCase($dataObject, 'Owner');
                $dataObject->Owner = $callerInfo["Id"];
                return $this->proxy->genericInsert("SecurityGroups", $dataObject);
            }
            else
                return new Exception("Forbidden",403);
        } else
            return  new Exception("Invalid URL parameters",405);
    }

    // Update security groups
    public function putAction($args, $callerInfo, $data, $request, $verb) {
        $dataObject = json_decode($data);
        if (isset($args[0]) && is_numeric($args[0]) && count($args) == 1 && empty($verb)) {
            $this->fixToCase($dataObject, 'Id');
            $dataObject->Id = $args[0];
            if ($callerInfo["IsSystemAdmin"] == 1) {
                $this->fixToCase($dataObject, 'Owner');
                unset($dataObject->Owner);
                return $this->proxy->genericUpdate("SecurityGroups", $dataObject);
            }
            else
                return new Exception("Forbidden",403);
        }
        else
                return new Exception("Invalid URL parameters",405);
    }
}
