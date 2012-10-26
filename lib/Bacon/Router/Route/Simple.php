<?php
namespace Bacon\Router\Route;
use Bacon\Http\Request;

use Bacon\Router\Route;
use Bacon\Router\Route\Abstr;

class Simple extends Abstr implements Route {
	
	protected $pattern;
	
	public function __construct($pattern, $controller, $action) {
		parent::__construct($controller, $action);
		$this->pattern = $pattern;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Bacon\Router.Route::isvalidate()
	 */
	public function isvalidate($request) {
		$pieces = explode('?', $request);
		if($pieces[0] == $this->pattern) {
			return true;
		}
		return false;		
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Bacon\Router.Route::getParams()
	 */
	public function getParams() {
		return array();
	}	
}