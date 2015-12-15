<?php
/**
 * Boot class.
 */
namespace Bluejacket\Framework;
class Boot
{
	/**
	 * _appName
	 *
	 * @var mixed
	 * @access private
	 */
	private $_appName;
	/**
	 * _folder
	 *
	 * @var mixed
	 * @access private
	 */
	private $_folder;
	/**
	 * _url
	 *
	 * @var mixed
	 * @access public
	 */
	public $_url;
	/**
	 * _db
	 *
	 * @var mixed
	 * @access private
	 */
	private $_db;
	/**
	 * _controller
	 *
	 * @var mixed
	 * @access public
	 */
	public $_controller;
	/**
	 * _controllerPath
	 *
	 * @var mixed
	 * @access public
	 */
	public $_controllerPath;
	/**
	 * _seoExtention
	 *
	 * (default value: false)
	 *
	 * @var bool
	 * @access public
	 */
	public $_seoExtention=false;
	/**
	 * _pageNotFound
	 *
	 * @var mixed
	 * @access public
	 */
	public $_pageNotFound;
	/**
	 * _route
	 *
	 * @var mixed
	 * @access public
	 */
	public $_route;
	/**
	 * _root
	 *
	 * @var mixed
	 * @access public
	 */
	public $_root;
	/**
	 * _redirect
	 *
	 * @var mixed
	 * @access public
	 */
	public $_redirect;
	/**
	 * post
	 *
	 * @var mixed
	 * @access public
	 */
	public $post;
	/**
	 * get
	 *
	 * @var mixed
	 * @access public
	 */
	public $get;
	/**
	 * header
	 *
	 * @var mixed
	 * @access public
	 */
	public $header;
	/**
	 * server
	 *
	 * @var mixed
	 * @access public
	 */
	public $server;
	/**
	 * cookie
	 *
	 * @var mixed
	 * @access public
	 */
	public $cookie;
	/**
	 * session
	 *
	 * @var mixed
	 * @access public
	 */
	public $session;

	public $_folders;


	/**
	 * __construct function.
	 *
	 * @access public
	 * @param mixed $config (default: null)
	 * @return void
	 */
	public function __construct(){
		$this->loader('Framework/');
		$this->loader("Config/",array("Route.php"));
		global $config;
		$this->_loadUrl();
		try{
				define('APPFOLDER',$config['application']);
				define('PUBLIC_DIR',$config['public']);

				$this->error = new Core\Error();

				if(isset($config['status'])){
					switch($config['status']){
						case "development":
							if(is_array($config['development'])){
								define('DEBUG',isset($config['development']['debug']) ? $config['development']['debug'] : true);
								define('CACHE_EXTENTION',isset($config['development']['cache']) ? $config['development']['cache'] : false);
								define('SSL_ACTIVE',isset($config['development']['ssl']) ? $config['development']['ssl'] : false);
								define('SECURITY_EXTENSION',isset($config['development']['security']) ? $config['development']['security'] : false);
							}
							break;
						case "public":
							if(is_array($config['public'])){
								define('DEBUG',isset($config['public']['debug']) ? $config['public']['debug'] : true);
								define('CACHE_EXTENTION',isset($config['public']['cache']) ? $config['public']['cache'] : false);
								define('SSL_ACTIVE',isset($config['public']['ssl']) ? $config['public']['ssl'] : false);
								define('SECURITY_EXTENSION',isset($config['public']['security']) ? $config['public']['security'] : false);
							}
							break;
						case "test":
							if(is_array($config['test'])){
								define('DEBUG',isset($config['test']['debug']) ? $config['test']['debug'] : true);
								define('CACHE_EXTENTION',isset($config['test']['cache']) ? $config['test']['cache'] : false);
								define('SSL_ACTIVE',isset($config['test']['ssl']) ? $config['test']['ssl'] : false);
								define('SECURITY_EXTENSION',isset($config['test']['security']) ? $config['test']['security'] : false);
							}
							break;
					}
					if(CACHE_EXTENTION){
						define('CACHE_FOLDER',isset($config['cache_folder']) ? $config['cache_folder'] : $config['application']."/cache/");
					}
				}


				if(isset($config['db']['driver'])) define('DB_DRIVER',$config['db']['driver']);
				if(isset($config['db']['server'])) define('DB_SERVER',$config['db']['server']);
				if(isset($config['db']['username'])) define('DB_USERNAME',$config['db']['username']);
				if(isset($config['db']['password'])) define('DB_PASSWORD',$config['db']['password']);
				if(isset($config['db']['database'])) define('DB_DATABASE',$config['db']['database']);
				if(isset($config['db']['port'])) define('DB_PORT',$config['db']['port']);
				if(isset($config['db']['charset'])) define('DB_CHARSET',$config['db']['charset']);

				if(isset($config['controller']['default'])) define('DEFAULT_CONTROLLER',$config['controller']['default']);

				if(isset($config['template_folder'])) define('TEMPLATE_FOLDER',$config['template_folder']);
				else define('TEMPLATE_FOLDER',$config['application']."/template/");

				if(isset($config['controller_folder'])){
					define('CONTROLLER_FOLDER',$config['controller_folder']);
					$this->loader($config['controller_folder']);
				}else {
					define('CONTROLLER_FOLDER',$config['application']."/controller/");
					$this->loader(getcwd()."/".$config['application']."/controller/");
				}

				$this->loader(getcwd()."/".$config['application']."/model/");

				$config['fileNotFoundFile'] = $config['public']."/404.html";
		}catch(\Exception $e){
			if(DEBUG){
				$this->error->show("Error: ".$e->getMessage());
			}
		}
		if(DEBUG == false){
			error_reporting(0);
			@header('X-Powered-By: Bluejacket.io');
			ini_set("expose_php","off");
			if(!ini_get('date.timezone')) date_default_timezone_set('GMT');
		}else{
			@header('X-Powered-By: Bluejacket.io');
			ini_set("expose_php","off");
			if(!ini_get('date.timezone')) date_default_timezone_set('GMT');

			$log = new File\Log(array("file" => "Log/Access", "errors" => "Log/Error"));
			$log->write("[".date("d-m-Y H:i")."] ".$_SERVER['REMOTE_ADDR']." - ".$_SERVER['REQUEST_METHOD'].":".$_SERVER['REQUEST_URI']."\n");
		}

		include("Config/Route.php");
	}


	/**
	 * init function.
	 *
	 * @access public
	 * @return void
	 */
	public function init(){
		if(isset($config['route'])){
			if(is_array($config['route']) && $config['route']['active']){
				if(is_array($config['route']['root'])){
					$this->root($config['route']['root'][0],$config['route']['root'][1]);
				}

				if(is_array($config['route']['aliases'])){
					foreach($config['route']['aliases'] as $alias => $newBind){
						$this->bind($alias,array(
								'controller' => $newBind['controller'],
								'default' => $newBind['default'],
								'custom' => $newBind['custom']
							));
					}
				}

				if(is_array($config['route']['redirects'])){
					foreach($config['route']['redirects'] as $alias => $newRedirect){
						$this->redirect($alias,$newRedirect);
					}
				}
			}
		}
	}

	/**
	 * err function.
	 *
	 * @access public
	 * @param mixed $msg
	 * @return void
	 */
	public function err($msg){
		print("<b style='color:red;'>".$msg."</b>");
	}

	/**
	 * config function.
	 *
	 * @access public
	 * @param mixed $ff
	 * @return void
	 */
	public function config($ff){
		if(isset($ff)){
			if(is_file($ff)){
				if(!in_array($ff, $this->_blockedFiles)){
					include $ff;
				}
			}else{
				$this->loader($ff);
			}
		}
	}

	/**
	 * __checkClassFunction function.
	 *
	 * @access public
	 * @param mixed $class
	 * @param mixed $function
	 * @return void
	 */
	public function __checkClassFunction($class,$function){
		$c = get_class_methods($class);
		foreach ($c as $val) {
			if($val == $function){
				return true;
			}
		}
		return false;
	}

	/**
	 * pageNotFound function.
	 *
	 * @access public
	 * @return void
	 */
	public function pageNotFound($content){
		if(is_file($config['fileNotFoundFile'])){
			$this->error->show("Page not found: ".$content->getClassName());
			include($config['fileNotFoundFile']);
		}else{
			$this->error->show("Page not found");
		}
	}


	/**
	 * _loadUrl function.
	 *
	 * @access public
	 * @return void
	 */
	public function _loadUrl(){
		//if(!isset($_SERVER['REQUEST_URI'],$_SERVER['SCRIPT_NAME']) return '';

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
			foreach($_url as $u){
				if($u != "") $this->_url[]=htmlspecialchars(stripcslashes(stripslashes($u)));
			}
		}

		$this->readServerParams();

		if(!isset($this->_url[0]) && isset($this->_root['controller'])){
			$controller=ucfirst($this->_root['controller'])."Controller";
			$action = $this->_root['action'];
			//require_once $this->_controllerPath.$controller.'.php';
			$this->_controller = new $controller();
			if($this->__checkClassFunction($this->_controller,$action)){
				$this->_controller->$action();
				return;
			}else{
				$this->pageNotFound($this->_controller);
				return;
			}
		}


		if(is_array($this->_redirect)){
			foreach (@$this->_redirect as $alias => $url) {
				if(isset($this->_url[0]) && $this->_url[0] == $alias){
					@header("Location: ".$url);
				}
			}
		}


		if(is_array($this->_route)){
			foreach (@$this->_route as $alias => $options) {
				if($this->_url[0] == $alias){
					$controller=ucfirst($options['controller'])."Controller";

					if(is_file(CONTROLLER_FOLDER.$controller.'.php')){
						$this->_controller = new $controller();

						if($options['custom']){
							if($this->__checkClassFunction($this->_controller,$options['default'])){
								$this->_controller->$options['default']();
								return;
							}else{
								$this->pageNotFound($this->_controller);
								return;
							}
						}else{
							if(isset($this->_url[1])){
								$action = $this->_url[1];
								if($this->__checkClassFunction($this->_controller,$action)){
									if(preg_match("/^[_]/i", $action)){
										$this->pageNotFound($this->_controller);
										return;
									}
									$this->_controller->$action();
									return;
								}
							}else{
								if($this->__checkClassFunction($this->_controller,$options['default'])){
									$this->_controller->$options['default']();
									return;
								}else{
									$this->pageNotFound($this->_controller);
									return;
								}
							}
						}
					}else{
						$this->pageNotFound(CONTROLLER_FOLDER.$controller.'.php');
						return;
					}
				}
			}
		}
	}

	/**
	 * _getController function.
	 *
	 * @access public
	 * @param mixed $controller
	 * @param mixed $action
	 * @return void
	 */
	public function _getController($controller,$action){
		if(is_file($this->_controllerPath.$controller.'.php')){
			//require_once $this->_controllerPath.$controller.'.php';
			$this->_controller = new $controller();
			if($this->__checkClassFunction($this->_controller,$action)){
				$this->_controller->$action();
				return;
			}
		}
		return false;
	}

	/**
	 * readServerParams function.
	 *
	 * @access public
	 * @return void
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
		/*
		foreach(apache_request_headers() as $k => $v){
			$this->header[strtolower($k)] = $v;
		}
		*/
	}

	/**
	 * setCookie function.
	 *
	 * @access public
	 * @param mixed $name
	 * @param mixed $value
	 * @param mixed $time
	 * @param mixed $folder
	 * @param mixed $domain
	 * @return void
	 */
	public function setcookie($name,$value,$time,$folder,$domain){
		setcookie($name, $value, time()+$time, $folder, $domain, 1);
	}

	/**
	 * deleteCookie function.
	 *
	 * @access public
	 * @param mixed $name
	 * @return void
	 */
	public function deleteCookie($name){
		setcookie($name, "", time()-39000);
	}

	/**
	 * setSession function.
	 *
	 * @access public
	 * @param mixed $key
	 * @param mixed $val
	 * @return void
	 */
	public function setSession($key,$val){
		$_SESSION[$key] = $val;
	}

	/**
	 * deleteSession function.
	 *
	 * @access public
	 * @param mixed $key
	 * @param mixed $val
	 * @return void
	 */
	public function deleteSession($key,$val){
		unset($_SESSION[$key]);
	}

	/**
	 * bind function.
	 *
	 * @access public
	 * @param mixed $alias
	 * @param mixed $controllerArgs
	 * @return void
	 */
	public function bind($alias,$controllerArgs){
		$controller = $controllerArgs['controller'];
		$default = $controllerArgs['default'];
		@$functions = $controllerArgs['functions'];
		@$customController = $controllerArgs['custom'];

		$this->_route[$alias] = array(
			'controller' => $controller,
			'default' => $default,
			'functions' => $functions,
			'custom' => $customController
		);
	}

	/**
	 * root function.
	 *
	 * @access public
	 * @param mixed $controller
	 * @param mixed $action
	 * @return void
	 */
	public function root($controller,$action){
		$this->_root=array(
			'controller' => $controller,
			'action' => $action
		);
	}

	/**
	 * redirect function.
	 *
	 * @access public
	 * @param mixed $alias
	 * @param mixed $url
	 * @return void
	 */
	public function redirect($alias,$url){
		$this->_redirect[$alias] = $url;
	}

	/**
	 * serverUrl function.
	 *
	 * @access public
	 * @return void
	 */
	public function serverUrl(){
		$protocol = isset($_SERVER['HTTPS']) && (strcasecmp('off', $_SERVER['HTTPS']) !== 0);
		if($protocol) $protocol = "https";
		else $protocol = "http";
		$hostname = $_SERVER['SERVER_NAME'];
		$port = $_SERVER['SERVER_PORT'];
		if($port == "80") $port = "";
		else $port = ":".$port;
		return $protocol."://".$hostname.$port;
	}

	/**
	 * loader function.
	 *
	 * @access public
	 * @static
	 * @param mixed $folder
	 * @return void
	 */
	public function loader($folder,$exclude=array()){
		$_blockedFiles=array(
			".DS_Store",
			".htaccess",
			"index.php",
			"..",
			".",
			".empty",
			"Boot.php"
		);
		@$fl = scandir($folder);
		if(is_array($fl)){
			foreach($fl as $f){
				if(!in_array($f,$_blockedFiles) && !in_array($f,$exclude)){
					if(is_dir($folder.$f)){
						$this->loader($folder.$f."/",$exclude);
					}else if(is_file($folder.$f)){
						$fileName = explode(".php",$f);
						//$controller=ucfirst($fileName[0])."Controller";
						if(class_exists($fileName[0]) != true){
						   require $folder.$f;
						}
					}
				}
			}
		}
	}

	public function loadClasses(){
		$this->load->add('Framework',__DIR__);
		$this->load->setUseIncludePath(true);
		$this->load->register();
	}

	public function spl_loader(){
		set_include_path(get_include_path().PATH_SEPARATOR.implode(PATH_SEPARATOR, $this->_folders));
		spl_autoload_extensions('.php');
		//spl_autoload_register();
		spl_autoload_register();
	}
}
?>
