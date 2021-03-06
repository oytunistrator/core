<?php
namespace Bluejacket\Connector;

class QueryBuilder {
	function __construct($query = null) {
		$this->query = $query;
	}

	function prepare() {
		return $this->query;
	}

	/**
	 * select sql generator
	 * @param  string $arr
	 * @return mixed
	 */
	function select($arr = "*") {
		$query = "SELECT ";
		if (is_array($arr) && count($arr) > 0) {
			$last_key = self::_getLastKey($arr);
			foreach ($arr as $key => $val) {
				$query .= $val;
				if ($key != $last_key) {
					$query .= ",";
				}
			}
		} else {
			$query .= "*";
		}

		$this->query = $query;
		return new self($this->query);
	}

	/**
	 * where sql generator
	 * @param  array $arr
	 * @param  string $bitwise
	 * @return mixed
	 */
	function where($arr = null, $bitwise = "and") {
		if (is_array($arr)) {
			$query    = " WHERE ";
			$last_key = self::_getLastKey($arr);
			foreach ($arr as $key => $val) {
				$query .= $key."=".$val;
				if ($key != $last_key) {
					$query .= " ".strtoupper($bitwise)." ";
				}
			}
		} else {
			$query .= $arr;
		}

		$this->query .= $query;
		return new self($this->query);
	}

	/**
	 * set query generator
	 * @param mixed $arr
	 * @return mixed
	 */
	function set($arr = null) {
		if (is_array($arr)) {
			$query    = " SET ";
			$last_key = self::_getLastKey($arr);
			foreach ($arr as $key => $val) {
				$query .= $key."=".$val;
				if ($key != $last_key) {
					$query .= ",";
				}
			}
		} else {
			$query .= $arr;
		}

		$this->query .= $query;
		return new self($this->query);
	}

	/**
	 * createFor query generator
	 * @param mixed $arr
	 * @return mixed
	 */
	function createFor($arr = null) {
		if (is_array($arr)) {
			$query    = " ( ";
			$last_key = self::_getLastKey($arr);
			foreach ($arr as $key => $val) {
				$query .= $key." ".$val;
				if ($key != $last_key) {
					$query .= ",";
				}
				$query = " ) ";
			}
		} else {
			$query .= $arr;
		}

		$this->query .= $query;
		return new self($this->query);
	}

	/**
	 * alterFor query generator
	 * @param mixed $arr
	 * @return mixed
	 */
	function alterFor($arr = null) {
		if (is_array($arr)) {
			$last_key = self::_getLastKey($arr);
			foreach ($arr as $key => $val) {
				$query .= " CHANGE ".$key;
				$query .= $key." ".$val;
				if ($key != $last_key) {
					$query .= ",";
				}
			}
		} else {
			$query .= $arr;
		}

		$this->query .= $query;
		return new self($this->query);
	}

	/**
	 * dataFor query generator
	 * @param mixed $arr
	 * @return mixed
	 */
	function dataFor($arr = null) {
		if (is_array($arr)) {
			$query    = " ( ";
			$last_key = self::_getLastKey($arr);
			foreach ($arr as $key => $val) {
				$query .= $key;
				if ($key != $last_key) {
					$query .= ",";
				}
			}
			$query = " ) VALUES ( ";
			foreach ($arr as $key => $val) {
				$query .= $value;
				if ($key != $last_key) {
					$query .= ",";
				}
			}
			$query = " ) ";
		} else {
			$query .= $arr;
		}

		$this->query .= $query;
		return new self($this->query);
	}

	/**
	 * getLastKey function.
	 *
	 * @access public
	 * @static
	 * @param mixed $data
	 * @return void
	 */
	static function _getLastKey($data) {
		if (!is_array($data)) {
			return false;
		}
		return key(array_slice($data, -1, 1, TRUE));
	}

	/**
	 * insert sql generator
	 * @param  array $data
	 * @return mixed
	 */
	function insert($table) {
		$this->query .= "INSERT INTO ".$table;
		return new self($this->query);
	}

	/**
	 * delete sql generator
	 * @return mixed
	 */
	function delete() {
		$this->query .= "DELETE";
		return new self($this->query);
	}

	/**
	 * update sql generator
	 * @return mixed
	 */
	function update() {
		$this->query .= "UPDATE";
		return new self($this->query);
	}

	/**
	 * and sql generator
	 * @return mixed
	 */
	function _and() {
		$this->query .= "AND";
		return new self($this->query);
	}

	/**
	 * or sql generator
	 * @return mixed
	 */
	function _or() {
		$this->query .= "OR";
		return new self($this->query);
	}

	/**
	 * regexp sql generator
	 * @return mixed
	 */
	function regexp() {
		$this->query .= "REGEXP";
		return new self($this->query);
	}

	/**
	 * equal sql generator
	 * @return mixed
	 */
	function eq($key, $val) {
		$this->query .= "`".$key."` = `".$val."`";
		return new self($this->query);
	}

	/**
	 * _not sql generator
	 * @return mixed
	 */
	function _not($key, $val) {
		$this->query .= "`".$key."` != `".$val."`";
		return new self($this->query);
	}

	/**
	 * greater equal sql generator
	 * @return mixed
	 */
	function greaterEq($key, $val) {
		$this->query .= "`".$key."` >= `".$val."`";
		return new self($this->query);
	}

	/**
	 * small equal sql generator
	 * @return mixed
	 */
	function smallEq($key, $val) {
		$this->query .= "`".$key."` <= `".$val."`";
		return new self($this->query);
	}

	/**
	 * greater sql generator
	 * @return mixed
	 */
	function greater($key, $val) {
		$this->query .= "`".$key."` > `".$val."`";
		return new self($this->query);
	}

	/**
	 * smaller sql generator
	 * @return mixed
	 */
	function smaller($key, $val) {
		$this->query .= "`".$key."` < `".$val."`";
		return new self($this->query);
	}

	/**
	 * from sql generator
	 * @return mixed
	 */
	function from($table) {
		$this->query .= " FROM ".$table;
		return new self($this->query);
	}

	/**
	 * limit sql generator
	 * @return mixed
	 */
	function limit($start, $end) {
		$this->query .= " LIMIT ".$start.",".$end;
		return new self($this->query);
	}

	/**
	 * create sql generator
	 * @return mixed
	 */
	function create($type = null, $name = null) {
		if (!is_null($type) && !is_null($name)) {
			$this->query .= "CREATE {$type} {$name}";
		}
		return new self($this->query);
	}

	/**
	 * drop sql generator
	 * @return mixed
	 */
	function drop($type = null, $name = null) {
		if (!is_null($type) && !is_null($name)) {
			$this->query .= "DROP {$type} {$name}";
		}
		return new self($this->query);
	}

	/*
	 * show sql generator
	 * @return mixed
	 */
	function show($type = null) {
		if (!is_null($type)) {
			$this->query .= "SHOW {$type}}";
		}
		return new self($this->query);
	}

	/*
	 * alter sql generator
	 * @return mixed
	 */
	function alter($type = null, $table = null) {
		if (!is_null($type) && !is_null($table)) {
			$this->query .= "ALTER {$type} {$table}";
		}
		return new self($this->query);
	}

	/*
	 * addPrimary sql generator
	 * @return mixed
	 */
	function addPrimary($key = null) {
		if (!is_null($key)) {
			$this->query .= " ADD PRIMARY KEY (".$key.") ";
		}
		return new self($this->query);
	}

	/**
	 * groupBy function.
	 * @return mixed
	 */
	function groupBy($object = null) {
		if (!is_null($object)) {
			$this->_query .= " GROUP BY ".$object;
		}
		return new self($this->query);
	}

	/**
	 * orderBy function.
	 * @return mixed
	 */
	function orderBy($object = null) {
		if (!is_null($object)) {
			$this->_query .= " ORDER BY ".$object;
		}
		return new self($this->query);
	}

	/**
	 * _asc function.
	 * @return mixed
	 */
	function _asc($object = null) {
		$this->_query .= " ASC ";
		return new self($this->query);
	}

	/**
	 * _desc function.
	 * @return mixed
	 */
	function _desc($object = null) {
		$this->_query .= " DESC ";
		return new self($this->query);
	}

	/**
	 * _in function.
	 * @return mixed
	 */
	function _in($data = array()) {
		$this->_query .= " IN (";
		if (is_array($data)) {
			$last_key = self::_getLastKey($data);
			foreach ($data as $key => $val) {
				$this->_query .= " CHANGE ".$key;
				$this->_query .= $key." ".$val;
				if ($key != $last_key) {
					$this->_query .= ",";
				}
			}
		}
		$this->_query .= ")";
		return new self($this->query);
	}

	/**
	 * union function
	 * @return mixed
	 */
	function union($arr = array()) {
		if (is_array($arr) && count($arr) > 0) {
			$last_key = self::_getLastKey($arr);
			foreach ($arr as $key => $query) {
				$this->query .= " (".$query.") ";
				if ($key != $last_key) {
					$this->_query .= " UNION ";
				}
			}
		} else {
			$this->_query .= " UNION ";
		}
		return new self($this->query);
	}
}
