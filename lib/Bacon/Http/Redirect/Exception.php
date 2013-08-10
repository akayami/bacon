<?php
namespace Bacon\Http\Redirect;

class Exception extends \Bacon\Http\Exception {

	public function __construct($message = null, $code = 302, $previous = null) {
		parent::__construct($message, $code, $previous);
	}
	
}