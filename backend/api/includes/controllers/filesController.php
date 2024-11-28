<?php

class filesController extends BaseController
{
    // Retrieves the files
    public function getAction($args, $callerInfo, $rawdata, $request, $verb) {
        if (isset($args[0]) && is_numeric($args[0]) && (count($args) == 1 ||count($args) == 2) && empty($verb)) {
            $toReturn =  $this->proxy->genericGetById("files",$args[0],$callerInfo);
            if (empty($toReturn))
                 return  new Exception("Unknown resource",404);
            else {
                if (!empty($this->proxy->genericGetById("alerts",$toReturn["OriginAlert"],$callerInfo)) || !empty($this->proxy->genericGetById("orders",$toReturn["OriginOrder"],$callerInfo))) {
                    header('Content-Disposition: attachment; filename='.$toReturn["Name"]);
                    header('Pragma: no-cache');
                    $name =  __DIR__."/../../dataFiles/gmaoFile".$toReturn["Id"].".bin";
                    
                    if (isset($args[1])) {
                        $fp = fopen($name, 'rb');
                        $data = fread($fp, filesize($name));
                
                        $encode = base64_encode($data);
                        fclose($fp);
                        echo $encode;
                    } else {
                        header("Content-Length: " .$toReturn["Size"]);
                        $fp = fopen($name, 'rb');
            
                        fpassthru($fp);
                    }
                    exit;
                }
                else 
                    return new Exception("Forbidden",403);
            }
        }
        else
            return  new Exception("Unknown resource",404);

    }

    // Update files
    public function putAction($args, $callerInfo, $data, $request, $verb) {
        $dataObject = json_decode($data); 
        if (isset($args[0]) && is_numeric($args[0]) && count($args) == 1 && empty($verb)) {
            $this->fixToCase($dataObject, 'Id');
            $dataObject->Id = $args[0];
            
            if (($callerInfo["IsSystemAdmin"] == 1 || isset($callerInfo["roles"]["CURA"])) && !empty($this->proxy->genericGetById("files",$args[0],$callerInfo)))  {
                return $this->proxy->genericUpdate("files", $dataObject);
            }
            else
                return new Exception("Forbidden",403);
        }
        else
            return  new Exception("Invalid URL parameters",405);
    }

    // Delete files
    public function deleteAction($args, $callerInfo, $data, $request, $verb) {
        if (isset($args[0]) && is_numeric($args[0]) && (count($args) == 1 ||count($args) == 2) && empty($verb)) {
            $toReturn =  $this->proxy->genericGetById("files",$args[0],$callerInfo);
            if (empty($toReturn))
                 return  new Exception("Unknown resource",404);
            else {
                if (!empty($this->proxy->genericGetById("alerts",$toReturn["OriginAlert"],$callerInfo)) && $toReturn["OriginOrder"] == null) {    
                    $urlImage = __DIR__."/../../dataFiles/gmaoFile".$toReturn["Id"].".bin";
                    if (unlink($urlImage)) {
                        return $this->proxy->genericDelete("files",$args[0]);
                    } else {
                        return  new Exception("Error delete image",405);
                    }
                } else if (isset($args[1]) && $args[1] == "families" && !empty($this->proxy->genericGetById("families",$toReturn["Family"],$callerInfo))) {
                    $fileName = $toReturn["Name"];
                    $extension = pathinfo($fileName, PATHINFO_EXTENSION);
                    $urlImage = __DIR__."/../../dataFiles/gmaoFile".$toReturn["Id"].".".$extension;
                    if (unlink($urlImage)) {
                        return $this->proxy->genericDelete("files",$args[0]);
                    } else {
                        return  new Exception("Error delete file",405);
                    }
                } else if (isset($args[1]) && $args[1] == "causes" && !empty($this->proxy->genericGetById("causes",$toReturn["Cause"],$callerInfo))) {
                    $fileName = $toReturn["Name"];
                    $extension = pathinfo($fileName, PATHINFO_EXTENSION);
                    $urlImage = __DIR__."/../../dataFiles/gmaoFile".$toReturn["Id"].".".$extension;
                    if (unlink($urlImage)) {
                        return $this->proxy->genericDelete("files",$args[0]);
                    } else {
                        return  new Exception("Error delete file",405);
                    }
                } else if (isset($args[1]) && $args[1] == "operations" && !empty($this->proxy->genericGetById("operations",$toReturn["Operation"],$callerInfo))) {
                    $fileName = $toReturn["Name"];
                    $extension = pathinfo($fileName, PATHINFO_EXTENSION);
                    $urlImage = __DIR__."/../../dataFiles/gmaoFile".$toReturn["Id"].".".$extension;
                    if (unlink($urlImage)) {
                        return $this->proxy->genericDelete("files",$args[0]);
                    } else {
                        return  new Exception("Error delete file",405);
                    }
                } else if (isset($args[1]) && $args[1] == "orderdetail" && !empty($this->proxy->genericGetById("orderdetail",$toReturn["OrderDetail"],$callerInfo))) {
                    $fileName = $toReturn["Name"];
                    $extension = pathinfo($fileName, PATHINFO_EXTENSION);
                    $urlImage = __DIR__."/../../dataFiles/gmaoFile".$toReturn["Id"].".".$extension;
                    if (unlink($urlImage)) {
                        return $this->proxy->genericDelete("files",$args[0]);
                    } else {
                        return  new Exception("Error delete file",405);
                    }
                } else if (isset($args[1]) && $args[1] == "tasks" && !empty($this->proxy->genericGetById("tasks",$toReturn["Task"],$callerInfo))) {
                    $fileName = $toReturn["Name"];
                    $extension = pathinfo($fileName, PATHINFO_EXTENSION);
                    $urlImage = __DIR__."/../../dataFiles/gmaoFile".$toReturn["Id"].".".$extension;
                    if (unlink($urlImage)) {
                        return $this->proxy->genericDelete("files",$args[0]);
                    } else {
                        return  new Exception("Error delete file",405);
                    }
                } else if (isset($args[1]) && $args[1] == "assets" && !empty($this->proxy->genericGetById("assets",$toReturn["Asset"],$callerInfo))) {
                    $fileName = $toReturn["Name"];
                    $extension = pathinfo($fileName, PATHINFO_EXTENSION);
                    $urlImage = __DIR__."/../../dataFiles/gmaoFile".$toReturn["Id"].".".$extension;
                    if (unlink($urlImage)) {
                        return $this->proxy->genericDelete("files",$args[0]);
                    } else {
                        return  new Exception("Error delete file",405);
                    }
                } else 
                    return new Exception("Forbidden",403);
            }
        }
        else
            return  new Exception("Unknown resource",404);
    }
}
