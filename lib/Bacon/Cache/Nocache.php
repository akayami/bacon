<?php
namespace Bacon\Cache;

use Bacon\Cache;

class Nocache extends Base {

	public function put($key, $value, $TTL = null, $realTTL = null) {
		return true;
	}

	public function get($key, callable $callback = null, $TTL = null, $realTTL = null) {
		return (isset($callback) ? $callback() : null);
	}

	public function delete($key) {
		return true;
	}

}