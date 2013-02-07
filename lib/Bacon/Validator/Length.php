<?php
namespace Bacon\Validator;


use Bacon\Validator;

class Length extends Validator {

	private $length;
	CONST LESS_THAN = '<';
	CONST GREATER_THAN = '>';

	public function __construct($length, $type = self::GREATER_THAN) {
		$this->length = $length;
		$this->type = $type;
	}

	public function validate($subject, array $messages = null) {
		switch ($this->type) {
			case self::GREATER_THAN:
				if(strlen($subject) <= $this->length) {
					throw new Exception('Length must be bigger than: '.$this->length);
				}
				break;
			case self::LESS_THAN:
				if(strlen($subject) >= $this->length) {
					throw new Exception('Length must be smaller than: '.$this->length);
				}
				break;
			default:
				throw new \Exception('Invalid type');
		}
		parent::validate($subject);

	}
}