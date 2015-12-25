<?php
namespace Bluejacket\Connectors;
/**
 * New SQL generator with extention PDO.
 */
class SQL
{
    function __construct($query = null){
        $this->query = $query;
    }

    /**
     * connect with config
     * @param  array $config
     * @return mixed
     */
    static function connect($config){
	    try {
  			if($config['driver'] == "mysql"){
  				$array = array(
  			    \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES ".$config['charset']
  			  );
  			}else{
  				$array = array();
  			}
        $conf=null;
        foreach($config as $k => $v){
          switch($k){
            case "driver":
              $conf.=$v;
              break;
            case "server":
              $conf.=':host='.$v;
              break;
            case "port":
              $conf.=';port='.$v;
              break;
            case "database":
              $conf.=';dbname='.$v;
              break;
            case "charset":
              $conf.=';charset='.$v;
              break;
            case "username":
              $username = isset($v) ? $v : false;
              break;
            case "password":
              $password = isset($v) ? $v : false;
              break;
          }
        }
  			@$pdo = new \PDO($conf, $username, $password, $array);
        @$pdo->beginTransaction();
    		} catch (\PDOException $e) {
    			if(DEBUG){
    				$this->error->show("Connection failed: ".$e->getMessage(),1);
    		}
  		}
  		if($pdo){
  			return $pdo;
  		}
  		return false;
    }

    public static function _connectCheck($config = array()){
      if(!isset($config) || (is_array($config) && count($config) == 0)){
        $config = array(
          "driver" => DB_DRIVER,
          "server" => DB_SERVER,
          "database" => DB_DATABASE,
          "username" => DB_USERNAME,
          "password" => DB_PASSWORD,
          "port" => DB_PORT,
          "charset" => DB_CHARSET
        );
      }

      return self::connect($config);
    }

    /**
     * execute command only
     * @return mixed
     */
    function run($config = array()){
      $db = self::_connectCheck($config);

  		try{
  			if(isset($this->query)){
          @$result = $db->run($this->query);
          @$result->commit();
  				if($result->errorCode() != 0){
  					throw new \Exception("Query not run! <br> Query: ".$this->query);
  				}
  			}else{
  				throw new \Exception("Query is null! <br> Query: ".$this->query);
  			}
  		}catch(\Exception $e){
  			if(DEBUG){
  				$this->error->show("Query Failed: ".$e->getMessage());
  			}
  			return false;
  		}
      return true;
    }

    /**
     * query and return results
     * @return mixed
     */
    function query($config = array()){
      $db = self::_connectCheck($config);

  		try{
  			if(isset($this->query)){
  				@$result = $db->query($this->query);
          @$result->commit();
  				if($result->errorCode() != 0){
  					return $result;
  				}else{
  					throw new \Exception("Output not array!");
  				}
  			}else{
  				throw new \Exception("Query is null!");
  			}
  		}catch(\Exception $e){
  			if(DEBUG){
  				$this->error->show("Failed: ".$e->getMessage()." <br> Query: ".$this->query);
  			}
  		}
	}

  function fetchAll($data = array(), $config = array()){
    $db = self::_connectCheck($config);

    try{
			if(isset($this->query)){
        @$beforeRun = $db->prepare($this->query);
        @$result = $beforeRun->execute($data);
        @$result->commit();
				if($result->errorCode() == 0){
					return $result->fetchAll();
				}else{
					throw new \Exception("Output not array!");
				}
			}else{
				throw new \Exception("Query is null!");
			}
		}catch(\Exception $e){
			if(DEBUG){
				$this->error->show("Failed: ".$e->getMessage()." <br> Query: ".$this->query);
			}
		}
  }

  function fetch($data = array(), $config = array()){
    $db = self::_connectCheck($config);

    try{
			if(isset($this->query)){
        @$beforeRun = $db->prepare($this->query);
        @$result = $beforeRun->execute($data);
        @$result->commit();
				if($result->errorCode() == 0){
					return $result->fetch();
				}else{
					throw new \Exception("Output not array!");
				}
			}else{
				throw new \Exception("Query is null!");
			}
		}catch(\Exception $e){
			if(DEBUG){
				$this->error->show("Failed: ".$e->getMessage()." <br> Query: ".$this->query);
			}
		}
  }

  function rollBack(){
    $db = self::_connectCheck($config);
    return $db->rollBack();
  }

  /**
   * select sql generator
   * @param  string $arr
   * @return mixed
   */
  function select($arr = "*"){
      $query = "SELECT ";
      if(is_array($arr)){
          $last_key=key(array_slice($arr, -1,1, TRUE));
          foreach($arr as $key => $val){
              $query .= $val;
           if($key != $last_key){
               $query .= ",";
           }
          }
      }else{
          $query .= $arr;
      }


      $this->query .= $query;
      return new self($this->query);
    }

    /**
     * where sql generator
     * @param  array $arr
     * @param  string $bitwise
     * @return mixed
     */
    function where($arr = null, $bitwise = "and"){
        $query = " WHERE ";
        if(is_array($arr)){
            $last_key=key(array_slice($arr, -1,1, TRUE));
            foreach($arr as $key => $val){
                $query .= $key."=".$val;
             if($key != $last_key){
                 $query .= " ".strtoupper($bitwise)." ";
             }
            }
        }else{
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
    function set($arr = null){
        $query = " SET ";
        if(is_array($arr)){
            $last_key=key(array_slice($arr, -1,1, TRUE));
            foreach($arr as $key => $val){
                $query .= $key."=".$val;
             if($key != $last_key){
                 $query .= ",";
             }
            }
        }else{
            $query .= $arr;
        }


        $this->query .= $query;
        return new self($this->query);
    }

    /**
     * insert sql generator
     * @param  array $data
     * @return mixed
     */
    function insert($data){
      $this->query = "INSERT INTO ".$this->_table;
  		$output=null;
  		$last_key=key(array_slice($data, -1,1, TRUE));
  		if(is_array($data)){
  			$output.="  (";
  			foreach($data as $key => $value){
  				$output.="`$key`";
  				if($key!=$last_key){
  					$output.=", ";
  				}
  			}
  			$output.=") VALUES (";
  			foreach($data as $key => $value){
  				$value = str_replace("'","\'",$value);
  				$value = str_replace('"','\"',$value);
  				$output.="'$value'";
  				if($key!=$last_key){
  					$output.=", ";
  				}
  			}
  			$output.=");";
  			$this->query .= $output;
  		}
		return new self($this->query);
	}

  /**
   * delete sql generator
   * @return mixed
   */
  function delete(){
      $this->query .= "DELETE";
      return new self($this->query);
  }

  /**
   * update sql generator
   * @return mixed
   */
  function update(){
      $this->query .= "UPDATE";
      return new self($this->query);
  }

  /**
   * and sql generator
   * @return mixed
   */
	function _and(){
      $this->query .= "AND";
      return new self($this->query);
  }

  /**
   * or sql generator
   * @return mixed
   */
	function _or(){
      $this->query .= "OR";
      return new self($this->query);
  }

  /**
   * regexp sql generator
   * @return mixed
   */
	function regexp(){
      $this->query .= "REGEXP";
      return new self($this->query);
  }

  /**
   * equal sql generator
   * @return mixed
   */
	function eq($key,$val){
      $this->query .= "`".$key."` = `".$val."`";
      return new self($this->query);
  }

  /**
   * greater equal sql generator
   * @return mixed
   */
	function greaterEq($key,$val){
      $this->query .= "`".$key."` >= `".$val."`";
      return new self($this->query);
  }

  /**
   * small equal sql generator
   * @return mixed
   */
	function smallEq($key,$val){
      $this->query .= "`".$key."` <= `".$val."`";
      return new self($this->query);
  }

  /**
   * greater sql generator
   * @return mixed
   */
	function greater($key,$val){
      $this->query .= "`".$key."` > `".$val."`";
      return new self($this->query);
  }

  /**
   * smaller sql generator
   * @return mixed
   */
	function smaller($key,$val){
      $this->query .= "`".$key."` < `".$val."`";
      return new self($this->query);
  }

  /**
   * from sql generator
   * @return mixed
   */
  function from($table){
      $this->query .= " FROM ".$table;
      return new self($this->query);
  }

  /**
   * limit sql generator
   * @return mixed
   */
  function limit($start,$end){
      $this->query .= " LIMIT ".$start.",".$end;
      return new self($this->query);
  }

  /**
   * create sql generator
   * @return mixed
   */
  function create($type = null, $name = null){
      if(!is_null($type) && !is_null($name)){
        $this->query .= "CREATE {$type} {$name}";
      }
      return new self($this->query);
  }

  /**
   * drop sql generator
   * @return mixed
   */
  function drop($type = null, $name = null){
      if(!is_null($type) && !is_null($name)){
        $this->query .= "DROP {$type} {$name}";
      }
      return new self($this->query);
  }

  /**
   * dump sql generator
   * @return mixed
   */
  public function dump(){
    return $this->query;
  }
}
