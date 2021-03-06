<?php
namespace Bacon\Database\Result;

use Bacon\Database\Result;

abstract class CommonResult implements Result {

	protected $fetchMode = self::FETCH_ASSOC;

	/**
	 * (non-PHPdoc)
	 * @see \Bacon\Database\Result::fetchMode()
	 */
	public function fetchMode($arg) {
		$this->fetchMode = $arg;
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see \Bacon\Database\Result::fetchMapped()
	 */
	public function fetchMapped() {
		$output = array();
		$fields = array();
		foreach($this->fields() as $field) {
			/** @var $field Bacon\Database\Field */
			$fields[] = array($field->table(), $field->name());
			if(!isset($output[$field->table()])) {
				$output[$field->table()] = array();
			}
		}
		$this->fetchMode(self::FETCH_NUM);
		$iRow = 0;
		while($row = $this->fetch()) {
			for($i = 0; $i < count($row); $i++) {
				$output[$fields[$i][0]][$iRow][$fields[$i][1]] = $row[$i];
			}
			$iRow++;
		}
		return $output;
	}

	public function fetchAllIndexedByCol($col) {
		$output = [];
		$this->fetchMode(self::FETCH_ASSOC);
		while($row = $this->fetch()) {
			if(!isset($row[$col])) {
				throw new \Exception('Failed to fetch. Indicated column not present in result');
			}
			$output[$row[$col]] = $row;
		}
		return $output;
	}

	/**
	 * (non-PHPdoc)
	 * @see \Bacon\Database\Result::fetchCol()
	 */
	public function fetchCol($columnName) {
		$output = array();
		while($row = $this->fetch()) {
			if(isset($row[$columnName])) {
				$output[] = $row[$columnName];
			} else {
				throw new \Exception('Specified column name not found:'.$columnName);
			}
		}
		return $output;
	}

}