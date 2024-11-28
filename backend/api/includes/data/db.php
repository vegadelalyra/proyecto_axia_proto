<?php
include_once('MySQLProxy.php');
class DbProxyBuilder {
	public static function getProxy() {
		return new MySQLProxy("gmao","localhost:3306","root","");
	}
}
?>