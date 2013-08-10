<?php

namespace Bacon;

use Bacon\Cache\Nocache;

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
abstract class Collection implements \ArrayAccess, \Iterator, \Countable
{

	/**
	 *
	 * @var Cache
	 */
	protected static $structureCacheAdapter;

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

	
	const UPDATE = 'update';
	const INSERT = 'insert';

	public function __construct(array $data = null)
	{
		if (is_array($data) && count($data) > 0)
		{
			$c = array_values($data);
			if (is_array(array_shift($c)))
			{ //checks if the first element is an array
				$this->__myArray = $data; // Looks like dataset
				$c = array_values($data);
				$this->__current = array_shift($c); // Setting this first entry as __current
			}
			else
			{
				$this->__myArray = array($data); // Looks like a row, making it a 1 row recordset
				$this->__current = $data;
			}
		}
	}

	public static function getCacheAdapter()
	{
		if (!isset(static::$structureCacheAdapter))
		{
			static::$structureCacheAdapter = new Nocache();
		}
		return static::$structureCacheAdapter;
	}

	public static function setCollectionStructureCaching(Cache $cache)
	{
		static::$structureCacheAdapter = $cache;
	}

	public static function getStructure()
	{
		if (!isset(static::$structure))
		{
			$tableName = static::$table;
			$cluster = static::getCluster();
			static::$structure = static::getCacheAdapter()->get('structure:' . static::$cluster . static::$table, function () use ($cluster, $tableName)
			{
				return $cluster->slave()->query('SHOW FULL columns FROM ' . $tableName)->fetchAllIndexedByCol('Field');
			});
		}
		return static::$structure;
	}

	/**
	 * 
	 * @param string $onlyAutoIncrement
	 */
	public static function getIdFields($onlyAutoIncrement = false) {
		if(!isset(static::$idFields[(int)$onlyAutoIncrement])) {
			static::$idFields[(int)$onlyAutoIncrement] = [];
			$structure = static::getStructure();
			foreach($structure as $fieldname => $field) {
				if ($field['Key'] === 'PRI') {
					if($onlyAutoIncrement == false || in_array('auto_increment', explode(',',$field['Extra']))) {
						static::$idFields[(int)$onlyAutoIncrement][] = $field['Field'];
					}
				}
			}
		}
		return static::$idFields[(int)$onlyAutoIncrement];
	}	
		
	/**
	 * 
	 * 
	 */	
	public static function getIDField()
	{
		$fields = static::getIdFields();
		if(count($fields) > 1) {
			throw new \Exception('This collection contains composite PK. Use getIDFields instead');
		}
		return $fields[0];		
	}

	/**
	 * Returns an array containing keys
	 * 
	 * @param string $assoc
	 * @return array
	 */
	public function getIds($assoc = true) {
		$a = $this::getIDFields();
		$output = [];
		foreach($a as $index => $field) {
			if($assoc) {
				$output[$field] = $this[$field];
			} else {
				$output[$index] = $this[$field];
			}
		}
		return $output;
	}
	
	/**
	 * Return current id
	 * 
	 * @return mixed
	 * @deprecated
	 */
	public function getId()
	{
		$id = $this::getIDFields();
		if(count($id) > 1) {
			throw new \Exception('getId function does not support compunded primary keys');
		}
		return $this[$id[0]];
	}
	
	protected function getPKWhere() {
		$ids = $this->getIds();
		$output = [];
		foreach($ids as $id => $value) {
			$output[] = $id.'={str:'.$id.'}';
		}
		return $output;
	}
	
	protected function getPKWhereString() {		
		return implode(' AND ', $this->getPKWhere());
	}
	
	protected function getPKCombo() {
		return array($this->getPKWhereString(), $this->getIds());
	}

	/**
	 * Saves item
	 *
	 * @param Adapter $conn
	 * @return Adapter
	 */

	public function save(Adapter $adapter = null)
	{
		$adapter = (is_null($adapter) ? static::getCluster()->master() : $adapter);
		if (isset($this[static::getIDField()]))
		{
			return self::update(array_intersect_key($this->getCurrent(), array_flip($this->__dirty)), $this->getPKCombo());
		}
		else
		{
			return self::insert($this->getCurrent());
		}
	}

	public function del(Adapter $adapter = null) {
		$adapter = (is_null($adapter) ? static::getCluster()->master() : $adapter);
		if (isset($this[static::getIDField()])) {
			return self::delete($this->getPKCombo());
		} else {
			throw new \Exception('Cannot delete, missing id');
		}
	}

	public function saveInsert(Adapter $adapter = null) {
		$adapter = (is_null($adapter) ? static::getCluster()->master() : $adapter);
		return self::insert($this->getCurrent());
	}

	public function saveUpdate(Adapter $adapter = null) {
		$adapter = (is_null($adapter) ? static::getCluster()->master() : $adapter);
		return self::update(array_intersect_key($this->getCurrent(), array_flip($this->__dirty)), $this->getPKCombo());
	}



	/**
	 * Check for fields type
	 *
	 * @param string $type
	 * @return string
	 */
	protected static function extractFieldType($type)
	{
		if ($type == 'text' || substr($type, 0, 3) == 'char' || substr($type, 0, 6) == 'varchar')
		{
			return 'string';
		}
		if (substr($type, 0, 3) == 'int')
		{
			return 'int';
		}
	}

	/**
	 * General purpose data sanitization function. Takes dirty fields and sanitizes their values
	 * @param array $data
	 * @return array
	 */
	public static function sanitize($data, $operation)
	{
		$structure = static::getStructure();
		array_walk($data, function ($val, $key) use ($structure)
		{
			switch (static::extractFieldType($structure[$key]['Type']))
			{
			case 'string':
				$newVal = (string) $val;
				break;
			case 'int':
				$newVal = (int) $val;
				break;
			default:
				$newVal = $val;
				break;
			}
// 			if ($newVal !== $val)
// 			{
// 				trigger_error('Collection sanitized value for key:' . $key. ' - '.$val.'=>'.$newVal);
// 			}
		});
		return $data;
	}

	/**
	 *
	 * @return \Bacon\Database\Cluster
	 */

	public static function getCluster()
	{
		return Manager::singleton()->get(static::$cluster);
	}

	/**
	 *
	 * @param unknown $extra
	 * @param array $phs
	 * @param Adapter $adapter
	 * @throws \Exception
	 * @return self
	 */
	public static function load($extra, array $phs = null, Adapter $adapter = null)
	{
		$adapter = (is_null($adapter) ? static::getCluster()->slave() : $adapter);
		if (($row = $adapter->pquery('SELECT * FROM `' . static::$table . '` ' . $extra, $phs)->fetch()) == null)
		{
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
	 * @return self
	 */

	public static function select($extra = '', array $phs = null, Adapter $conn = null, Cache $cache = null)
	{
		$q = 'SELECT ' . static::$table . '.* FROM ' . static::$table . ' ' . $extra;

		return static::query($q, $phs, $conn, $cache);
	}

	/**
	 *
	 * @param unknown $q
	 * @param array $phs
	 * @param Adapter $conn
	 * @param Cache $cache
	 * @return self
	 */
	public static function query($q, array $phs = null, Adapter $conn = null, Cache $cache = null)
	{
		if (is_null($conn))
		{
			$conn = static::getCluster()->slave();
		}
		if (!is_null($cache))
		{
			if ($phs)
			{
				ksort($phs);
			}

			$key = $q . serialize($phs);
			$data = $cache->get($key, function () use ($conn, $q, $phs)
					{
						return $conn->pquery($q, $phs)->fetchAll();
					});
		}
		else
		{
			$data = $conn->pquery($q, $phs)->fetchAll();
		}
		return new static($data);
	}

	protected static function parseIds(array $ids) {
		$output = [];
		$keys = [];	
		foreach($ids as $i => $k) {
			$keys['name_'.$i] = $i;
			$output[] = '{name:name_'.$i.'} = {str:'.$i.'}';
		}
		return [$output, $keys];
	}
	
	/**
	 * 
	 * @param array $ids
	 * @param Adapter $conn
	 * @param Cache $cache
	 */
	public static function byIds(array $ids, Adapter $conn = null, Cache $cache = null) {
		list($parts, $keyMap) = static::parseIds($ids);
		return static::select(' WHERE '.implode(' AND ', $parts), array_merge($keyMap, $ids), $conn, $cache);		
	}
	
	
	/**
	 *
	 * @param int $id
	 * @return self
	 * @deprecated
	 */

	public static function byId($id, Adapter $conn = null, Cache $cache = null)
	{
		return static::getBy(static::getIDField(), $id, $conn, $cache);
	}

	/**
	 *
	 * @param unknown $field
	 * @param unknown $value
	 * @param Adapter $conn
	 * @param Cache $cache
	 * @return self
	 */
	public static function getBy($field, $value, Adapter $conn = null, Cache $cache = null)
	{
		return static::select('WHERE `' . $field . '` = {str:' . $field . '}', [$field => $value], $conn, $cache);
	}

	protected static function shortHandWhere($where) {
		$idFields = static::getIdFields();
		if(count($idFields) > 1) {
			throw new \Exception('Cannot use shorthand where on tables with composite PK');
		}
		$cond = $idFields[0] . '={str:id}';
		return array($cond, array('id' => $where));
	}
	
	/**
	 *
	 * @param array $data
	 * @param array|int $where
	 * @param Adapter $adapter
	 * @return Adapter
	 */

	public static function update(array $data, $where, Adapter $adapter = null)
	{
		if (is_null($adapter))
		{
			$adapter = static::getCluster()->master();
		}
		if (is_int($where))
		{			
			$where = static::shortHandWhere($where);
		}
		$data = array_diff_key($data, array_flip(self::getStandardFilteredFields()));

		if (static::$updateFields !== false)
		{
			$data = array_intersect_key($data, array_flip(static::$updateFields));
		}
		$adapter->update(static::$table, $where, static::sanitize($data, self::UPDATE));
		return $adapter;
	}

	/**
	 *
	 * @param unknown $where
	 * @param Adapter $adapter
	 * @return Adapter
	 */
	public static function delete($where, Adapter $adapter = null)
	{
		if (is_null($adapter))
		{
			$adapter = static::getCluster()->master();
		}
		if (is_int($where))
		{
			$where = static::shortHandWhere($where);
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

	public static function insert(array $data, Adapter $adapter = null)
	{
		if (is_null($adapter))
		{
			$adapter = static::getCluster()->master();
		}
		$data = array_diff_key($data, array_flip(self::getStandardFilteredFields()));
		if (static::$insertFields !== false)
		{
			$data = array_intersect_key($data, static::$insertFields);
		}
		$adapter->insert(static::$table, static::sanitize($data, self::INSERT));
		return $adapter;
	}

	protected static function getStandardFilteredFields()
	{
		return array_merge(static::getIDFields(true), static::$readonlyFields);
	}

	public function seek($index)
	{
		$this->rewind();
		foreach ($this as $key => $row)
		{
			if ($key == $index)
			{
				return $this;
			}
		}
		$this->__current = [];
		return $this;
	}

	public function toArray(array $columns = null) {
		if(is_null($columns)) {
			return $this->__myArray;
		} else {
			$output = [];
 			foreach($this as $index => $row) {
 				$row = [];
 				foreach($columns as $c) {
					$row[$c] = $this[$c];
 				}
 				$output[$index] = $row;
 			}
			return $output;
		}
	}

	/**
	 *
	 * @return array
	 */

	public function getCurrent()
	{
		return $this->__current;
	}

	/**
	 * (non-PHPdoc)
	 * @see ArrayAccess::offsetExists()
	 */

	public function offsetExists($offset)
	{
		return isset($this->__current[$offset]);
	}

	/**
	 * (non-PHPdoc)
	 * @see ArrayAccess::offsetGet()
	 */

	public function offsetGet($offset)
	{
		return $this->__current[$offset];
	}

	/**
	 * (non-PHPdoc)
	 * @see ArrayAccess::offsetSet()
	 */

	public function offsetSet($offset, $value)
	{
		$this->__dirty[] = $offset;
		$this->__current[$offset] = $value;
	}

	/**
	 * (non-PHPdoc)
	 * @see ArrayAccess::offsetUnset()
	 */

	public function offsetUnset($offset)
	{
		unset($this->__current[$offset]);
	}
	
	public function mergeFileds(array $values) {
		$this->__current = array_merge($this->__current, $values);
		$this->__dirty = array_merge($this->__dirty, array_keys($values));
	}

	/**
	 * (non-PHPdoc)
	 * @see Iterator::rewind()
	 */

	public function rewind()
	{
		return reset($this->__myArray);
	}

	/**
	 *
	 * @return $this
	 */

	public function current()
	{
		$this->__current = current($this->__myArray);
		$this->__dirty = []; // Resets dirty
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see Iterator::key()
	 */

	public function key()
	{
		return key($this->__myArray);
	}

	/**
	 * (non-PHPdoc)
	 * @see Iterator::next()
	 */

	public function next()
	{
		return next($this->__myArray);
	}

	/**
	 * (non-PHPdoc)
	 * @see Iterator::valid()
	 */

	public function valid()
	{
		return key($this->__myArray) !== null;
	}

	/**
	 * (non-PHPdoc)
	 * @see Countable::count()
	 */

	public function count()
	{
		return count($this->__myArray);
	}

	/**
	 * Extract array of values for specific colunn in array
	 *
	 * @param string $field
	 * @return array
	 */

	public function values($field)
	{
		$output = [];
		foreach ($this->__myArray as $row)
		{
			if (isset($row[$field]))
			{
				$output[] = $row[$field];
			}
		}
		return $output;
	}

	/**
	 * Get safely a value for the current selected item
	 *
	 * @param string $key
	 * @param string $default
	 * @return \Bacon\Collection|string
	 */
	public function get($key, $default = false)
	{
		if (isset($this[$key]))
		{
			return $this[$key];
		}
		else
		{
			return $default;
		}
	}

	/**
	 *
	 * @param unknown $name
	 * @return string
	 */
	public function __get($name)
	{
		return $this->get($name, false);
	}

	/**
	 *
	 * @param string $name
	 * @param string $value
	 */
	public function __set($name, $value) {
		$this[$name] = $value;
	}

}
