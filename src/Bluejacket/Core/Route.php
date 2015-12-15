<?php
namespace Bluajacket\Framework\Core;
/**
 * Route class
 */
class Route
{
  /**
   * request setup
   * @var [type]
   */
  static $_request;
  /**
   * controller setup
   * @var [type]
   */
  static $_controller;
  /**
   * model setup
   * @var [type]
   */
  static $_model;
  /**
   * id setup
   * @var [type]
   */
  static $_id;
  /**
   * url setup
   * @var [type]
   */
  static $_url;
  /**
   * path setup
   * @var [type]
   */
  static $_path;

  /**
   * get url contents
   * @return mixed
   */
  public static function _uri(){
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



		$_url = explode('/',$uri);

		if(!isset($_url[0]) && $_url[0] == "index" || $_url[0] == "index.php" || $_url[0] == ""){
			unset($_url[0]);
		}

		if(is_array($_url)){
      self::$_path = null;
      self::$_url = array();
			foreach($_url as $k => $u){
				if($u != "") self::$_url[]=htmlspecialchars(stripcslashes(stripslashes($u)));
        if($u != "") self::$_path.="/".htmlspecialchars(stripcslashes(stripslashes($u)));
			}
		}
  }

  /**
   * path converter for urls
   * @param  string $path setup path
   * @return string       new path return
   */
  public static function _convertPath($path){
    global $_ROUTE;
    $_ROUTE = array();
    $rex = "/(:[a-z]+)|(\.\(:[a-z]+\))|(\{[a-z]+\})/mi";
    preg_match_all($rex,$path,$match);
    $path_c = explode("/",$path);
    unset($path_c[0]);

    $path_t = count($path_c);
    $match_t = count($match[0]);
    $escape_t = $path_t - $match_t;

    $new_path = $path;
    foreach ($match[0] as $k => $v) {
      $key = array_search($v,$path_c);

      switch($v){
        case ":controller":
          if(isset(self::$_url[$key-$escape_t])){
            $controller = ucfirst(self::$_url[$key-$escape_t])."Controller";
            $_ROUTE['controller'] = $controller;
            $new_path = preg_replace($rex,$model,$new_path);
          }
          break;
        case ":model":
          if(isset(self::$_url[$key-$escape_t])){
            $model = ucfirst(self::$_url[$key-$escape_t]);
            $_ROUTE['model'] = self::$_url[$key-$escape_t];
            $new_path = preg_replace($rex,$model,$new_path);
          }
          break;
        case ":id":
          if(isset(self::$_url[$key-$escape_t])){
            $id = self::$_url[$key-$escape_t];
            $_ROUTE['id'] = $id;
            $new_path = preg_replace($rex,$id,$new_path);
          }
          break;
        case ":format":
          $last_key = key( array_slice( self::$_url, -1, 1, TRUE ) );
          $last_element = self::$_url[$last_key];
          $last_geta = explode(".",$last_element);
          $_ROUTE['format'] = $last_geta[1];
          $new_path = preg_replace($rex,".".$last_geta[1],$new_path);

          if(isset(self::$_url[$key-$escape_t])){
            $format = self::$_url[$key-$escape_t];
            $_ROUTE['format'] = $format;
            $new_path = preg_replace($rex,$format,$new_path);
          }
          break;
        default:
          $newVal = str_replace("{","",$v);
          $newVal = str_replace("}","",$newVal);
          $_ROUTE["keys"][$newVal] = self::$_url[$key-$escape_t];
          $place = $_ROUTE["keys"][$newVal];
          $new_path = preg_replace($rex,$place,$new_path);
          break;
      }

    }
    $_ROUTE['path'] = $new_path;
    return $new_path;
  }

  /**
   * call controller and action
   * @param  string $controller
   * @param  string $action
   * @return mixed
   */
  public static function _getController($controller,$action){
		if(is_file(CONTROLLER_FOLDER.$controller.'.php')){
			//require_once $this->_controllerPath.$controller.'.php';
			self::$_controller = new $controller();
			if($this->__checkClassFunction(self::_controller,$action)){
				self::$_controller->$action();
			}
		}
	}

  /**
   * check function on class
   * @param  string $class
   * @param  string $function
   * @return boolean
   */
  public static function __checkClassFunction($class,$function){
		$c = get_class_methods($class);
		foreach ($c as $val) {
			if($val == $function){
				return true;
			}
		}
		return false;
	}

  /**
   * root controller callback function
   * @param  string $controller
   * @param  string $action
   * @param  array  $arguments
   * @return mixed
   */
  public static function _rootControllerCallback($controller,$action,$arguments=array()){
    $_controller = isset($controller) ? ucfirst($controller)."Controller" : null;
    $_action = isset($action) ? $action : null;
    if(self::__checkClassFunction($_controller,$_action)){
      $_controller = new $_controller();
      //$_action = $_controller->$_action();
      call_user_func_array(array($_controller,$_action),$arguments);
    }else{
      if(DEBUG){
        $error = new Error();
        $error->show("Not Found: ".$_controller."/".$_action."();",1);
      }
    }
    exit;
  }


  /**
   * controller callback function
   * @param  string $controller
   * @param  string $action
   * @param  array  $arguments
   * @return mixed
   */
  public static function _controllerCallback($controller,$action,$arguments=array()){
    if(isset($action)){
      $_controller = isset($controller) ? ucfirst($controller)."Controller" : null;
      $_action = $action;
      if(self::__checkClassFunction($_controller,$_action)){
        $_controller = new $_controller();
        //$_action = $_controller->$_action();
        call_user_func_array(array($_controller,$_action),$arguments);
      }else{
        if(DEBUG){
          $error = new Error();
          $error->show("Not Found: ".$_controller."/".$_action."();",1);
        }
      }
    }else if(!isset($action)){
      $_controller = isset($controller) ? ucfirst($controller)."Controller" : null;
      $_action = isset(self::$_url[1]) ? self::$_url[1] : "index";
      if(self::__checkClassFunction($_controller,$_action)){
        $_controller = new $_controller();
        call_user_func_array(array($_controller,$_action),$arguments);
      }else{
        if(DEBUG){
          $error = new Error();
          $error->show("Not Found: ".$_controller."/".$_action."();",1);
        }
      }
    }
    exit;
  }

  /**
   * read server params and set this class
   * @return mixed
   */
  public function readServerParams(){
		foreach($_POST as $k => $v){
			$this->post[$k] = $v;
		}
		foreach($_GET as $k => $v){
			$this->get[$k] = $v;
		}
		foreach($_SERVER as $k => $v){
			$this->server[strtolower($k)] = $v;
		}
		if(!@isset($_SESSION['PHPSESSID'])){
			@session_start();
		}
		foreach($_SESSION as $k => $v){
			$this->session[$k] = $v;
		}
		foreach($_COOKIE as $k => $v){
			$this->cookie[$k] = $v;
		}
		foreach(apache_request_headers() as $k => $v){
			$this->header[strtolower($k)] = $v;
		}
	}

  /**
   * root path redirecter
   * @param  string $method
   * @param  array  $controller
   * @return mixed
   */
  public static function root($method = null, $controller = array()){
    self::_uri();
    if(count(self::$_url) == 0){
      if($_SERVER['REQUEST_METHOD'] != $method) die();
      if(is_callable($controller)){
        $controller();
      }else if(count($controller) > 0){
        self::_rootControllerCallback($controller['controller'],$controller['action'],array());
      }
    }
  }

  /**
   * post requests
   * @param  string $method
   * @param  array  $controller
   * @return mixed
   */
  public static function post($path = null, $controller = array()){
    self::_uri();
    $path = self::_convertPath($path);
    $path_c = explode("/",$path);
    if(count(self::$_url) > 0 && $path_c[1] == self::$_url[0]){
      if($_SERVER['REQUEST_METHOD'] != "POST") die();
      if(is_callable($controller)){
        $arguments = $_ROUTE['keys'];
        call_user_func_array($controller,$arguments);
      }else if(isset($path) && count($controller) > 0){
        global $_ROUTE;
        if(isset($controller['arguments']) && !is_null($controller['arguments'])){
          $controller['arguments'] = $controller['arguments'];
        }else if(isset($_ROUTE['keys']) && is_array($_ROUTE['keys'])){
          $controller['arguments'] = $_ROUTE['keys'];
        }else{
          $controller['arguments'] = array();
        }
        self::_controllerCallback($controller['controller'],$controller['action'],$controller['arguments']);
      }
    }
  }

  /**
   * put requests
   * @param  string $method
   * @param  array  $controller
   * @return mixed
   */
  public static function put($path = null, $controller = array()){
    self::_uri();
    $path = self::_convertPath($path);
    $path_c = explode("/",$path);
    if(count(self::$_url) > 0 && $path_c[1] == self::$_url[0]){
      if($_SERVER['REQUEST_METHOD'] != "PUT") die();
      if(is_callable($controller)){
        $arguments = $_ROUTE['keys'];
        call_user_func_array($controller,$arguments);
      }else if(isset($path) && count($controller) > 0){
        global $_ROUTE;
        if(isset($controller['arguments']) && !is_null($controller['arguments'])){
          $controller['arguments'] = $controller['arguments'];
        }else if(isset($_ROUTE['keys']) && is_array($_ROUTE['keys'])){
          $controller['arguments'] = $_ROUTE['keys'];
        }else{
          $controller['arguments'] = array();
        }
        self::_controllerCallback($controller['controller'],$controller['action'],$controller['arguments']);
      }
    }
  }

  /**
   * get requests
   * @param  string $method
   * @param  array  $controller
   * @return mixed
   */
  public static function get($path = null, $controller = array()){
    self::_uri();
    $path = self::_convertPath($path);
    $path_c = explode("/",$path);
    if(count(self::$_url) > 0 && $path_c[1] == self::$_url[0]){
      if($_SERVER['REQUEST_METHOD'] != "GET") die();
      if(is_callable($controller)){
        $arguments = $_ROUTE['keys'];
        call_user_func_array($controller,$arguments);
      }else if(isset($path) && count($controller) > 0){
        global $_ROUTE;
        if(isset($controller['arguments']) && !is_null($controller['arguments'])){
          $controller['arguments'] = $controller['arguments'];
        }else if(isset($_ROUTE['keys']) && is_array($_ROUTE['keys'])){
          $controller['arguments'] = $_ROUTE['keys'];
        }else{
          $controller['arguments'] = array();
        }
        self::_controllerCallback($controller['controller'],$controller['action'],$controller['arguments']);
      }
    }
  }

  /**
   * delete requests
   * @param  string $method
   * @param  array  $controller
   * @return mixed
   */
  public static function delete($path = null, $controller = array()){
    self::_uri();
    $path = self::_convertPath($path);
    $path_c = explode("/",$path);
    if($path_c[1] == self::$_url[0]){
      if($_SERVER['REQUEST_METHOD'] != "DELETE") die();
      if(is_callable($controller)){
        $arguments = $_ROUTE['keys'];
        call_user_func_array($controller,$arguments);
      }else if(isset($path) && count($controller) > 0){
        global $_ROUTE;
        if(isset($controller['arguments']) && !is_null($controller['arguments'])){
          $controller['arguments'] = $controller['arguments'];
        }else if(isset($_ROUTE['keys']) && is_array($_ROUTE['keys'])){
          $controller['arguments'] = $_ROUTE['keys'];
        }else{
          $controller['arguments'] = array();
        }
        self::_controllerCallback($controller['controller'],$controller['action'],$controller['arguments']);
      }
    }
  }

  /**
   * redirect path to url
   * @param  string $path
   * @param  string $redirect
   * @param  array  $accept
   * @return mixed
   */
  public static function redirect($path = null, $redirect = null, $accept = array()){
    self::_uri();
    $path_c = explode("/",$path);
    $path = self::_convertPath($path);
    if(count(self::$_url) > 0 && $path_c[1] == self::$_url[0]){
      if(is_array($accept) && count($accept) > 0){
        if(!in_array($_SERVER['REQUEST_METHOD'], $accept)) die();
      }
      if(!is_null($redirect)){
        header("Location: ".$add.$redirect);
      }
    }
  }

  /**
   * custom requests
   * @param  string $path
   * @param  array  $controller
   * @return mixed
   */
  public static function custom($path = null, $controller = array(), $accept = array()){
    self::_uri();
    $path_c = explode("/",$path);
    $path = self::_convertPath($path);
    if(count(self::$_url) > 0 && $path_c[1] == self::$_url[0]){
      if(is_array($accept) && count($accept) > 0){
        if(!in_array($_SERVER['REQUEST_METHOD'], $accept)) die();
      }
      global $_ROUTE;
      if(isset($controller['arguments']) && !is_null($controller['arguments'])){
        $controller['arguments'] = $controller['arguments'];
      }else if(isset($_ROUTE['keys']) && is_array($_ROUTE['keys'])){
        $controller['arguments'] = $_ROUTE['keys'];
      }else{
        $controller['arguments'] = array();
      }
      if(is_callable($controller)){
        $arguments = $_ROUTE['keys'];
        call_user_func_array($controller,$arguments);
      }else if(isset($path) && count($controller) > 0){
        self::_controllerCallback($controller['controller'],$controller['action'],$controller['arguments']);
      }
    }
  }
}
?>
