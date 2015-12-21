<?php
/**
 * Boot class.
 */
namespace Bluejacket;
use Bluejacket\Core\JSON;
use Bluejacket\Core\Route;
use Bluejacket\Core\Error;
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
			$this->app = JSON::decode(file_get_contents($this->_config['app']));
		}

		if(isset($this->_config['database'])){
			$this->database = JSON::decode(file_get_contents($this->_config['database']));
		}

		if(isset($this->_config['security'])){
			$this->security = JSON::decode(file_get_contents($this->_config['security']));
		}

		if(isset($this->_config['types'])){
			$this->types = JSON::decode(file_get_contents($this->_config['types']));
		}

		$this->url = Route::_url();
		$this->error = new Error;

		if(isset($this->database)){
			if(isset($this->database->driver)) define('DB_DRIVER',$this->database->driver);
			if(isset($this->database->server)) define('DB_SERVER',$this->database->server);
			if(isset($this->database->username)) define('DB_USERNAME',$this->database->username);
			if(isset($this->database->password)) define('DB_PASSWORD',$this->database->password);
			if(isset($this->database->port)) define('DB_PORT',$this->database->port);
			if(isset($this->database->charset)) define('DB_CHARSET',$this->database->charser);
		}

		if(isset($this->security->status)){
			define('DEBUG',$this->types->{$this->security->status}->debug);
			define('CACHE',$this->types->{$this->security->status}->cache);
			define('SSL',$this->types->{$this->security->status}->ssl);
			if(isset($this->database->{$this->security->status})){
				define('DB_DATABASE',$this->database->{$this->security->status});
			}
			if(CACHE){
				define('CACHE_FOLDER',isset($this->app->cache) ? $this->app->cache : $this->app->application."/cache/");
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

		include($this->app->route);	
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
