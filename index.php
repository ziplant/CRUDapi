<?php 
//-----------------------------------
// Copyright © 2019 ZipLant 
// https://github.com/ziplant/CRUDapi
//-----------------------------------
require 'config.php';
require 'crud.php';

$url = parse_url($_SERVER['REQUEST_URI']);
$root = $_SERVER['PHP_SELF'];
$root = substr_replace($root, '', strrpos($root, '/'));
$path = explode('/', trim(str_replace($root, '', $url['path']), '/'));

if (count($path) == 2) {
	$path = [
		"database" => $path[0],
		"table" => $path[1]
	];
} else {
	exit("Error: incorrect path, expected $root/dbname/tbname/\n");
}

$db = new mysqli(
	$connection['server'], 
	$connection['user'], 
	$connection['password'], 
	$path['database']
);

if ($db->connect_errno) {
    exit("Connection error\n");
}

$db->set_charset("utf8");

$getTable = $db->query("show tables 
						  from $path[database] 
						  like '$path[table]'");
if ($getTable->num_rows == 0) {
	exit("Error: no such table '$path[table]'\n");
}

function createRow($data) {
	$arr = [];
	foreach ($data as $k => $v) {
		$arr = array_merge($arr, array($k => $v));
	}
	return $arr;
}

function queryResponse($db, $query) {
	if($query)
		exit("Success\n");
	else
		exit("Error: " . $db->error . "\n");
}

$crud = new CRUD($db, $path['table']);
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

		queryResponse($db, $insert);
		break;
	case 'PUT':
		if (!$access['update']) {
			exit("Error: access denied\n");
		}
		if (!$crud->dataExists()) {
			exit("Error: missing data\n");	
		}
		$update = $db->query($crud->update());
		
		queryResponse($db, $update);
		break;
	case 'DELETE':
		if (!$access['delete']) {
			exit("Error: access denied\n");
		}
		$delete = $db->query($crud->delete());

		queryResponse($db, $delete);
		break;
}
?>