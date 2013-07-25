<?php
namespace Bacon\Cache;

use Bacon\Cache;

class Nocache extends Base {

	public function put($key, $value, $TTL = null, $realTTL = null) {
		return true;
	}

	public function get($key, callable $callback, $TTL = null, $realTTL = null) {
		return $callback();
	}

	public function delete($key) {
		return true;
	}

}