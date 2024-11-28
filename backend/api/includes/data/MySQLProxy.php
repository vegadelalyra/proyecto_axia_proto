<?php
include_once("iDbProxy.php");

class MySQLProxy //implements iDbProxy
{
	const DEFAULT_LIMIT = 100;
	private $user = null;
	private $password = null;
	private $dsn = null;
	private $options = array(
		PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
	);

	function __construct($dbname, $server, $user, $password)
	{
		$this->dsn = 'mysql:host=' . $server . ';dbname=' . $dbname;
		$this->user = $user;
		$this->password = $password;
	}

	private $preparedQueryCache = array();
	private $connectionCache = null;

	// Retrieves item request totals
	private function addCount($array, $pageSize = null, $start = 0, $count = null, $precision = null)
	{
		$result = new stdClass();
		$result->precision = "APROX";
		$result->data = $array;
		if (empty($count) && !empty($array)) {
			$result->total = count($array);
			if ($result->total == 0 || (!empty($pageSize) && $result->total < $pageSize)) {
				$result->total += $start;
				$result->precision = "EXACT";
			} else
				if (!empty($pageSize))
					$result->total = $start + $pageSize * 3;

		} else if (empty($array) || count($array) == 0) {
			$result->total = 0 + $start;
			$result->precision = "EXACT";
		} else if (isset($count)) {
			$result->total = 0 + $count;
			$result->precision = "EXACT";
		} else
			if (empty($precision))
				$result->precision = "APROX";

		return $result;
	}

	// Retrieves the current security restrictions
	public function getSecurityRestriction($table, $callerInfo)
	{
		if (empty($callerInfo)) {
			return "";
		} else {
			if (strtoupper($table) == "USERS") {
				if ($callerInfo["rol"] == "RP")
					return " AND users.DefaultSecurityGroup in (select SecurityGroups_Id from users_has_securityGroups where users_id = " . $callerInfo["Id"] . ") ";
				else
					return " AND (users.Owner='" . $callerInfo["Id"] . "' OR users.Id = '" . $callerInfo["Id"] . "' OR " . $callerInfo["IsSystemAdmin"] . " = 1) ";
			} else if (strtoupper($table) == "SECURITYGROUPS" && $callerInfo["IsSystemAdmin"] == 0)
				return " AND (securityGroups.owner = " . $callerInfo["Id"] . " OR securityGroups.Id in (select SecurityGroups_Id from users_has_securityGroups where users_id = " . $callerInfo["Id"] . ")) ";
			else if (strtoupper($table) == "LOCATIONS" && $callerInfo["IsSystemAdmin"] == 0)
				return " AND locations.SecurityGroup in (select SecurityGroups_Id from users_has_securityGroups where users_id = " . $callerInfo["Id"] . ") ";
			else if (strtoupper($table) == "ASSETS" && $callerInfo["IsSystemAdmin"] == 0)
				return " AND assets.SecurityGroup in (select SecurityGroups_Id from users_has_securityGroups where users_id = " . $callerInfo["Id"] . ") ";
			else if (strtoupper($table) == "FAMILIES" && $callerInfo["IsSystemAdmin"] == 0)
				return " AND families.SecurityGroup  in (select SecurityGroups_Id from users_has_securityGroups where users_id = " . $callerInfo["Id"] . ") ";
			else if (strtoupper($table) == "ELEMENTS" && $callerInfo["IsSystemAdmin"] == 0)
				return " AND elements.family in (select families.id from families, users_has_securityGroups  where families.securityGroup = securitygroups_id AND users_id = " . $callerInfo["Id"] . ") ";
			else if (strtoupper($table) == "SYMPTOMS" && $callerInfo["IsSystemAdmin"] == 0)
				return " AND symptoms.family in (select families.id from families, users_has_securityGroups where families.securityGroup = securitygroups_id AND users_id = " . $callerInfo["Id"] . ") ";
			else if (strtoupper($table) == "CAUSES" && $callerInfo["IsSystemAdmin"] == 0)
				return " AND causes.SecurityGroup in (select SecurityGroups_Id from users_has_securityGroups where users_id = " . $callerInfo["Id"] . ") ";
			else if (strtoupper($table) == "OPERATIONS" && $callerInfo["IsSystemAdmin"] == 0)
				return " AND operations.SecurityGroup in (select SecurityGroups_Id from users_has_securityGroups where users_id = " . $callerInfo["Id"] . ") ";
			else if (strtoupper($table) == "ALERTS" && $callerInfo["IsSystemAdmin"] == 0)
				return " AND exists (select assets.id from assets, users_has_securityGroups, families where assets.id = alerts.asset AND assets.family = families.id AND families.securityGroup = securitygroups_id AND users_id = " . $callerInfo["Id"] . ") ";
			else if (strtoupper($table) == "ORDERS")
				return " AND exists (select assets.id from assets, users_has_securityGroups, families  where assets.id = orders.asset AND assets.family = families.id AND families.securityGroup = securitygroups_id AND users_id = " . $callerInfo["Id"] . ") ";
			else if (strtoupper($table) == "ORDERDETAIL") {
				if ($callerInfo["rol"] == "RO")
					return " AND (orderdetail.solver = " . $callerInfo["Id"] . " OR orderdetail.solver is null) AND (orderdetail.status = 2 OR orderdetail.status = 4)";
				else
					return " AND exists (select assets.id from orders, assets, users_has_securityGroups, families  where orders.id = orderdetail.order AND assets.id = orders.asset AND assets.family = families.id AND families.securityGroup = securitygroups_id AND users_id = " . $callerInfo["Id"] . ") ";
			} else if (strtoupper($table) == "ORDERDETAILOPERATORS") {
				if ($callerInfo["rol"] == "RO")
					return " AND (orderdetail.solver = " . $callerInfo["Id"] . " OR orderdetail.solver is null) AND (orderdetail.status = 2 OR orderdetail.status = 4)";
			} else if ($callerInfo["IsSystemAdmin"] == 1)
				return " AND 1=1 ";
			else
				return "";
		}
	}

	// Retrieves the database
	private function getDB()
	{
		if (empty($this->connectionCache)) {
			$dbConnection = new PDO($this->dsn, $this->user, $this->password, $this->options);
			$dbConnection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			$dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->connectionCache = $dbConnection;
		}
		return $this->connectionCache;
	}

	// Retrieves the user information
	public function getUser($code)
	{
		$cmd = "SELECT * FROM users WHERE LOWER(users.user) = LOWER(:code)";
		$stmt = $this->getDB()->prepare($cmd);
		$stmt->execute(array('code' => $code));
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	// Delete a user
	public function userDelete($id, $callerInfo = null)
	{
		$this->getDB()->beginTransaction();
		$cmd = "DELETE FROM users_has_roles WHERE users_id = :id";
		$stmt = $this->getDB()->prepare($cmd);
		$stmt->execute(array('id' => $id));
		$cmd = "DELETE FROM users_has_securitygroups WHERE users_id = :id";
		$stmt = $this->getDB()->prepare($cmd);
		$stmt->execute(array('id' => $id));
		$cmd = "DELETE FROM users WHERE id = :id";
		$stmt = $this->getDB()->prepare($cmd);
		$stmt->execute(array('id' => $id));
		$this->getDB()->commit();
		$result = new stdClass();
		$result->success = true;
		$result->deletedId = $id;
		return $result;
	}

	// Delete a file
	public function fileDelete($id, $callerInfo = null)
	{
		$this->getDB()->beginTransaction();
		$cmd = "DELETE FROM files WHERE id = :id";
		$stmt = $this->getDB()->prepare($cmd);
		$stmt->execute(array('id' => $id));
		$this->getDB()->commit();
		unlink(__DIR__ . "/../../dataFiles/gmaoFile" . $id . ".bin"); //Fisically delete the file
		$result = new stdClass();
		$result->success = true;
		$result->deletedId = $id;
		return $result;
	}

	// Delete a alert
	public function alertDelete($id, $callerInfo = null)
	{
		$this->getDB()->beginTransaction();
		$cmd = "DELETE FROM alertdetail WHERE Alert = :id";
		$stmt = $this->getDB()->prepare($cmd);
		$stmt->execute(array('id' => $id));
		$cmd = "DELETE FROM alerts WHERE id = :id";
		$stmt = $this->getDB()->prepare($cmd);
		$stmt->execute(array('id' => $id));
		$this->getDB()->commit();
		$result = new stdClass();
		$result->success = true;
		$result->deletedId = $id;
		return $result;
	}

	// Delete a order
	public function orderDelete($id, $callerInfo = null)
	{
		$this->getDB()->beginTransaction();
		$cmd = "DELETE FROM orderdetail WHERE `Order` = :id";
		$stmt = $this->getDB()->prepare($cmd);
		$stmt->execute(array('id' => $id));
		$cmd = "DELETE FROM orders WHERE id = :id";
		$stmt = $this->getDB()->prepare($cmd);
		$stmt->execute(array('id' => $id));
		$this->getDB()->commit();
		$result = new stdClass();
		$result->success = true;
		$result->deletedId = $id;
		return $result;
	}

	// Complete the alert information
	public function completeAlert($alert)
	{
		if (!empty($alert)) {
			$cmd = "select a.Id, a.Status, a.Symptom, s.Name as symptomName, a.Element, e.name as elementName from alertDetail as a LEFT JOIN elements as e ON (e.Id = a.Element), symptoms as s WHERE s.id = a.Symptom AND a.Alert = :id";
			$stmt = $this->getDB()->prepare($cmd);
			$stmt->execute(array('id' => $alert["Id"]));
			$alert["details"] = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$description = "";
			$pending = "";
			$countWarnings = 0;
			$countPendingWarnings = 0;
			foreach ($alert["details"] as $detail) {
				if ($description != "")
					$description .= ", ";
				if ($pending != "")
					$pending .= ", ";
				if ($detail["Status"] === 0)
					$countPendingWarnings++;
				if ($detail["Status"] < 3) {
					$countWarnings++;
					$pending .= $detail["symptomName"];
					if (!is_null($detail["elementName"]))
						$pending .= '_' . $detail["elementName"];
				}
				$description .= $detail["symptomName"];
				if (!is_null($detail["elementName"]))
					$description .= '_' . $detail["elementName"];
			}
			$alert["warnings"] = $countWarnings;
			$alert["pendingWarnings"] = $countPendingWarnings;
			$alert["description"] = $description;
			$alert["pending"] = $pending;
		}

		return $alert;
	}

	// Retrieves the information of all the alerts
	public function completeAlerts($alerts)
	{
		$newData = array();
		foreach ($alerts->data as $alert) {
			$completeAlert = $this->completeAlert($alert);
			if (!is_null($completeAlert))
				$newData[] = $this->completeAlert($alert);
		}
		$alerts->data = $newData;
		$alerts->total = count($alerts->data);
		return $alerts;
	}

	// Retrieves an alert for your Id
	public function getAlertById($id, $callerInfo = null)
	{
		$cmd = "SELECT alerts.*, users.Name as UserName, assets.Code as AssetCode, assets.Family as Family FROM alerts 
		LEFT JOIN users on (users.Id = alerts.Owner) 
		LEFT JOIN assets on (assets.Id = alerts.Asset)
		WHERE alerts.Id = :id";
		$cmd .= " " . $this->getSecurityRestriction('alerts', $callerInfo);
		$stmt = $this->getDB()->prepare($cmd);
		$stmt->execute(array('id' => $id));
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if (count($results) != 1)
			return null;
		else
			return $this->lowerCaseOwner($results)[0];
	}

	// Retrieve alerts
	public function getAlerts($table, $callerInfo, $all, $limit, $start, $sortField, $sortAsc, $filterField, $filterValue)
	{
		$cmd = "SELECT alerts.*, users.Name as UserName, assets.Code as AssetCode, assets.Family as Family,
			COUNT(*) AS warnings, 
			SUM(CASE WHEN alertdetail.status = 0 THEN 1 ELSE 0 END) AS pendingWarnings
			FROM alerts
			LEFT JOIN users on (users.Id = alerts.Owner) 
			LEFT JOIN assets on (assets.Id = alerts.Asset)
			LEFT JOIN alertdetail ON alerts.Id = alertdetail.Alert";

		$cmd .= " WHERE 1=1 ";
		if (isset($filterField)) {
			for ($i = 0; $i < count($filterField); $i++) {

				$cmd .= " AND (";
				$firstOr = true;
				foreach (explode("|", $filterValue[$i]) as $orValue) {
					if (!$firstOr)
						$cmd .= " OR ";
					$firstOr = false;
					switch (strtoupper($filterField[$i])) {
						case 'ASSETCODE':
							$filterField[$i] = "assets.`Code`";
							break;
						case 'SECURITYGROUPNAME':
							$filterField[$i] = "securitygroups.`Name`";
							break;
						case 'FAMILYNAME':
							$filterField[$i] = "families.`Name`";
							break;
						case 'FAMILYKEY':
							break;
							$filterField[$i] = "families.`Key`";
							break;
						case 'USERNAME':
							$filterField[$i] = "users.`Name`";
							break;
						case 'LOCATIONNAME':
							$filterField[$i] = "locations.`Name`";
							break;
						case 'LOCATIONOPERATOR':
							$filterField[$i] = "l.`Name`";
							break;
						case 'STATUSNAME':
							$filterField[$i] = "orderstatus.`Key`";
							break;
						case 'ASSETNAME':
							$filterField[$i] = "ass.`Name`";
							break;
						default:
							if (strpos($filterField[$i], '.') <= 0)
								$filterField[$i] = $table . "." . "`" . escape_string($filterField[$i]) . "`";
							break;
					}
					$cmd .= $filterField[$i];
					if (strpos($orValue, ">=") === 0 && is_numeric(str_replace(">=", "", $orValue)))
						$cmd .= " >= " . str_replace(">=", "", $orValue);
					else if (strpos($orValue, "<=") === 0 && is_numeric(str_replace("<=", "", $orValue)))
						$cmd .= " <= " . str_replace("<=", "", $orValue);
					else if (strpos($orValue, "lte|") === 0 && is_numeric(str_replace("lte|", "", $orValue)))
						$cmd .= " <= " . str_replace("lte|", "", $orValue);
					else if (strpos($orValue, ">") === 0 && is_numeric(str_replace(">", "", $orValue)))
						$cmd .= " > " . str_replace(">", "", $orValue);
					else if (strpos($orValue, "<") === 0 && is_numeric(str_replace("<", "", $orValue)))
						$cmd .= " < " . str_replace("<", "", $orValue);
					else if (strpos($orValue, "lt|") === 0 && is_numeric(str_replace("lt|", "", $orValue)))
						$cmd .= " < " . str_replace("lt|", "", $orValue);
					else if (strpos($orValue, "=") === 0 && is_numeric(str_replace("=", "", $orValue)))
						$cmd .= " LIKE " . str_replace("=", "", $orValue);
					else if (strpos($orValue, "!=") === 0 && is_numeric(str_replace("!=", "", $orValue)))
						$cmd .= " <> " . str_replace("!=", "", $orValue);
					else if (strpos($orValue, "=") === 0 && !is_numeric(str_replace("=", "", $orValue)))
						$cmd .= " LIKE '" . str_replace("=", "", $orValue) . "'";
					else if (strpos($orValue, "!=") === 0 && !is_numeric(str_replace("!=", "", $orValue)))
						$cmd .= " <> '" . str_replace("!=", "", $orValue) . "'";
					else
						$cmd .= " LIKE '%" . escape_string($orValue) . "%'";
				}
				$cmd .= " ) ";
			}
			$cmd .= " " . $this->getSecurityRestriction($table, $callerInfo);
		} else {
			$restriction = $this->getSecurityRestriction($table, $callerInfo);
			if (!empty($restriction))
				$cmd .= $restriction;
		}

		$cmd .= " GROUP BY alerts.Id DESC";
		if (isset($all) && $all == false)
			$cmd .= " HAVING SUM(CASE WHEN alertdetail.Status = 0 THEN 1 ELSE 0 END) > 0";

		if (isset($sortField)) {
			$cmd .= " ORDER BY `" . escape_string($sortField) . "` " . ((escape_string($sortAsc) == 1) ? " ASC" : " DESC");
		}

		if (isset($limit) && is_numeric($limit)) {
			if (!isset($start) || !is_numeric($limit))
				$start = 0;
			$cmd .= " LIMIT " . $start . ", " . $limit . "";
		}

		$stmt = $this->getDB()->prepare($cmd);
		$stmt->execute();
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if (count($results) === 0)
			return $this->addCount([], $limit, $start);
		else
			return $this->addCount($this->lowerCaseOwner($results), $limit, $start);
	}

	// Complete the order information
	public function completeOrder($order)
	{
		if (!empty($order)) {
			$cmd = "select o.Id, o.Status, o.Symptom, o.AlertDetail, s.Name as symptomName, o.Element, e.name as elementName, 
			o.Cause, c.name as causeName, o.Location, l.name as locationName, o.Solver, u.name as solverName, o.Operation, 
			p.name as operationName, o.RepairTime from orderDetail as o 
			LEFT JOIN elements as e ON (e.Id = o.Element) 
			LEFT JOIN causes as c ON (c.Id = o.Cause) 
			LEFT JOIN locations as l ON (l.Id = o.Location) 
			LEFT JOIN users as u ON (u.Id = o.Solver) 
			LEFT JOIN operations as p ON (p.Id = o.Operation)
			LEFT JOIN symptoms as s ON (s.Id = o.Symptom)
			WHERE o.Order = :id";
			$stmt = $this->getDB()->prepare($cmd);
			$stmt->execute(array('id' => $order["Id"]));
			$order["details"] = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$description = "";
			$pending = "";
			foreach ($order["details"] as $detail) {
				if ($description != "")
					$description .= ", ";
				if ($pending != "")
					$pending .= ", ";
				if ($detail["Status"] < 3) {
					$pending .= $detail["symptomName"];
					if (!is_null($detail["elementName"]))
						$pending .= $detail["elementName"];
				}
				$description .= $detail["symptomName"];
				if (!is_null($detail["elementName"]))
					$description .= $detail["elementName"];
			}
			$order["description"] = $description;
			$order["pending"] = $pending;
		}
		return $order;
	}

	// Retrieves the information of all the orders
	public function completeOrders($orders)
	{
		$newData = array();
		foreach ($orders->data as $order) {
			$newData[] = $this->completeOrder($order);
		}
		$orders->data = $newData;
		return $orders;
	}

	// Returns lowercase to the owner
	private function lowerCaseOwner($list)
	{
		for ($i = 0; $i < count($list); $i++) {
			if (isset($list[$i]['Owner'])) {
				$list[$i]['owner'] = $list[$i]['Owner'];
				unset($list[$i]['Owner']);
			}
		}
		return $list;
	}

	// Retrieve users
	public function getUsers($userId = null, $start = 0, $limit = 1000, $sortField = null, $sortAsc = null, $filterField = null, $filterValue = null, $callerInfo = null)
	{

		if (!empty($filterField) && !is_array($filterField))
			$filterField = array($filterField);
		if (!empty($filterValue) && !is_array($filterValue))
			$filterValue = array($filterValue);

		$cmd = "SELECT users.*, locations.Name as locationName, securityGroups.name as defaultSecurityGroupName, roles.name as defaultRolName, roles.`key` as defaultRolKey, owners.user as ownerName  FROM users left join securityGroups on (users.DefaultSecurityGroup = securityGroups.id) left join roles on (users.DefaultRol = roles.id)  left join users as owners on (users.owner = owners.id) left join locations on (locations.Id = users.Location)";
		if (!empty($userId) && is_numeric($userId))
			$cmd .= " WHERE users.Id = " . $userId . " ";
		else
			$cmd .= " WHERE 1=1 ";
		$cmd .= " " . $this->getSecurityRestriction("users", $callerInfo);
		if (isset($filterField)) {
			for ($i = 0; $i < count($filterField); $i++) {
				$cmd .= " AND users.`" . escape_string($filterField[$i]) . "` ";
				if (strpos($filterValue[$i], ">=") === 0 && is_numeric(str_replace(">=", "", $filterValue[$i])))
					$cmd .= " >= " . str_replace(">=", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "<=") === 0 && is_numeric(str_replace("<=", "", $filterValue[$i])))
					$cmd .= " <= " . str_replace("<=", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "lte|") === 0 && is_numeric(str_replace("lte|", "", $filterValue[$i])))
					$cmd .= " <= " . str_replace("lte|", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], ">") === 0 && is_numeric(str_replace(">", "", $filterValue[$i])))
					$cmd .= " > " . str_replace(">", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "<") === 0 && is_numeric(str_replace("<", "", $filterValue[$i])))
					$cmd .= " < " . str_replace("<", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "lt|") === 0 && is_numeric(str_replace("lt|", "", $filterValue[$i])))
					$cmd .= " < " . str_replace("lt|", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "=") === 0 && is_numeric(str_replace("=", "", $filterValue[$i])))
					$cmd .= " = " . str_replace("=", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "=") === 0 && !is_numeric(str_replace("=", "", $filterValue[$i])))
					$cmd .= " = '" . str_replace("=", "", $filterValue[$i]) . "'";
				else if (strpos($filterValue[$i], "!=") === 0 && !is_numeric(str_replace("!=", "", $filterValue[$i])))
					$cmd .= " <> '" . str_replace("!=", "", $filterValue[$i]) . "'";
				else
					$cmd .= " LIKE '%" . escape_string($filterValue[$i]) . "%'";
			}
		}

		if (isset($sortField)) {
			$cmd .= " ORDER BY  `" . escape_string($sortField) . "` " . ((escape_string($sortAsc) == 1) ? " ASC" : " DESC");
		}

		if (isset($limit) && is_numeric($limit)) {
			if (!isset($start) || !is_numeric($limit))
				$start = 0;
			$cmd .= " LIMIT " . $start . ", " . $limit . "";
		}
		$stmt = $this->getDB()->prepare($cmd);
		$stmt->execute();
		$response = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if (!isset($response))
			return $this->addCount(array(), $limit, $start);
		else
			return $this->addCount($this->lowerCaseOwner($response), $limit, $start);
	}

	// Retrieve active alerts
	public function getActiveAlerts($start = 0, $limit = 1000, $sortField = null, $sortAsc = null, $filterField = null, $filterValue = null, $callerInfo = null)
	{

		if (!empty($filterField) && !is_array($filterField))
			$filterField = array($filterField);
		if (!empty($filterValue) && !is_array($filterValue))
			$filterValue = array($filterValue);

		$cmd = "SELECT alerts.*, users.Name as UserName, assets.Code as AssetCode, assets.Family FROM alerts left join users on (users.Id = alerts.Owner) left join assets on (assets.Id = alerts.Asset)";
		$cmd .= " WHERE (select count(*) from alertdetail where alertDetail.alert = alerts.id AND alertdetail.status < 3 )>0 ";

		$cmd .= " " . $this->getSecurityRestriction("alerts", $callerInfo);
		if (isset($filterField)) {
			for ($i = 0; $i < count($filterField); $i++) {
				$cmd .= " AND alerts.`" . escape_string($filterField[$i]) . "` ";
				if (strpos($filterValue[$i], ">=") === 0 && is_numeric(str_replace(">=", "", $filterValue[$i])))
					$cmd .= " >= " . str_replace(">=", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "<=") === 0 && is_numeric(str_replace("<=", "", $filterValue[$i])))
					$cmd .= " <= " . str_replace("<=", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "lte|") === 0 && is_numeric(str_replace("lte|", "", $filterValue[$i])))
					$cmd .= " <= " . str_replace("lte|", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], ">") === 0 && is_numeric(str_replace(">", "", $filterValue[$i])))
					$cmd .= " > " . str_replace(">", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "<") === 0 && is_numeric(str_replace("<", "", $filterValue[$i])))
					$cmd .= " < " . str_replace("<", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "lt|") === 0 && is_numeric(str_replace("lt|", "", $filterValue[$i])))
					$cmd .= " < " . str_replace("lt|", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "=") === 0 && is_numeric(str_replace("=", "", $filterValue[$i])))
					$cmd .= " = " . str_replace("=", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "=") === 0 && !is_numeric(str_replace("=", "", $filterValue[$i])))
					$cmd .= " = '" . str_replace("=", "", $filterValue[$i]) . "'";
				else if (strpos($filterValue[$i], "!=") === 0 && !is_numeric(str_replace("!=", "", $filterValue[$i])))
					$cmd .= " <> '" . str_replace("!=", "", $filterValue[$i]) . "'";
				else
					$cmd .= " LIKE '%" . escape_string($filterValue[$i]) . "%'";
			}
		}

		if (isset($sortField)) {
			$cmd .= " ORDER BY  `" . escape_string($sortField) . "` " . ((escape_string($sortAsc) == 1) ? " ASC" : " DESC");
		}

		if (isset($limit) && is_numeric($limit)) {
			if (!isset($start) || !is_numeric($limit))
				$start = 0;
			$cmd .= " LIMIT " . $start . ", " . $limit . "";
		}
		$stmt = $this->getDB()->prepare($cmd);
		$stmt->execute();
		$response = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if (!isset($response))
			return $this->addCount(array(), $limit, $start);
		else
			return $this->addCount($this->lowerCaseOwner($response), $limit, $start);
	}

	// Retrieve active files
	public function getActiveFiles($asset, $start = 0, $limit = 1000, $sortField = null, $sortAsc = null, $filterField = null, $filterValue = null, $callerInfo = null)
	{

		if (!empty($filterField) && !is_array($filterField))
			$filterField = array($filterField);
		if (!empty($filterValue) && !is_array($filterValue))
			$filterValue = array($filterValue);

		$cmd = "SELECT files.* FROM files, alerts";
		$cmd .= " WHERE alerts.id = files.OriginAlert AND alerts.asset = " . $asset . " AND (select count(*) from alertdetail where alertDetail.alert = alerts.id AND alertdetail.status < 3 )>0 ";
		$cmd .= " " . $this->getSecurityRestriction("files", $callerInfo);
		if (isset($filterField)) {
			for ($i = 0; $i < count($filterField); $i++) {
				$cmd .= " AND files.`" . escape_string($filterField[$i]) . "` ";
				if (strpos($filterValue[$i], ">=") === 0 && is_numeric(str_replace(">=", "", $filterValue[$i])))
					$cmd .= " >= " . str_replace(">=", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "<=") === 0 && is_numeric(str_replace("<=", "", $filterValue[$i])))
					$cmd .= " <= " . str_replace("<=", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "lte|") === 0 && is_numeric(str_replace("lte|", "", $filterValue[$i])))
					$cmd .= " <= " . str_replace("lte|", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], ">") === 0 && is_numeric(str_replace(">", "", $filterValue[$i])))
					$cmd .= " > " . str_replace(">", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "<") === 0 && is_numeric(str_replace("<", "", $filterValue[$i])))
					$cmd .= " < " . str_replace("<", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "lt|") === 0 && is_numeric(str_replace("lt|", "", $filterValue[$i])))
					$cmd .= " < " . str_replace("lt|", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "=") === 0 && is_numeric(str_replace("=", "", $filterValue[$i])))
					$cmd .= " = " . str_replace("=", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "=") === 0 && !is_numeric(str_replace("=", "", $filterValue[$i])))
					$cmd .= " = '" . str_replace("=", "", $filterValue[$i]) . "'";
				else if (strpos($filterValue[$i], "!=") === 0 && !is_numeric(str_replace("!=", "", $filterValue[$i])))
					$cmd .= " <> '" . str_replace("!=", "", $filterValue[$i]) . "'";
				else
					$cmd .= " LIKE '%" . escape_string($filterValue[$i]) . "%'";
			}
		}

		if (isset($sortField)) {
			$cmd .= " ORDER BY  `" . escape_string($sortField) . "` " . ((escape_string($sortAsc) == 1) ? " ASC" : " DESC");
		}

		if (isset($limit) && is_numeric($limit)) {
			if (!isset($start) || !is_numeric($limit))
				$start = 0;
			$cmd .= " LIMIT " . $start . ", " . $limit . "";
		}
		$stmt = $this->getDB()->prepare($cmd);
		$stmt->execute();
		$response = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if (!isset($response))
			return $this->addCount(array(), $limit, $start);
		else
			return $this->addCount($this->lowerCaseOwner($response), $limit, $start);
	}

	// Retrieve images
	public function getImages($table, $field, $id, $callerInfo = null)
	{
		$cmd = "SELECT * FROM " . $table . " WHERE `{$field}` = :id AND files.Category = 5";
		$cmd .= " " . $this->getSecurityRestriction($table, $callerInfo);
		$stmt = $this->getDB()->prepare($cmd);
		$stmt->execute(array('id' => $id));
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $results;
	}

	// Retrieve files
	public function getFiles($table, $field, $id, $callerInfo = null)
	{
		$cmd = "SELECT * FROM " . $table . " WHERE `{$field}` = :id AND files.Category = 6";
		$cmd .= " " . $this->getSecurityRestriction($table, $callerInfo);
		$stmt = $this->getDB()->prepare($cmd);
		$stmt->execute(array('id' => $id));
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $results;
	}

	// Retrieve family files
	public function getFamilyFiles($family, $start = 0, $limit = 1000, $sortField = null, $sortAsc = null, $filterField = null, $filterValue = null, $callerInfo = null)
	{
		if (!empty($filterField) && !is_array($filterField))
			$filterField = array($filterField);
		if (!empty($filterValue) && !is_array($filterValue))
			$filterValue = array($filterValue);

		$cmd = "SELECT files.* FROM files, families";
		$cmd .= " WHERE families.id = files.Family AND families.Id = " . $family . " ";
		$cmd .= " " . $this->getSecurityRestriction("files", $callerInfo);
		if (isset($filterField)) {
			for ($i = 0; $i < count($filterField); $i++) {
				$cmd .= " AND files.`" . escape_string($filterField[$i]) . "` ";
				if (strpos($filterValue[$i], ">=") === 0 && is_numeric(str_replace(">=", "", $filterValue[$i])))
					$cmd .= " >= " . str_replace(">=", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "<=") === 0 && is_numeric(str_replace("<=", "", $filterValue[$i])))
					$cmd .= " <= " . str_replace("<=", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "lte|") === 0 && is_numeric(str_replace("lte|", "", $filterValue[$i])))
					$cmd .= " <= " . str_replace("lte|", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], ">") === 0 && is_numeric(str_replace(">", "", $filterValue[$i])))
					$cmd .= " > " . str_replace(">", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "<") === 0 && is_numeric(str_replace("<", "", $filterValue[$i])))
					$cmd .= " < " . str_replace("<", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "lt|") === 0 && is_numeric(str_replace("lt|", "", $filterValue[$i])))
					$cmd .= " < " . str_replace("lt|", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "=") === 0 && is_numeric(str_replace("=", "", $filterValue[$i])))
					$cmd .= " = " . str_replace("=", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "=") === 0 && !is_numeric(str_replace("=", "", $filterValue[$i])))
					$cmd .= " = '" . str_replace("=", "", $filterValue[$i]) . "'";
				else if (strpos($filterValue[$i], "!=") === 0 && !is_numeric(str_replace("!=", "", $filterValue[$i])))
					$cmd .= " <> '" . str_replace("!=", "", $filterValue[$i]) . "'";
				else
					$cmd .= " LIKE '%" . escape_string($filterValue[$i]) . "%'";
			}
		}

		if (isset($sortField)) {
			$cmd .= " ORDER BY  `" . escape_string($sortField) . "` " . ((escape_string($sortAsc) == 1) ? " ASC" : " DESC");
		}

		if (isset($limit) && is_numeric($limit)) {
			if (!isset($start) || !is_numeric($limit))
				$start = 0;
			$cmd .= " LIMIT " . $start . ", " . $limit . "";
		}
		$stmt = $this->getDB()->prepare($cmd);
		$stmt->execute();
		$response = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if (!isset($response))
			return $this->addCount(array(), $limit, $start);
		else
			return $this->addCount($this->lowerCaseOwner($response), $limit, $start);
	}

	// Retrieve cause files
	public function getCauseFiles($cause, $start = 0, $limit = 1000, $sortField = null, $sortAsc = null, $filterField = null, $filterValue = null, $callerInfo = null)
	{
		if (!empty($filterField) && !is_array($filterField))
			$filterField = array($filterField);
		if (!empty($filterValue) && !is_array($filterValue))
			$filterValue = array($filterValue);

		$cmd = "SELECT files.*, categories.Name as CategoryName FROM files";
		$cmd .= " left join categories on (categories.Id = files.Category) left join causes on (causes.id = files.Cause) WHERE causes.Id = " . $cause . " ";
		$cmd .= " " . $this->getSecurityRestriction("files", $callerInfo);
		if (isset($filterField)) {
			for ($i = 0; $i < count($filterField); $i++) {
				$cmd .= " AND files.`" . escape_string($filterField[$i]) . "` ";
				if (strpos($filterValue[$i], ">=") === 0 && is_numeric(str_replace(">=", "", $filterValue[$i])))
					$cmd .= " >= " . str_replace(">=", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "<=") === 0 && is_numeric(str_replace("<=", "", $filterValue[$i])))
					$cmd .= " <= " . str_replace("<=", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "lte|") === 0 && is_numeric(str_replace("lte|", "", $filterValue[$i])))
					$cmd .= " <= " . str_replace("lte|", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], ">") === 0 && is_numeric(str_replace(">", "", $filterValue[$i])))
					$cmd .= " > " . str_replace(">", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "<") === 0 && is_numeric(str_replace("<", "", $filterValue[$i])))
					$cmd .= " < " . str_replace("<", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "lt|") === 0 && is_numeric(str_replace("lt|", "", $filterValue[$i])))
					$cmd .= " < " . str_replace("lt|", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "=") === 0 && is_numeric(str_replace("=", "", $filterValue[$i])))
					$cmd .= " = " . str_replace("=", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "=") === 0 && !is_numeric(str_replace("=", "", $filterValue[$i])))
					$cmd .= " = '" . str_replace("=", "", $filterValue[$i]) . "'";
				else if (strpos($filterValue[$i], "!=") === 0 && !is_numeric(str_replace("!=", "", $filterValue[$i])))
					$cmd .= " <> '" . str_replace("!=", "", $filterValue[$i]) . "'";
				else
					$cmd .= " LIKE '%" . escape_string($filterValue[$i]) . "%'";
			}
		}

		if (isset($sortField)) {
			$cmd .= " ORDER BY  `" . escape_string($sortField) . "` " . ((escape_string($sortAsc) == 1) ? " ASC" : " DESC");
		}

		if (isset($limit) && is_numeric($limit)) {
			if (!isset($start) || !is_numeric($limit))
				$start = 0;
			$cmd .= " LIMIT " . $start . ", " . $limit . "";
		}
		$stmt = $this->getDB()->prepare($cmd);
		$stmt->execute();
		$response = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if (!isset($response))
			return $this->addCount(array(), $limit, $start);
		else
			return $this->addCount($this->lowerCaseOwner($response), $limit, $start);
	}

	// Retrieve operation files
	public function getOperationFiles($operation, $start = 0, $limit = 1000, $sortField = null, $sortAsc = null, $filterField = null, $filterValue = null, $callerInfo = null)
	{
		if (!empty($filterField) && !is_array($filterField))
			$filterField = array($filterField);
		if (!empty($filterValue) && !is_array($filterValue))
			$filterValue = array($filterValue);

		$cmd = "SELECT files.*, categories.Name as CategoryName FROM files";
		$cmd .= " left join categories on (categories.Id = files.Category) left join operations on (operations.id = files.Operation) WHERE operations.Id = " . $operation . " ";
		$cmd .= " " . $this->getSecurityRestriction("files", $callerInfo);
		if (isset($filterField)) {
			for ($i = 0; $i < count($filterField); $i++) {
				$cmd .= " AND files.`" . escape_string($filterField[$i]) . "` ";
				if (strpos($filterValue[$i], ">=") === 0 && is_numeric(str_replace(">=", "", $filterValue[$i])))
					$cmd .= " >= " . str_replace(">=", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "<=") === 0 && is_numeric(str_replace("<=", "", $filterValue[$i])))
					$cmd .= " <= " . str_replace("<=", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "lte|") === 0 && is_numeric(str_replace("lte|", "", $filterValue[$i])))
					$cmd .= " <= " . str_replace("lte|", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], ">") === 0 && is_numeric(str_replace(">", "", $filterValue[$i])))
					$cmd .= " > " . str_replace(">", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "<") === 0 && is_numeric(str_replace("<", "", $filterValue[$i])))
					$cmd .= " < " . str_replace("<", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "lt|") === 0 && is_numeric(str_replace("lt|", "", $filterValue[$i])))
					$cmd .= " < " . str_replace("lt|", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "=") === 0 && is_numeric(str_replace("=", "", $filterValue[$i])))
					$cmd .= " = " . str_replace("=", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "=") === 0 && !is_numeric(str_replace("=", "", $filterValue[$i])))
					$cmd .= " = '" . str_replace("=", "", $filterValue[$i]) . "'";
				else if (strpos($filterValue[$i], "!=") === 0 && !is_numeric(str_replace("!=", "", $filterValue[$i])))
					$cmd .= " <> '" . str_replace("!=", "", $filterValue[$i]) . "'";
				else
					$cmd .= " LIKE '%" . escape_string($filterValue[$i]) . "%'";
			}
		}

		if (isset($sortField)) {
			$cmd .= " ORDER BY  `" . escape_string($sortField) . "` " . ((escape_string($sortAsc) == 1) ? " ASC" : " DESC");
		}

		if (isset($limit) && is_numeric($limit)) {
			if (!isset($start) || !is_numeric($limit))
				$start = 0;
			$cmd .= " LIMIT " . $start . ", " . $limit . "";
		}
		$stmt = $this->getDB()->prepare($cmd);
		$stmt->execute();
		$response = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if (!isset($response))
			return $this->addCount(array(), $limit, $start);
		else
			return $this->addCount($this->lowerCaseOwner($response), $limit, $start);
	}

	// Retrieve order detail files
	public function getOrderDetailFiles($orderdetail, $start = 0, $limit = 1000, $sortField = null, $sortAsc = null, $filterField = null, $filterValue = null, $callerInfo = null)
	{
		if (!empty($filterField) && !is_array($filterField))
			$filterField = array($filterField);
		if (!empty($filterValue) && !is_array($filterValue))
			$filterValue = array($filterValue);

		$cmd = "SELECT files.* FROM files, orderdetail";
		$cmd .= " WHERE orderdetail.id = files.OrderDetail AND orderdetail.Id = " . $orderdetail . " ";
		$cmd .= " " . $this->getSecurityRestriction("files", $callerInfo);
		if (isset($filterField)) {
			for ($i = 0; $i < count($filterField); $i++) {
				$cmd .= " AND files.`" . escape_string($filterField[$i]) . "` ";
				if (strpos($filterValue[$i], ">=") === 0 && is_numeric(str_replace(">=", "", $filterValue[$i])))
					$cmd .= " >= " . str_replace(">=", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "<=") === 0 && is_numeric(str_replace("<=", "", $filterValue[$i])))
					$cmd .= " <= " . str_replace("<=", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "lte|") === 0 && is_numeric(str_replace("lte|", "", $filterValue[$i])))
					$cmd .= " <= " . str_replace("lte|", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], ">") === 0 && is_numeric(str_replace(">", "", $filterValue[$i])))
					$cmd .= " > " . str_replace(">", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "<") === 0 && is_numeric(str_replace("<", "", $filterValue[$i])))
					$cmd .= " < " . str_replace("<", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "lt|") === 0 && is_numeric(str_replace("lt|", "", $filterValue[$i])))
					$cmd .= " < " . str_replace("lt|", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "=") === 0 && is_numeric(str_replace("=", "", $filterValue[$i])))
					$cmd .= " = " . str_replace("=", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "=") === 0 && !is_numeric(str_replace("=", "", $filterValue[$i])))
					$cmd .= " = '" . str_replace("=", "", $filterValue[$i]) . "'";
				else if (strpos($filterValue[$i], "!=") === 0 && !is_numeric(str_replace("!=", "", $filterValue[$i])))
					$cmd .= " <> '" . str_replace("!=", "", $filterValue[$i]) . "'";
				else
					$cmd .= " LIKE '%" . escape_string($filterValue[$i]) . "%'";
			}
		}

		if (isset($sortField)) {
			$cmd .= " ORDER BY  `" . escape_string($sortField) . "` " . ((escape_string($sortAsc) == 1) ? " ASC" : " DESC");
		}

		if (isset($limit) && is_numeric($limit)) {
			if (!isset($start) || !is_numeric($limit))
				$start = 0;
			$cmd .= " LIMIT " . $start . ", " . $limit . "";
		}
		$stmt = $this->getDB()->prepare($cmd);
		$stmt->execute();
		$response = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if (!isset($response))
			return $this->addCount(array(), $limit, $start);
		else
			return $this->addCount($this->lowerCaseOwner($response), $limit, $start);
	}

	// Retrieve assets
	public function getAssets($table, $asset, $callerInfo = null, $start = 0, $limit = DEFAULT_LIMIT, $sortField = null, $sortAsc = null, $filterField = null, $filterValue = null)
	{
		if (!empty($filterField) && !is_array($filterField))
			$filterField = array($filterField);
		if (!empty($filterValue) && !is_array($filterValue))
			$filterValue = array($filterValue);

		$cmd = "SELECT assets.*, families.Name as familyName, locations.Name as locationName, securityGroups.name as SecurityGroupName, assetstatus.Key as StatusKey, versions.Number as VersionNumber FROM assets 
				left join assetstatus on (assets.Status = assetstatus.Id) 
				left join families on (assets.Family = families.Id) 
				left join versions ON assets.Version = versions.Id 
				left join locations on (assets.Location = locations.Id) 
				left join securityGroups on (assets.Securitygroup = securityGroups.id)";

		if (isset($asset))
			$cmd .= " WHERE assets.Id = " . $asset . " ";
		else
			$cmd .= " WHERE 1=1 ";
		if (isset($filterField)) {
			for ($i = 0; $i < count($filterField); $i++) {

				$cmd .= " AND (";
				$firstOr = true;
				foreach (explode("|", $filterValue[$i]) as $orValue) {
					if (!$firstOr)
						$cmd .= " OR ";
					$firstOr = false;
					switch (strtoupper($filterField[$i])) {
						case 'ASSETCODE':
							$filterField[$i] = "assets.`Code`";
							break;
						case 'STATUSKEY':
							$filterField[$i] = "assets.`Status`";
							break;
						case 'SECURITYGROUPNAME':
							$filterField[$i] = "securityGroups.`name`";
							break;
						case 'LOCATIONNAME':
							$filterField[$i] = "locations.`Name`";
							break;
						case 'FAMILYNAME':
							$filterField[$i] = "families.`Name`";
							break;
						default:
							if (strpos($filterField[$i], '.') <= 0)
								$filterField[$i] = $table . "." . "`" . escape_string($filterField[$i]) . "`";
							break;
					}
					$cmd .= $filterField[$i];
					if (strpos($orValue, ">=") === 0 && is_numeric(str_replace(">=", "", $orValue)))
						$cmd .= " >= " . str_replace(">=", "", $orValue);
					else if (strpos($orValue, "<=") === 0 && is_numeric(str_replace("<=", "", $orValue)))
						$cmd .= " <= " . str_replace("<=", "", $orValue);
					else if (strpos($orValue, "lte|") === 0 && is_numeric(str_replace("lte|", "", $orValue)))
						$cmd .= " <= " . str_replace("lte|", "", $orValue);
					else if (strpos($orValue, ">") === 0 && is_numeric(str_replace(">", "", $orValue)))
						$cmd .= " > " . str_replace(">", "", $orValue);
					else if (strpos($orValue, "<") === 0 && is_numeric(str_replace("<", "", $orValue)))
						$cmd .= " < " . str_replace("<", "", $orValue);
					else if (strpos($orValue, "lt|") === 0 && is_numeric(str_replace("lt|", "", $orValue)))
						$cmd .= " < " . str_replace("lt|", "", $orValue);
					else if (strpos($orValue, "=") === 0 && is_numeric(str_replace("=", "", $orValue)))
						$cmd .= " LIKE " . str_replace("=", "", $orValue);
					else if (strpos($orValue, "!=") === 0 && is_numeric(str_replace("!=", "", $orValue)))
						$cmd .= " <> " . str_replace("!=", "", $orValue);
					else if (strpos($orValue, "=") === 0 && !is_numeric(str_replace("=", "", $orValue)))
						$cmd .= " LIKE '" . str_replace("=", "", $orValue) . "'";
					else if (strpos($orValue, "!=") === 0 && !is_numeric(str_replace("!=", "", $orValue)))
						$cmd .= " <> '" . str_replace("!=", "", $orValue) . "'";
					else
						$cmd .= " LIKE '%" . escape_string($orValue) . "%'";
				}
				$cmd .= " ) ";
			}
			$cmd .= " " . $this->getSecurityRestriction($table, $callerInfo);
		} else {
			$restriction = $this->getSecurityRestriction($table, $callerInfo);
			if (!empty($restriction))
				$cmd .= $restriction;
		}

		if (isset($sortField)) {
			$cmd .= " ORDER BY  `" . escape_string($sortField) . "` " . ((escape_string($sortAsc) == 1) ? " ASC" : " DESC");
		}

		if (isset($limit) && is_numeric($limit)) {
			if (!isset($start) || !is_numeric($limit))
				$start = 0;
			$cmd .= " LIMIT " . $start . ", " . $limit . "";
		}
		$stmt = $this->getDB()->prepare($cmd);
		$stmt->execute();
		$response = $stmt->fetchAll(PDO::FETCH_ASSOC);

		//GET DETAILS
		if (!empty($response)) {
			for ($i = 0; $i < count($response); $i++) {
				$chkexec = $this->getCheckExecsByAsset($response[$i]['Id'], $callerInfo, false);
				if (isset($chkexec)) {
					$response[$i]["chkexec"] = $chkexec;
				} else
					$response[$i]["chkexec"] = null;
			}
		}

		if (isset($asset)) {
			if (!isset($response))
				return null;
			else
				return $response[0];
		} else {
			if (!isset($response))
				return $this->addCount(array(), $limit, $start);
			else
				return $this->addCount($this->lowerCaseOwner($response), $limit, $start);
		}
	}

	// Retrieve asset files
	public function getAssetFiles($asset, $start = 0, $limit = 1000, $sortField = null, $sortAsc = null, $filterField = null, $filterValue = null, $callerInfo = null)
	{
		if (!empty($filterField) && !is_array($filterField))
			$filterField = array($filterField);
		if (!empty($filterValue) && !is_array($filterValue))
			$filterValue = array($filterValue);

		$cmd = "SELECT files.*, categories.Name as CategoryName FROM files";
		$cmd .= " left join categories on (categories.Id = files.Category) left join assets on (assets.id = files.Asset) WHERE assets.Id = " . $asset . " ";
		$cmd .= " " . $this->getSecurityRestriction("files", $callerInfo);
		if (isset($filterField)) {
			for ($i = 0; $i < count($filterField); $i++) {
				$cmd .= " AND files.`" . escape_string($filterField[$i]) . "` ";
				if (strpos($filterValue[$i], ">=") === 0 && is_numeric(str_replace(">=", "", $filterValue[$i])))
					$cmd .= " >= " . str_replace(">=", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "<=") === 0 && is_numeric(str_replace("<=", "", $filterValue[$i])))
					$cmd .= " <= " . str_replace("<=", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "lte|") === 0 && is_numeric(str_replace("lte|", "", $filterValue[$i])))
					$cmd .= " <= " . str_replace("lte|", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], ">") === 0 && is_numeric(str_replace(">", "", $filterValue[$i])))
					$cmd .= " > " . str_replace(">", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "<") === 0 && is_numeric(str_replace("<", "", $filterValue[$i])))
					$cmd .= " < " . str_replace("<", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "lt|") === 0 && is_numeric(str_replace("lt|", "", $filterValue[$i])))
					$cmd .= " < " . str_replace("lt|", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "=") === 0 && is_numeric(str_replace("=", "", $filterValue[$i])))
					$cmd .= " = " . str_replace("=", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "=") === 0 && !is_numeric(str_replace("=", "", $filterValue[$i])))
					$cmd .= " = '" . str_replace("=", "", $filterValue[$i]) . "'";
				else if (strpos($filterValue[$i], "!=") === 0 && !is_numeric(str_replace("!=", "", $filterValue[$i])))
					$cmd .= " <> '" . str_replace("!=", "", $filterValue[$i]) . "'";
				else
					$cmd .= " LIKE '%" . escape_string($filterValue[$i]) . "%'";
			}
		}

		if (isset($sortField)) {
			$cmd .= " ORDER BY  `" . escape_string($sortField) . "` " . ((escape_string($sortAsc) == 1) ? " ASC" : " DESC");
		}

		if (isset($limit) && is_numeric($limit)) {
			if (!isset($start) || !is_numeric($limit))
				$start = 0;
			$cmd .= " LIMIT " . $start . ", " . $limit . "";
		}
		$stmt = $this->getDB()->prepare($cmd);
		$stmt->execute();
		$response = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if (!isset($response))
			return $this->addCount(array(), $limit, $start);
		else
			return $this->addCount($this->lowerCaseOwner($response), $limit, $start);
	}

	// Retrieve asset checklists
	public function getAssetChecklists($asset, $start = 0, $limit = 1000, $sortField = null, $sortAsc = null, $filterField = null, $filterValue = null, $callerInfo = null)
	{
		if (!empty($filterField) && !is_array($filterField))
			$filterField = array($filterField);
		if (!empty($filterValue) && !is_array($filterValue))
			$filterValue = array($filterValue);

		$cmd = "SELECT checklists.*, checklistscategories.Name as categoryName FROM checklists";
		$cmd .= " left join assets on (assets.Family = checklists.family) left join checklistscategories on (checklistscategories.Id = checklists.category) WHERE assets.Id = " . $asset . " ";
		$cmd .= " " . $this->getSecurityRestriction("checklists", $callerInfo);
		if (isset($filterField)) {
			for ($i = 0; $i < count($filterField); $i++) {
				$cmd .= " AND checklists.`" . escape_string($filterField[$i]) . "` ";
				if (strpos($filterValue[$i], ">=") === 0 && is_numeric(str_replace(">=", "", $filterValue[$i])))
					$cmd .= " >= " . str_replace(">=", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "<=") === 0 && is_numeric(str_replace("<=", "", $filterValue[$i])))
					$cmd .= " <= " . str_replace("<=", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "lte|") === 0 && is_numeric(str_replace("lte|", "", $filterValue[$i])))
					$cmd .= " <= " . str_replace("lte|", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], ">") === 0 && is_numeric(str_replace(">", "", $filterValue[$i])))
					$cmd .= " > " . str_replace(">", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "<") === 0 && is_numeric(str_replace("<", "", $filterValue[$i])))
					$cmd .= " < " . str_replace("<", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "lt|") === 0 && is_numeric(str_replace("lt|", "", $filterValue[$i])))
					$cmd .= " < " . str_replace("lt|", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "=") === 0 && is_numeric(str_replace("=", "", $filterValue[$i])))
					$cmd .= " = " . str_replace("=", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "=") === 0 && !is_numeric(str_replace("=", "", $filterValue[$i])))
					$cmd .= " = '" . str_replace("=", "", $filterValue[$i]) . "'";
				else if (strpos($filterValue[$i], "!=") === 0 && !is_numeric(str_replace("!=", "", $filterValue[$i])))
					$cmd .= " <> '" . str_replace("!=", "", $filterValue[$i]) . "'";
				else
					$cmd .= " LIKE '%" . escape_string($filterValue[$i]) . "%'";
			}
		}

		if (isset($sortField)) {
			$cmd .= " ORDER BY  `" . escape_string($sortField) . "` " . ((escape_string($sortAsc) == 1) ? " ASC" : " DESC");
		}

		if (isset($limit) && is_numeric($limit)) {
			if (!isset($start) || !is_numeric($limit))
				$start = 0;
			$cmd .= " LIMIT " . $start . ", " . $limit . "";
		}
		$stmt = $this->getDB()->prepare($cmd);
		$stmt->execute();
		$response = $stmt->fetchAll(PDO::FETCH_ASSOC);

		//GET DETAILS
		if (!empty($response) && !empty($asset)) {
			$cmd = "SELECT chkdetails.* FROM chkdetails WHERE chkdetails.checklist = :0";
			$stmt = $this->getDB()->prepare($cmd);
			for ($i = 0; $i < count($response); $i++) {
				$stmt->execute(array($response[$i]['Id']));
				$aditionalInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);
				$response[$i]["details"] = array();
				$response[$i]["details"] = $aditionalInfo;
			}
		}

		if (!isset($response))
			return $this->addCount(array(), $limit, $start);
		else
			return $this->addCount($this->lowerCaseOwner($response), $limit, $start);
	}

	// Retrieve active symptoms
	public function getActiveSymptoms($asset, $start = 0, $limit = 1000, $sortField = null, $sortAsc = null, $filterField = null, $filterValue = null, $callerInfo = null)
	{
		if (!empty($filterField) && !is_array($filterField))
			$filterField = array($filterField);
		if (!empty($filterValue) && !is_array($filterValue))
			$filterValue = array($filterValue);

		$cmd = "select a.Id, ale.origin, a.Status, a.Symptom, s.Name as symptomName, a.Element, e.name as elementName from alerts as ale, alertDetail as a LEFT JOIN elements as e ON (e.Id = a.Element), symptoms as s WHERE a.Status < 3 AND s.id = a.Symptom AND a.Alert = ale.id and ale.asset = " . $asset . " ";
		if (isset($filterField)) {
			for ($i = 0; $i < count($filterField); $i++) {
				$cmd .= " AND alertDetail.`" . escape_string($filterField[$i]) . "` ";
				if (strpos($filterValue[$i], ">=") === 0 && is_numeric(str_replace(">=", "", $filterValue[$i])))
					$cmd .= " >= " . str_replace(">=", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "<=") === 0 && is_numeric(str_replace("<=", "", $filterValue[$i])))
					$cmd .= " <= " . str_replace("<=", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "lte|") === 0 && is_numeric(str_replace("lte|", "", $filterValue[$i])))
					$cmd .= " <= " . str_replace("lte|", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], ">") === 0 && is_numeric(str_replace(">", "", $filterValue[$i])))
					$cmd .= " > " . str_replace(">", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "<") === 0 && is_numeric(str_replace("<", "", $filterValue[$i])))
					$cmd .= " < " . str_replace("<", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "lt|") === 0 && is_numeric(str_replace("lt|", "", $filterValue[$i])))
					$cmd .= " < " . str_replace("lt|", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "=") === 0 && is_numeric(str_replace("=", "", $filterValue[$i])))
					$cmd .= " = " . str_replace("=", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "=") === 0 && !is_numeric(str_replace("=", "", $filterValue[$i])))
					$cmd .= " = '" . str_replace("=", "", $filterValue[$i]) . "'";
				else if (strpos($filterValue[$i], "!=") === 0 && !is_numeric(str_replace("!=", "", $filterValue[$i])))
					$cmd .= " <> '" . str_replace("!=", "", $filterValue[$i]) . "'";
				else
					$cmd .= " LIKE '%" . escape_string($filterValue[$i]) . "%'";
			}
		}

		if (isset($sortField)) {
			$cmd .= " ORDER BY  `" . escape_string($sortField) . "` " . ((escape_string($sortAsc) == 1) ? " ASC" : " DESC");
		}

		if (isset($limit) && is_numeric($limit)) {
			if (!isset($start) || !is_numeric($limit))
				$start = 0;
			$cmd .= " LIMIT " . $start . ", " . $limit . "";
		}
		$stmt = $this->getDB()->prepare($cmd);
		$stmt->execute();
		$response = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if (!isset($response))
			return $this->addCount(array(), $limit, $start);
		else
			return $this->addCount($this->lowerCaseOwner($response), $limit, $start);
	}

	// Retrieves a list of elements based on specified table
	public function genericGetElements($table, $start = 0, $limit = DEFAULT_LIMIT, $sortField = null, $sortAsc = null, $filterField = null, $filterValue = null, $callerInfo = null, $location = null, $assetName = null)
	{

		if (!empty($filterField) && !is_array($filterField))
			$filterField = array($filterField);
		if (!empty($filterValue) && !is_array($filterValue))
			$filterValue = array($filterValue);

		if (isset($table) && $table == "families")
			$cmd = "SELECT families.*, familyStatus.Key as StatusKey, familyStatus.Id as StatusId, securityGroups.name as SecurityGroupName, versions.Number as VersionNumber FROM families left join securityGroups on (families.Securitygroup = securityGroups.id) left join versions ON families.CurrentVersion = versions.id left join familyStatus on (versions.Status = familyStatus.id)";
		else if (isset($table) && $table == "symptoms")
			$cmd = "SELECT symptoms.*, families.Name as familyName, families.Key as familyKey FROM symptoms left join families on (symptoms.Family = families.Id)";
		else if (isset($table) && $table == "elements")
			$cmd = "SELECT elements.*, families.Name as familyName, families.Key as familyKey FROM elements left join families on (elements.Family = families.Id)";
		else if (isset($table) && $table == "causes")
			$cmd = "SELECT causes.*, securityGroups.name as SecurityGroupName, families.Name as familyName, families.Key as familyKey FROM causes left join families on (causes.Family = families.Id) left join securityGroups on (causes.Securitygroup = securityGroups.id)";
		else if (isset($table) && $table == "locations")
			$cmd = "SELECT locations.*, securityGroups.name as SecurityGroupName FROM locations left join securityGroups on (locations.Securitygroup = securityGroups.id)";
		else if (isset($table) && $table == "assets")
			$cmd = "SELECT assets.*, families.Name as familyName, locations.Name as locationName, securityGroups.name as SecurityGroupName, assetstatus.Key as StatusKey, versions.Number as VersionNumber FROM assets left join assetstatus on (assets.Status = assetstatus.Id) left join families on (assets.Family = families.Id) left join versions ON assets.Version = versions.Id left join locations on (assets.Location = locations.Id) left join securityGroups on (assets.Securitygroup = securityGroups.id)";
		else if (isset($table) && $table == "alerts")
			$cmd = "SELECT alerts.*, users.Name as UserName, assets.Code as AssetCode, assets.Family as Family FROM alerts left join users on (users.Id = alerts.Owner) left join assets on (assets.Id = alerts.Asset)";
		else if (isset($table) && $table == "orders")
			$cmd = "SELECT orders.*, users.Name as OwnerName, assets.Code as AssetCode, assets.Family as Family, families.Name as familyName, families.RepairTimes as repairTimes, locations.Name as locationName, orderstatus.Key as statusName FROM orders left join users on (users.Id = orders.Owner) left join assets on (assets.Id = orders.Asset) left join locations on (locations.Id = orders.Destination) left join families on (assets.Family = families.Id) left join orderstatus on (orders.Status = orderstatus.Value)";
		else if (isset($table) && ($table == "orderDetailOperators" || $table == "orderDetail"))
			$cmd = "SELECT orderDetail.*, e.Name as elementName, c.Name as causeName, l.Name as locationName, u.Name as userName, o.Name as operationName, ord.Asset as asset, ass.Name as assetName FROM orderdetail left join elements e on (e.Id = orderDetail.Element) left join causes c on (c.Id = orderDetail.Cause) left join locations l on (l.Id = orderDetail.Location) left join users u on (u.Id = orderDetail.Solver OR u.Id = null) left join operations o on (o.Id = orderDetail.Operation) left join orders ord on (ord.Id = orderdetail.Order) left join assets ass on (ass.Id = ord.Asset)";
		else if (isset($table) && $table == "operations")
			$cmd = "SELECT operations.*, securityGroups.name as SecurityGroupName FROM operations left join securityGroups on (operations.Securitygroup = securityGroups.id)";
		else if (isset($table) && $table == "assetdestinations" || $table == "repairlocations")
			$cmd = "SELECT " . $table . ".*, securityGroups.name as SecurityGroupName FROM " . $table . " left join securityGroups on (" . $table . ".Securitygroup = securityGroups.id)";
		else if (isset($table) && $table == "users")
			$cmd = "SELECT users.*, locations.Name as locationName FROM users left join locations on (locations.Id = users.Location)";
		else if (isset($table) && $table == "checklists")
			$cmd = "SELECT checklists.*, users.Name as OwnerName, families.Name as familyName FROM checklists left join users on (users.Id = checklists.owner) left join families on (families.Id = checklists.family)";
		else if (isset($table) && $table == "categories")
			$cmd = "SELECT categories.*, families.Name as familyName FROM categories left join families on (families.Id = categories.EspecificFamily)";
		else
			$cmd = "SELECT * FROM " . $table;

		$cmd .= " WHERE 1=1 ";
		if (isset($filterField)) {
			for ($i = 0; $i < count($filterField); $i++) {

				$cmd .= " AND (";
				$firstOr = true;
				foreach (explode("|", $filterValue[$i]) as $orValue) {
					if (!$firstOr)
						$cmd .= " OR ";
					$firstOr = false;
					switch (strtoupper($filterField[$i])) {
						case 'ASSETCODE':
							$filterField[$i] = "assets.`Code`";
							break;
						case 'SECURITYGROUPNAME':
							$filterField[$i] = "securitygroups.`Name`";
							break;
						case 'FAMILYNAME':
							$filterField[$i] = "families.`Name`";
							break;
						case 'FAMILYKEY':
							break;
							$filterField[$i] = "families.`Key`";
							break;
						case 'USERNAME':
							$filterField[$i] = "users.`Name`";
							break;
						case 'LOCATIONNAME':
							$filterField[$i] = "locations.`Name`";
							break;
						case 'LOCATIONOPERATOR':
							$filterField[$i] = "l.`Name`";
							break;
						case 'STATUSNAME':
							$filterField[$i] = "orderstatus.`Key`";
							break;
						case 'ASSETNAME':
							$filterField[$i] = "ass.`Name`";
							break;
						default:
							if (strpos($filterField[$i], '.') <= 0)
								$filterField[$i] = $table . "." . "`" . escape_string($filterField[$i]) . "`";
							break;
					}

					$cmd .= $filterField[$i];
					if (strpos($orValue, ">=") === 0 && is_numeric(str_replace(">=", "", $orValue)))
						$cmd .= " >= " . str_replace(">=", "", $orValue);
					else if (strpos($orValue, "<=") === 0 && is_numeric(str_replace("<=", "", $orValue)))
						$cmd .= " <= " . str_replace("<=", "", $orValue);
					else if (strpos($orValue, "lte|") === 0 && is_numeric(str_replace("lte|", "", $orValue)))
						$cmd .= " <= " . str_replace("lte|", "", $orValue);
					else if (strpos($orValue, ">") === 0 && is_numeric(str_replace(">", "", $orValue)))
						$cmd .= " > " . str_replace(">", "", $orValue);
					else if (strpos($orValue, "<") === 0 && is_numeric(str_replace("<", "", $orValue)))
						$cmd .= " < " . str_replace("<", "", $orValue);
					else if (strpos($orValue, "lt|") === 0 && is_numeric(str_replace("lt|", "", $orValue)))
						$cmd .= " < " . str_replace("lt|", "", $orValue);
					else if (strpos($orValue, "=") === 0 && is_numeric(str_replace("=", "", $orValue)))
						$cmd .= " LIKE " . str_replace("=", "", $orValue);
					else if (strpos($orValue, "!=") === 0 && is_numeric(str_replace("!=", "", $orValue)))
						$cmd .= " <> " . str_replace("!=", "", $orValue);
					else if (strpos($orValue, "=") === 0 && !is_numeric(str_replace("=", "", $orValue)))
						$cmd .= " LIKE '" . str_replace("=", "", $orValue) . "'";
					else if (strpos($orValue, "!=") === 0 && !is_numeric(str_replace("!=", "", $orValue)))
						$cmd .= " <> '" . str_replace("!=", "", $orValue) . "'";
					else
						$cmd .= " LIKE '%" . escape_string($orValue) . "%'";
				}
				$cmd .= " ) ";
			}
			$cmd .= " " . $this->getSecurityRestriction($table, $callerInfo);
		} else {
			$restriction = $this->getSecurityRestriction($table, $callerInfo);
			if (!empty($restriction))
				$cmd .= $restriction;
		}

		if ($callerInfo["rol"] == "RO") {
			//OPERATORS FILTER UBICATION
			if (isset($location)) {
				$cmd .= " AND (l.Name LIKE '" . $location . "' OR l.Name is null)";
			}
		}

		if (isset($table) && $table == "orders" && $callerInfo["Location"] != null && $callerInfo["rol"] != "QA")
			$cmd .= " AND locations.Id = " . $callerInfo["Location"];

		if (isset($sortField)) {
			$cmd .= " ORDER BY  `" . escape_string($sortField) . "` " . ((escape_string($sortAsc) == 1) ? " ASC" : " DESC");
		}

		if (isset($limit) && is_numeric($limit)) {
			if (!isset($start) || !is_numeric($limit))
				$start = 0;
			$cmd .= " LIMIT " . $start . ", " . $limit . "";
		}
		$stmt = $this->getDB()->prepare($cmd);
		$stmt->execute();
		$response = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if (!isset($response))
			return $this->addCount(array(), $limit, $start);
		else
			return $this->addCount($this->lowerCaseOwner($response), $limit, $start);
	}

	// Retrieves a element based on specified table and id
	public function genericGetById($table, $id, $callerInfo = null)
	{
		$cmd = "SELECT * FROM " . $table . " WHERE id = :id";
		$cmd .= " " . $this->getSecurityRestriction($table, $callerInfo);
		$stmt = $this->getDB()->prepare($cmd);
		$stmt->execute(array('id' => $id));
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if (count($results) != 1)
			return null;
		else
			return $this->lowerCaseOwner($results)[0];
	}

	// Retrieves a elements based on specified table, field and id
	public function genericGetByKeyField($table, $field, $id, $callerInfo = null)
	{
		$cmd = "SELECT * FROM " . $table . " WHERE `{$field}` = :id";
		$cmd .= " " . $this->getSecurityRestriction($table, $callerInfo);
		$stmt = $this->getDB()->prepare($cmd);
		$stmt->execute(array('id' => $id));
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if (count($results) > 0)
			return $results[0];
		else
			return null;
	}

	// Retrieves a elements based on specified table, field and id
	public function genericGetAllByKeyField($table, $field, $id, $callerInfo = null)
	{
		if ($table == "orders")
			$cmd = "SELECT orders.*, users.Name as OwnerName, assets.Code as AssetCode, assets.Family as Family, families.Name as familyName, families.RepairTimes as repairTimes, locations.Name as locationName, orderstatus.Key as statusName FROM orders left join users on (users.Id = orders.Owner) left join assets on (assets.Id = orders.Asset) left join locations on (locations.Id = orders.Destination) left join families on (assets.Family = families.Id) left join orderstatus on (orders.Status = orderstatus.Value) WHERE `{$field}` = :id";
		else
			$cmd = "SELECT * FROM " . $table . " WHERE `{$field}` = :id";

		$cmd .= " " . $this->getSecurityRestriction($table, $callerInfo);
		$stmt = $this->getDB()->prepare($cmd);
		$stmt->execute(array('id' => $id));
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if (isset($results))
			return $results;
		else
			return null;
	}

	// Retrieves a element based on specified table, field and id
	public function genericGetChildren($table, $field, $id, $callerInfo = null)
	{
		$cmd = "SELECT * FROM " . $table . " WHERE `{$field}` = :id";
		$cmd .= " " . $this->getSecurityRestriction($table, $callerInfo);
		$stmt = $this->getDB()->prepare($cmd);
		$stmt->execute(array('id' => $id));
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $results;
	}

	// Generic deletion for elements
	public function genericDelete($table, $id, $callerInfo = null)
	{
		$cmd = "DELETE FROM " . $table . " WHERE id = :id";
		$cmd .= " " . $this->getSecurityRestriction($table, $callerInfo);
		$stmt = $this->getDB()->prepare($cmd);
		$stmt->execute(array('id' => $id));
		$result = new stdClass();
		$result->success = true;
		$result->deletedId = $id;
		return $result;
	}

	// Generic deletion for related elements
	public function genericRelationDelete($table, $field1, $id1, $field2, $id2, $callerInfo = null)
	{
		$cmd = "DELETE FROM " . $table . " WHERE " . escape_string($field1) . " = :id1 AND " . escape_string($field2) . " = :id2 ";
		$stmt = $this->getDB()->prepare($cmd);
		$stmt->execute(array('id1' => $id1, 'id2' => $id2));
		$result = new stdClass();
		$result->success = true;
		$result->{$field1} = $id1;
		$result->{$field2} = $id2;
		return $result;
	}

	// Generic insert for element
	public function genericInsert($table, $data, $silent = false)
	{
		$db = $this->getDB();
		$query = "INSERT INTO " . $table;
		$fields = "";
		$values = "";
		$valuesArray = array();
		$i = 0;
		foreach ($data as $key => $value) {
			if ($key != "id" && $key != "Id") {
				if ($fields != "") {
					$fields .= ", ";
					$values .= ", ";
				}

				$fields .= "`" . escape_string($key) . "`";
				$values .= ":" . $i;
				$valuesArray[$i] = $value;
				$i++;
			}
		}

		$query = "INSERT INTO " . $table . " (" . $fields . ") VALUES (" . $values . ")";
		$hash = md5($query);
		if (!isset($this->preparedQueryCache[$hash])) {
			$stmt = $db->prepare($query);
			$this->preparedQueryCache[$hash] = $stmt;
		}

		try {
			$this->preparedQueryCache[$hash]->execute($valuesArray);
			$insertedId = $db->lastInsertId();

			if ($silent === true)
				return $insertedId;
			else {
				if ($insertedId > 0)
					return $this->genericGetById($table, $insertedId);
				else
					return null;
			}
		} catch (Exception $e) {
			//echo 'Ha habido una excepción: ' . $e->getMessage() . "<br>";
			return null;
		}
	}

	// Generic update for element
	public function genericUpdate($table, $data, $callerInfo = null, $silent = false)
	{
		if (!isset($data->Id) || $data->Id == null || $data->Id <= 0)
			return null;
		else {
			$valuesArray = array();
			$db = $this->getDB();
			$updates = "";
			$i = 0;
			foreach ($data as $key => $value) {
				if ($key != 'Id') {
					if ($updates != "") {
						$updates .= ", ";
					}
					$valuesArray[$i] = $value;
					$updates .= "`" . escape_string($key) . "` = :" . $i;
					$i++;
				}
			}
			if ($i > 0) {
				$valuesArray['id'] = $data->Id;
				$query = "UPDATE " . $table . " SET " . $updates . " WHERE id = :id";
				$query .= " " . $this->getSecurityRestriction($table, $callerInfo);
				$hash = md5($query);
				if (!isset($this->preparedQueryCache[$hash])) {
					$stmt = $db->prepare($query);
					$this->preparedQueryCache[$hash] = $stmt;
				}

				$this->preparedQueryCache[$hash]->execute($valuesArray);
			}
			if ($silent)
				return true;
			else
				return $this->genericGetById($table, $data->Id);
		}
	}

	// Generic update for element
	// public function genericGetSubelements($parent, $parentKeyField, $parentField, $referencedField, $parentTable, $referencedTable, $start, $limit = DEFAULT_LIMIT, $sortField, $sortAsc, $filterField, $filterValue, $callerInfo = null) {

	// 	if (!empty($filterField) && !is_array($filterField))
	// 		$filterField = array($filterField);
	// 	if (!empty($filterValue) && !is_array($filterValue))
	// 		$filterValue = array($filterValue);

	// 	$cmd = "SELECT ".escape_string($referencedTable).".* FROM ".escape_string($parentTable)." inner join ".escape_string($referencedTable)." on (".escape_string($referencedTable).".".escape_string($referencedField)." = ".escape_string($parentTable).".".escape_string($parentField).") ";
	// 	$cmd.= "WHERE ".escape_string($parentTable).".".escape_string($parentKeyField)." = ".$parent. " ";

	// 	if (isset($filterField)) {
	// 		for ($i = 0; $i < count($filterField); $i++){
	// 			$cmd.= " AND `".escape_string($filterField[$i])."` LIKE '%".escape_string($filterValue[$i])."%'"; 
	// 		}
	// 	}

	// 	$cmd.= " ".$this->getSecurityRestriction($parentTable,$callerInfo);
	// 	$cmd.= " ".$this->getSecurityRestriction($referencedTable,$callerInfo);

	// 	if (isset($sortField)) {
	// 		$cmd.= " ORDER BY  `".escape_string($sortField)."` ".((escape_string($sortAsc)==1)?"ASC":"DESC"); 
	// 	}

	// 	if (isset($limit) && is_numeric($limit)) {
	// 		if (!isset($start) || !is_numeric($limit))
	// 			$start = 0;
	// 		$cmd.= " LIMIT ".$start.", ".$limit."";
	// 	}
	//     $stmt = $this->getDB()->prepare($cmd);
	//     $stmt->execute();
	//     $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
	//     if (!isset($response))
	// 		return  $this->addCount(array(),$limit,$start);
	// 	else
	// 		return $this->addCount($this->lowerCaseOwner($response),$limit,$start);
	// }

	public function genericGetSubelements($parent, $parentKeyField, $parentField, $referencedField, $parentTable, $referencedTable, $start, $limit = DEFAULT_LIMIT, $sortField, $sortAsc, $filterField, $filterValue, $callerInfo = null)
	{
		if (!empty($filterField) && !is_array($filterField))
			$filterField = array($filterField);
		if (!empty($filterValue) && !is_array($filterValue))
			$filterValue = array($filterValue);

		// Construcción de la consulta base
		$cmd = "SELECT {$referencedTable}.* FROM {$parentTable} 
				INNER JOIN {$referencedTable} 
				ON ({$referencedTable}.{$referencedField} = {$parentTable}.{$parentField}) 
				WHERE {$parentTable}.{$parentKeyField} = :parent";

		$params = [':parent' => $parent];

		// Aplicar filtros dinámicos
		if (isset($filterField)) {
			for ($i = 0; $i < count($filterField); $i++) {
				$cmd .= " AND `{$filterField[$i]}` LIKE :filterValue{$i}";
				$params[":filterValue{$i}"] = "%{$filterValue[$i]}%";
			}
		}

		// Restricciones de seguridad
		$cmd .= " " . $this->getSecurityRestriction($parentTable, $callerInfo);
		$cmd .= " " . $this->getSecurityRestriction($referencedTable, $callerInfo);

		// Ordenación
		if (isset($sortField)) {
			$cmd .= " ORDER BY `{$sortField}` " . ($sortAsc == 1 ? "ASC" : "DESC");
		}

		// Paginación
		if (isset($limit) && is_numeric($limit)) {
			$start = isset($start) && is_numeric($start) ? $start : 0;
			$cmd .= " LIMIT :start, :limit";
			$params[':start'] = (int) $start;
			$params[':limit'] = (int) $limit;
		}

		// Preparar y ejecutar la consulta
		$stmt = $this->getDB()->prepare($cmd);
		foreach ($params as $key => $value) {
			$stmt->bindValue($key, $value, is_numeric($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
		}
		$stmt->execute();
		$response = $stmt->fetchAll(PDO::FETCH_ASSOC);

		// Formatear respuesta
		return isset($response) ? $this->addCount($this->lowerCaseOwner($response), $limit, $start) : $this->addCount([], $limit, $start);
	}

	// Retrieves the schema of the specified table
	public function getSchemaDatabase($table)
	{
		$db = $this->getDB();
		$stmt = $db->prepare('DESCRIBE ' . $table);
		$stmt->execute();
		$response = $stmt->fetchAll(PDO::FETCH_ASSOC);

		if (isset($response))
			return $response;
		else
			return null;
	}

	// Updates the records in a table through the data in a file
	public function updateByFile($files, $table, $callerInfo)
	{
		set_time_limit(600);
		$this->delimiter = ";";
		$this->enclosure = '"';
		$result = array();
		foreach ($files as $file) {
			$file = $file['tmp_name'];
			$csv = file_get_contents($file);
			$array = array_map(function ($row) {
				return str_getcsv($row, $this->delimiter, $this->enclosure);
			}, explode("\n", $csv));

			for ($i = 1; $i < count($array); $i++) {
				$currentObject = new stdClass();
				$j = 0;
				if (isset($array[$i]) && is_array($array[$i]) && count($array[$i]) > 1) {
					foreach ($array[0] as $field) {
						$exploded = explode(".", $field);
						if (count($exploded) == 2) {
							if (!isset($currentObject->$exploded[0]))
								$currentObject->$exploded[0] = new stdClass();
							$currentObject->$exploded[0]->$exploded[1] = $array[$i][$j];
						} else
							$currentObject->$field = $array[$i][$j];
						$j++;
					}
					$keyField = array_keys(get_object_vars($currentObject))[0];
					$toUpdate = $this->genericGetByKeyField($table, $keyField, $currentObject->$keyField, $callerInfo);
					unset($currentObject->$keyField);
					if (!empty($toUpdate)) {
						$result[] = $this->putAction(array($toUpdate['Id']), $callerInfo, json_encode($currentObject), $table);
					} else {
						$result[] = null;
					}
				}
			}
		}
		$mainResult = new stdClass();
		$mainResult->success = true;
		$mainResult->data = $result;
		return $mainResult;
	}

	// Update a specific record
	public function putAction($args, $callerInfo, $data, $table)
	{
		$dataObject = json_decode($data);
		//Get record
		$oldRecord = $this->getRecord($args[0], $table);

		if (isset($args[0]) && is_numeric($args[0])) {
			$dataObject->Id = $args[0];

			if (!empty($oldRecord)) {
				if (count(get_object_vars($dataObject)) > 1) {
					$result = $this->genericUpdate($table, $dataObject, $callerInfo);
					return $result;
				} else {
					$returnValue = $this->getAction(array($dataObject->Id), $callerInfo, null, null, null);
					return $returnValue;
				}
			}
		} else
			return new Exception("Invalid URL parameters", 405);

	}

	// Get record from table
	public function getRecord($recordId, $table)
	{
		$db = $this->getDB();
		$stmt = $db->prepare('select * from ' . $table . ' where id = ' . $recordId);
		$stmt->execute();
		$response = $stmt->fetchAll(PDO::FETCH_ASSOC);

		if (isset($response))
			return $response;
		else
			return null;
	}

	// Retrieves a family based on specified id
	public function getFamilyById($id, $callerInfo = null)
	{
		$cmd = "SELECT families.*, familyStatus.Key as StatusKey, familyStatus.Id as StatusId, securityGroups.name as SecurityGroupName FROM families left join securityGroups on (families.Securitygroup = securityGroups.id) left join versions ON families.CurrentVersion = versions.id left join familyStatus on (versions.Status = familyStatus.id)";
		if (isset($id))
			$cmd .= " WHERE families.Id = :id";
		$cmd .= " " . $this->getSecurityRestriction('families', $callerInfo);
		$stmt = $this->getDB()->prepare($cmd);
		if (isset($id))
			$stmt->execute(array('id' => $id));
		else
			$stmt->execute();
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if (count($results) != 1)
			return null;
		else
			return $this->lowerCaseOwner($results)[0];
	}

	// Retrieves assets status (stock, scrapped, etc) for families 
	public function recoveryFamilyStatus($user, $family)
	{
		$cmd = "SELECT families.Key,
					families.Name,
					COALESCE(containers.stock, 0) AS stock,
					COALESCE(scrapped.total, 0) AS scrapped,
					COALESCE(available.in_circuit, 0) AS in_circuit,
					COALESCE(repairs.reparation, 0) AS reparation,
					COALESCE(repairs.reparation, 0) / COALESCE(containers.stock, 1) AS repair_percentage
				FROM families
				LEFT JOIN (
					SELECT assets.Family,
							COUNT(DISTINCT assets.Id) AS stock
					FROM assets
					WHERE assets.Status = 5
					GROUP BY assets.Family
				) AS containers ON containers.Family = families.Id
				LEFT JOIN (
					SELECT COALESCE(COUNT(DISTINCT assets.Id), 0) AS total,
							families.Id AS Family
					FROM families
					LEFT JOIN assets ON families.Id = assets.Family AND assets.Status = 7
					GROUP BY families.Id
				) AS scrapped ON scrapped.Family = families.Id
				LEFT JOIN (
					SELECT assets.Family,
							COUNT(DISTINCT assets.Id) AS reparation
					FROM orders
					JOIN assets ON assets.Id = orders.Asset
					WHERE (orders.Status = 2 OR orders.Status = 3) AND assets.Status = 5
					GROUP BY assets.Family
				) AS repairs ON repairs.Family = families.Id
				LEFT JOIN (
					SELECT a.Family,
							COUNT(*) AS in_circuit
					FROM (
						SELECT Asset,
								MIN(orders.Status) AS MinStatus,
								assets.Family
						FROM orders
						JOIN assets ON assets.Id = orders.Asset
						GROUP BY Asset, assets.Family
					) AS a
					WHERE a.MinStatus < 5 OR a.MinStatus IS NULL
					GROUP BY a.Family
				) AS available ON available.Family = families.Id ";

		if (isset($family))
			$cmd .= "WHERE families.Securitygroup = " . $user["DefaultSecurityGroup"] . " AND families.Key LIKE '%" . $family . "%'";
		else
			$cmd .= "WHERE families.Securitygroup = " . $user["DefaultSecurityGroup"];

		$stmt = $this->getDB()->prepare($cmd);
		$stmt->execute();
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $results;
	}

	// Retrieves the total availability of the families according to the status of their assets
	public function getGlobalFamilyStatus($user, $family)
	{
		$cmd = "SELECT
					SUM(CASE WHEN available_percentage < 8 THEN 1 ELSE 0 END) AS green_families,
					SUM(CASE WHEN available_percentage >= 8 AND available_percentage <= 10 THEN 1 ELSE 0 END) AS yellow_families,
					SUM(CASE WHEN available_percentage > 10 THEN 1 ELSE 0 END) AS red_families
				FROM (
					SELECT 
						families.Key,
						families.Name,
						COALESCE(containers.stock, 0) AS stock,
						COALESCE(scrapped.total, 0) AS scrapped,
						COALESCE(available.in_circuit, 0) AS in_circuit,
						COALESCE(repairs.reparation, 0) AS reparation,
						LEAST(GREATEST(COALESCE(available.in_circuit, 0) / NULLIF(COALESCE(containers.stock, 1), 0), 0), 1) * 100 AS available_percentage
					FROM families
					LEFT JOIN (
						SELECT 
							assets.Family,
							COUNT(DISTINCT assets.Id) AS stock
						FROM assets
						WHERE assets.Status = 5
						GROUP BY assets.Family
					) AS containers ON containers.Family = families.Id
					LEFT JOIN (
						SELECT 
							COALESCE(COUNT(DISTINCT assets.Id), 0) AS total,
							families.Id AS Family
						FROM families
						LEFT JOIN assets ON families.Id = assets.Family AND assets.Status = 7
						GROUP BY families.Id
					) AS scrapped ON scrapped.Family = families.Id
					LEFT JOIN (
						SELECT 
							assets.Family,
							COUNT(DISTINCT assets.Id) AS reparation
						FROM orders
						JOIN assets ON assets.Id = orders.Asset
						WHERE (orders.Status = 2 OR orders.Status = 3) AND assets.Status = 5
						GROUP BY assets.Family
					) AS repairs ON repairs.Family = families.Id
					LEFT JOIN (
						SELECT 
							a.Family,
							COUNT(*) AS in_circuit
						FROM (
							SELECT 
								Asset,
								MIN(orders.Status) AS MinStatus,
								assets.Family
							FROM orders
							JOIN assets ON assets.Id = orders.Asset
							GROUP BY Asset, assets.Family
						) AS a
						WHERE a.MinStatus < 5 OR a.MinStatus IS NULL
						GROUP BY a.Family
					) AS available ON available.Family = families.Id ";

		if (isset($family))
			$cmd .= "WHERE families.Securitygroup = " . $user["DefaultSecurityGroup"] . " AND families.Id = " . $family;
		else
			$cmd .= "WHERE families.Securitygroup = " . $user["DefaultSecurityGroup"];

		$cmd .= ") AS subquery";
		$stmt = $this->getDB()->prepare($cmd);
		$stmt->execute();
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $results;
	}

	// Retrieves the total quantity of the families according to the status of their assets
	public function getGlobalFamilyQuantity($user, $family)
	{
		$cmd = "SELECT
					SUM(stock) AS stocktotal,
					SUM(stock) - SUM(in_circuit) AS available,
					SUM(in_circuit) AS maintenance
				FROM (
					SELECT 
						families.Key,
						families.Name,
						COALESCE(containers.stock, 0) AS stock,
						COALESCE(available.in_circuit, 0) AS in_circuit,
						COALESCE(repairs.reparation, 0) AS reparation
					FROM families
					LEFT JOIN (
						SELECT 
							assets.Family,
							COUNT(DISTINCT assets.Id) AS stock
						FROM assets
						WHERE assets.Status = 5
						GROUP BY assets.Family
					) AS containers ON containers.Family = families.Id
					LEFT JOIN (
						SELECT 
							COALESCE(COUNT(DISTINCT assets.Id), 0) AS total,
							families.Id AS Family
						FROM families
						LEFT JOIN assets ON families.Id = assets.Family AND assets.Status = 7
						GROUP BY families.Id
					) AS scrapped ON scrapped.Family = families.Id
					LEFT JOIN (
						SELECT 
							assets.Family,
							COUNT(DISTINCT assets.Id) AS reparation
						FROM orders
						JOIN assets ON assets.Id = orders.Asset
						WHERE (orders.Status = 2 OR orders.Status = 3) AND assets.Status = 5
						GROUP BY assets.Family
					) AS repairs ON repairs.Family = families.Id
					LEFT JOIN (
						SELECT 
							a.Family,
							COUNT(*) AS in_circuit
						FROM (
							SELECT 
								Asset,
								MIN(orders.Status) AS MinStatus,
								assets.Family
							FROM orders
							JOIN assets ON assets.Id = orders.Asset
							GROUP BY Asset, assets.Family
						) AS a
						WHERE a.MinStatus < 5 OR a.MinStatus IS NULL
						GROUP BY a.Family
					) AS available ON available.Family = families.Id ";

		if (isset($family))
			$cmd .= "WHERE families.Securitygroup = " . $user["DefaultSecurityGroup"] . " AND families.Id =" . $family;
		else
			$cmd .= "WHERE families.Securitygroup = " . $user["DefaultSecurityGroup"];

		$cmd .= ") AS subquery";
		$stmt = $this->getDB()->prepare($cmd);
		$stmt->execute();
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $results;
	}

	// Retrieves family versions
	public function getFamilyVersions($table, $family, $callerInfo = null)
	{
		$cmd = "SELECT versions.*, familyStatus.Key as StatusKey FROM versions left join familyStatus on (versions.Status = familyStatus.id) WHERE Family = :family";
		$cmd .= " " . $this->getSecurityRestriction($table, $callerInfo);
		$stmt = $this->getDB()->prepare($cmd);
		$stmt->execute(array('family' => $family));
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if (count($results) == 0)
			return null;
		else
			return $results;
	}

	// Retrieve the latest version of a family 
	public function getLastVersionFamily($family, $callerInfo = null)
	{
		$cmd = "SELECT * FROM versions WHERE Family = :family ORDER BY versions.Number DESC LIMIT 1";
		$stmt = $this->getDB()->prepare($cmd);
		$stmt->execute(array('family' => $family));
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if (count($results) == 0)
			return null;
		else
			return $results[0];
	}

	// Update the family version
	public function updateFamilyVersion($data, $task)
	{
		if (!isset($data->Id) || $data->Id == null || $data->Id <= 0)
			return null;
		else {
			$valuesArray = array();
			$db = $this->getDB();
			$valuesArray['family'] = $data->Family;

			if ($task == true)
				$query = "UPDATE families SET Version = " . $data->Number . ", UpgradeTask = " . $data->UpgradeTask . " WHERE Family = :family";
			else
				$query = "UPDATE families SET Version = " . $data->Number . " WHERE Family = :family";

			$hash = md5($query);
			if (!isset($this->preparedQueryCache[$hash])) {
				$stmt = $db->prepare($query);
				$this->preparedQueryCache[$hash] = $stmt;
			}

			$this->preparedQueryCache[$hash]->execute($valuesArray);
			if ($silent)
				return true;
			else
				return $this->genericGetById($table, $data->Id);
		}
	}

	// Create upgrade task
	public function createUpgradeTask($version)
	{
		$db = $this->getDB();
		$description = 'Actualizar versión';
		$query = "INSERT INTO tasks (Family, Version, Description) VALUES (" . $version["Family"] . "," . $version["Id"] . ",'" . $description . "')";
		$hash = md5($query);
		$stmt = $db->prepare($query);
		$this->preparedQueryCache[$hash] = $stmt;

		try {
			$insertedId = $db->lastInsertId();

			if ($insertedId > 0)
				return $this->genericGetById('tasks', $insertedId);
			else
				return null;

		} catch (Exception $e) {
			return null;
		}
	}

	// Retrieves the family files for their corresponding version
	public function getFamilyFilesByVersion($version, $start = 0, $limit = 1000, $sortField = null, $sortAsc = null, $filterField = null, $filterValue = null, $callerInfo = null)
	{
		if (!empty($filterField) && !is_array($filterField))
			$filterField = array($filterField);
		if (!empty($filterValue) && !is_array($filterValue))
			$filterValue = array($filterValue);

		if (isset($version)) {
			$cmd = "SELECT f.*, c.Name as CategoryName, counts.DocumentationVersion FROM files f INNER JOIN versions v ON v.id = f.Version INNER JOIN categories c ON c.Id = f.Category";
			$cmd .= " LEFT JOIN (
				SELECT Category, COUNT(*) AS DocumentationVersion
				FROM files
				WHERE Version = " . $version . "
				GROUP BY Category
			) counts ON f.Category = counts.Category
			WHERE f.Created = (
				SELECT MAX(Created)
				FROM files
				WHERE Version = " . $version . "
				AND Category = f.Category
			)
			AND f.Version = " . $version . " ";
		} else
			$cmd .= "SELECT f.*, c.Name as CategoryName FROM files f INNER JOIN versions v ON v.id = f.Version INNER JOIN categories c ON c.Id = f.Category WHERE v.id = f.Version ";

		$cmd .= " " . $this->getSecurityRestriction("files", $callerInfo);
		if (isset($filterField)) {
			for ($i = 0; $i < count($filterField); $i++) {
				$cmd .= " AND f.`" . escape_string($filterField[$i]) . "` ";
				if (strpos($filterValue[$i], ">=") === 0 && is_numeric(str_replace(">=", "", $filterValue[$i])))
					$cmd .= " >= " . str_replace(">=", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "<=") === 0 && is_numeric(str_replace("<=", "", $filterValue[$i])))
					$cmd .= " <= " . str_replace("<=", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "lte|") === 0 && is_numeric(str_replace("lte|", "", $filterValue[$i])))
					$cmd .= " <= " . str_replace("lte|", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], ">") === 0 && is_numeric(str_replace(">", "", $filterValue[$i])))
					$cmd .= " > " . str_replace(">", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "<") === 0 && is_numeric(str_replace("<", "", $filterValue[$i])))
					$cmd .= " < " . str_replace("<", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "lt|") === 0 && is_numeric(str_replace("lt|", "", $filterValue[$i])))
					$cmd .= " < " . str_replace("lt|", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "=") === 0 && is_numeric(str_replace("=", "", $filterValue[$i])))
					$cmd .= " = " . str_replace("=", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "=") === 0 && !is_numeric(str_replace("=", "", $filterValue[$i])))
					$cmd .= " = '" . str_replace("=", "", $filterValue[$i]) . "'";
				else if (strpos($filterValue[$i], "!=") === 0 && !is_numeric(str_replace("!=", "", $filterValue[$i])))
					$cmd .= " <> '" . str_replace("!=", "", $filterValue[$i]) . "'";
				else
					$cmd .= " LIKE '%" . escape_string($filterValue[$i]) . "%'";
			}
		}
		$cmd .= "ORDER BY f.Created DESC";

		if (isset($limit) && is_numeric($limit)) {
			if (!isset($start) || !is_numeric($limit))
				$start = 0;
			$cmd .= " LIMIT " . $start . ", " . $limit . "";
		}
		$stmt = $this->getDB()->prepare($cmd);
		$stmt->execute();
		$response = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if (!isset($response))
			return $this->addCount(array(), $limit, $start);
		else
			return $this->addCount($this->lowerCaseOwner($response), $limit, $start);
	}

	// Retrieve all files by family and version
	public function getFamilyAllFilesByVersion($family, $version, $start = 0, $limit = 1000, $sortField = null, $sortAsc = null, $filterField = null, $filterValue = null, $callerInfo = null)
	{
		if (!empty($filterField) && !is_array($filterField))
			$filterField = array($filterField);
		if (!empty($filterValue) && !is_array($filterValue))
			$filterValue = array($filterValue);

		$cmd = "SELECT f.*, u.Name as OwnerName FROM files f INNER JOIN users u ON u.Id = f.Owner";
		$cmd .= " WHERE f.Family = " . $family . " AND f.Version = " . $version . " ";

		$cmd .= " " . $this->getSecurityRestriction("files", $callerInfo);
		if (isset($filterField)) {
			for ($i = 0; $i < count($filterField); $i++) {
				$cmd .= " AND f.`" . escape_string($filterField[$i]) . "` ";
				if (strpos($filterValue[$i], ">=") === 0 && is_numeric(str_replace(">=", "", $filterValue[$i])))
					$cmd .= " >= " . str_replace(">=", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "<=") === 0 && is_numeric(str_replace("<=", "", $filterValue[$i])))
					$cmd .= " <= " . str_replace("<=", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "lte|") === 0 && is_numeric(str_replace("lte|", "", $filterValue[$i])))
					$cmd .= " <= " . str_replace("lte|", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], ">") === 0 && is_numeric(str_replace(">", "", $filterValue[$i])))
					$cmd .= " > " . str_replace(">", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "<") === 0 && is_numeric(str_replace("<", "", $filterValue[$i])))
					$cmd .= " < " . str_replace("<", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "lt|") === 0 && is_numeric(str_replace("lt|", "", $filterValue[$i])))
					$cmd .= " < " . str_replace("lt|", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "=") === 0 && is_numeric(str_replace("=", "", $filterValue[$i])))
					$cmd .= " = " . str_replace("=", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "=") === 0 && !is_numeric(str_replace("=", "", $filterValue[$i])))
					$cmd .= " = '" . str_replace("=", "", $filterValue[$i]) . "'";
				else if (strpos($filterValue[$i], "!=") === 0 && !is_numeric(str_replace("!=", "", $filterValue[$i])))
					$cmd .= " <> '" . str_replace("!=", "", $filterValue[$i]) . "'";
				else
					$cmd .= " LIKE '%" . escape_string($filterValue[$i]) . "%'";
			}
		}

		if (isset($sortField)) {
			$cmd .= " ORDER BY  `" . escape_string($sortField) . "` " . ((escape_string($sortAsc) == 1) ? " ASC" : " DESC");
		}

		if (isset($limit) && is_numeric($limit)) {
			if (!isset($start) || !is_numeric($limit))
				$start = 0;
			$cmd .= " LIMIT " . $start . ", " . $limit . "";
		}
		$stmt = $this->getDB()->prepare($cmd);
		$stmt->execute();
		$response = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if (!isset($response))
			return $this->addCount(array(), $limit, $start);
		else
			return $this->addCount($this->lowerCaseOwner($response), $limit, $start);
	}

	// Retrieves categories
	public function getCategories($family, $forFamilies, $callerInfo = null)
	{
		$cmd = "SELECT * FROM categories WHERE ForFamilies = " . $forFamilies;
		if (isset($family))
			$cmd .= " OR (EspecificFamily = " . $family . " AND ForFamilies = " . $forFamilies . ") ";
		$cmd .= " " . $this->getSecurityRestriction('categories', $callerInfo);
		$stmt = $this->getDB()->prepare($cmd);
		$stmt->execute();
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if (count($results) == 0)
			return null;
		else
			return $results;
	}

	// Retrieve total files by category and family
	public function recoverTotalFilesByCategoryAndFamily($family, $version, $callerInfo = null)
	{
		$cmd = "SELECT c.Id, c.Name, COUNT(f.Id) AS filesTotal
			FROM categories c
			LEFT JOIN files f ON c.Id = f.Category AND f.Family = " . $family . " AND f.Version = " . $version . "
			WHERE c.Mandatory = 1
			GROUP BY c.Id, c.Name";

		$stmt = $this->getDB()->prepare($cmd);
		$stmt->execute();
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if (count($results) == 0)
			return null;
		else
			return $results;
	}

	// Retrieve the code of the last asset in the family
	public function getLastAsset($family)
	{
		$cmd = "SELECT a.Code from assets a where Family = " . $family . " ORDER BY Code DESC LIMIT 1";
		$stmt = $this->getDB()->prepare($cmd);
		$stmt->execute();
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $results;
	}

	// Returns the following asset code of the asset to be registered for the family
	public function getNextAsset($family)
	{
		$cmd = "SELECT a.Code from assets a where Family = " . $family . " ORDER BY Code DESC LIMIT 1";
		$stmt = $this->getDB()->prepare($cmd);
		$stmt->execute();
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if (count($results) == 0) {
			$cmd = "SELECT f.Key from families f where f.Id = " . $family;
			$stmt = $this->getDB()->prepare($cmd);
			$stmt->execute();
			$newResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
			if (count($newResult) == 0)
				return null;
			else
				return $newResult[0]["Key"] . '-000';

		} else {
			// Separate code and numbering
			list($code, $numbering) = explode('-', $results[0]["Code"]);

			// Increment numbering and formatting with leading zeros
			$newNumber = sprintf('%03d', $numbering + 1);

			// Build the new identifier
			$newIdentifier = $code . '-' . $newNumber;

			return $newIdentifier;
		}
	}

	// Retrieves family tasks by version
	public function getFamilyTasksByVersion($family, $version, $start = 0, $limit = 1000, $sortField = null, $sortAsc = null, $filterField = null, $filterValue = null, $callerInfo = null)
	{
		if (!empty($filterField) && !is_array($filterField))
			$filterField = array($filterField);
		if (!empty($filterValue) && !is_array($filterValue))
			$filterValue = array($filterValue);

		$cmd = "SELECT t.* FROM tasks t";
		$cmd .= " WHERE t.Family = " . $family . " AND t.Version = " . $version . " ";

		$cmd .= " " . $this->getSecurityRestriction("tasks", $callerInfo);
		if (isset($filterField)) {
			for ($i = 0; $i < count($filterField); $i++) {
				$cmd .= " AND f.`" . escape_string($filterField[$i]) . "` ";
				if (strpos($filterValue[$i], ">=") === 0 && is_numeric(str_replace(">=", "", $filterValue[$i])))
					$cmd .= " >= " . str_replace(">=", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "<=") === 0 && is_numeric(str_replace("<=", "", $filterValue[$i])))
					$cmd .= " <= " . str_replace("<=", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "lte|") === 0 && is_numeric(str_replace("lte|", "", $filterValue[$i])))
					$cmd .= " <= " . str_replace("lte|", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], ">") === 0 && is_numeric(str_replace(">", "", $filterValue[$i])))
					$cmd .= " > " . str_replace(">", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "<") === 0 && is_numeric(str_replace("<", "", $filterValue[$i])))
					$cmd .= " < " . str_replace("<", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "lt|") === 0 && is_numeric(str_replace("lt|", "", $filterValue[$i])))
					$cmd .= " < " . str_replace("lt|", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "=") === 0 && is_numeric(str_replace("=", "", $filterValue[$i])))
					$cmd .= " = " . str_replace("=", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "=") === 0 && !is_numeric(str_replace("=", "", $filterValue[$i])))
					$cmd .= " = '" . str_replace("=", "", $filterValue[$i]) . "'";
				else if (strpos($filterValue[$i], "!=") === 0 && !is_numeric(str_replace("!=", "", $filterValue[$i])))
					$cmd .= " <> '" . str_replace("!=", "", $filterValue[$i]) . "'";
				else
					$cmd .= " LIKE '%" . escape_string($filterValue[$i]) . "%'";
			}
		}

		if (isset($sortField)) {
			$cmd .= " ORDER BY  `" . escape_string($sortField) . "` " . ((escape_string($sortAsc) == 1) ? " ASC" : " DESC");
		}

		if (isset($limit) && is_numeric($limit)) {
			if (!isset($start) || !is_numeric($limit))
				$start = 0;
			$cmd .= " LIMIT " . $start . ", " . $limit . "";
		}
		$stmt = $this->getDB()->prepare($cmd);
		$stmt->execute();
		$response = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if (!isset($response))
			return $this->addCount(array(), $limit, $start);
		else
			return $this->addCount($this->lowerCaseOwner($response), $limit, $start);
	}

	// Retrieve family upgrade task by version
	public function getFamilyUpgradeTaskByVersion($family, $version)
	{
		$cmd = "SELECT tasks.* FROM tasks 
		LEFT JOIN versions ON versions.Id = tasks.Version 
		LEFT JOIN families ON families.Id = tasks.Family 
		WHERE versions.UpgradeTask = tasks.Id AND tasks.Family = " . $family . " AND tasks.Version = " . $version;
		$stmt = $this->getDB()->prepare($cmd);
		$stmt->execute();
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if (count($results) == 0)
			return null;
		else
			return $results[0];
	}

	// Retrieve family checklists by version
	public function getFamilyCheckListsByVersion($family, $version, $start = 0, $limit = 1000, $sortField = null, $sortAsc = null, $filterField = null, $filterValue = null, $callerInfo = null)
	{
		if (!empty($filterField) && !is_array($filterField))
			$filterField = array($filterField);
		if (!empty($filterValue) && !is_array($filterValue))
			$filterValue = array($filterValue);

		$cmd = "SELECT c.*, cat.Name as CategoryName, counts.DocumentationVersion, f.Name as familyName, u.Name as ownerName, cat.Name as categoryName FROM checklists c 
		INNER JOIN versions v ON v.id = c.familyversion 
		INNER JOIN checklistscategories cat ON cat.Id = c.category
		INNER JOIN users u on u.Id = c.owner
		INNER JOIN families f on (f.Id = c.family)
		LEFT JOIN (
			SELECT category, COUNT(*) AS DocumentationVersion
			FROM checklists
			WHERE familyversion = " . $version . "
			GROUP BY category
		) counts ON c.category = counts.category
		WHERE c.created = (
			SELECT MAX(created)
			FROM checklists
			WHERE familyversion = " . $version . "
			AND category = c.category
		)
		AND c.familyversion = " . $version . " AND c.family = " . $family . " ";

		$cmd .= " " . $this->getSecurityRestriction("checklists", $callerInfo);
		if (isset($filterField)) {
			for ($i = 0; $i < count($filterField); $i++) {
				$cmd .= " AND f.`" . escape_string($filterField[$i]) . "` ";
				if (strpos($filterValue[$i], ">=") === 0 && is_numeric(str_replace(">=", "", $filterValue[$i])))
					$cmd .= " >= " . str_replace(">=", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "<=") === 0 && is_numeric(str_replace("<=", "", $filterValue[$i])))
					$cmd .= " <= " . str_replace("<=", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "lte|") === 0 && is_numeric(str_replace("lte|", "", $filterValue[$i])))
					$cmd .= " <= " . str_replace("lte|", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], ">") === 0 && is_numeric(str_replace(">", "", $filterValue[$i])))
					$cmd .= " > " . str_replace(">", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "<") === 0 && is_numeric(str_replace("<", "", $filterValue[$i])))
					$cmd .= " < " . str_replace("<", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "lt|") === 0 && is_numeric(str_replace("lt|", "", $filterValue[$i])))
					$cmd .= " < " . str_replace("lt|", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "=") === 0 && is_numeric(str_replace("=", "", $filterValue[$i])))
					$cmd .= " = " . str_replace("=", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "=") === 0 && !is_numeric(str_replace("=", "", $filterValue[$i])))
					$cmd .= " = '" . str_replace("=", "", $filterValue[$i]) . "'";
				else if (strpos($filterValue[$i], "!=") === 0 && !is_numeric(str_replace("!=", "", $filterValue[$i])))
					$cmd .= " <> '" . str_replace("!=", "", $filterValue[$i]) . "'";
				else
					$cmd .= " LIKE '%" . escape_string($filterValue[$i]) . "%'";
			}
		}

		if (isset($sortField)) {
			$cmd .= " ORDER BY  `" . escape_string($sortField) . "` " . ((escape_string($sortAsc) == 1) ? " ASC" : " DESC");
		} else
			$cmd .= " ORDER BY created DESC ";

		if (isset($limit) && is_numeric($limit)) {
			if (!isset($start) || !is_numeric($limit))
				$start = 0;
			$cmd .= " LIMIT " . $start . ", " . $limit . "";
		}
		$stmt = $this->getDB()->prepare($cmd);
		$stmt->execute();
		$response = $stmt->fetchAll(PDO::FETCH_ASSOC);

		//GET DETAILS
		if (!empty($response)) {
			$cmd = "SELECT chkdetails.* FROM chkdetails WHERE chkdetails.checklist = :0";
			$stmt = $this->getDB()->prepare($cmd);
			for ($i = 0; $i < count($response); $i++) {
				$stmt->execute(array($response[$i]['Id']));
				$aditionalInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);
				$response[$i]["details"] = array();
				$response[$i]["details"] = $aditionalInfo;
			}
		}

		if (!isset($response))
			return $this->addCount(array(), $limit, $start);
		else {
			if (isset($checklist))
				return $response[0];
			else
				return $this->addCount($this->lowerCaseOwner($response), $limit, $start);
		}
	}

	// Retrieve all checklists by version and family
	public function getAllCheckListsByFamilyVersion($family, $familyVersion, $category, $start = 0, $limit = 1000, $sortField = null, $sortAsc = null, $filterField = null, $filterValue = null, $callerInfo = null)
	{
		if (!empty($filterField) && !is_array($filterField))
			$filterField = array($filterField);
		if (!empty($filterValue) && !is_array($filterValue))
			$filterValue = array($filterValue);

		$cmd = "SELECT c.*, u.Name as OwnerName FROM checklists c INNER JOIN users u ON u.Id = c.owner";
		$cmd .= " WHERE c.family = " . $family . " AND c.familyversion = " . $familyVersion . " AND c.category = " . $category . " ";

		$cmd .= " " . $this->getSecurityRestriction("checklists", $callerInfo);
		if (isset($filterField)) {
			for ($i = 0; $i < count($filterField); $i++) {
				$cmd .= " AND c.`" . escape_string($filterField[$i]) . "` ";
				if (strpos($filterValue[$i], ">=") === 0 && is_numeric(str_replace(">=", "", $filterValue[$i])))
					$cmd .= " >= " . str_replace(">=", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "<=") === 0 && is_numeric(str_replace("<=", "", $filterValue[$i])))
					$cmd .= " <= " . str_replace("<=", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "lte|") === 0 && is_numeric(str_replace("lte|", "", $filterValue[$i])))
					$cmd .= " <= " . str_replace("lte|", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], ">") === 0 && is_numeric(str_replace(">", "", $filterValue[$i])))
					$cmd .= " > " . str_replace(">", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "<") === 0 && is_numeric(str_replace("<", "", $filterValue[$i])))
					$cmd .= " < " . str_replace("<", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "lt|") === 0 && is_numeric(str_replace("lt|", "", $filterValue[$i])))
					$cmd .= " < " . str_replace("lt|", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "=") === 0 && is_numeric(str_replace("=", "", $filterValue[$i])))
					$cmd .= " = " . str_replace("=", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "=") === 0 && !is_numeric(str_replace("=", "", $filterValue[$i])))
					$cmd .= " = '" . str_replace("=", "", $filterValue[$i]) . "'";
				else if (strpos($filterValue[$i], "!=") === 0 && !is_numeric(str_replace("!=", "", $filterValue[$i])))
					$cmd .= " <> '" . str_replace("!=", "", $filterValue[$i]) . "'";
				else
					$cmd .= " LIKE '%" . escape_string($filterValue[$i]) . "%'";
			}
		}

		if (isset($sortField)) {
			$cmd .= " ORDER BY  `" . escape_string($sortField) . "` " . ((escape_string($sortAsc) == 1) ? " ASC" : " DESC");
		}

		if (isset($limit) && is_numeric($limit)) {
			if (!isset($start) || !is_numeric($limit))
				$start = 0;
			$cmd .= " LIMIT " . $start . ", " . $limit . "";
		}
		$stmt = $this->getDB()->prepare($cmd);
		$stmt->execute();
		$response = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if (!isset($response))
			return $this->addCount(array(), $limit, $start);
		else
			return $this->addCount($this->lowerCaseOwner($response), $limit, $start);
	}

	// Retrieve task files by version
	public function getTasksFilesByVersion($task, $version, $start = 0, $limit = 1000, $sortField = null, $sortAsc = null, $filterField = null, $filterValue = null, $callerInfo = null)
	{
		if (!empty($filterField) && !is_array($filterField))
			$filterField = array($filterField);
		if (!empty($filterValue) && !is_array($filterValue))
			$filterValue = array($filterValue);

		$cmd = "SELECT f.*, c.Name as CategoryName FROM files f LEFT JOIN categories c ON c.Id = f.Category WHERE f.Task = " . $task . " AND f.Version = " . $version . " ";

		$cmd .= " " . $this->getSecurityRestriction("files", $callerInfo);
		if (isset($filterField)) {
			for ($i = 0; $i < count($filterField); $i++) {
				$cmd .= " AND f.`" . escape_string($filterField[$i]) . "` ";
				if (strpos($filterValue[$i], ">=") === 0 && is_numeric(str_replace(">=", "", $filterValue[$i])))
					$cmd .= " >= " . str_replace(">=", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "<=") === 0 && is_numeric(str_replace("<=", "", $filterValue[$i])))
					$cmd .= " <= " . str_replace("<=", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "lte|") === 0 && is_numeric(str_replace("lte|", "", $filterValue[$i])))
					$cmd .= " <= " . str_replace("lte|", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], ">") === 0 && is_numeric(str_replace(">", "", $filterValue[$i])))
					$cmd .= " > " . str_replace(">", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "<") === 0 && is_numeric(str_replace("<", "", $filterValue[$i])))
					$cmd .= " < " . str_replace("<", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "lt|") === 0 && is_numeric(str_replace("lt|", "", $filterValue[$i])))
					$cmd .= " < " . str_replace("lt|", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "=") === 0 && is_numeric(str_replace("=", "", $filterValue[$i])))
					$cmd .= " = " . str_replace("=", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "=") === 0 && !is_numeric(str_replace("=", "", $filterValue[$i])))
					$cmd .= " = '" . str_replace("=", "", $filterValue[$i]) . "'";
				else if (strpos($filterValue[$i], "!=") === 0 && !is_numeric(str_replace("!=", "", $filterValue[$i])))
					$cmd .= " <> '" . str_replace("!=", "", $filterValue[$i]) . "'";
				else
					$cmd .= " LIKE '%" . escape_string($filterValue[$i]) . "%'";
			}
		}

		if (isset($sortField)) {
			$cmd .= " ORDER BY  `" . escape_string($sortField) . "` " . ((escape_string($sortAsc) == 1) ? " ASC" : " DESC");
		}

		if (isset($limit) && is_numeric($limit)) {
			if (!isset($start) || !is_numeric($limit))
				$start = 0;
			$cmd .= " LIMIT " . $start . ", " . $limit . "";
		}
		$stmt = $this->getDB()->prepare($cmd);
		$stmt->execute();
		$response = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if (!isset($response))
			return $this->addCount(array(), $limit, $start);
		else
			return $this->addCount($this->lowerCaseOwner($response), $limit, $start);
	}

	// Saves asset status changes
	public function insertStatusHistory($asset, $oldStatus, $callerInfo)
	{
		$date = date("Y-m-d H:i:s");
		$db = $this->getDB();
		$query = "INSERT INTO statushistory (asset, status, previousstatus, date, user) VALUES (" . $asset["Id"] . "," . $asset["Status"] . "," . $oldStatus . ",'" . $date . "'," . $callerInfo["Id"] . ")";
		$hash = md5($query);
		$stmt = $db->prepare($query);
		$this->preparedQueryCache[$hash] = $stmt;

		try {
			$stmt->execute();
			$insertedId = $db->lastInsertId();

			if ($insertedId > 0)
				return $insertedId;
			else
				return null;

		} catch (Exception $e) {
			return null;
		}
	}

	// Retrieve checklist details
	public function getChkDetails($checklist)
	{
		$cmd = "SELECT * from chkdetails where checklist = " . $checklist;
		$stmt = $this->getDB()->prepare($cmd);
		$stmt->execute();
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $results;
	}

	// Retrieve checklists with all your information
	public function getChecklists($checklist, $start = 0, $limit = 1000, $sortField = null, $sortAsc = null, $filterField = null, $filterValue = null, $callerInfo = null)
	{
		if (!empty($filterField) && !is_array($filterField))
			$filterField = array($filterField);
		if (!empty($filterValue) && !is_array($filterValue))
			$filterValue = array($filterValue);

		$cmd = "SELECT checklists.*, checklistscategories.Name as categoryName FROM checklists";
		$cmd .= " left join checklistscategories on (checklistscategories.Id = checklists.category) ";
		if (isset($checklist))
			$cmd .= "WHERE checklists.Id = " . $checklist . " ";

		$cmd .= " " . $this->getSecurityRestriction("checklists", $callerInfo);
		if (isset($filterField)) {
			for ($i = 0; $i < count($filterField); $i++) {
				$cmd .= " AND checklists.`" . escape_string($filterField[$i]) . "` ";
				if (strpos($filterValue[$i], ">=") === 0 && is_numeric(str_replace(">=", "", $filterValue[$i])))
					$cmd .= " >= " . str_replace(">=", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "<=") === 0 && is_numeric(str_replace("<=", "", $filterValue[$i])))
					$cmd .= " <= " . str_replace("<=", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "lte|") === 0 && is_numeric(str_replace("lte|", "", $filterValue[$i])))
					$cmd .= " <= " . str_replace("lte|", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], ">") === 0 && is_numeric(str_replace(">", "", $filterValue[$i])))
					$cmd .= " > " . str_replace(">", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "<") === 0 && is_numeric(str_replace("<", "", $filterValue[$i])))
					$cmd .= " < " . str_replace("<", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "lt|") === 0 && is_numeric(str_replace("lt|", "", $filterValue[$i])))
					$cmd .= " < " . str_replace("lt|", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "=") === 0 && is_numeric(str_replace("=", "", $filterValue[$i])))
					$cmd .= " = " . str_replace("=", "", $filterValue[$i]);
				else if (strpos($filterValue[$i], "=") === 0 && !is_numeric(str_replace("=", "", $filterValue[$i])))
					$cmd .= " = '" . str_replace("=", "", $filterValue[$i]) . "'";
				else if (strpos($filterValue[$i], "!=") === 0 && !is_numeric(str_replace("!=", "", $filterValue[$i])))
					$cmd .= " <> '" . str_replace("!=", "", $filterValue[$i]) . "'";
				else
					$cmd .= " LIKE '%" . escape_string($filterValue[$i]) . "%'";
			}
		}

		if (isset($sortField)) {
			$cmd .= " ORDER BY  `" . escape_string($sortField) . "` " . ((escape_string($sortAsc) == 1) ? " ASC" : " DESC");
		}

		if (isset($limit) && is_numeric($limit)) {
			if (!isset($start) || !is_numeric($limit))
				$start = 0;
			$cmd .= " LIMIT " . $start . ", " . $limit . "";
		}
		$stmt = $this->getDB()->prepare($cmd);
		$stmt->execute();
		$response = $stmt->fetchAll(PDO::FETCH_ASSOC);

		//GET DETAILS
		if (!empty($response)) {
			$cmd = "SELECT chkdetails.* FROM chkdetails WHERE chkdetails.checklist = :0";
			$stmt = $this->getDB()->prepare($cmd);
			for ($i = 0; $i < count($response); $i++) {
				$stmt->execute(array($response[$i]['Id']));
				$aditionalInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);
				$response[$i]["details"] = array();
				$response[$i]["details"] = $aditionalInfo;
			}
		}

		if (!isset($response))
			return $this->addCount(array(), $limit, $start);
		else {
			if (isset($checklist))
				return $response[0];
			else
				return $this->addCount($this->lowerCaseOwner($response), $limit, $start);
		}
	}

	// Copy the checklist and its details 
	public function copyChecklistAndDetails($checklist, $callerInfo)
	{
		//CREATE CHECKLIST
		$insertData = new stdClass();
		$insertData->category = $checklist["category"];
		$insertData->created = date("Y-m-d H:i:s");
		$insertData->family = $checklist["family"];
		$insertData->owner = $callerInfo["Id"];
		$insertData->familyversion = $checklist["familyversion"];
		$newChecklist = $this->genericInsert("checklists", $insertData);
		if (isset($newChecklist)) {
			for ($i = 0; $i < count($checklist["details"]); $i++) {
				$detail = $checklist["details"][$i];
				//CREATE CHECKLIST DETAIL
				$insertChkDetail = new stdClass();
				$insertChkDetail->checklist = $newChecklist["Id"];
				$insertChkDetail->name = $detail["name"];
				$insertChkDetail->description = $detail["description"];
				$insertChkDetail->requiresdata = $detail["requiresdata"];
				$insertChkDetail->requiresfile = $detail["requiresfile"];
				$insertChkDetail->datatitle = $detail["datatitle"];
				$insertChkDetail->filetitle = $detail["filetitle"];
				$newChkDetail = $this->genericInsert("chkdetails", $insertChkDetail);
			}
		}
		return $this->getChecklists($newChecklist["Id"], null . null, null, null, null, null, $callerInfo);
	}

	// Deleting a checklist by passing an id
	public function checklistDelete($id, $callerInfo = null)
	{
		$this->getDB()->beginTransaction();
		$cmd = "DELETE FROM chkdetails WHERE checklist = :id";
		$stmt = $this->getDB()->prepare($cmd);
		$stmt->execute(array('id' => $id));
		$cmd = "DELETE FROM checklists WHERE id = :id";
		$stmt = $this->getDB()->prepare($cmd);
		$stmt->execute(array('id' => $id));
		$this->getDB()->commit();
		$result = new stdClass();
		$result->success = true;
		$result->deletedId = $id;
		return $result;
	}

	// Retrieve the checklists executed for an asset
	public function getCheckExecsByAsset($assetId, $callerInfo, $viewDetails)
	{
		$db = $this->getDB();
		$stmt = $db->prepare('select chkexecs.*, users.Name as userName from chkexecs left join users on (users.Id = chkexecs.user) where asset = ' . $assetId . ' ORDER BY date DESC');
		$stmt->execute();
		$response = $stmt->fetchAll(PDO::FETCH_ASSOC);

		//GET DETAILS
		if (!empty($response) && !empty($viewDetails) && $viewDetails == true) {
			$cmd = "SELECT chkexecdetails.* FROM chkexecdetails WHERE chkexecdetails.chkexec = :0";
			$stmt = $this->getDB()->prepare($cmd);
			for ($i = 0; $i < count($response); $i++) {
				$stmt->execute(array($response[$i]['Id']));
				$aditionalInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);
				$response[$i]["details"] = array();
				$response[$i]["details"] = $aditionalInfo;
			}
		}

		if (isset($response) && count($response) > 0)
			return $response;
		else
			return null;
	}

	// Retrieve the file of a checklist executed by passing the Id of the detail
	public function getChkExecDetailFile($recordId, $callerInfo)
	{
		$db = $this->getDB();
		$cmd = "SELECT files.*, categories.Name as CategoryName FROM files";
		$cmd .= " left join categories on (categories.Id = files.Category) left join chkexecdetails on (chkexecdetails.id = files.ChkExecDetail) WHERE chkexecdetails.Id = " . $recordId . " ";
		$cmd .= " " . $this->getSecurityRestriction("files", $callerInfo);

		$stmt = $db->prepare($cmd);
		$stmt->execute();
		$response = $stmt->fetchAll(PDO::FETCH_ASSOC);

		if (isset($response))
			return $response;
		else
			return null;
	}

}
?>