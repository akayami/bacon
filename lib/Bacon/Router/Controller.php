<?php
namespace Bacon\Router;

use Bacon\Misc\Singleton;
use Bacon\Http\Request;

class Controller extends Singleton {

	
	protected static $instance;
	
	public $routes;
	
	/**
	 * 
	 * @var Route
	 */
	public $route;

	/**
	 * 
	 * @param Route $route
	 * @param int $priority
	 */
	public function addRoute(Route $route, $priority = 0) {
		if(!isset($this->routes[$priority])) {
			$this->routes[$priority] = array();
		}
		$this->routes[$priority][] = $route;
	}
	
	/**
	 * 
	 * @param string $request
	 */
	public function route($request) {		
		ksort($this->routes);
		foreach($this->routes as $routeBlock) {
			foreach($routeBlock as /* @var $route Route */ $route) {
				if($route->isValid($request)) {
					Request::getInstance()->setURI($route->getParams());	// Reading extracted parameters
					$this->route = $route;
					$this->handleRoute($this->route);
					return true;						
				}
			}
		}
		throw new \Exception('Requested resource not found: '.$request, 404);
	}	
	
	/**
	 * 
	 * @param Route $route
	 * @throws Exception
	 */
	protected function handleRoute(Route $route) {
		$cName = $route->getController();
		$a = new $cName($route->getAction());
		$a->{$route->getAction()}();			
		$a->render();
	}
	
	/**
	 * @return Controller
	 */
	static public function getInstance() {
		return parent::getInstance();
	}
	
}