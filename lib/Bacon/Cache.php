<?php
namespace Bacon;

interface Cache {

	/**
	 * 
	 * @param string $key
	 * @param mixed $value
	 * @param int $TTL
	 * @param int $realTTL
	 */
	public function put($key, $value, $TTL = null, $realTTL = null);

	/**
	 * 
	 * @param string $key
	 * @param callable $callback
	 * @param int $TTL
	 * @param int $realTTL
	 */
	public function get($key, callable $callback, $TTL = null, $realTTL = null);

	/**
	 * 
	 * @param string $key
	 */
	public function delete($key);

}