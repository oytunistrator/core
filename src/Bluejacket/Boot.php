<?php
/**
 * Boot class.
 */
namespace Bluejacket;
class Boot
{


	/**
	 * __construct function.
	 *
	 * @access public
	 * @param mixed $array (default: array)
	 * @return void
	 */
	public function __construct($array = array()){
		if(is_array($array) && count($array) > 0){
			foreach($array as $k => $v){
				$this->_config[$k] = $v;
			}
		}

		if(isset($this->_config['app'])){
			$this->app = Formatter\JSON::decode(file_get_contents($this->_config['app']));
		}

		if(isset($this->_config['database'])){
			$this->database = Formatter\JSON::decode(file_get_contents($this->_config['database']));
		}

		if(isset($this->_config['security'])){
			$this->security = Formatter\JSON::decode(file_get_contents($this->_config['security']));
		}

		if(isset($this->_config['types'])){
			$this->types = Formatter\JSON::decode(file_get_contents($this->_config['types']));
		}



		$this->url = Core\Route::_uri();


		if(isset($this->database)){
			define('DB_DRIVER',$this->database->driver);
			define('DB_SERVER',$this->database->server);
			define('DB_USERNAME',$this->database->username);
			define('DB_PASSWORD',$this->database->password);
			define('DB_PORT',$this->database->port);
			define('DB_CHARSET',$this->database->charser);
		}
		if(isset($this->security->status)){
			define('DEBUG',$this->types->{$this->security->status}->debug);
			define('CACHE',$this->types->{$this->security->status}->cache);
			define('SSL',$this->types->{$this->security->status}->ssl);
			if(isset($this->database->{$this->security->status})){
				define('DB_DATABASE',$this->database->{$this->security->status});
			}
			if(DEBUG == false){
                            error_reporting(0);
                            header('X-Powered-By: Bluejacket.io');
                            ini_set("expose_php","off");
                            if(!ini_get('date.timezone')){
                                date_default_timezone_set('GMT');
                            }
			}else{
                            header('X-Powered-By: Bluejacket.io');
                            ini_set("expose_php","off");
                            if(!ini_get('date.timezone')){
                                date_default_timezone_set('GMT');
                            }

                            $log = new File\Log(array("file" => "Log/Access", "errors" => "Log/Error"));
                            $log->write("[".date("d-m-Y H:i")."] ".$_SERVER['REMOTE_ADDR']." - ".$_SERVER['REQUEST_METHOD'].":".$_SERVER['REQUEST_URI']."\n");
			}
			if(CACHE){
                            define('CACHE_FOLDER',isset($this->app->cache) ? $this->app->cache : $this->app->application."/cache/");
			}
		}

		include($this->app->route);
	}

	public function dump(){
		var_dump($this);
	}
}
?>
