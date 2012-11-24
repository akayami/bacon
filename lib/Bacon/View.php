<?php
namespace Bacon;

use Bacon\Misc\ArrayObject;

class View extends ArrayObject {
	
	protected $action;
	
	/**
	 * Constructs the view with the data
	 *
	 * @param array $data
	 */
	
 	public function __construct(array $data = array()) {
 		$this->__data = $data;
 	}
	
 	/**
 	 * Fetch the value of a key or return default
 	 * 
 	 * @param string $key
 	 * @param string $default
 	 * @throws Exception
 	 * @return multitype:|string
 	 */
 	public function get($key, $default = null) {
 		try {
 			return parent::offsetGet($key);
 		} catch(\Exception $e) {
 			if(!is_null($default)) {
 				return $default;
 			} else {
 				throw $e;
 			}
 		}
 	}
	
 	/**
 	 * Renders current view
 	 * 
 	 * @throws \Exception
 	 */
	public function render() {
		ob_start();
		$a = $this->action;
		if(substr($a, 0, 1) == '/') {
			$a = substr($a, 1);
		} 		
		if(!include($a.'.phtml')) {
			throw new \Exception('Missing view:'.$a.'.phtml');
		}
		$out = ob_get_contents();
		ob_end_clean();
		echo $out;
	}
	
	/**
	 * Sets then action name. Used for switching views in controller 
	 * 
	 * @param string $string
	 */
	public function setActionName($string) {
		$this->action = $string;
	}
	
	/**
	 * 
	 * 
	 * @param filepath $path  A filepaht within the include path available
	 * @param array $data	  Provide dataset to use if different than within the main scope
	 */
	public function inject($path, array $data = null) {
		if(is_null($data)) {
			if(!include($path.'.phtml')) {
				throw new \Exception('Failed to include: '.$path.'.phtml');	
			}
		} else {
			$v = new Static($data);
			$v->setActionName($path);
			$v->render();
		}
	}
}