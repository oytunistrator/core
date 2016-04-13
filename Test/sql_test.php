<?php
include '../src/Bluejacket/Core/Core.php';
include '../src/Bluejacket/Connector/QueryBuilder.php';
include '../src/Bluejacket/Connector/SQLDB.php';

use Bluejacket\Connector\QueryBuilder;
use Bluejacket\Connector\SQLDB;

$dbo = new SQLDB(array(
		"driver"   => "pgsql",
		"server"   => "localhost",
		"database" => "alfred",
		"username" => "postgres",
		"password" => "postgres",
	));

$qb = new QueryBuilder();

$sql = $qb->select(array("name"))->from("test")->where(array(
		"id" => ":id",
	))->prepare();

$res = $dbo->fetchAll($sql, array(":id" => 1));

var_dump($res);