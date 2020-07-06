<?php 
//-----------------------------------
// Copyright © 2019 ZipLant 
// https://github.com/ziplant/CRUDapi
//-----------------------------------

require 'scripts/crud.php';
require 'scripts/funcs.php';

$config = json_decode(file_get_contents('config.json'), true);

header("Access-Control-Allow-Origin: *");

$path = getPath();

if (!$path) {
	apiResponse(createResponse("Error", "Incorrect path, expected $root/dbname/tbname/"));
}

$db = new mysqli(
	$config['connection']['server'], 
	$config['connection']['user'], 
	$config['connection']['password'], 
	$path['database']
);

if ($db->connect_errno) {
		apiResponse(createResponse("Error", "Coonection error"));
}

$db->set_charset("utf8");

$getTable = $db->query("show tables from $path[database] like '$path[table]'");

if ($getTable->num_rows == 0) {
	apiResponse(createResponse("Error", "No such table '$path[table]'"));
}							

if (!checkAccess($config['allowed'], $path['database'], $path['table'])) {
	apiResponse(createResponse("Error", "Access denied"));
}

$crud = new CRUD($db, $path['table']);
$result = [];

switch ($_SERVER['REQUEST_METHOD']) {
	case 'GET':
		$read = $db->query($crud->read());

		while ($row = $read->fetch_assoc()) {
			array_push($result, $row);
		}

		exit(apiResponse($result));
		break;
	case 'POST':
		if (!$crud->dataExists()) {
			apiResponse(createResponse("Error", "Missing data"));
		}
		
		$insert = $db->query($crud->create());	

		apiResponse(queryResponse($db, $insert));
		break;
	case 'PUT':
		if (!$crud->dataExists()) {
			apiResponse(createResponse("Error", "Missing data"));	
		}

		$update = $db->query($crud->update());
		
		apiResponse(queryResponse($db, $update));
		break;
	case 'DELETE':
		$delete = $db->query($crud->delete());

		apiResponse(queryResponse($db, $delete));
		break;
}
?>