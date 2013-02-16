<?php
/**
 *
 * @author tomasz
 *
 * Simplest possible file template class. View should probably extend from this.
 *
 */
namespace Bacon;

use Bacon\Misc\ArrayObject;

class Template extends ArrayObject {


	protected $__template;

	/**
	 *
	 * @param array $data
	 * @param string $template
	 */
	public function __construct(array $data = array(), $template) {
		$this->__data = $data;
		$this->__template = $template;
	}

	/**
	 * Renders current view
	 *
	 * @throws \Exception
	 */
	public function render() {
		echo $this->getOutput();
	}

	/**
	 * Returns the output
	 *
	 * @return string
	 */
	public function getOutput() {
		ob_start();
		$a = $this->__template;
		if(substr($a, 0, 1) == '/') {
			$a = substr($a, 1);
		}
		if(!include($a)) {
			throw new \Exception('Missing template:'.$a.'.phtml');
		}
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}
}