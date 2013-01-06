<?php
namespace Bacon\Cache;

use Bacon\Cache;

class Nocache implements Cache {

	public function put($key, $value, $TTL = null, $realTTL = null) {
		return true;
	}

	public function get($key, $callback) {
		return $callback();
	}

	public function delete($key) {
		return true;
	}

}