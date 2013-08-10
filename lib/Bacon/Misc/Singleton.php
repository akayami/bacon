<?php
namespace Bacon\Misc;

/**
 *
 * @author tomasz
 */

Trait Singleton {

	protected static $_instance;

	/**
	 * @return self
	 */
	public static function getInstance() {
		if(!isset(static::$_instance)) {
			static::$_instance = static::_instance();
		}
		return static::$_instance;
	}

	protected static function _instance() {
		return new static();
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