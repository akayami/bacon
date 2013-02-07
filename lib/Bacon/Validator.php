<?php
namespace Bacon;

abstract class Validator {

	protected $message;
	protected $child;

	public function validate($subject) {
		if(!is_null($this->child)) {
			$this->child->validate($subject);
		}
	}

	/**
	 *
	 * @param Validator $validator
	 * @return \Bacon\Validator
	 */
	public function append(Validator $validator) {
		$this->child = $validator;
		return $this;
	}

}