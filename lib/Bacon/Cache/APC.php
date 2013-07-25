<?php
namespace Bacon\Cache;

use Bacon\Cache;

/**
 *
 * @author t_rakowski
 *
 */
class APC extends Base {

	protected $TTL = 60;					// Amount of time the data will be considered stale
	protected $realTTL = 120;				// Total amount of time the will remain in cache
	protected $refreshEnthropy = 10;		// Default refresh frequency
	protected $useDynamicRefreshEntropy = true;

	public function __construct($TTL = null, $realTTL = null, $refreshEnthropy = null, $useDynamicRefreshEntropy = true) {
		if(is_int($TTL)) $this->TTL = $TTL;
		if(is_int($realTTL)) $this->realTTL = $realTTL;
		if(is_int($refreshEnthropy)) $this->$refreshEnthropy = $refreshEnthropy;
		$this->useDynamicRefreshEntropy = ($useDynamicRefreshEntropy ? true : false);
	}

	/**
	 * (non-PHPdoc)
	 * @see \Bacon\Cache::put()
	 */
	public function put($key, $value, $TTL = null, $realTTL = null) {
		return apc_store($this->keyHash($key), array('p' => $value, 'ttl' => time() + $this->getTTL($TTL)), $this->getRealTTL($TTL, $realTTL));
	}

	/**
	 * (non-PHPdoc)
	 * @see \Bacon\Cache::get()
	 */
	public function get($key, callable $callback, $TTL = null, $realTTL = null) {
		$key = md5($key);
		$r = false;
		$val = apc_fetch($this->keyHash($key), $r);
		if(!$r) {
			$result = $callback();
			$this->put($key, $result, $TTL, $realTTL);
			return $result;
		} else {
			if($val['ttl'] < time()) {
				$ok = false;
				$c = 0;
				if($this->useDynamicRefreshEntropy) {
					$lastKey = md5($key.'_cnt_'.mktime(date('H'), date('i') - 1, 0));	// Reads last minue hit count
					$c = apc_fetch($lastKey, $ok);
				}
				$entr = ($ok ? round(($c * 0.05)) : $this->refreshEnthropy);

				if(mt_rand(0, $entr) == $entr) {
					$result = $callback();
					$this->put($key, $result, $TTL, $realTTL);
					return $result;
				}
			}

			if($this->useDynamicRefreshEntropy) {
				$key_cnt = md5($key.'_cnt_'.mktime(date('H'), date('i'), 0));	// Builds current minue hit count
				if(!apc_add($key_cnt, 1)) {
					$v = apc_inc($key_cnt);
				}
			}

			return $val['p'];
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see \Bacon\Cache::delete()
	 */
	public function delete($key) {
		return apc_delete($this->keyHash($key));
	}
}