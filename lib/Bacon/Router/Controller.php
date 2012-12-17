<?php
namespace Bacon\Router;

use Bacon\Router\Action\Controller as ActionCtrl;

use Bacon\Misc\Singleton;
use Bacon\Http\Request;

class Controller {

	use Singleton;

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
					try {
						Request::getInstance()->setURI($route->getParams());	// Reading extracted parameters
						$this->route = $route;
						$this->handleRoute($this->route);
						return true;
					} catch(\Exception $e) {
						throw new Exception('not.found', 'Requested resource was not found.', null, $e);
					}
				}
			}
		}
		throw new Exception('not.found', 'Requested resource was not found.');
	}

	/**
	 *
	 * @param Route $route
	 * @throws Exception
	 */
	protected function handleRoute(Route $route) {
		$controller = $this->loadController($route);
		$controller->postConstruct();
		$this->executeAction($route, $controller);
		$this->render($controller);
	}

	/**
	 *
	 * @param Route $route
	 * @throws Exception
	 * @return ActionCtrl
	 */
	protected function loadController(Route $route) {
		$cName = $route->getControllerClass();
		if($route->validate() && !class_exists($cName, true)) {
			throw new \Exception('Route resolved to an invalid controller: '.$cName);
		}
		return new $cName($route->getAction());
	}

	/**
	 *
	 * @param Route $route
	 * @param ActionCtrl $c
	 * @throws Exception
	 */
	public function executeAction(Route $route, ActionCtrl $c) {
		if($route->validate()) {
			$rfl = new \ReflectionMethod($c, $route->getAction());
			if(!$rfl->isPublic()) {
				throw new \Exception('Route resolved to an invalid action:'.$route->getAction());
			}
		}
		$c->{$route->getAction()}();
	}

	/**
	 * Renders action
	 *
	 * @param Controller $c
	 */
	protected function render(ActionCtrl $c) {
		return $c->render();
	}
}