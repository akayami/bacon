<?php
namespace Bacon\Cache;
use Bacon\Cache;

abstract class Base implements Cache {

	protected $TTL = 60; // Default TTL - Time after which the value should be replaced
	protected $realTTL = 120; // Default realTTL - Time for which the value will be stored
	protected $refreshEnthropy = 10;
	protected $useDynamicRefreshEntropy = true;

	/**
	 * 
	 * @param string $TTL
	 * @param string $realTTL
	 * @return Ambigous <string, number>
	 */
	protected function getRealTTL($TTL = null, $realTTL = null) {
		return 
		(is_null($realTTL)
			? (!is_null($TTL) ? 2 * $TTL : $this->realTTL) // IF realTTL is not provided and TTL is, realTTL is auto-calculated as half the TTL time
			: $realTTL
		);
	}
	
	/**
	 * 
	 * @param string $TTL
	 * @return Ambigous <unknown, number>
	 */
	protected function getTTL($TTL = null) {
		return (is_null($TTL) ? $this->TTL : $TTL);
	}
	
	protected function keyHash($key) {
		return md5($key);
	}
}