<?php
/**
 * API class.
 */
namespace Bluejacket\Core;
class JsonAPI
{

	/**
	 * users
	 *
	 * @var mixed
	 * @access public
	 */
	public $users;

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct($options=null){
		if(is_array($options)){
			foreach($options as $key => $val){
				$this->{$key} = $val;
			}
		}

		$uri = parse_url($_SERVER['REQUEST_URI']);
		$query = isset($uri['query']) ? $uri['query'] : '';
		$uri = isset($uri['path']) ? rawurldecode($uri['path']) : '';


		if (strpos($uri, $_SERVER['SCRIPT_NAME']) === 0)
		{
			$uri = (string) substr($uri, strlen($_SERVER['SCRIPT_NAME']));
		}
		elseif (strpos($uri, dirname($_SERVER['SCRIPT_NAME'])) === 0)
		{
			$uri = (string) substr($uri, strlen(dirname($_SERVER['SCRIPT_NAME'])));
		}

		$this->_url = explode('/',$uri);

		if(isset($this->_url[0])
			&& $this->_url[0] == "index"
			|| $this->_url[0] == "index.php"
			|| $this->_url[0] == ""){
			unset($this->_url[0]);
		}

		$model = explode(".json",$this->_url[1]);
		$this->model = $model[0];
		$id = explode(".json",$this->_url[2]);
		$this->id = $id[0];
		$this->options = explode("|",$_GET['options']);
		if(!isset($this->privateKey)){
			$this->privateKey = $_GET['key'];
			unset($_GET['key']);
		}else{
			if(in_array($this->privateKey, $_GET)){
				unset($_GET[array_search($this->privateKey, $_GET)]);
			}
		}
		unset($_GET['options']);
		foreach ($_GET as $key => $val){
			$this->where[$key]=$val;
		}

		if($params==null){
			$this->params = $_POST;
		}else{
			$this->params = $this->objectToArray($this->options['params']);
		}
	}


	/**
	 * objectToArray function.
	 *
	 * @access public
	 * @param mixed $object
	 * @return void
	 */
	function objectToArray($object){
		if(is_object($object)){
			$object = get_object_vars($object);
		}
		if(is_array($object)){
			return array_map(array($this,__FUNCTION__),$object);
		}else{
			return $object;
		}
	}

	/**
	 * basicAuth function.
	 *
	 * @access public
	 * @return void
	 */
	public function auth(){
		if(isset($this->privateKey) && in_array($this->privateKey, $this->keys)){
			return true;
		}
		return false;
	}

	/**
	 * addUser function.
	 *
	 * @access public
	 * @param mixed $config
	 * @return void
	 */
	public function addKey($key){
		$this->keys[] = $key;
	}



	/**
	 * method function.
	 *
	 * @access public
	 * @return void
	 */
	public function result(){
		$method = $_SERVER['REQUEST_METHOD'];
		header('Content-Type: application/json; charset=utf-8');
		switch($method) {
			case 'PUT':
				if($this->put){
					return $this->put();
				}else{
					header('HTTP/1.1 405 Method Not Allowed');
				}
				break;
			case 'POST':
				if($this->post){
					return $this->post();
				}else{
					header('HTTP/1.1 405 Method Not Allowed');
				}
				break;
			case 'DELETE':
				if($this->delete){
					return $this->delete();
				}else{
					header('HTTP/1.1 405 Method Not Allowed');
				}
				break;
			case 'GET':
				if($this->get){
					return $this->get();
				}else{
					header('HTTP/1.1 405 Method Not Allowed');
				}
				break;
			case 'UPDATE':
				if($this->update){
					return $this->update();
				}else{
					header('HTTP/1.1 405 Method Not Allowed');
				}
				break;
			default:
				if($this->directAccess){
					return $this->get();
				}else{
					header('HTTP/1.1 405 Method Not Allowed');
				}
				break;
		}
		return false;
	}

	/**
	 * delete function.
	 *
	 * @access public
	 * @return void
	 */
	public function delete(){
		$model = $this->model;
		$delete = new $model();
		if($delete->delete($this->id)) $result['status'] = true;
		else $result['status'] = false;
		return json_encode($result);
	}
	/**
	 * put function.
	 *
	 * @access public
	 * @return void
	 */
	public function put(){
		$model = $this->model;
		parse_str(file_get_contents("php://input"),$post_vars);
		$put = new $model($post_vars);

		$result['status'] = false;
		if(isset($this->id)){
			if($put->update($this->id)){
				$result['status'] = true;
			}
		}
		return json_encode($result);
	}

	/**
	 * get function.
	 *
	 * @access public
	 * @return void
	 */
	public function get(){
		$model = $this->model;
		$get = new $model();
		if(is_numeric($this->id)){
			$get->find($this->id);
			if(is_array($get->getModelVars())){
				$result['status'] = true;
				foreach ($get->getModelVars() as $key => $val){
					if(!is_numeric($key)){
						$data[$key] = $val;
					}
				}
				$result['data'] = $data;

			}else{
				$result['status'] = false;
			}
		}else{
			$limit = null;
			$where = null;
			$group = null;
			$order = null;
			$select = null;
			$search = null;

			if(is_array($this->options)){
				foreach ($this->options as $option) {
					$opt = explode(":",$option);
					$val = explode(",",$opt[1]);
					if($opt[0] == "order"){
						$order = array($val[0],$val[1]);
					}

					if($opt[0] == "limit"){
						$limit = array($val[0],$val[1]);
					}

					if($opt[0] == "group"){
						$group = array($val[0],$val[1]);
					}

					if($opt[0] == "select"){
						$select = array($val[0],$val[1]);
					}

					if($opt[0] == "search"){
						$search = true;
						$query = $val[0];
					}
				}
			}



			if($search){
				$out = $get->searchQuery($query,array());
			}else{
				if(isset($this->where)){
					$where = $this->where;
				}


				$out = $get->special(array(
					"select" => $select,
					"where" => $where,
					"order" => $order,
					"limit" => $limit,
					"groupBy" => $group
				));
			}


			if(is_array($out)){
				$result['status'] = true;
				$i=0;
				while($i<count($out)){
					foreach ($out[$i] as $key => $val){
						if (!is_numeric($key)) {
							$result['data'][$i][$key] = $val;
						}
					}
					$i++;
				}
				$result['count'] = count($out);
			}else{
				$result['status'] = false;
			}
		}

		return json_encode($result,JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP);
	}

	/**
	 * post function.
	 *
	 * @access public
	 * @return void
	 */
	public function post(){
		$model = $this->model;
		$post = new $model($this->params);

		$result['status'] = false;
		if($post->save()){
			$result['status'] = true;
		}
		return json_encode($result);
	}

	/**
	 * update function.
	 *
	 * @access public
	 * @return void
	 */
	public function update(){
		$model = $this->model;
		$post = new $model($this->params);

		$result['status'] = false;
		if($this->id){
			if($post->update($this->id)){
				$result['status'] = true;
			}
		}
		return json_encode($result);
	}
}
?>
