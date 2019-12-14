<?php 

require 'config.php';
//-------------------------------------------
$db = new mysqli(
	$connection[server], 
	$connection[user], 
	$connection[password], 
	$connection[database]
);

if ($db->connect_errno) {
    exit("Connection error\n");
}

$db->set_charset("utf8");
$table = $connection[table];

$getTable = $db->query("show tables 
						  from $connection[database] 
						  like '$connection[table]'");
if ($getTabl->num_rows == 0) {
	exit("Error: no such table exists '$table'");
}
//-------------------------------------------
function create($table, $data) {
	$keys = [];
	$values = [];

	foreach ($data as $key => $value) {
		array_push($keys , $key);
		array_push($values , "'".$value."'");
	}

	$keysStr = join(', ', $keys);
	$valuesStr = join(', ', $values);
	return "insert into $table($keysStr) 
					values($valuesStr)";
}
//-------------------------------------------
function read($table, $condition) {
	if (count($_GET) == 0) {
		return "select * from $table";
	} else {
		$conditionString = createCondition($table, $condition);

	return "select * from $table 
					where $conditionString";
	}
}
//-------------------------------------------
function update($table, $data, $condition) {
	$attr = [];

	foreach ($data as $k => $v) {
		array_push($attr , $k." = '".$v."'");
	}

	$attrString = join(', ', $attr);
	$conditionString = createCondition($table, $condition);

	return "update $table set $attrString 
					where $conditionString";
}
//-------------------------------------------
function delete($table, $condition) {
	$conditionString = createCondition($table, $condition);
	return "delete from $table 
					where $conditionString";
}
//-------------------------------------------
function createRow($data) {
	$arr = [];
	foreach ($data as $k => $v) {
		$arr = array_merge($arr, array($k => $v));
	}
	return $arr;
}
//-------------------------------------------
function createCondition($table, $attr) {
	parse_str($attr, $attr);
	$condition = [];
	global $db;
	$columns = [];
	$result = $db->query("show columns from $table");

	while ($col = $result->fetch_assoc()) {
		array_push($columns, $col[Field]);
	}

	foreach ($attr as $k => $v) {
		try {
			if (is_bool(array_search($k, $columns))) {
				throw new Exception("Column $k is not valid\n");
			}
		} catch (Exception $ex) {
			echo $ex->getMessage();
		}

		if ($v == 'null') {
			array_push($condition, "$k is null or $k like 'null'");
		} else {
			array_push($condition, "$k = '$v'");
		}
	}

	return join(' and ', $condition);
}
//-------------------------------------------
$arr = [];
//-------------------------------------------
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
	//-------------------------------------------
	$result = $db->query(read($table, $_SERVER['QUERY_STRING']));

	while ($row = $result->fetch_assoc()) {
		array_push($arr, createRow($row));
	}

	exit(json_encode($arr, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
//-------------------------------------------
else if ($_SERVER['REQUEST_METHOD'] == "POST") {
	//-------------------------------------------
	$postData = file_get_contents('php://input');
	$data = json_decode($postData, true);
	
	if (is_null($data)) {
		$data = $_POST;
	}
	if (count($data) > 0) {
		$insert = $db->query(create($table, $data));	
	}
	else {
		exit("Error\n");
	}

	if($insert)
		exit("Success\n");
	else
		exit("Error\n");
}
//-------------------------------------------
else if ($_SERVER['REQUEST_METHOD'] == "PUT") {
	//-------------------------------------------
	$postData = file_get_contents('php://input');
	$data = json_decode($postData, true);

	if (is_null($data)) {
		parse_str($postData, $data);
	}

	$update = $db->query(update($table, $data, $_SERVER['QUERY_STRING']));

	if ($update)
		exit("Success\n");
	else
		exit("Error\n");	
}
//-------------------------------------------
else if ($_SERVER['REQUEST_METHOD'] == "DELETE") {
	//-------------------------------------------
	$delete = $db->query(delete($table, $_SERVER['QUERY_STRING']));

	if ($delete)
		exit("Success\n");
	else
		exit("Error\n");
}
//-------------------------------------------
?>