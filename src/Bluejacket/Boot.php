<?php
/**
 * Boot class.
 */
namespace Bluejacket;
use Bluejacket\Core\JSON;
class Boot
{

	/**
	 * __construct function.
	 *
	 * @access public
	 * @param mixed $config (default: null)
	 * @return void
	 */
	public function __construct($array = array()){
		if(is_array($array) && count($array) > 0){
			foreach($array as $k => $v){
				$this->_config[$k] = $v;
			}
		}


		if(isset($this->_config['app'])){
			$this->app = JSON::decode(file_get_contents(__DIR__."/../".$this->_config['app']));
		}

		if(isset($this->_config['database'])){
			$this->database = JSON::decode(file_get_contents(__DIR__."/../".$this->_config['database']));
		}

		if(isset($this->_config['security'])){
			$this->security = JSON::decode(file_get_contents(__DIR__."/../".$this->_config['security']));
		}

		if(isset($this->_config['types'])){
			$this->types = JSON::decode(file_get_contents(__DIR__."/../".$this->_config['types']));
		}

		/*
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
		*/
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

	public function dump(){
		var_dump($this);
	}
}
?>
