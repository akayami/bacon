<?php
namespace Bacon\Database\Adapter\Mysqli;

use Bacon\Database\Statement;

class Statement implements Statement {

	/**
	 *
	 * Enter description here ...
	 * @var mysqli_stmt
	 */
	protected $stmt;

	public function __construct(\mysqli_stmt $stmt) {
		$this->stmt = $stmt;
	}
}