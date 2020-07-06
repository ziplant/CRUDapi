<?php
class CRUD {
	protected $table, $data, $condition;
	
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
	
	function createCondition($db) {
		parse_str($_SERVER['QUERY_STRING'], $attr);
		$condition = [];
		$columns = [];
		$result = $db->query("show columns from $this->table");

		while ($col = $result->fetch_assoc()) {
			array_push($columns, strtolower($col['Field']));
		}

		foreach ($attr as $k => $v) {
			if (is_bool(array_search(strtolower($k), $columns))) {
				exit("Column '$k' is not valid\n");
			}

			if ($v == 'null') {
				array_push($condition, "$k is null or $k like 'null'");
			} else {
				array_push($condition, "$k = '$v'");
			}
		}

		return join(' and ', $condition);
	}
	
	public function dataExists() {
		return (count($this->data) > 0) ? true : false;
	}
	
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
	
	public function read() {
		if (count($_GET) == 0) {
			return "select * from $this->table";
		} else {
		return "select * from $this->table 
						where $this->condition";
		}
	}
	
	public function update() {
		$attr = [];

		foreach ($this->data as $k => $v) {
			array_push($attr , $k." = '".$v."'");
		}

		$attrString = join(', ', $attr);

		return "update $this->table set $attrString 
						where $this->condition";
	}
	
	public function delete() {
		return "delete from $this->table 
						where $this->condition";
	}
}
?>