<?php 
//-----------------------------------
// Copyright © 2019 ZipLant 
// https://github.com/ziplant/CRUDapi
//-----------------------------------
require 'config.php';
require 'crud.php';

header("Access-Control-Allow-Origin: *");

$url = parse_url($_SERVER['REQUEST_URI']);
$root = $_SERVER['PHP_SELF'];
$root = substr_replace($root, '', strrpos($root, '/'));
$path = explode('/', trim(str_replace($root, '', $url['path']), '/'));

$res = [
	"Status" => "",
	"Message" => ""
];

function apiResponse($array) {
	exit(json_encode($array, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n");
}

if (count($path) == 2) {
	$path = [
		"database" => $path[0],
		"table" => $path[1]
	];
} else {
	$res['Status'] = "Error";
	$res['Message'] = "Incorrect path, expected $root/dbname/tbname/";
	apiResponse($res);
}

$db = new mysqli(
	$connection['server'], 
	$connection['user'], 
	$connection['password'], 
	$path['database']
);

if ($db->connect_errno) {
		$res['Status'] = "Error";
		$res['Message'] = "Connection error";
		apiResponse($res);
}

$db->set_charset("utf8");


$getTable = $db->query("show tables 
						  from $path[database] 
							like '$path[table]'");

if ($getTable->num_rows == 0) {
	$res['Status'] = "Error";
	$res['Message'] = "No such table '$path[table]'";
	apiResponse($res);
}							

$access = false;
foreach ($allowed as $allowedDB => $allowedTB) {
	if ($allowedDB == $path['database']) {
		$access = in_array($path['table'], $allowedTB);
	}
}
if (!$access) {
	$res['Status'] = "Error";
	$res['Message'] = "Access denied";
	apiResponse($res);
}


function queryResponse($db, $query) {
	if($query) {
		return [
			"Status" => "Success",
			"Message" => "Query completed"
		];
	}
	else {
		return [
			"Status" => "Error",
			"Message" => $db->error
		];
	}	
}

$crud = new CRUD($db, $path['table']);
$arr = [];

switch ($_SERVER['REQUEST_METHOD']) {
	case 'GET':
		$result = $db->query($crud->read());

		while ($row = $result->fetch_assoc()) {
			array_push($arr, $row);
		}

		exit(apiResponse($arr));
		break;
	case 'POST':
		if (!$crud->dataExists()) {
			$res['Status'] = "Error";
			$res['Message'] = "Missing data";
			apiResponse($res);
		}
		$insert = $db->query($crud->create());	

		apiResponse(queryResponse($db, $insert));
		break;
	case 'PUT':
		if (!$crud->dataExists()) {
			$res['Status'] = "Error";
			$res['Message'] = "Missing data";
			apiResponse($res);	
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