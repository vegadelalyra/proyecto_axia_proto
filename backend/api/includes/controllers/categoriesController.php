<?php

class categoriesController extends BaseController
{

    // Retrieves the categories
    public function getAction($args, $callerInfo, $rawdata, $request, $verb) {
        if (!isset($args[0]) && isset($request["family"]) && isset($request["forFamilies"])) {
            $toReturn =  $this->proxy->getCategories($request["family"],$request["forFamilies"],$callerInfo);
            if (empty($toReturn))
                return  new Exception("Unknown resource",404);
            else
                return $toReturn;
        } else if (!isset($args[0]) && isset($verb) && $verb == "mandatoryfiles" && isset($request["family"]) && isset($request["version"])) {
            $toReturn =  $this->proxy->recoverTotalFilesByCategoryAndFamily($request["family"],$request["version"],$callerInfo);
            if (empty($toReturn))
                return  new Exception("Unknown resource",404);
            else
                return $toReturn;
        } else if (isset($args[0]) && is_numeric($args[0]) && count($args) == 1 && empty($verb)) {
            $toReturn =  $this->proxy->genericGetById("categories",$args[0],$callerInfo);
            if (empty($toReturn))
                return  new Exception("Unknown resource",404);
            else
                return $toReturn;
        } else
            if (count($args) == 0) {
                return $this->proxy->genericGetElements("categories", isset($request["start"])?$request["start"]:null, isset($request["limit"])?$request["limit"]:BaseController::DEFAULT_LIMIT,
                    isset($request["sortField"])?$request["sortField"]:null, isset($request["sortAsc"])?$request["sortAsc"]:null,
                    isset($request["filterField"])?$request["filterField"]:null, isset($request["filterValue"])?$request["filterValue"]:null,$callerInfo);
            }
            else
                return  new Exception("Unknown resource",404);
    }

    // Delete categories
    public function deleteAction($args, $callerInfo, $data, $request, $verb) {
        if (($callerInfo["IsSystemAdmin"] == 1 || isset($callerInfo["roles"]["SA"])) && !empty($this->proxy->genericGetById("categories",$args[0],$callerInfo))) {
            if (isset($args[0]) && is_numeric($args[0]) && count($args) == 1 && empty($verb))
                return $this->proxy->genericDelete("categories",$args[0]);
            else
                return  new Exception("Invalid URL parameters",405);
        } else
            return new Exception("Forbidden",403);
    }

    // Create categories
    public function postAction($args, $callerInfo, $data, $request, $verb) {
         if ($callerInfo["IsSystemAdmin"] == 1 || isset($callerInfo["roles"]["SA"])) {
            if (count($args) == 0 && empty($verb)) {
                $dataObject = json_decode($data);
                return $this->proxy->genericInsert("categories", $dataObject);
            }
            else
                return  new Exception("Invalid URL parameters",405);  
        }
        else
            return new Exception("Forbidden",403);
    }

    // Update categories
    public function putAction($args, $callerInfo, $data, $request, $verb) {
        $dataObject = json_decode($data); 
        if (isset($args[0]) && is_numeric($args[0]) && count($args) == 1 && empty($verb)) {
            $this->fixToCase($dataObject, 'Id');
            $dataObject->Id = $args[0];
            if (($callerInfo["IsSystemAdmin"] == 1 || isset($callerInfo["roles"]["SA"]) || isset($callerInfo["roles"]["SUP"])) && !empty($this->proxy->genericGetById("categories",$args[0],$callerInfo)))  {
                return $this->proxy->genericUpdate("categories", $dataObject);
            }
            else
                return new Exception("Forbidden",403);
        }
        else
            return  new Exception("Invalid URL parameters",405);
    }

}