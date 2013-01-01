<?php

namespace Bacon;

use Bacon\Cache;
use Bacon\Database\Adapter;
use Bacon\Database\Manager;

/**
 *
 * @author tomasz
 *
 * 	A hybrid collection/entity object.
 *
 */
abstract class Collection implements \ArrayAccess, \Iterator, \Countable {

	static $idField;
	static $table;
	static $cluster = 'default';
	static $readonlyFields = array();
	static $insertFields = false;
	static $updateFields = false;

	protected $__dirty = array();

	/**
	 *
	 * @var array
	 */
	protected $__current;

	/**
	 *
	 * @var array
	 */
	protected $__myArray = array();

	/**
	 *
	 * @param array $data
	 */

	public function __construct(array $data = null) {
		if (is_array($data)) {
			if (is_array(array_shift(array_values($data)))) { //checks if the first element is an array
				$this->__myArray = $data; // Looks like dataset
				$this->__current = array_shift(array_values($data)); // Setting this first entry as __current
			} else {
				$this->__myArray = array($data); // Looks like a row, making it a 1 row recordset
				$this->__current = $data;
			}
		}
	}

	public function getId() {
		return $this[$this::$idField];
	}

	/**
	 * Saves item
	 *
	 * @param Adapter $conn
	 */

	public function save(Adapter $adapter = null) {
		$adapter = (is_null($adapter) ? static::getCluster()->master() : $adapter);
//		error_log(print_r($this->getCurrent(), true));exit;
		if (isset($this[static::$idField])) {
			return self::update(
					array_intersect_key($this->getCurrent(), array_flip($this->__dirty)),
					array(
							$this::$idField . '={int:id}',
							array('id' => $this[static::$idField])));
		} else {
			return self::insert($this->getCurrent());
		}
	}

	/**
	 *
	 * @return \Bacon\Database\Cluster
	 */

	public static function getCluster() {
		global $config;
		return Manager::singleton()->get(static::$cluster);
	}

	public static function load($extra, array $phs = null, Adapter $adapter = null) {
		$adapter = (is_null($adapter) ? static::getCluster()->slave() : $adapter);
		if (($row = $adapter->pquery(
				'SELECT * FROM `' . static::$table . '` ' . $extra,
				$phs)->fetch()) == null) {
			throw new \Exception('Record not found');
		}
		return new static($row);
	}

	/**
	 *
	 * @param string $extra
	 * @param array $phs
	 * @param Adapter $conn
	 * @param Cache $cache
	 * @return multitype:|\Bacon\Collection
	 */

	public static function select($extra, array $phs = null, Adapter $conn = null,
			Cache $cache = null) {
		$q = 'SELECT ' . static::$table . '.* FROM ' . static::$table . ' ' . $extra;
		if (is_null($conn)) {
			$conn = static::getCluster()->slave();
		}
		if (!is_null($cache)) {
			ksort($phs);
			$key = $q . serialize($phs);
			$data = $cache->get(
					$key,
					function () use ($conn, $q, $phs) {
						return $conn->pquery($q, $phs)->fetchAll();
					});
		} else {
			$data = $conn->pquery($q, $phs)->fetchAll();
		}
		return new static($data);
	}

	/**
	 *
	 * @param int $id
	 * @return \Bacon\Collection
	 */

	public static function byId($id, Adapter $adapter = null) {
		$adapter = (is_null($adapter) ? static::getCluster()->slave() : $adapter);
		return new static($adapter->pquery(
				'SELECT * FROM `' . static::$table . '` WHERE `' . static::$idField . '`={int:id}',
				array('id' => $id))->fetch());
	}

	/**
	 *
	 * @param array $data
	 * @param array|int $where
	 * @param Adapter $adapter
	 * @return Adapter
	 */

	public static function update(array $data, $where, Adapter $adapter = null) {
		if (is_null($adapter)) {
			$adapter = static::getCluster()->master();
		}
		//error_log(static::$idField);exit;
		//sstatic::$idField.'={int:id}');
		if (is_int($where)) {
			$cond = static::$idField.'={int:id}';
			$where = array(
					$cond,
					array('id' => $where));
		}
		$data = array_diff_key($data, array_flip(self::getStandardFilteredFields()));

		if(self::$updateFields !== false) {
			$data = array_intersect_key($data, array_flip(self::$updateFields));
		}

		$adapter->update(
				static::$table,
				$where,
				$data);
		return $adapter;
	}

	/**
	 *
	 * @param unknown $where
	 * @param Adapter $adapter
	 * @return Adapter
	 */
	public static function delete($where, Adapter $adapter = null) {
		if (is_null($adapter)) {
			$adapter = static::getCluster()->master();
		}
		if (is_int($where)) {
			$cond = static::$idField.'={int:id}';
			$where = array(
				$cond,
				array('id' => $where));
		}
		$adapter->delete(static::$table, $where);
		return $adapter;
	}

	/**
	 *
	 * @param array $data
	 * @param Adapter $adapter
	 * @return Adapter
	 */

	public static function insert(array $data, Adapter $adapter = null) {
		if (is_null($adapter)) {
			$adapter = static::getCluster()->master();
		}

		$data = array_diff_key($data, array_flip(self::getStandardFilteredFields()));
		if(self::$insertFields !== false) {
			$data = array_intersect_key($data, self::$insertFields);
		}

		$adapter->insert(static::$table, $data);
		return $adapter;
	}

	public static function getStandardFilteredFields() {
		return array_merge(array(static::$idField), static::$readonlyFields);
	}

	public function seek($index) {
		$this->rewind();
		foreach ($this as $key => $row) {
			if ($key == $index) {
				return $this;
			}
		}
		return $this;
	}

	/**
	 *
	 * @return array
	 */

	public function getCurrent() {
		return $this->__current;
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
		$this->__dirty[] = $offset;
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
		$this->__dirty = []; // Resets dirty
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

	/**
	 * Extract array of values for specific colunn in array
	 *
	 * @param string $field
	 * @return array
	 */

	public function values($field) {
		$output = [];
		foreach ($this->__myArray as $row) {
			if (isset($row[$field])) {
				$output[] = $row[$field];
			}
		}
		return $output;
	}

}
