<?php
namespace Bluejacket\Connector;
/**
 * New SQL generator with extention PDO.
 */
use Bluejacket\Core\Core;

class SQLDB {
	function __construct($config = array()) {
		if (!isset($config) || (is_array($config) && count($config) == 0)) {
			$config = array(
				"driver"   => DB_DRIVER,
				"server"   => DB_SERVER,
				"database" => DB_DATABASE,
				"username" => DB_USERNAME,
				"password" => DB_PASSWORD,
				"port"     => DB_PORT,
				"charset"  => DB_CHARSET
			);
		}

		try {
			if ($config['driver'] == "mysql") {
				$array = array(
					\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES ".$config['charset'],
				);
			} else {
				$array = array();
			}
			$conf = null;
			foreach ($config as $k => $v) {
				switch ($k) {
					case "driver":
						$conf .= $v;
						break;
					case "server":
						$conf .= ':host='.$v;
						break;
					case "port":
						$conf .= ';port='.$v;
						break;
					case "database":
						$conf .= ';dbname='.$v;
						break;
					case "charset":
						$conf .= ';charset='.$v;
						break;
					case "username":
						$username = isset($v)?$v:false;
						break;
					case "password":
						$password = isset($v)?$v:false;
						break;
				}
			}
			$this->db = new \PDO($conf, $username, $password, $array);
			$this->db->beginTransaction();
		} catch (\PDOException $e) {
			if (DEBUG) {
				Core::showErrorMsg("Connection failed: ".$e->getMessage(), 3);
			}
		}
		if ($this->db) {
			$this->db;
		}
	}

	/**
	 * execute command only
	 * @return mixed
	 */
	function run($query) {
		try {
			if ($query !== null) {
				$result = $this->db->run($query);
				if (!$result) {
					throw new \Exception("Error: \n".var_dump($this->db->errorInfo()));
				} else {
					$this->db->commit();
				}
			} else {
				throw new \Exception("Error: \n".var_dump($this->db->errorInfo()));
			}
		} catch (\Exception $e) {
			$this->db->rollBack();
			if (DEBUG) {
				Core::showErrorMsg("Query Failed: \n".$e->getMessage(), 3);
			}
			return false;
		}
		return true;
	}

	/**
	 * query and return results
	 * @return mixed
	 */
	function query($query) {
		try {
			if ($query !== null) {
				$result = $this->db->query($query);
				if ($result) {
					$this->db->commit();
					return $result;
				} else {
					throw new \Exception("Error: \n".var_dump($this->db->errorInfo()));
				}
			} else {
				throw new \Exception("Error: \n".var_dump($this->db->errorInfo()));
			}
		} catch (\Exception $e) {
			$this->db->rollBack();
			if (DEBUG) {
				Core::showErrorMsg("Failed: ".$e->getMessage()." \nQuery: ".$query, 3);
			}
		}
		return false;
	}

	function fetchAll($query = null, $data = array(), $config = array()) {
		try {
			if ($query !== null) {
				$beforeRun = $this->db->prepare($query);
				$beforeRun->execute($data);
				$result = $beforeRun->fetchAll();
				if ($result) {
					$this->db->commit();
					return $result;
				} else {
					throw new \Exception("Error: \n".var_dump($this->db->errorInfo()));
				}
			} else {
				throw new \Exception("Error: \n".var_dump($this->db->errorInfo()));
			}
		} catch (\Exception $e) {
			$this->db->rollBack();
			if (DEBUG) {
				Core::showErrorMsg("Failed: ".$e->getMessage()."  \nQuery: ".$query, 3);
			}
		}
	}

	function fetch($query = null, $data = array(), $config = array()) {
		try {
			if ($query !== null) {
				$beforeRun = $this->db->prepare($query);
				$beforeRun->execute($data);
				$result = $beforeRun->fetch();
				if ($result) {
					$this->db->commit();
					return $result;
				} else {
					throw new \Exception("Error: \n".var_dump($this->db->errorInfo()));
				}
			} else {
				throw new \Exception("Error: \n".var_dump($this->db->errorInfo()));
			}
		} catch (\Exception $e) {
			$this->db->rollBack();
			if (DEBUG) {
				Core::showErrorMsg("Failed: ".$e->getMessage()."  \nQuery: ".$query);
			}
		}
	}

	function rollBack() {
		return $this->db->rollBack();
	}

	/**
	 * lastInsertedId function.
	 * @return mixed
	 */
	function lastInsertedId() {
		return $this->db->lastInsertId();
	}
}