<?php
namespace Bacon\Database\Adapter\Mysqli;

class Statement implements \Bacon\Database\Statement {

	const PARAM_STR = 's';

	/**
	 *
	 * Enter description here ...
	 * @var \mysqli_stmt
	 */
	protected $stmt;

	protected $params = array();
	protected $types;

	public function __construct(\mysqli_stmt $stmt, \mysqli $handle) {
		$this->stmt = $stmt;
		$this->handle = $handle;
	}

	public function prepare($query) {
		return $this->stmt->prepare($query);
	}

	public function getResult() {
		return new Result($this->stmt->get_result(), $this->handle);
	}

	public function execute() {
		if(count($this->params)) {
			$this->mbind_param_do();
		}
		return $this->stmt->execute();
	}

	public function bindParam(&$value, $type = 's') {
		$this->params[] = &$value;
		$this->types .= $type;
	}

	private function mbind_param_do() {
        $params = array_merge(array($this->types), $this->params);
        error_log(print_r($params, true));
        return call_user_func_array(array($this->stmt, 'bind_param'), $this->makeValuesReferenced($params));
    }

	private function makeValuesReferenced($arr){
		$refs = array();
		foreach($arr as $key => $value) {
			$refs[$key] = &$arr[$key];
		}
		return $refs;
	}
}