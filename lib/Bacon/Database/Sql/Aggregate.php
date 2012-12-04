<?php
namespace Bacon\Database\Sql;
use Bacon\Database\Sql\Expression;

class Aggregate extends Field {
	
	public function __construct($field = null) {
		if(strlen($field)) $this->parseFieldName($field);
	}
	
	public function parseStructure(array $expr) {
		if($expr['type'] != 'expression') {
			throw new \Exception('Unknown Expression Type: '.$expr['type']);
		}
		$this->parseFieldName($expr['base_expr']);
		
	}
	
}