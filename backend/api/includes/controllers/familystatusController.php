<?php

class familystatusController extends BaseController
{

    // Retrieves the family status
    public function getAction($args, $callerInfo, $rawdata, $request, $verb) {
        if (isset($args[0]) && is_numeric($args[0]) && count($args) == 1 && empty($verb)) {
            $toReturn =  $this->proxy->genericGetById("familystatus",$args[0],$callerInfo);
            if (empty($toReturn))
                return  new Exception("Unknown resource",404);
            else
                return $toReturn;
        } else
            if (count($args) == 0) {
                return $this->proxy->genericGetElements("familystatus", isset($request["start"])?$request["start"]:null, isset($request["limit"])?$request["limit"]:BaseController::DEFAULT_LIMIT,
                    isset($request["sortField"])?$request["sortField"]:null, isset($request["sortAsc"])?$request["sortAsc"]:null,
                    isset($request["filterField"])?$request["filterField"]:null, isset($request["filterValue"])?$request["filterValue"]:null,$callerInfo);
            }
            else
                return  new Exception("Unknown resource",404);
    }

}