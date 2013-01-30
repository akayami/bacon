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

	/**
	 *
	 * @param array $data
	 */

	public function __construct(array $data = null)
	{
		if (is_array($data))
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

	public static function getIDField()
	{
		if (!is_null(static::$idField))
		{
			$structure = static::getStructure();
			foreach ($structure as $fieldname => $field)
			{
				if ($field['Key'] === 'PRI')
				{
					static::$idField = $field['Field'];
				}
			}
		}
		return static::$idField;
	}

	public function getId()
	{
		return $this[$this::getIDField()];
	}

	/**
	 * Saves item
	 *
	 * @param Adapter $conn
	 */

	public function save(Adapter $adapter = null)
	{
		$adapter = (is_null($adapter) ? static::getCluster()->master() : $adapter);
		if (isset($this[static::getIDField()]))
		{
			return self::update(self::sanitize(array_intersect_key($this->getCurrent(), array_flip($this->__dirty))), array(static::getIDField() . '={int:id}', array('id' => $this->getId())));
		}
		else
		{
			return self::insert(self::sanitize($this->getCurrent()));
		}
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
	public static function sanitize($data)
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
			if ($newVal !== $val)
			{
				trigger_error('Collection sanitized value for key:' . $key);
			}
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
	 * @return multitype:|\Bacon\Collection
	 */

	public static function select($extra, array $phs = null, Adapter $conn = null, Cache $cache = null)
	{
		$q = 'SELECT ' . static::$table . '.* FROM ' . static::$table . ' ' . $extra;

		return static::query($q, $phs, $conn, $cache);
	}

	public static function query($q, array $phs = null, Adapter $conn = null, Cache $cache = null)
	{
		if (is_null($conn))
		{
			$conn = static::getCluster()->slave();
		}
		if (!is_null($cache))
		{
			ksort($phs);
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

	/**
	 *
	 * @param int $id
	 * @return \Bacon\Collection
	 */

	public static function byId($id, Adapter $conn = null, Cache $cache = null)
	{		
		return static::getBy(static::getIDField(), $id, $conn, $cache);
	}
	
	public static function getBy($field, $value, Adapter $conn = null, Cache $cache = null)
	{
		return static::select('WHERE `' . $field . '` = {str:' . $field . '}', [$field => $value], $conn, $cache);
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
		//error_log(static::getIDField());exit;
		//sstatic::getIDField().'={int:id}');
		if (is_int($where))
		{
			$cond = static::getIDField() . '={int:id}';
			$where = array($cond, array('id' => $where));
		}
		$data = array_diff_key($data, array_flip(self::getStandardFilteredFields()));

		if (static::$updateFields !== false)
		{
			$data = static::sanitize(array_intersect_key($data, array_flip(static::$updateFields)));
		}

		$adapter->update(static::$table, $where, $data);
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
			$cond = static::getIDField() . '={int:id}';
			$where = array($cond, array('id' => $where));
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
			$data = static::sanitize(array_intersect_key($data, static::$insertFields));
		}

		$adapter->insert(static::$table, $data);
		return $adapter;
	}

	protected static function getStandardFilteredFields()
	{
		return array_merge(array(static::getIDField()), static::$readonlyFields);
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
		return $this;
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
	
	public function __get($name)
	{
		return $this->get($name, false);
	}

}
