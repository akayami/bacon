<?php

namespace Bacon\Router\Route;
use Bacon\Http\Request;

use Bacon\Router\Route;

class Regex extends Abstr implements Route {

	protected $regex;
	public $matches;
	
	/**
	 * 
	 * @param string $regex
	 * @param string $controller
	 * @param string $action
	 */
	public function __construct($regex, $controller, $action) {
		parent::__construct($controller, $action);
		$this->regex = $regex;	
	}

	/**
	 * (non-PHPdoc)
	 * @see Bacon\Router.Route::getParams()
	 */
	public function getParams() {
		return $this->matches;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Bacon\Router.Route::isvalidate()
	 */
	public function isvalidate($request) {
		$pieces = explode('?', $request);	
		if(preg_match($this->regex, $pieces[0], $matches)) {
			$this->matches = $matches;
			return true;
		}
 		return false;
	}		
}