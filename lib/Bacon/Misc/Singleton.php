<?php
namespace Bacon\Misc;

/**
 *
 * @author tomasz
 */

Trait Singleton {

	protected static $__instance;

	/**
	 * @return self
	 */
	static public function getInstance() {
		if(!isset(static::$__instance)) {
			static::$__instance = new static();
		}
		return static::$__instance;
	}

	protected function __construct() {

	}

	public function __clone() {
		throw new \Exception('Cannot clone a singleton:'.get_called_class());
	}

	public function __wakeup() {
		throw new \Exception('Unserializing is not allowed for singleton:'.get_called_class());
	}
}