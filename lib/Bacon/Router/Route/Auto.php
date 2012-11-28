<?php
namespace Bacon\Router\Route;
use Bacon\Http\Request;

use Bacon\Router\Route;

class Auto extends Regex {

	protected $regex;
	public $matches;
	protected $namespace;
	protected $defaultAction;

	/**
	 *
	 */
	public function __construct($defaultControllerNamespace = '', $defaultAction = 'index') {
		parent::__construct('#/(?<controller>\w+)(/(?<action>\w+))?(?<uriparams>.*)$#', '', '');
		$this->namespace = $defaultControllerNamespace;
		$this->defaultAction = $defaultAction;
	}

	public function getController() {
		return $this->namespace.'\\'.ucfirst($this->controller);
	}

	public function getControllerClass() {
		return $this->namespace.'\\'.ucfirst($this->controller).$this->controllerSuffix;
	}

	/**
	 * (non-PHPdoc)
	 * @see Bacon\Router.Route::isValid()
	 */
	public function isValid($request) {
		if($result = parent::isValid($request)) {
			$this->controller = $this->matches['controller'];
			$this->action = (strlen($this->matches['action']) ? $this->matches['action'] : $this->defaultAction);
			if(isset($this->matches['uriparams'])) {
				$this->matches = array_merge($this->matches, $this->parseURI($this->matches['uriparams']));
			}
		}
		return $result;
	}

	/**
	 *
	 * @param unknown_type $uri
	 * @return array
	 */
	protected function parseURI($uri) {
		$list = explode('/', trim($this->matches['uriparams'], '/'));
		$i = 2;
		$values = array();
		foreach($list as $element) {
			if($i % 2 == 0) {
				$key = $element;
			} else {
				$values[$key] = $element;
			}
			$i++;
		}
		return $values;
	}

	function validate() {
		return true;
	}
}