<?php
/**
 * DB class.
 */
namespace Framework\Core;
class DB
{
	/**
	 * pdo
	 *
	 * @var mixed
	 * @access private
	 */
	private $pdo;
	/**
	 * boot
	 *
	 * @var mixed
	 * @access private
	 */
	private $boot;
	/**
	 * _query
	 *
	 * @var mixed
	 * @access public
	 */
	public $_query;
	/**
	 * output
	 *
	 * @var mixed
	 * @access public
	 */
	public $output;
	/**
	 * _table
	 *
	 * @var mixed
	 * @access public
	 */
	public $_table;
	/**
	 * count
	 *
	 * @var mixed
	 * @access public
	 */
	public $count;
	/**
	 * _config
	 *
	 * (default value: array())
	 *
	 * @var array
	 * @access public
	 */
	public $_config = array();

	public $_history = array();

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct(){
		$this->error = new Error();

		if(is_file('Config/Database.php')){
			$this->_config = array(
				"driver" => DB_DRIVER,
				"server" => DB_SERVER,
				"database" => DB_DATABASE,
				"username" => DB_USERNAME,
				"password" => DB_PASSWORD,
				"port" => DB_PORT,
				"charset" => DB_CHARSET
			);

			$this->_connect();
		}
	}

	/**
	 * _connect function.
	 *
	 * @access public
	 * @return void
	 */
	public function _connect(){
		try {
			if($this->_config['driver'] == "mysql"){
				$array = array(
			    \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES ".$this->_config['charset']
			  );
			}else{
				$array = array();
			}
			@$this->pdo = new \PDO($this->_config['driver'].':host='.$this->_config['server'].';port='.$this->_config['port'].';dbname='.$this->_config['database'].';charset='.$this->_config['charset'], $this->_config['username'], $this->_config['password'],$array);
		} catch (\PDOException $e) {
			if(DEBUG){
				$this->error->show("Connection failed: ".$e->getMessage(),1);
			}
		}
		//$this->pdo->exec("SET NAMES UTF8");
		//$this->pdo->exec("SET CHARACTER SET UTF8");
	}

	/**
	 * changeConnection function.
	 *
	 * @access public
	 * @param mixed $server
	 * @param mixed $username
	 * @param mixed $password
	 * @param mixed $database
	 * @param mixed $driver (default: null)
	 * @return void
	 */
	public function changeConnection($server,$username,$password,$database,$port,$driver=null){
		$this->_config['server'] = $server;
		$this->_config['username'] = $username;
		$this->_config['password'] = $password;
		$this->_config['database'] = $database;
		$this->_config['port'] = $port;
		if(!is_null($driver)) $this->_config['driver'] = $driver;

		$this->_connect();
	}

	/**
	 * changeDb function.
	 *
	 * @access public
	 * @param mixed $db
	 * @return void
	 */
	public function changeDb($db){
		$this->_config['database'] = $db;
		$this->_connect();
	}

	/**
	 * getLastInsertedId function.
	 *
	 * @access public
	 * @return void
	 */
	public function getLastInsertedId(){
		$out = $this->pdo->lastInsertId();
		return $out;
	}

	/**
	 * query function.
	 *
	 * @access public
	 * @return void
	 */
	public function query(){
		try{
			if(isset($this->_query)){
				$this->_history[] = $this->_query;
				@$out = $this->pdo->query($this->_query);
				if($out){
					$this->output = $out;
				}else{
					throw new \Exception("Output not array!");
				}
			}else{
				throw new \Exception("Query is null!");
			}
		}catch(\Exception $e){
			if(DEBUG){
				$this->error->show("Failed: ".$e->getMessage()." <br> Query: ".$this->_query);
			}
		}
	}

	/**
	 * run function.
	 *
	 * @access public
	 * @return void
	 */
	public function run(){
		try{
			if(isset($this->_query)){
				$this->_history[] = $this->_query;
				if(!$this->pdo->exec($this->_query)){
					throw new \Exception("Output not array! <br> Query: ".$this->_query);
				}
			}else{
				throw new \Exception("Query is null! <br> Query: ".$this->_query);
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
	 * table function.
	 *
	 * @access public
	 * @param mixed $name
	 * @return void
	 */
	public function table($name){
		$this->_table = $name;
	}

	/**
	 * select function.
	 *
	 * @access public
	 * @param mixed $array (default: null)
	 * @return void
	 */
	public function select($array=null){
		if(is_array($array)){
			$selector=null;
			$last_key=key(array_slice($array, -1,1, TRUE));
			foreach($array as $key => $val){
				$selector.="$val";
				if($key!=$last_key){
					$selector.=",";
				}
			}
		}else{
			$selector = "*";
		}

		$this->_query = "SELECT  $selector  FROM ".$this->_table;
	}

	/**
	 * delete function.
	 *
	 * @access public
	 * @return void
	 */
	public function delete(){
		$this->_query = "DELETE FROM ".$this->_table;
	}

	/**
	 * count function.
	 *
	 * @access public
	 * @return void
	 */
	public function count(){
		$this->_query = "SELECT count(*) as count FROM ".$this->_table;
	}

	/**
	 * insert function.
	 *
	 * @access public
	 * @param mixed $data
	 * @return void
	 */
	public function insert($data){
		$this->_query = "INSERT INTO ".$this->_table;
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
			$this->_query .= $output;
		}
	}

	/**
	 * where function.
	 *
	 * @access public
	 * @param mixed $data
	 * @param mixed $exclude (default: null)
	 * @param bool $or (default: false)
	 * @return void
	 */
	public function where($data,$exclude=null,$or=false){
		$output=null;
		if(is_array($data)){
			$last_key=key(array_slice($data, -1,1, TRUE));
			foreach($data as $key => $value){
				$output.="`$key`='$value'";
				if($key!=$last_key){
					if($or) $output.=" OR ";
					else $output.=" AND ";
				}
			}
		}
		if(is_array($exclude)){
			$last_key2=key(array_slice($exclude, -1,1, TRUE));
			foreach($exclude as $key => $value){
				$output.="`$key`!='$value'";
				if($key!=$last_key2){
					$output.=" AND ";
				}
			}
		}
		$this->_query .= " WHERE ".$output;
	}


	/**
	 * _where function add ends of _query to where
	 * @return mixed
	 */
	public function _where(){
		$this->_query .= " WHERE ";
	}

	/**
	 * _like adds from array ends of _query like queries
	 * @param  array  $data data content to array
	 * @return mixed
	 */
	public function _like($data = array()){
		$last_key2=key(array_slice($data, -1,1, TRUE));
		foreach($data as $key => $value){
			$output.="`$key` %'$value'%";
			if($key!=$last_key2){
				$output.=" LIKE ";
			}
		}
		$this->_query .= $output;
	}

	/**
	 * _and adds from array ends of _query and queries
	 * @param  array  $data data
	 * @return mixed
	 */
	public function _and($data = array()){
		$last_key2=key(array_slice($data, -1,1, TRUE));
		foreach($data as $key => $value){
			$output.="`$key`=='$value'";
			if($key!=$last_key2){
				$output.=" AND ";
			}
		}
		$this->_query .= $output;
	}

	/**
	 * adds from array ends of _query or queries
	 * @param  array  $data data
	 * @return mixed
	 */
	public function _or($data = array()){
		$last_key2=key(array_slice($data, -1,1, TRUE));
		foreach($data as $key => $value){
			$output.="`$key`=='$value'";
			if($key!=$last_key2){
				$output.=" OR ";
			}
		}
		$this->_query .= $output;
	}

	/**
	 * adds from array ends of _query and not queries
	 * @param  array  $data data
	 * @return mixed
	 */
	public function _not($data = array()){
		$last_key2=key(array_slice($data, -1,1, TRUE));
		foreach($data as $key => $value){
			$output.="`$key`!='$value'";
			if($key!=$last_key2){
				$output.=" AND ";
			}
		}
		$this->_query .= $output;
	}

	/**
	 * adds from array ends of _query order by queries
	 * @param  string $object string to object
	 * @param  string $asc    string to asc desc val
	 * @return mixed
	 */
	public function _orderby($object,$asc){
		return " ORDER BY ".$object." ".$asc;
	}

	/**
	 * adds from array ends of _query group by queries
	 * @param  string $object string to object
	 * @return mixed
	 */
	public function _groupby($object){
		return " GROUP BY ".$object;
	}


	/**
	 * create function.
	 *
	 * @access public
	 * @param mixed $data
	 * @return void
	 */
	public function create($data){
		$q_create_table="CREATE TABLE IF NOT EXISTS `".$this->_table."`";
		$q_create_table.="(";
		$output=null;
		$last_key=key(array_slice($data, -1,1, TRUE));
		if(is_array($data)){
			foreach($data as $key => $value){
				$output.="`$key` $value";
				if($key!=$last_key){
					$output.=", ";
				}
			}
			$q_create_table .= $output;
		}
		$q_create_table.=")";


		if($this->_config['charset']){
			$q_create_table .=" CHARSET=".$this->_config['charset'];
		}


		$this->_query .= $q_create_table;
	}

	/**
	 * drop function.
	 *
	 * @access public
	 * @return void
	 */
	public function drop(){
		$this->_query = "DROP TABLE ".$this->_table;
	}

	/**
	 * colmns function.
	 *
	 * @access public
	 * @return void
	 */
	public function colmns(){
		$this->_query = "SHOW COLUMNS FROM ".$this->_table;
	}

	/**
	 * alter function.
	 *
	 * @access public
	 * @param mixed $data
	 * @return void
	 */
	public function alter($data){
		$this->_query = "ALTER ".$this->_table." ";
		$last_key=key(array_slice($data, -1,1, TRUE));
		foreach ($data as $key => $value) {
			$this->_query .= $value;
			if($key!=$last_key){
				$this->_query.=", ";
			}

		}
	}

	/**
	 * orderBy function.
	 *
	 * @access public
	 * @param mixed $object (default: null)
	 * @param bool $asc (default: true)
	 * @return void
	 */
	public function orderBy($object=null,$asc=true){
		if($asc) $asc = "ASC";
		else $asc = "DESC";
		$this->_query .= " ORDER BY ".$object." ".$asc;
	}

	/**
	 * groupBy function.
	 *
	 * @access public
	 * @param mixed $object
	 * @return void
	 */
	public function groupBy($object){
		if(isset($object)) $this->_query .= " GROUP BY ".$object;
	}

	/**
	 * limit function.
	 *
	 * @access public
	 * @param int $start (default: 0)
	 * @param int $end (default: 200)
	 * @return void
	 */
	public function limit($start=0,$end=200){
		$this->_query .= " LIMIT ".$start.",".$end;
	}

	/**
	 * update function.
	 *
	 * @access public
	 * @param mixed $data
	 * @return void
	 */
	public function update($data){
		$this->_query = "UPDATE ".$this->_table." SET ";

		$output=null;
		$last_key=key(array_slice($data, -1,1, TRUE));
		if(is_array($data)){
			foreach($data as $key => $value){
				$value = str_replace("'","\'",$value);
				$value = str_replace('"','\"',$value);
				$output.="`$key`='$value'";
				if($key!=$last_key){
					$output.=", ";
				}
			}
		}
		$this->_query .= $output;
	}

	/**
	 * extra function.
	 *
	 * @access public
	 * @param mixed $extra
	 * @return void
	 */
	public function extra($extra){
		$this->_query .= $extra;
	}

	/**
	 * keys function.
	 *
	 * @access public
	 * @return void
	 */
	public function keys(){
		$this->_query = "SHOW KEYS FROM ".$this->_table;
	}

	/**
	 * columns function.
	 *
	 * @access public
	 * @return void
	 */
	public function columns(){
		$this->_query = "SHOW COLUMNS FROM ".$this->_table;
	}


	/**
	 * search function.
	 *
	 * @access public
	 * @param mixed $data (default: null)
	 * @param array $config (default: array())
	 * @return void
	 */
	public function search($data=null,$config=null){
		$output = null;
		$this->_query = "SELECT * FROM ".$this->_table;
		if($data!=null){
			$this->_query.=" WHERE ";
		}else{
			return;
		}

		if(is_array($config['filter'])){
			$last_key=key(array_slice($config['filter'], -1,1, TRUE));
			if(is_array($config['filter'])){
				foreach($config['filter'] as $key => $value){
					$output.=" `$key`='$value' ";
					$output.= " AND ";
					/*
						if($key!=$last_key){
							$output.= " AND ";
						}
						*/
				}
			}
		}

		if(is_array($config['regexp'])){
			$last_key=key(array_slice($data, -1,1, TRUE));
			if(is_array($data)){
				foreach($data as $key => $value){
					$output.=" `$key` REGEXP '$value' ";
					if($key!=$last_key){
						$output.= $config['or'] ? " OR " : " AND ";
					}
				}
			}
		}else{
			$last_key=key(array_slice($data, -1,1, TRUE));
			if(is_array($data)){
				foreach($data as $key => $value){
					$output.=" `$key` LIKE '%$value%' ";
					if($key!=$last_key){
						$output.=" OR ";
					}
				}
			}
		}

		if(isset($config['where']) && is_array($config['where'])){
			$output.=" AND ";
			$last_key2=key(array_slice($config['where'], -1,1, TRUE));
			foreach($config['where'] as $key => $value){
				$output.=" `$key`='$value' ";
				if($key!=$last_key2){
					$output.=" AND ";
				}
			}
		}

		if(isset($config['orderby']) && is_array($config['orderby'])){
			$output.=$this->_orderby($config['orderby'][0],$config['orderby'][1]);
		}

		if(isset($config['groupby'])){
			$output.=$this->_groupby($config['groupby']);
		}

		if(!is_null($config['extra'])){
			$output.=$config['extra'];
		}

		$this->_query .= $output;

	}

	/**
	 * custom function.
	 *
	 * @access public
	 * @param mixed $query
	 * @return void
	 */
	public function custom($query){
		$this->_query = $query;
	}


	/**
	 * repair function.
	 *
	 * @access public
	 * @return void
	 */
	public function fixCharset(array $options = array()){
		//header("Content-type: text/plain");
		$this->_query = "SHOW TABLES";
		$this->query();

		if(isset($options['charset']) && isset($options['collate'])){
			foreach ($this->output->fetchAll() as $table) {

				$this->_query = "ALTER TABLE $table[0] DEFAULT CHARACTER SET ".$options['charset']." COLLATE ".$options['collate'].", CONVERT TO CHARACTER SET ".$options['charset'];
				$this->pdo->exec($this->_query);

				$this->_query = "SHOW COLUMNS FROM $table[0]";
				$out = $this->pdo->query($this->_query);

				foreach($out->fetchAll() as $d){
					$this->_query = "ALTER TABLE $table[0]  ".$d['Field']."  ".$d['Field']." CHARACTER SET ".$options['charset']." COLLATE ".$options['collate'];
					$this->pdo->exec($this->_query);
					$out2[$table[0]][$d['Field']]['charset']=$options['charset'];
					$out2[$table[0]][$d['Field']]['collate']=$options['collate'];
				}
				//$out2.="$table[0] changed to UTF-8. <br>";
			}
			//header("Content-type: text/html");
			return $out2;
		}
		return false;
	}


	/**
	 * addPrimaryKey function.
	 *
	 * @access public
	 * @param mixed $key
	 * @return void
	 */
	public function addPrimaryKey($key){
		$this->_query = "ALTER TABLE ".$this->_table." ADD PRIMARY KEY (".$key.")  ";
	}

	/**
	 * is_iterable function.
	 *
	 * @access private
	 * @param mixed $var
	 * @return void
	 */
	private function is_iterable($var)
	{
		return $var !== null && (is_array($var) || $var instanceof Iterator || $var instanceof IteratorAggregate);
	}


	/**
	 * searchColumn function.
	 *
	 * @access public
	 * @param mixed $column (default: null)
	 * @return void
	 */
	public function searchColumn($column=null){
		if(!is_null($column)){

			if(is_array($column)){
				$output1=null;
				$output2=null;
				$last_key=key(array_slice($column, -1,1, TRUE));
				foreach($column as $key => $val){
					$output1.="'$key'";
					$output2.="$key";
					if($key!=$last_key){
						$output1.=",";
						$output2.=",";
					}
				}


				$this->_query = 'SELECT DISTINCT TABLE_NAME
		    					FROM INFORMATION_SCHEMA.COLUMNS
								WHERE COLUMN_NAME IN ('.$output1.')
								AND TABLE_SCHEMA=\''.$this->_config['database'].'\'';


				$this->query();

				$tables = $this->output->fetchAll();
				foreach($tables as $tb){

					$output=null;
					$last_key=key(array_slice($column, -1,1, TRUE));

					foreach($column as $key => $val){
						$output.="$key LIKE '%$val%'";
						if($key!=$last_key){
							$output.=" OR ";
						}
					}
					$this->_query = 'SELECT '.$output2.' FROM '.$tb['TABLE_NAME'];
					$this->_query.=" WHERE ".$output;


					$this->query();
					$find[$tb['TABLE_NAME']]=$this->output->fetchAll();
				}
				return $find;
			}else{
				$output='\''.$column.'\'';
				$this->_query = 'SELECT DISTINCT TABLE_NAME
		    					FROM INFORMATION_SCHEMA.COLUMNS
								WHERE COLUMN_NAME IN ('.$output.')
								AND TABLE_SCHEMA=\''.$this->_config['database'].'\'';


				$this->query();

				$tables = $this->output->fetchAll();
				foreach($tables as $tb){
					$this->_query = 'SELECT '.$column.' FROM '.$tb['TABLE_NAME'];
					$this->query();
					$find[$tb['TABLE_NAME']]=$this->output->fetchAll();

				}
				return $find;
			}

			return false;
		}

	}

	/**
	 * getLastKey function.
	 *
	 * @access public
	 * @static
	 * @param mixed $data
	 * @return void
	 */
	public static function getLastKey($data){
		if(!is_array($data)) return false;
		return key(array_slice($data, -1,1, TRUE));
	}

	/**
	 * cQuery function.
	 *
	 * @access public
	 * @param mixed $query
	 * @return void
	 */
	public function cQuery($query){
		$this->_query = $query;
		$this->query();
		return $this->output;
	}


	public function tables(){
		$this->_query = "SHOW TABLES FROM ".$this->_config['database'];
		$this->query();
		$result = @$this->db->output ? $this->db->output->fetchAll() : false;
		return $result;
	}


	public function databases(){
		$this->_query = "SHOW DATABASES";
		$this->query();
		$result = @$this->db->output ? $this->db->output->fetchAll() : false;
		return $result;
	}


	/**
	 * sum function.
	 *
	 * @access public
	 * @param array $arr (default: array())
	 * @return void
	 */
	public function sum($arr=array()){
		$output=null;
		if(isset($arr['cols']) && is_array($arr['cols'])){
			$output.='SELECT ';
			$last_key=key(array_slice($arr['cols'], -1,1, TRUE));
			foreach($arr['cols'] as $key){
				$output.="SUM(`$key`) as `$key`";
				if($key!=$last_key){
					$output.=",";
				}
			}
			$output.=" FROM ".$this->_table;
			if(is_array($arr['where'])){
				$output.=" WHERE ";
				$last_key=key(array_slice($arr['where'], -1,1, TRUE));
				foreach($arr['where'] as $key => $value){
					$output.="`$key`='$value'";
					if($key!=$last_key){
						if($or) $output.=" OR ";
						else $output.=" AND ";
					}
				}
			}
			if(is_array($arr['not'])){
				$last_key2=key(array_slice($arr['not'], -1,1, TRUE));
				foreach($arr['not'] as $key => $value){
					$output.="`$key`!='$value'";
					if($key!=$last_key2){
						$output.=" AND ";
					}
				}
			}
			$this->_query = $output;
		}
	}
}
?>
