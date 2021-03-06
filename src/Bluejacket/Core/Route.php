<?php
namespace Bluejacket\Core;
/**
 * Route class
 */
use Bluejacket\Boot;

class Route {
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
	public static function _uri() {
		$uri   = parse_url($_SERVER['REQUEST_URI']);
		$query = isset($uri['query'])?$uri['query']:'';
		$uri   = isset($uri['path'])?rawurldecode($uri['path']):'';

		if (strpos($uri, $_SERVER['SCRIPT_NAME']) === 0) {
			$uri = (string) substr($uri, strlen($_SERVER['SCRIPT_NAME']));
		} elseif (strpos($uri, dirname($_SERVER['SCRIPT_NAME'])) === 0) {
			$uri = (string) substr($uri, strlen(dirname($_SERVER['SCRIPT_NAME'])));
		}

		$_url = explode('/', $uri);

		if (!isset($_url[0]) && $_url[0] == "index" || $_url[0] == "index.php" || $_url[0] == "") {
			unset($_url[0]);
		}

		if (is_array($_url)) {
			self::$_path = null;
			self::$_url  = array();
			foreach ($_url as $k => $u) {
				if ($u != "") {self::$_url[] = htmlspecialchars(stripcslashes(stripslashes($u)));}
				if ($u != "") {self::$_path .= "/".htmlspecialchars(stripcslashes(stripslashes($u)));}
			}
		}
	}

	/**
	 * path converter for urls
	 * @param  string $path setup path
	 * @return string       new path return
	 */
	public static function _convertPath($path) {
		global $_ROUTE;
		$_ROUTE               = array();
		$_ROUTE['controller'] = null;
		$_ROUTE['model']      = null;
		$_ROUTE['format']     = null;
		$_ROUTE['id']         = null;
		$_ROUTE['keys']       = array();
		$rex                  = "/(:[a-z]+)|(\.\(:[a-z]+\))|(\{[a-z]+\})/mi";
		preg_match_all($rex, $path, $match);
		$path_c = explode("/", $path);
		unset($path_c[0]);

		$path_t   = count($path_c);
		$match_t  = count($match[0]);
		$escape_t = $path_t-$match_t;

		$new_path = $path;
		foreach ($match[0] as $k => $v) {
			$key                     = array_search($v, $path_c);
			$newVal                  = str_replace("{", "", $v);
			$newVal                  = str_replace("}", "", $newVal);
			$_ROUTE["keys"][$newVal] = self::$_url[$key-$escape_t];
			$place                   = $_ROUTE["keys"][$newVal];
			$new_path                = preg_replace($rex, $place, $new_path);
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
	public static function _getController($controller, $action) {
		if (is_file(CONTROLLER_FOLDER.$controller.'.php')) {
			//require_once $this->_controllerPath.$controller.'.php';
			$app               = Boot::APP;
			$controller        = "{$app}\\{$controller}";
			self::$_controller = new $controller();
			if ($this->__checkClassFunction(self::_controller, $action)) {
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
	public static function __checkClassFunction($class, $function) {
		$c = get_class_methods($class);
		foreach ($c as $val) {
			if ($val == $function) {
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
	public static function _rootControllerCallback($controller, $action, $arguments = array()) {
		$app         = Boot::APP;
		$controller  = isset($controller)?"{$app}\\Controller\\{$controller}":null;
		$_action     = isset($action)?$action:null;
		$_controller = new $controller();
		if (self::__checkClassFunction($_controller, $_action)) {
			call_user_func_array(array($controller, $_action), $arguments);
		} else {
			if (DEBUG) {
				Core::showErrorMsg("Not Found: ".$_controller."/".$_action."();", 1);
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
	public static function _controllerCallback($controller, $action, $arguments = array()) {
		if (isset($action)) {
			$app         = Boot::APP;
			$controller  = isset($controller)?"{$app}\\Controller\\{$controller}":null;
			$_action     = $action;
			$_controller = new $controller();
			if (self::__checkClassFunction($_controller, $_action)) {
				call_user_func_array(array($controller, $_action), $arguments);
			} else {
				if (DEBUG) {
					Core::showErrorMsg("Not Found: ".$_controller."/".$_action."();", 1);
				}
			}
		} else if (!isset($action)) {
			$app         = Boot::APP;
			$controller  = isset($controller)?"{$app}\\Controller\\{$controller}":null;
			$_action     = isset(self::$_url[1])?self::$_url[1]:"index";
			$_controller = new $_controller();
			if (self::__checkClassFunction($_controller, $_action)) {
				call_user_func_array(array($controller, $_action), $arguments);
			} else {
				if (DEBUG) {
					Core::showErrorMsg("Not Found: ".$_controller."/".$_action."();", 1);
				}
			}
		}
		exit;
	}

	public static function __controllerToArray($controller) {
		$controller = explode("@", $controller);
		return array(
			"controller" => $controller[0],
			"action"     => $controller[1],
		);
	}

	/**
	 * root path redirecter
	 * @param  string $method
	 * @param  array  $controller
	 * @return mixed
	 */
	public static function root($method = null, $controller = array()) {
		self::_uri();
		if (is_string($controller)) {
			$controller = self::__controllerToArray($controller);
		}
		if (count(self::$_url) == 0) {
			if ($_SERVER['REQUEST_METHOD'] != $method) {die();
			}

			if (is_callable($controller)) {
				$controller();
			} else if (count($controller) > 0) {
				self::_rootControllerCallback($controller['controller'], $controller['action'], array());
			}
		}
	}

	/**
	 * post requests
	 * @param  string $method
	 * @param  array  $controller
	 * @return mixed
	 */
	public static function post($path = null, $controller = array()) {
		global $_ROUTE;
		self::_uri();
		if (is_string($controller)) {
			$controller = self::__controllerToArray($controller);
		}
		$path   = self::_convertPath($path);
		$path_c = explode("/", $path);
		if (count(self::$_url) > 0 && $path_c[1] == self::$_url[0]) {
			if ($_SERVER['REQUEST_METHOD'] != "POST") {die();
			}

			if (is_callable($controller)) {
				$arguments = $_ROUTE['keys'];
				call_user_func_array($controller, $arguments);
			} else if (isset($path) && count($controller) > 0) {
				global $_ROUTE;
				if (isset($controller['arguments']) && !is_null($controller['arguments'])) {
					$controller['arguments'] = $controller['arguments'];
				} else if (isset($_ROUTE['keys']) && is_array($_ROUTE['keys'])) {
					$controller['arguments'] = $_ROUTE['keys'];
				} else {
					$controller['arguments'] = array();
				}
				self::_controllerCallback($controller['controller'], $controller['action'], $controller['arguments']);
			}
		}
	}

	/**
	 * put requests
	 * @param  string $method
	 * @param  array  $controller
	 * @return mixed
	 */
	public static function put($path = null, $controller = array()) {
		global $_ROUTE;
		self::_uri();
		if (is_string($controller)) {
			$controller = self::__controllerToArray($controller);
		}
		$path   = self::_convertPath($path);
		$path_c = explode("/", $path);
		if (count(self::$_url) > 0 && $path_c[1] == self::$_url[0]) {
			if ($_SERVER['REQUEST_METHOD'] != "PUT") {die();
			}

			if (is_callable($controller)) {
				$arguments = $_ROUTE['keys'];
				call_user_func_array($controller, $arguments);
			} else if (isset($path) && count($controller) > 0) {
				global $_ROUTE;
				if (isset($controller['arguments']) && !is_null($controller['arguments'])) {
					$controller['arguments'] = $controller['arguments'];
				} else if (isset($_ROUTE['keys']) && is_array($_ROUTE['keys'])) {
					$controller['arguments'] = $_ROUTE['keys'];
				} else {
					$controller['arguments'] = array();
				}
				self::_controllerCallback($controller['controller'], $controller['action'], $controller['arguments']);
			}
		}
	}

	/**
	 * get requests
	 * @param  string $method
	 * @param  array  $controller
	 * @return mixed
	 */
	public static function get($path = null, $controller = array()) {
		global $_ROUTE;
		self::_uri();
		if (is_string($controller)) {
			$controller = self::__controllerToArray($controller);
		}
		$path   = self::_convertPath($path);
		$path_c = explode("/", $path);
		if (count(self::$_url) > 0 && $path_c[1] == self::$_url[0]) {
			if ($_SERVER['REQUEST_METHOD'] != "GET") {die();
			}

			if (is_callable($controller)) {
				$arguments = $_ROUTE['keys'];
				call_user_func_array($controller, $arguments);
			} else if (isset($path) && count($controller) > 0) {
				global $_ROUTE;
				if (isset($controller['arguments']) && !is_null($controller['arguments'])) {
					$controller['arguments'] = $controller['arguments'];
				} else if (isset($_ROUTE['keys']) && is_array($_ROUTE['keys'])) {
					$controller['arguments'] = $_ROUTE['keys'];
				} else {
					$controller['arguments'] = array();
				}
				self::_controllerCallback($controller['controller'], $controller['action'], $controller['arguments']);
			}
		}
	}

	/**
	 * delete requests
	 * @param  string $method
	 * @param  array  $controller
	 * @return mixed
	 */
	public static function delete($path = null, $controller = array()) {
		global $_ROUTE;
		self::_uri();
		if (is_string($controller)) {
			$controller = self::__controllerToArray($controller);
		}
		$path   = self::_convertPath($path);
		$path_c = explode("/", $path);
		if ($path_c[1] == self::$_url[0]) {
			if ($_SERVER['REQUEST_METHOD'] != "DELETE") {die();
			}

			if (is_callable($controller)) {
				$arguments = $_ROUTE['keys'];
				call_user_func_array($controller, $arguments);
			} else if (isset($path) && count($controller) > 0) {
				global $_ROUTE;
				if (isset($controller['arguments']) && !is_null($controller['arguments'])) {
					$controller['arguments'] = $controller['arguments'];
				} else if (isset($_ROUTE['keys']) && is_array($_ROUTE['keys'])) {
					$controller['arguments'] = $_ROUTE['keys'];
				} else {
					$controller['arguments'] = array();
				}
				self::_controllerCallback($controller['controller'], $controller['action'], $controller['arguments']);
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
	public static function redirect($path = null, $redirect = null, $accept = array()) {
		self::_uri();
		$path_c = explode("/", $path);
		$path   = self::_convertPath($path);
		if (count(self::$_url) > 0 && $path_c[1] == self::$_url[0]) {
			if (is_array($accept) && count($accept) > 0) {
				if (!in_array($_SERVER['REQUEST_METHOD'], $accept)) {die();
				}
			}

			if (!is_null($redirect)) {
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
	public static function custom($path = null, $controller = array(), $accept = array()) {
		global $_ROUTE;
		self::_uri();
		if (is_string($controller)) {
			$controller = self::__controllerToArray($controller);
		}
		$path_c = explode("/", $path);
		$path   = self::_convertPath($path);
		if (count(self::$_url) > 0 && $path_c[1] == self::$_url[0]) {
			if (is_array($accept) && count($accept) > 0) {
				if (!in_array($_SERVER['REQUEST_METHOD'], $accept)) {die();
				}
			}

			if (isset($controller['arguments']) && !is_null($controller['arguments'])) {
				$controller['arguments'] = $controller['arguments'];
			} else if (isset($_ROUTE['keys']) && is_array($_ROUTE['keys'])) {
				$controller['arguments'] = $_ROUTE['keys'];
			} else {
				$controller['arguments'] = array();
			}
			if (is_callable($controller)) {
				$arguments = $_ROUTE['keys'];
				call_user_func_array($controller, $arguments);
			} else if (isset($path) && count($controller) > 0) {
				self::_controllerCallback($controller['controller'], $controller['action'], $controller['arguments']);
			}
		}
	}
}
