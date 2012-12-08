<?php
namespace Bacon;

use Bacon\Cache;

use Bacon\Database\Adapter;

use Bacon\Database\Manager;

/**
 *
 * @author tomasz
 *
 *	A hybrid collection/entity object.
 *
 */

abstract class Collection implements \ArrayAccess, \Iterator, \Countable {

	static $idField;
	static $table;
	static $cluster = 'default';
	static $readonlyFields = array();
	static $insertFields = array();
	static $updateFields = array();

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
		if(is_array($data)) {
			if(isset($data[0])) {
				$this->__myArray = $data;
			} else {
				$this->__current = $data;
			}
		}
	}

	/**
	 * Saves item
	 *
	 * @param Adapter $conn
	 */
	public function save(Adapter $conn = null) {
		$adapter = (is_null($adapter) ? static::getCluster()->master() : $adapter);
		if(isset($this[static::$idField])) {
			return self::update($this->getCurrent(), array(self::$idField.'={int:id}', array('id' => $this[static::$idField])));
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

	/**
	 *
	 * @param string $extra
	 * @param array $phs
	 * @param Adapter $conn
	 * @param Cache $cache
	 * @return multitype:|\Bacon\Collection
	 */
	public static function select($extra, $phs, Adapter $conn = null, Cache $cache = null) {
		$q = 'SELECT '.static::$table.'.* FROM '.static::$table.' '.$extra;
		if(is_null($conn)) {
			$conn = static::getCluster()->slave();
		}
		if(!is_null($cache)) {
			ksort($phs);
			$key = $q.serialize($phs);
			$data = $cache->get($key, function() use ($conn, $q, $phs) {
				error_log('Building cache');
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
		return new static($adapter->pquery('SELECT * FROM `'.static::$table.'` WHERE `'.static::$idField.'`={int:id}', array('id' => $id))->fetch());
	}

	/**
	 *
	 * @param array $data
	 * @param array|int $where
	 * @param Adapter $adapter
	 * @return Adapter
	 */
	public static function update(array $data, $where, Adapter $adapter = null) {
		if(is_null($adapter)) {
			$adapter = static::getCluster()->master();
		}
		if(is_int($where)) {
			$where = array(static::$idField.'={int:id}', array('id' => $where));
		}
		$adapter->update(static::$table, $where, array_intersect_key($data, array_flip(static::$updateFields)), static::getStandardFilteredFields());
		return $adapter;
	}

	/**
	 *
	 * @param array $data
	 * @param Adapter $adapter
	 * @return Adapter
	 */
	public static function insert(array $data, Adapter $adapter = null) {
		if(is_null($adapter)) {
			$adapter = static::getCluster()->master();
		}
		$adapter->insert(static::$table, array_intersect_key($data, array_flip(static::$insertFields)), static::getStandardFilteredFields());
		return $adapter;
	}

	public static function getStandardFilteredFields() {
		return array_merge(array(static::$idField), static::$readonlyFields);
	}

	public function seek($index) {
		foreach($this as $key => $row) {
			if($key === $index) {
				$this->rewind();
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

	/**
	 * Extract array of values for specific colunn in array
	 *
	 * @param string $field
	 * @return array
	 */
	public function values($field) {
		$output = [];
		foreach($this->__myArray as $row) {
			if(isset($row[$field])) {
				$output[] = $row[$field];
			}
		}
		return $output;
	}
}