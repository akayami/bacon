<?php
namespace Bacon\Http;

use Bacon\Misc\ArrayObject;

class Request extends ArrayObject {

	protected static $instance;
	protected $uri = array();
	protected $isUriSet = false;

	/**
	 * @return self
	 */
	static public function getInstance() {
		if(!isset(static::$instance)) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	protected function __construct() {
		$this->__data = array_merge($_SERVER, $_POST, $_GET, $_COOKIE, $_REQUEST, $this->uri, $this->__data);
	}

	public function __clone() {
		throw new \Exception('Cannot clone a singleton:'.get_called_class());
	}

	public function __wakeup() {
		throw new \Exception('Unserializing is not allowed for singleton:'.get_called_class());
	}

	/**
	 * Flatterns a multi-level array into: key[sub1][sub2][sub3] = $val
	 * Returns a flat array that can be piped into query string
	 *
	 * @param array $data
	 * @return array
	 */
	public function queryStringFlattern(array $data = array()) {
		$output = array();
		foreach($data as $key => $val) {
			if(is_array($val)) {
				foreach($this->readSub($val) as $item => $val) {
					$output[$key.$item] = $val;
				}
			} else {
				$output[$key] = $val;
			}
		}
		return $output;
	}

	private function readSub(array $data) {
		$output = array();
		foreach($data as $key => $val) {
			if(is_array($val)) {
				$sub = $this->readSub($val);
				foreach($sub as $subkey => $val) {
					$output['['.$key.']'.$subkey] = $val;
				}
			} else {
				$output['['.$key.']'] = $val;
			}
		}
		return $output;
	}

	public function queryString(array $replacements = array()) {
		$d = $this->queryStringFlattern($replacements);
		$out = array();
		foreach($d as $key => $val) {
			$out[] = $key.'='.urldecode($val);
		}
		return htmlentities(implode('&', $out));
	}

	/**
	 *
	 * @param string $key
	 * @param string $source
	 */
	public function has($key, $source = null) {

		switch($source) {
			case 'request':
				return isset($_REQUEST[$key]);
				break;
			case 'get':
				return isset($_GET[$key]);
				break;
			case 'post':
				return isset($_POST[$key]);
				break;
			case 'cookie':
				return isset($_COOKIE[$key]);
				break;
			case 'uri':
				return isset($this->uri[$key]);
				break;
			default:
				return isset($this->__data[$key]);
		}
	}

	/**
	 *
	 * @param string $key
	 * @param string $default
	 * @param string $source
	 */
	public function get($key, $default = null, $source = null, $sanitize = true) {
		if($this->has($key, $source)) {
			switch($source) {
				case 'request':
					$out = $_REQUEST[$key];
					break;
				case 'get':
					$out = $_GET[$key];
					break;
				case 'post':
					$out = $_POST[$key];
					break;
				case 'cookie':
					$out = $_COOKIE[$key];
					break;
				case 'uri':
					$out = $this->uri[$key];
					break;
				default:
					$out = $this->__data[$key];
			}
			return ($sanitize) ? $this->recursiveSanitize($out) : $out;
		} else {
			return $default;
		}
	}

	/**
	 *
	 * @param mixed $var
	 * @return string
	 */
	private function recursiveSanitize($var) {
		if(is_array($var)) {
			foreach($var as $key => $val) {
				$var[$key] = $this->recursiveSanitize($val);
			}
		} else {
			if(!is_object($var)) {
				$var = strip_tags(trim($var));
			}
		}
		return $var;
	}

	/**
	 * (non-PHPdoc)
	 * @see \Bacon\Misc\ArrayObject::offsetGet()
	 */
	public function offsetGet($offset) {
		return $this->get($offset, null, null, true);
	}

	/**
	 *
	 * @param string $key
	 * @param string $value
	 * @param string $source
	 * @param boolean $overwriteURI
	 * @throws Exception
	 */
	public function set($key, $value, $source = null, $overwriteURIBlock = false) {
		switch($source) {
			case 'uri':
				if(!$overwriteURIBlock) {
					throw new \Exception('Cannot set uri type');
				} else {
					$this->uri[$key] = $value;
					$this->__data[$key] = $value;
				}
				break;
			case 'request':
				throw new \Exception('Cannot set request type');
			case 'get':
				throw new \Exception('Cannot set get type');
			case 'post':
				throw new \Exception('Cannot set post type');
			case 'cookie':
				throw new \Exception('Cannot set cookie type');
			default:
				$this->__data[$key] = $value;
		}
	}

	public function setURI(array $uri = array()) {
		if($this->isUriSet === true) {
			throw new \Exception('uri namespace already set');
		} else {
			$this->uri = $uri;
		}
		$this->__data = array_merge($_POST, $_GET, $_COOKIE, $_REQUEST, $this->uri, $this->__data);
	}
}