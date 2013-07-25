<?php
namespace Bacon\Cache;

use Bacon\Cache;
use Bacon\Redis\Cluster;

class Redis extends Base {

	/**
	 *
	 * @var Cluster
	 */
	protected $redisCluster;

	public function __construct(Cluster $redisCluster, $TTL = null, $realTTL = null, $refreshEnthropy = null, $useDynamicRefreshEntropy = true) {
		$this->redisCluster = $redisCluster;
		if(is_int($TTL)) $this->TTL = $TTL;
		if(is_int($realTTL)) $this->realTTL = $realTTL;
		if(is_int($refreshEnthropy)) $this->$refreshEnthropy = $refreshEnthropy;
		$this->useDynamicRefreshEntropy = ($useDynamicRefreshEntropy ? true : false);
	}

	/** 
	 * 
	 * 
	 * (non-PHPdoc)
	 * @see \Bacon\Cache::put()
	 */
	public function put($key, $value, $TTL = null, $realTTL = null) {				
		return $this->redisCluster->master()->setex(
				$this->keyHash($key),
				$this->getRealTTL($TTL, $realTTL), 
				serialize(array('p' => $value, 'ttl' => time() +  $this->getTTL($TTL)))
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see \Bacon\Cache::get()
	 */
	public function get($key, callable $callback, $TTL = null, $realTTL = null) {
		if($val = $this->redisCluster->slave()->get($this->keyHash($key))) {
			$val = unserialize($val);
			if(isset($val['p']) && isset($val['ttl'])) {
				if($val['ttl'] < time()) {
					$ok = false;
					$c = 0;
					if($this->useDynamicRefreshEntropy) {
						$lastKey = md5($key.'_cnt_'.mktime(date('H'), date('i') - 1, 0));
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
					$key_cnt = md5($key.'_cnt_'.mktime(date('H'), date('i'), 0));
					if(!apc_add($key_cnt, 1)) {
						$v = apc_inc($key_cnt);
					}
				}
				return $val['p'];
			}
		}
		$result = $callback();
		$this->put($key, $result, $TTL, $realTTL);
		return $result;
	}

	/**
	 * (non-PHPdoc)
	 * @see \Bacon\Cache::delete()
	 */
	public function delete($key) {
		return $this->redisCluster->master()->delete($this->keyHash($key));
	}
}