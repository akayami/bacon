<?php
namespace Bacon;

interface Cache {

	public function put($key, $value, $TTL = null, $realTTL = null);

	public function get($key, callable $callback, $TTL = null, $realTTL = null);

	public function delete($key);

}