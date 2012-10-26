<?php
namespace Bacon\Router;

class Exception extends \Exception {
	
	protected $key;
	
	/**
	 * 
	 * @param string $key
	 * @param string $message
	 * @param int $code
	 * @param \Exception $previous
	 */
	public function __construct($key = null, $message = null, $code = null, \Exception $previous = null) {
		parent::__construct($message, $code, $previous);
		$this->key = $key;
	}
	
	public function getKey() {
		return $this->key;
	}
	
}