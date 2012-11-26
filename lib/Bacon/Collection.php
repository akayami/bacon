<?php
namespace Bacon;

/**
 *
 * @author tomasz
 *
 *	A hybrid collection/entity object.
 *
 */

class Collection implements \ArrayAccess, \Iterator, \Countable {

	private $__current;
	private $__myArray = array();

	/**
	 *
	 * @param array $data
	 */
	public function __construct(array $data = null) {
		if(is_array($data)) {
			if(isset($data[0])) {
				$this->__myArray = $data;
			} else {
				$this->__current = $data;
			}
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see ArrayAccess::offsetExists()
	 */
	public function offsetExists($offset) {
		return isset($this->__current[$offset]);
	}

	/**
	 * (non-PHPdoc)
	 * @see ArrayAccess::offsetGet()
	 */
	public function offsetGet($offset) {
		return $this->__current[$offset];
	}

	/**
	 * (non-PHPdoc)
	 * @see ArrayAccess::offsetSet()
	 */
	public function offsetSet($offset, $value) {
		$this->__current[$offset] = $value;
	}

	/**
	 * (non-PHPdoc)
	 * @see ArrayAccess::offsetUnset()
	 */
	public function offsetUnset($offset) {
		unset($this->__current[$offset]);
	}

	/**
	 * (non-PHPdoc)
	 * @see Iterator::rewind()
	 */
	public function rewind() {
		return reset($this->__myArray);
	}

	/**
	 *
	 * @return $this
	 */
	public function current() {
		$this->__current = current($this->__myArray);
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see Iterator::key()
	 */
	public function key() {
		return key($this->__myArray);
	}

	/**
	 * (non-PHPdoc)
	 * @see Iterator::next()
	 */
	public function next() {
		return next($this->__myArray);
	}

	/**
	 * (non-PHPdoc)
	 * @see Iterator::valid()
	 */
	public function valid() {
		return key($this->__myArray) !== null;
	}

	/**
	 * (non-PHPdoc)
	 * @see Countable::count()
	 */
	public function count() {
		return count($this->__myArray);
	}
}