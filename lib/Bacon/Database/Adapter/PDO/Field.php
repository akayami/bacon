<?php
namespace Bacon\Database\Adapter\PDO;

use Bacon\Database\Field as FieldInterface;

class Field implements FieldInterface {
	
	protected $pdoField;
	
	public function __construct($field) {
		$this->pdoField = $field;
	}
	
	public function name() {
		return $this->pdoField['name'];
	}
	
	public function table() {
		return $this->pdoField['table'];
	}
	
	public function identifier() {
		return strlen($this->table()) > 0 && $this->table() != 'COLUMNS' ? $this->table().'.'.$this->name() : $this->name();
	}
	
}