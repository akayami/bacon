<?php
namespace Bacon;

class Config implements \ArrayAccess {

	protected static $instance;
	protected static $config;

	public function offsetExists($offset) {
		return isset(static::$config[$offset]);
	}

	public function offsetGet($offset) {
		return static::$config[$offset];
	}

	public function offsetUnset($offset) {
		throw new \Exception('Cannot unset data in config');
	}

	public function offsetSet($offset, $value) {
		throw new \Exception('Cannot overwrite config data');
	}

	/**
	 *
	 * @param string $offset
	 * @param string $default
	 * @throws \Exception
	 * @return string
	 */
	public function get($offset, $default = null)
	{
		if ($this->offsetExists($offset))
		{
			return $this->offsetGet($offset);
		}
		elseif ($default !== null)
		{
			return $default;
		}

		throw new \Exception('Requested key does not exist, not-null default not provided');
	}


	/**
	 * @return self
	 */
	static public function getInstance() {
		if(!isset(static::$instance)) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	public static function isInitialized() {
		return !is_null(static::$config);
	}

	private function __construct() {

	}

	public function __clone() {
		throw new \Exception('Cannot clone a singleton:'.get_called_class());
	}

	public function __wakeup() {
		throw new \Exception('Unserializing is not allowed for singleton:'.get_called_class());
	}

	public static function setConfig(array $data) {
		static::$config = $data;
	}

}