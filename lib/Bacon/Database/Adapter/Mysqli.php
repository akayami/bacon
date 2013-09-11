<?php
namespace Bacon\Database\Adapter;

use Bacon\Database\Adapter\Mysqli\Statement;

use Bacon\Database\Adapter\Exception\Duplicate;

use Bacon\Database\Adapter\Mysqli\Result;

class Mysqli extends Abstr {

	protected $config;

	/**
	 *
	 * Enter description here ...
	 * @var \mysqli
	 */
	protected $__handle;

	protected $__type;

	public function __construct(array $config, $type = 'master') {
		$this->config = $config;
		$this->__type = $type;

	}


	public function prepare($query) {
		return new Statement($this->handle->prepare($query), $this->handle);
	}

	/**
	 *
	 * @param string $key
	 * @throws \Exception
	 * @return \Bacon\Database\Adapter\mysqli
	 */
	public function __get($key) {
		switch($key) {
			case 'handle':
				if(!isset($this->__handle)) {
					$config = $this->config;
					$this->__handle = new \mysqli($config['hostname'], $config['username'], $config['password'], $config['dbname'], $config['port'], $config['socket']);
					if($this->__handle->connect_errno > 0) {
						throw new \Exception($this->__handle->connect_error, $this->__handle->connect_errno);
					}
				}
				$this->handle = $this->__handle;
				return $this->__handle;
				break;
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see \Bacon\Database\Adapter::query()
	 */
	public function query($query, $buffered = true) {
		$res = $this->handle->query($query ,($buffered ? MYSQLI_STORE_RESULT : MYSQLI_USE_RESULT));
		if(is_bool($res)) {
			if($res === false) {
				switch($this->handle->errno) {
					case 1062:
						throw new Duplicate($this->handle->error.":".$query, $this->handle->errno);
						break;
					default:
						throw new \Exception($this->handle->error.":".$query, $this->handle->errno);
						break;
				}
			}
			return $res;
		}
		return new Result($res, $this->handle);
	}

	public function transaction() {
		$this->handle->autocommit(false);
	}

	public function commit() {
		$this->handle->commit();
		$this->handle->autocommit(true);
	}

	public function rollback() {
		$this->handle->rollback();
		$this->handle->autocommit(true);
	}

	/**
	 * (non-PHPdoc)
	 * @see Bacon\Database.Adapter::escape()
	 */
	public function escape($string) {
		return $this->handle->real_escape_string($string);
	}


	/**
	 * (non-PHPdoc)
	 * @see Bacon\Database.Adapter::quote()
	 */
	public function quote($string, $escape = true) {
		return "'" . ($escape ? $this->escape($string) : $string) . "'";
	}

	/**
	 * (non-PHPdoc)
	 * @see Bacon\Database\Adapter.Abstr::quoteIdentifier()
	 */
	public function quoteIdentifier($string) {
		return '`'.$string.'`';
	}

	public function getLastInsertID() {
		return $this->handle->insert_id;
	}

	public function affectedRows() {
		return $this->handle->affected_rows;
	}
}