<?php
namespace Example\Router;

use Bacon\Router\Action\Controller as ActionCtrl;

use Bacon\Router\Route;

use Bacon\Router\Controller as stdCtrl;


/**
 * For sake of example we're using 
 * @author t_rakowski
 *
 */
class Controller extends stdCtrl {
	
// 	/**
// 	 * (non-PHPdoc)
// 	 * @see \Bacon\Router\Controller::loadController()
// 	 */
// 	protected function loadController(Route $route) {
// 		$cName = $route->getController();
// 		if(!class_exists($cName, true)) {
// 			throw new Exception('Route resolved to an invalid controller: '.$cName);
// 		}
// 		return parent::loadController($route);		
// 	}
	
// 	/**
// 	 * (non-PHPdoc)
// 	 * @see \Bacon\Router\Controller::executeAction()
// 	 */
// 	public function executeAction(Route $route, ActionCtrl $c) {
// 		$rfl = new \ReflectionMethod($c, $route->getAction());
// 		if(!$rfl->isPublic()) {
// 			throw new Exception('Route resolved to an invalid action:'.$route->getAction());
// 		}
// 		$c->{$route->getAction()}();		
// 	}	
}