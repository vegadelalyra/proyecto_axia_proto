<?php
require_once __DIR__."/../business/cryptography.php";
include_once __DIR__."/../utils/apcqueue.php";
class usersController extends BaseController
{
    // Retrieves the users
    public function getAction($args, $callerInfo, $rawdata, $request, $verb) {
        if (isset($args[0]) && is_numeric($args[0])) {
            if ($args[0] == 0)
                $args[0] = $callerInfo["Id"];
                if (isset($args[1])) {
                    if (strtolower($args[1]) == "roles") {
                        if ($callerInfo["IsSystemAdmin"] == 1 || !empty($this->proxy->genericGetById("Users",$args[0],$callerInfo)))
                            return $this->proxy->genericGetSubelements($args[0], "Users_Id", "Roles_Id", "Id", "Users_has_roles", "Roles", isset($request["start"])?$request["start"]:null, isset($request["limit"])?$request["limit"]:BaseController::DEFAULT_LIMIT,
                                isset($request["sortField"])?$request["sortField"]:null, isset($request["sortAsc"])?$request["sortAsc"]:null,
                                isset($request["filterField"])?$request["filterField"]:null, isset($request["filterValue"])?$request["filterValue"]:null);
                        else
                            return new Exception("Forbidden",403);
                    }
                    else
                        if (strtolower($args[1]) == "securitygroups") {
                            if ($callerInfo["IsSystemAdmin"] == 1 || !empty($this->proxy->genericGetById("Users",$args[0],$callerInfo)))
                                return $this->proxy->genericGetSubelements($args[0], "Users_Id", "SecurityGroups_Id", "Id", "users_has_securitygroups", "securitygroups", isset($request["start"])?$request["start"]:null, isset($request["limit"])?$request["limit"]:BaseController::DEFAULT_LIMIT,
                                    isset($request["sortField"])?$request["sortField"]:null, isset($request["sortAsc"])?$request["sortAsc"]:null,
                                    isset($request["filterField"])?$request["filterField"]:null, isset($request["filterValue"])?$request["filterValue"]:null);
                            else
                                return new Exception("Forbidden",403);
                        }
                } else {
                    $toReturn = $this->proxy->getUsers($args[0], null, 1, null, null, null, null, $callerInfo);
                    if (empty($toReturn) || empty($toReturn->data))
                        return new Exception("Forbidden",403);
                    else
                        return $toReturn->data[0];
                }
        }
        else
            if (count($args) == 0 && empty($verb)) {
                    return $this->proxy->getUsers(null, isset($request["start"])?$request["start"]:null, isset($request["limit"])?$request["limit"]:BaseController::DEFAULT_LIMIT,
                        isset($request["sortField"])?$request["sortField"]:null, isset($request["sortAsc"])?$request["sortAsc"]:null,
                        isset($request["filterField"])?$request["filterField"]:null, isset($request["filterValue"])?$request["filterValue"]:null, $callerInfo);
            } else if (count($args) == 0 && $verb == "operators") {
                $users = $this->proxy->getUsers(null, isset($request["start"])?$request["start"]:null, isset($request["limit"])?$request["limit"]:BaseController::DEFAULT_LIMIT,
                        isset($request["sortField"])?$request["sortField"]:null, isset($request["sortAsc"])?$request["sortAsc"]:null,
                        isset($request["filterField"])?$request["filterField"]:null, isset($request["filterValue"])?$request["filterValue"]:null, $callerInfo);
                $usersHasRoles = $this->proxy->genericGetElements('users_has_roles', isset($request["start"])?$request["start"]:null, isset($request["limit"])?$request["limit"]:BaseController::DEFAULT_LIMIT,
                                isset($request["sortField"])?$request["sortField"]:null, isset($request["sortAsc"])?$request["sortAsc"]:null, isset($request["filterField"])?$request["filterField"]:null, isset($request["filterValue"])?$request["filterValue"]:null,$callerInfo);
                $operators = array();
                for ($i=0; $i < count($users->data); $i++) { 
                    for ($s=0; $s < count($usersHasRoles->data); $s++) { 
                        $user = $users->data[$i]["Id"];
                        $location = $users->data[$i]["Location"];
                        if ($user == $usersHasRoles->data[$s]["users_id"] && $usersHasRoles->data[$s]["roles_id"] == 5 && $callerInfo["Location"] == $location) {
                            array_push($operators, $users->data[$i]);
                        }
                    }
                }
                return $operators;
            } else
                return new Exception("Unknown resource",404);
    }

    // Delete users
    public function deleteAction($args, $callerInfo) {
            if ($callerInfo["IsSystemAdmin"] == 1 && isset($args[0]) && is_numeric($args[0]) && !empty($this->proxy->genericGetById("Users",$args[0],$callerInfo)))
                if (isset($args[1])) { 
                    if (strtolower($args[1]) == "roles") {
                        if (isset($args[2]) && is_numeric($args[2])) {
                            return $this->proxy->genericRelationDelete("Users_has_roles","Users_Id",$args[0],"Roles_Id",$args[2],$callerInfo);
                        }
                        else
                            return  new Exception("Unknown resource",404);
                    }
                    else
                        if (strtolower($args[1]) == "securitygroups") {
                            if (isset($args[2]) && is_numeric($args[2])) {
                                return $this->proxy->genericRelationDelete("users_has_securitygroups","Users_Id",$args[0],"SecurityGroups_Id",$args[2],$callerInfo);
                            } else
                                return  new Exception("Unknown resource",404);
                        }
                        else
                            return  new Exception("Unknown resource",404);
                }
                else
                    return $this->proxy->userDelete($args[0],$callerInfo);
            else
                return new Exception("Forbidden",403);
    }

    // Create users
    public function postAction($args, $callerInfo, $data, $request, $verb) {
        if (isset($verb) && $verb == "retrievepassword") {
            $dataObject = json_decode($data);
            if (isset($dataObject->user)) {
                $candidate = $this->proxy->genericGetByKeyField("users", "user", $dataObject->user);
                if (empty($candidate))
                    return  new Exception("Unknown resource",404);
                else if (empty($candidate["Email"]))
                    return new Exception("Forbidden",403);
                else {
                    $cadena = $dataObject->user.":".$candidate["Password"];
                    $message = file_get_contents(dirname(__FILE__)."/../templates/recoveryMail.tpl");
                    $message = str_replace("{{user}}", $candidate["User"], $message);
                    $message = str_replace("{{token}}", bin2hex(encrypt($cadena)), $message);
                    if (isset($dataObject->baseurl))
                        $message = str_replace("{{baseurl}}", $dataObject->baseurl, $message);
                    if (isset($dataObject->appname))
                        $message = str_replace("{{appname}}", $dataObject->appname, $message);
                    $email = new stdClass();
                    $email->to = $candidate["Email"];
                    if (isset($dataObject->subject))
                        $email->subject = $dataObject->subject;
                    else
                        $email->subject = "GMAO recuperación de contraseña";
                    $email->message = $message;
                    $q = new apc_queue('pendingMails', false);
                    $q->add($email);

                    return true;
                }
            } else
                new Exception("Invalid parameters",405);

        } else {
            if ($callerInfo["IsSystemAdmin"] == 1) {
                $dataObject = json_decode($data);
                    if (isset($args[0]) && is_numeric($args[0])) {
                        if (isset($args[1])) {
                            if (($callerInfo["IsSystemAdmin"] == 1) && !empty($this->proxy->genericGetById("Users",$args[0],$callerInfo))) {
                                if (strtolower($args[1]) == "roles") {
                                    $result = array();
                                    foreach(explode(",",$data) as $currentRol) {
                                        if (!empty($this->proxy->genericGetById("Roles",$currentRol,$callerInfo))) {
                                            $insertData = new stdClass();
                                            $insertData->Users_Id = $args[0];
                                            $insertData->Roles_Id = $currentRol;
                                            $result[] =  $this->proxy->genericInsert("users_has_roles", $insertData);
                                        }
                                    }
                                    return (count($result)==1)?$result[0]:$result;
                                }
                                else if (strtolower($args[1]) == "securitygroups") {
                                    $result = array();
                                    foreach(explode(",",$data) as $currentSecurityGroup) {
                                        if (!empty($this->proxy->genericGetById("securityGroups",$currentSecurityGroup,$callerInfo))) {
                                            $insertData = new stdClass();
                                            $insertData->Users_Id = $args[0];
                                            $insertData->SecurityGroups_Id = $currentSecurityGroup;
                                            $result[] = $this->proxy->genericInsert("users_has_securitygroups", $insertData);
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
                    } else {
                        $this->fixToCase($dataObject, 'Owner');
                        $dataObject->Owner = $callerInfo["Id"];
                        $this->fixToCase($dataObject, 'IsUserAdmin');
                        if (isset($dataObject->IsUserAdmin)) {
                            $dataObject->IsUserAdmin = $dataObject->IsUserAdmin & $callerInfo["IsUserAdmin"];
                        }
                        $this->fixToCase($dataObject, 'IsSystemAdmin');
                        if (isset($dataObject->IsSystemAdmin)) {
                            $dataObject->IsSystemAdmin = $dataObject->IsSystemAdmin & $callerInfo["IsSystemAdmin"];
                        }
                        $this->fixToCase($dataObject, 'DefaultRol');
                        if (isset($dataObject->DefaultRol)) {
                            if (empty($this->proxy->genericGetById("Roles", $dataObject->DefaultRol, $callerInfo)))
                                return new Exception("Invalid Default Rol",403);
                        }
                        $this->fixToCase($dataObject, 'DefaultSecurityGroup');
                        if (isset($dataObject->DefaultSecurityGroup)) {
                            if (empty($this->proxy->genericGetById("SecurityGroups", $dataObject->DefaultSecurityGroup, $callerInfo)))
                                return new Exception("Invalid Default SecurityGroups",403);
                        }
                        return $this->proxy->genericInsert("Users", $dataObject);
                    }
            }
            else
                return new Exception("Forbidden",403);
        }
        
    }

    // Update users
    public function putAction($args, $callerInfo, $data) {
        $dataObject = json_decode($data);
         if (isset($args[0]) && is_numeric($args[0]) && count($args) == 1) {
            $this->fixToCase($dataObject, 'Id');
            $dataObject->Id = $args[0];
            if (($callerInfo["IsSystemAdmin"] == 1 || $callerInfo["Id"] == $dataObject->Id ) && !empty($this->proxy->genericGetById("users",$args[0],$callerInfo)))  {
                $this->fixToCase($dataObject, 'IsSystemAdmin');
                if (isset($dataObject->IsSystemAdmin)) {
                    $dataObject->IsSystemAdmin = $dataObject->IsSystemAdmin & $callerInfo["IsSystemAdmin"];
                }
                return $this->proxy->genericUpdate("users", $dataObject, $callerInfo);
            }
            else
                return new Exception("Forbidden",403);
        }
        else
            return  new Exception("Invalid URL parameters",405);
    }
}
?>