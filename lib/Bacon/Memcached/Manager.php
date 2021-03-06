<?php
namespace Bacon\Memcached;

use Bacon\Config;

class Manager {

	/**
	 *
	 * @var Manager
	 */
	private static $instance;
	private static $config;
	private static $clusters = array();

	private function __construct() {
		if(!isset(static::$config)) {			// Lazy self-provisioning.
			if(class_exists('\Bacon\Config', true)) {
				static::setConfig(Config::getInstance()['mc']);
			} else {
				global $config;
				static::setConfig($config['mc']);
			}
		}
	}

	public function __clone() {
		throw new \Exception('Cannot clone a singleton:'.get_called_class());
	}

	public function __wakeup() {
		throw new \Exception('Unserializing is not allowed for singleton:'.get_called_class());
	}

	public static function setConfig(array $config) {
		static::$config = $config;
	}

	/**
	 * Enter description here ...
	 *
	 * @return Manager
	 */
	public static function singleton() {
		if(!isset(static::$instance)) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	/**
	 *
	 * Enter description here ...
	 * @param string $cluster
	 * @return \Memcached
	 * @throws Exception
	 */
	public function get($cluster = 'default') {
		if(isset(static::$clusters[$cluster])) {
			return static::$clusters[$cluster];
		}
		if(isset(static::$config[$cluster])) {
			static::$clusters[$cluster] = new \Memcached();
			if(static::$clusters[$cluster]->addServers(static::$config[$cluster])) {
				return static::$clusters[$cluster];
			} else {
				throw new \Exception('Error adding MC cluster '.$cluster.'. Misconfiguration ?');
			}
		} else {
			throw new \Exception('MC Cluster '.$cluster.' not defined!');
		}
	}

}