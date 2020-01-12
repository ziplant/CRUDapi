<?php 
//-----------------------------------
// Copyright © 2019 ZipLant 
// https://github.com/ziplant/CRUDapi
//-----------------------------------
require 'config.php';
require 'crud.php';

$db = new mysqli(
	$connection['server'], 
	$connection['user'], 
	$connection['password'], 
	$connection['database']
);

if ($db->connect_errno) {
    exit("Connection error\n");
}

$db->set_charset("utf8");

$getTable = $db->query("show tables 
						  from $connection[database] 
						  like '$connection[table]'");
if ($getTable->num_rows == 0) {
	exit("Error: no such table '$connection[table]'");
}

$crud = new CRUD($db, $connection['table']);
$arr = [];

switch ($_SERVER['REQUEST_METHOD']) {
	
	case 'GET':
		if (!$access['read']) {
			exit("Error: access denied\n");
		}
		$result = $db->query($crud->read());

		while ($row = $result->fetch_assoc()) {
			array_push($arr, createRow($row));
		}

		exit(json_encode($arr, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\n");
		break;
	
	case 'POST':
		if (!$access['create']) {
			exit("Error: access denied\n");
		}
		if (!$crud->dataExists()) {
			exit("Error: missing data\n");
		}
		$insert = $db->query($crud->create());	

		queryResponse($insert);
		break;
	
	case 'PUT':
		if (!$access['update']) {
			exit("Error: access denied\n");
		}
		if (!$crud->dataExists()) {
			exit("Error: missing data\n");	
		}
		$update = $db->query($crud->update());
		
		queryResponse($update);
		break;
	
	case 'DELETE':
		if (!$access['delete']) {
			exit("Error: access denied\n");
		}
		$delete = $db->query($crud->delete());

		queryResponse($delete);
		break;
}
?>