<?php
namespace Bacon\Validator;

class Exception extends \Exception {

	protected $validationFailures = [];

	public function __construct($validationFailure) {
		parent::__construct('Validation Exception');
		$this->addValidationFailure($validationFailure);
	}

	public function addValidationFailure($message) {
		$this->validationFailures[] = $message;
	}

	public function getValidationFailures() {
		return $this->validationFailures;
	}
}