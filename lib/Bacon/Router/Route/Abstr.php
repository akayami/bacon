<?php
namespace Bacon\Router\Route;

abstract class Abstr {

	public $controller;
	public $action;
	protected $controllerSuffix = 'Controller';


	/**
	 *
	 * @param string $controller
	 * @param string $action
	 */
	public function __construct($controller, $action) {
		$this->controller = $controller;
		$this->action = $action;
	}

	public function getController() {
		return $this->controller;
	}

	public function getControllerClass() {
		return $this->controller.$this->controllerSuffix;
	}

	public function getAction() {
		return $this->action;
	}

	public function validate() {
		return false;
	}
}