<?php
/**
 * Model class.
 */
namespace Bluajacket\Core;
class Model
{
	/**
	 * __construct function.
	 *
	 * @access public
	 * @param Array $properties (default: array())
	 * @return void
	 */
	public function __construct(array $properties=array()){
		$this->error = new Error();
		$this->db = new DB();
		$this->db->table($this->table());

		foreach($properties as $key => $value){
			$this->{$key} = $value;
		}
	}


	/**
	 * generate function.
	 *
	 * @access public
	 * @return void
	 */
	public function generate(){
		$this->db->table($this->table());
		try{
			if(is_array($this->migrations())){
				$this->db->create($this->migrations());
				$this->db->run();
				$this->db->alter($this->alter());
				$this->db->run();
			}
		}catch(\Exception $e){
			$this->error->show("Generate Error: ".$e->getMessage());
		}

	}


	/**
	 * destroy function.
	 *
	 * @access public
	 * @return void
	 */
	public function destroy(){
		$this->db->drop();
	}


	/**
	 * set function.
	 *
	 * @access public
	 * @param string $key (default: null)
	 * @param string $value (default: null)
	 * @return void
	 */
	public function set(string $key=null, string $value=null){
		$this->{$key} = $value;
	}


	/**
	 * delete function.
	 *
	 * @access public
	 * @param mixed $primaryKey
	 * @return void
	 */
	public function delete($primaryKey){
		if(is_array($primaryKey)){
			$this->db->delete();
			$this->db->where($primaryKey);
			$this->db->run();
			return true;
		}else if(is_numeric($primaryKey)){
			$key = $this->getPrimaryKey();
			$this->db->delete();
			$this->db->where(array($key => $primaryKey));
			$this->db->run();
			return true;
		}
		return true;
	}


	/**
	 * save function.
	 *
	 * @access public
	 * @return void
	 */
	public function save(){
		$array = $this->getModelVars();
		$this->db->insert($array);
		if($this->db->run()){
			$this->find($this->db->getLastInsertedId());
			return true;
		}
		return false;
	}


	/**
	 * find function.
	 *
	 * @access public
	 * @param mixed $primaryKey
	 * @return void
	 */
	public function find($primaryKey){
		$key = $this->getPrimaryKey();
		$this->db->select();
		$this->db->where(array($key => $primaryKey));

		$this->db->query();
		$arr = $this->db->output->fetch();
		if($arr){
			foreach($arr as $key => $val){
				if(!is_numeric($key)){
					$this->{$key} = $val;
				}
			}
			return true;
		}
		return false;
	}



	/**
	 * get function.
	 *
	 * @access public
	 * @param array $array (default: array())
	 * @param array $options (default: array())
	 * @return void
	 */
	public function get($array = array(), $options = null){
		$pk = $this->getPrimaryKey();
		$this->db->select();
		if(is_array($array)){
			$this->db->where($array);
		}else if(is_numeric($array)){
			$this->db->where(array($pk => $array));
		}

		if(is_array($options['order'])){
			$column = $options['order']['column'];
			$desc = isset($options['order']['desc']) ? $options['order']['desc'] : false;
			$asc = isset($options['order']['asc']) ? $options['order']['asc'] : false;
			if($desc){
				$opt = false;
			}else if($asc){
					$opt = true;
				}else{
				$opt = false;
			}

			$this->db->orderBy($column,$opt);
		}else{
			$this->db->orderBy($pk,false);
		}
		$this->db->query();

		$out = $this->db->output->fetch();
		if(is_array($out)){
			foreach ($out as $key => $val) {
				if(!is_numeric($key)){
					$this->{$key} = $val;
				}
			}
			if(isset($options['return'])){
				return $out[$options['return']];
			}
			return $out;
		}


		return false;
	}


	/**
	 * all function.
	 *
	 * @access public
	 * @param mixed $array (default: array|null)
	 * @param mixed $array['select'] (default: array)
	 * @param mixed $array['where'] (default: array)
	 * @param mixed $array['excludeOptions'] (default: array)
	 * @param mixed $array['excludeOptions']['exclude'] (default: array)
	 * @param mixed $array['excludeOptions']['or'] (default: array)
	 * @param mixed $array['groupBy'] (default: array)
	 * @param mixed $array['orderBy'] (default: array)
	 * @param mixed $array['limit'] (default: array)
	 * @param mixed $array['extra'] (default: null)
	 * @return void
	 */
	public function special($options=array()){
		if(is_array($options)){
			if(isset($options['select']) && is_array($options['select']))
				$this->db->select($options['select']);
			else
				$this->db->select();

			if(isset($options['where']) &&  is_array($options['where']))
				if(isset($options['excludeOptions']) && is_array($options['excludeOptions'])){
					$or = is_bool($options['excludeOptions']['or']) ?  $options['excludeOptions']['or'] : false;
					$exclude = is_array($options['excludeOptions']['exclude']) ?  $options['excludeOptions']['exclude'] : null;
					$this->db->where($options['where'],$exclude,$or);
				}else{
				$this->db->where($options['where']);
			}else if(isset($options['search']) && is_array($options['search'])){
				$this->db->search($options['search']['query'],($options['search']['options'] ? $options['search']['options'] : null));
			}

			if(isset($options['extra']) && !is_null($options['extra']))
				$this->db->extra($options['extra']);

			if(isset($options['groupBy']))
				$this->db->groupBy($options['groupBy']);

			if(isset($options['orderBy'][0]) && is_bool($options['orderBy'][1]))
				$this->db->orderBy($options['orderBy'][0],$options['orderBy'][1]);

			if(isset($options['limit'][0]) && is_numeric($options['limit'][0]) && is_numeric($options['limit'][1]))
				$this->db->limit($options['limit'][0],$options['limit'][1]);

			$run = true;
		}else if(isset($options)){
				$this->db->custom($options);

				$run = true;
			}else{
			$run = false;
		}

		if($run){
			$this->db->query();
			$result = @$this->db->output ? $this->db->output->fetchAll() : false;
			return $result;
		}
		return false;
	}


	/**
	 * count function.
	 *
	 * @access public
	 * @param array $array (default: array())
	 * @return void
	 */
	public function count($array=array()){
		$this->db->count();
		if(isset($array)){
			$this->db->where($array);
		}
		$this->db->query();
		$result = @$this->db->output ? $this->db->output->fetch() : false;
		$result = $result ? $result[0] : false;
		return $result;
	}


	/**
	 * update function.
	 *
	 * @access public
	 * @param int $id (default: 0)
	 * @return void
	 */
	public function update($primaryKey=0){
		if(isset($primaryKey)){
			$key = $this->getPrimaryKey();
			$array = $this->getModelVars();
			if(is_array($array)){
				$this->db->update($array);
			}
			if(is_numeric($primaryKey)) $this->db->where(array($key => $primaryKey));
			else if(is_array($primaryKey)) $this->db->where($primaryKey);
			return $this->db->run();
		}
		return false;
	}


	/**
	 * getPrimaryKey function.
	 *
	 * @access public
	 * @return void
	 */
	public function getPrimaryKey(){
		$this->db->keys();
		$this->db->where(array('Key_name' => 'PRIMARY'));
		$this->db->query();
		$result = @$this->db->output ? $this->db->output->fetch() : false;
		$result = $result ? $result['Column_name'] : false;
		return $result;
	}


	/**
	 * searchQuery function.
	 *
	 * @access public
	 * @param mixed $query
	 * @param mixed $config
	 * @return void
	 */
	public function searchQuery($query,$config=null){
		foreach ($this->search() as $key) {
			$sq[$key] = $query;
		}
		$this->db->search($sq,$config);
		$this->db->query();
		$result = @$this->db->output ? $this->db->output->fetchAll() : false;
		return $result;
	}


	/**
	 * getLastData function.
	 *
	 * @access public
	 * @return void
	 */
	public function getLastData(){
		$this->db->_query="SELECT * FROM ".$this->db->_table." ORDER BY ". $this->getPrimaryKey()." DESC LIMIT 1,1";
		$this->db->query();
		$result = @$this->db->output ? $this->db->output->fetch() : false;
		return $result;
	}


	/**
	 * sum function.
	 *
	 * @access public
	 * @param array $array (default: array())
	 * @return void
	 */
	public function sum(array $array = array()){
		$this->db->sum($array);
		$this->db->query();
		$result = @$this->db->output ? $this->db->output->fetch() : false;
		return $result;
	}

	/**
	 * getModelVars function.
	 *
	 * @access public
	 * @return void
	 */
	function getModelVars(){
		$d = get_object_vars($this);
		foreach($d as $key => $value){
			if(!is_object($value)){
				$newArr[$key] = $value;
			}

		}
		return $newArr;
	}

	/**
	 * check col name if exist
	 * @param  string $name colname
	 * @return boolean       return boolean
	 */
	function checkColName($name){
		$this->db->columns();
		$this->db->query();
		$result = @$this->db->output ? $this->db->output->fetchAll() : false;

		$i=0;
		while($i<count($result)){
			if($result[$i]['Field'] == $name){
				return $result[$i]['Field'];
			}
			$i++;
		}

		return false;
	}
}
?>
