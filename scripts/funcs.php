<?php 

function getPath() {
	$url = parse_url($_SERVER['REQUEST_URI']);
	$root = $_SERVER['PHP_SELF'];
	$root = substr_replace($root, '', strrpos($root, '/'));
	$path = explode('/', trim(str_replace($root, '', $url['path']), '/'));

	if (count($path) == 2) {
		return [
			"database" => $path[0],
			"table" => $path[1]
		];
	} else {
		return null;
	}
}

function apiResponse($array) {
	exit(json_encode($array, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n");
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

function checkAccess($allowed, $db, $tb) {
	$access = false;
	foreach ($allowed as $allowedDB => $allowedTB) {
		if ($allowedDB == $db) {
			$access = in_array($tb, $allowedTB);
		}
	}
	return $access;
}

function createResponse($status, $message) {
	return [
		"Status" => $status,
		"Message" => $message
	];
}

?>