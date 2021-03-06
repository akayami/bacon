<?php
namespace Bacon\Cache;

use Bacon\Cache;

/**
 *
 * @author t_rakowski
 *
 */
class Memcached extends Base {

	/**
	 *
	 * @var \Memcached
	 */
	protected $mc;
	protected $TTL = 60;
	protected $realTTL = 120;
	protected $refreshEnthropy = 10;
	protected $useDynamicRefreshEntropy = true;

	public function __construct(\Memcached $memcached, $TTL = null, $realTTL = null, $refreshEnthropy = null, $useDynamicRefreshEntropy = true) {
		$this->mc = $memcached;
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
		return $this->mc->set($this->keyHash($key), array('p' => $value, 'ttl' => time() + $this->getTTL($TTL)), $this->getRealTTL($TTL, $realTTL));
	}

	/**
	 * (non-PHPdoc)
	 * @see \Bacon\Cache::get()
	 */
	public function get($key, callable $callback = null, $TTL = null, $realTTL = null) {
		if(!($val = $this->mc->get($this->keyHash($key)))) {
			if($this->mc->getResultCode() == \Memcached::RES_NOTFOUND) {
				if(isset($callback)) {
					$result = $callback();
					$this->put($key, $result, $TTL, $realTTL);
					return $result;
				} else {
					return null;
				}
			} else {
				throw new \Exception('Unhandled cache condition');
			}
		} else {
			if($val['ttl'] < time() && isset($callback)) {
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
			return (isset($val['p']) ? $val['p'] : null);
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see \Bacon\Cache::delete()
	 */
	public function delete($key) {
		return $this->mc->delete($this->keyHash($key));
	}

}