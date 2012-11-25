<?php
namespace Bacon\Router\Action;

use Bacon\View;
use Bacon\Router\Controller as AppController;

class Controller {

	/**
	 *
	 * @var Bacon\View
	 */
	public $view;
	public $action;
	protected $render = true;

	/**
	 * Constructs the most basic controller suppored by Bacon. Passes action name
	 *
	 * @param string $action
	 */
	public function __construct($action = null) {
		$this->action = $action;
		$this->view = $this->getView();
		$this->setActionName(AppController::getInstance()->route->getAction());
	}

	protected function getView() {
		return new View();
	}

	public function setActionName($name) {
		$this->view->setActionName(str_replace('\\', DIRECTORY_SEPARATOR, AppController::getInstance()->route->getController().'\\'.$name));
	}

	public function disableViewRendering() {
		$this->render = false;
	}

	public function enableViewRendering() {
		$this->render = true;
	}

	public function render() {
		if($this->render) {
			$this->view->render();
		}
	}

	/**
	 * @todo Option to render on destuct. May or may not be benefical. To be evaluated.
	 */
	public function __destruct() {
		//		$this->render();
	}
}