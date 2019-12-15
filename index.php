<?php 
//-----------------------------------------------------------------------------
require 'config.php';
//-----------------------------------------------------------------------------
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

$getTable = $db->query("show tables 
						  from $connection[database] 
						  like '$connection[table]'");
if ($getTable->num_rows == 0) {
	exit("Error: no such table '$connection[table]'");
}
//-----------------------------------------------------------------------------
class CRUD {
	protected $table, $data, $condition;
	//---------------------------------------------------------------------------
	function __construct($db, $table) {
		$this->table = $table;
		$postData = file_get_contents('php://input');
		$data = json_decode($postData, true);

		if (is_null($data)) {
			parse_str($postData, $data);
		}
		$this->data = $data;
		$this->condition = $this->createCondition($db);
	}
	//---------------------------------------------------------------------------
	function createCondition($db) {
		parse_str($_SERVER['QUERY_STRING'], $attr);
		$condition = [];
		$columns = [];
		$result = $db->query("show columns from $this->table");

		while ($col = $result->fetch_assoc()) {
			array_push($columns, $col[Field]);
		}

		foreach ($attr as $k => $v) {
			try {
				if (is_bool(array_search($k, $columns))) {
					throw new Exception("Column '$k' is not valid\n");
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
	//---------------------------------------------------------------------------
	public function dataExists() {
		return (count($this->data) > 0) ? true : false;
	}
	//---------------------------------------------------------------------------
	public function create() {
		$keys = [];
		$values = [];

		foreach ($this->data as $k => $v) {
			array_push($keys , $k);
			array_push($values , "'".$v."'");
		}

		$keysStr = join(', ', $keys);
		$valuesStr = join(', ', $values);
		return "insert into $this->table(${keysStr}) 
						values($valuesStr)";
	}
	//---------------------------------------------------------------------------
	public function read() {
		if (count($_GET) == 0) {
			return "select * from $this->table";
		} else {
		return "select * from $this->table 
						where $this->condition";
		}
	}
	//---------------------------------------------------------------------------
	public function update() {
		$attr = [];

		foreach ($this->data as $k => $v) {
			array_push($attr , $k." = '".$v."'");
		}

		$attrString = join(', ', $attr);

		return "update $this->table set $attrString 
						where $this->condition";
	}
	//---------------------------------------------------------------------------
	public function delete() {
		return "delete from $this->table 
						where $this->condition";
	}
}
//-----------------------------------------------------------------------------
function createRow($data) {
	$arr = [];
	foreach ($data as $k => $v) {
		$arr = array_merge($arr, array($k => $v));
	}
	return $arr;
}
//-----------------------------------------------------------------------------
function queryResponse($query) {
	if($query)
		exit("Success\n");
	else
		exit("Error\n");
}
//-----------------------------------------------------------------------------
$crud = new CRUD($db, $connection[table]);
$arr = [];
//-----------------------------------------------------------------------------
switch ($_SERVER['REQUEST_METHOD']) {
	//---------------------------------------------------------------------------
	case 'GET':
		if (!$access[read]) {
			exit("Error: access denied\n");
		}
		$result = $db->query($crud->read());

		while ($row = $result->fetch_assoc()) {
			array_push($arr, createRow($row));
		}

		exit(json_encode($arr, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\n");
		break;
	//---------------------------------------------------------------------------
	case 'POST':
		if (!$access[create]) {
			exit("Error: access denied\n");
		}
		if (!$crud->dataExists()) {
			exit("Error: missing data\n");
		}
		$insert = $db->query($crud->create());	

		queryResponse($insert);
		break;
	//---------------------------------------------------------------------------
	case 'PUT':
		if (!$access[update]) {
			exit("Error: access denied\n");
		}
		if (!$crud->dataExists()) {
			exit("Error: missing data\n");	
		}
		$update = $db->query($crud->update());
		
		queryResponse($update);
		break;
	//---------------------------------------------------------------------------
	case 'DELETE':
		if (!$access[delete]) {
			exit("Error: access denied\n");
		}
		$delete = $db->query($crud->delete());

		queryResponse($delete);
		break;
}
//-----------------------------------------------------------------------------
?>