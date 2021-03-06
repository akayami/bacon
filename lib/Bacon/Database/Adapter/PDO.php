<?php
namespace Bacon\Database\Adapter;

use Bacon\Database\Adapter\PDO\Result;

class PDO extends Abstr {
	
	public $handle;
	/**
	 * 
	 * Enter description here ...
	 * @var Result
	 */
	protected $lastResult;
	
	public function __construct(array $config) {
		$this->handle = new \PDO($this->makeDSN($config), $config['username'], $config['password']);
	}
	
	public function query($query, $buffered = true) {
		$this->handle->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, $buffered);
		$this->lastResult = new Result($this->handle->query($query));
		return $this->lastResult;
	}
	
	public function transaction() {
		$this->handle->beginTransaction();
	}
	
	public function commit() {
		$this->handle->commit();
	}
	
	public function rollback() {
		$this->handle->rollBack();
	}
	
	protected function makeDSN(array $config) {
		return 'mysql:dbname='.$config['dbname'].';host='.$config['hostname'].';port='.$config['port'];
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Bacon\Database\Adapter.Abstr::quote()
	 */
	public function quote($string) {
		return $this->handle->quote($string);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Bacon\Database\Adapter.Abstr::quoteIdentifier()
	 */
	public function quoteIdentifier($string) {
		return $string;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Bacon\Database.Adapter::getLastInsertID()
	 */
	public function getLastInsertID() {
		return $this->handle->lastInsertId();
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Bacon\Database.Adapter::affectedRows()
	 */
	public function affectedRows() {
		return $this->lastResult->affectedRows();
	}
}