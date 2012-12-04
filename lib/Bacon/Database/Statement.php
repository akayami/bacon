<?php
namespace Bacon\Database;

use Bacon\Database\Adapter\Mysqli\Result;

interface Statement {


	/**
	 * @param $query string
	 * @return boolean
	 */
	public function prepare($query);

	/**
	 * @return boolean
	 */
	public function execute();


	/**
	 *
	 * @param unknown $value
	 * @param string $type
	 * @return static
	 */
	public function bindParam(&$value, $type = null);

	/**
	 * @return Result
	 */
	public function getResult();

}