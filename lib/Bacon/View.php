<?php
namespace Bacon;

class View extends Template {

	protected $action;

	/**
	 * Constructs the view with the data
	 *
	 * @param array $data
	 */

 	public function __construct(array $data = array()) {
 		parent::__construct($data, null);
 	}

	/**
	 * Sets then action name. Used for switching views in controller
	 *
	 * @param string $string
	 */
	public function setActionName($string) {
		$this->action = $string;
		$this->__template = $string.'.phtml';
	}

	/**
	 *
	 *
	 * @param filepath $path  A filepaht within the include path available
	 * @param array $data	  Provide dataset to use if different than within the main scope
	 */
	public function inject($path, array $data = null, $copyScope = false) {
		if(is_null($data)) {
			if(!include($path.'.phtml')) {
				throw new \Exception('Failed to include: '.$path.'.phtml');
			}
		} else {
			$v = new Static($copyScope ? array_merge($this->__data, $data) : $data);
			$v->setActionName($path);
			$v->render();
		}
	}
}