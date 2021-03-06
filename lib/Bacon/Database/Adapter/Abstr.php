<?php
namespace Bacon\Database\Adapter;

use Bacon\Database\Adapter\Mysqli\Result;

use Bacon\Database\Adapter;

abstract class Abstr implements Adapter {

	protected $__type;
	/**
	 * (non-PHPdoc)
	 * @see Bacon\Database.Adapter::pquery_sql()
	 */
	public function pquery_sql($query, array $phs = null) {
		for ($pos = 0; preg_match('/\{(str|int|dec|raw|name):(\w++)\}/', $query, $match, PREG_OFFSET_CAPTURE, $pos);) {
			$ph_pos = $match[0][1];
			$ph_len = strlen($match[0][0]);
			$ph_type = $match[1][0];
			$ph_name = $match[2][0];

			if (! array_key_exists($ph_name, $phs)) {
				throw new \Exception("query contains placeholder '{$ph_name}', but a value was not passed");
			}

			$ph_vals = array();
			foreach (($phs[$ph_name] !== null ? (array)$phs[$ph_name] : array(null)) as $ph_val) {
				if ($ph_val === null) {
					$ph_vals[] = 'NULL';
					continue;
				}

				if (! is_string($ph_val) && ! is_int($ph_val) && ! is_float($ph_val)) {
					throw new \Exception("value for placeholder '{$ph_name}' must be of type '{$ph_type}', got type '" . gettype($ph_val) . "'");
				}

				switch ($ph_type) {
					case 'str':
						$ph_vals[] = $this->quote($ph_val);
						break;

					case 'int':
						if (! preg_match('/^\d++\z/', $ph_val)) {
							throw new \Exception("value for placeholder '{$ph_name}' must be of type 'int', got type '" . gettype($ph_val) . "'");
						}

						$ph_vals[] = $ph_val;
						break;
					case 'dec':
						if (! preg_match('/^(?:\d++(?:\.\d*+)?|\.\d++)\z/', $ph_val)) {
							throw new \Exception("value for placeholder '{$ph_name}' must be of type 'dec', got type '" . gettype($ph_val) . "'");
						}

						$ph_vals[] = (float)$ph_val;
						break;
					case 'name':
						$ph_vals[] = $this->quoteIdentifier($ph_val);
						break;
					case 'raw':
						$ph_vals[] = $ph_val;
						break;
				}
			}

			$query = substr_replace($query, $replace = join(', ', $ph_vals), $ph_pos, $ph_len);
			$pos = $ph_pos + strlen($replace);
		}
		return $query;
	}

	/**
	 * (non-PHPdoc)
	 * @see \Bacon\Database\Adapter::pquery()
	 */
	public function pquery($query, array $phs = null, $buffered = true) {
		return $this->query($this->pquery_sql($query, $phs), $buffered);
	}

	//	abstract public function quote($string);

	abstract public function quoteIdentifier($string);


	/**
	 * (non-PHPdoc)
	 * @see Bacon\Database.Adapter::getType()
	*/
	public function getType() {
		return $this->__type;
	}

	/**
	 * (non-PHPdoc)
	 * @see Bacon\Database.Adapter::isMaster()
	 */
	public function isMaster() {
		if($this->__type == 'master') {
			return true;
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see \Bacon\Database\Adapter::insert()
	 */
	public function insert($table, array $data = array(), array $filter = array()) {
		$data = array_diff_key($data, array_flip($filter));
		$fields = array_keys($data);
		array_walk($fields,
		function(&$item, $key) 	{
			$item = $this->quoteIdentifier($item);
		}
		);

		array_walk($data,
		function(&$item, $key) {
			$item = $this->quote($item);
		}
		);

		$q = 'INSERT INTO '.$this->quoteIdentifier($table).' ('.implode(',', $fields).') VALUES ('.implode(',', $data).')';
		return $this->query($q);
	}

	/**
	 * (non-PHPdoc)
	 * @see \Bacon\Database\Adapter::update()
	 */
	public function update($table, array $where, array $data = array(), array $filter = array()) {
		$data = array_diff_key($data, array_flip($filter));
		$fields = array_keys($data);
		$cols = array();
		foreach($data as $key => $val) {
			$cols[] = $this->quoteIdentifier($key).'='.$this->quote($val, true);
		}

		$q = 'UPDATE '.$this->quoteIdentifier($table).' SET '.implode(', ', $cols).' WHERE '.$where[0];
		return $this->query($this->pquery_sql($q, $where[1]));
	}


	/**
	 * (non-PHPdoc)
	 * @see \Bacon\Database\Adapter::delete()
	 */
	public function delete($table, array $where) {
		$q = 'DELETE FROM '.$this->quoteIdentifier($table).' WHERE '.$where[0];
		return $this->query($this->pquery_sql($q, $where[1]));
	}
}