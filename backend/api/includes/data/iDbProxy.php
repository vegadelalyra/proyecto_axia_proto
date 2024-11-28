<?php

interface iDbProxy
{
	public function getUser($code);
	public function genericGetElements($table, $start, $limit, $sortField, $sortAsc, $filterField, $filterValue, $callerInfo);
	public function genericGetById($table, $id, $callerInfo);
	public function genericGetByKeyField($table, $field, $id, $callerInfo);
	public function genericInsert($table, $data);
	public function getSecurityRestriction($table, $callerInfo);
	public function genericDelete($table, $id, $callerInfo);
}

?>