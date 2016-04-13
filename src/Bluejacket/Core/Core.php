<?php
/**
 * Cache class.
 */
namespace Bluejacket\Core;
class Core {
	/**
	 * isFunction function.
	 *
	 * @access public
	 * @param mixed $c
	 * @return void
	 */
	public static function isFunction($c) {
		try {
			if (is_array($c)) {
				if (!method_exists($c[1], $c[0])) {
					throw new \Exception("Function doesnt exists!");
					return false;
				} else {
					throw new \Exception("Function exists!");
					return true;
				}
			} else {
				if (!function_exists($c)) {
					throw new \Exception("Function doesnt exists!");
					return false;
				} else {
					throw new \Exception("Function exists!");
					return true;
				}
			}
		} catch (\Exception $e) {
			echo $e->getMessage();
		}
	}

	/**
	 * checkClass function.
	 *
	 * @access public
	 * @param mixed $class
	 * @return void
	 */
	public function checkClass($class) {
		if (!class_exists($class)) {
			if (APP_DEBUGING) {
				print$this->show("Class not exist: ".$class);
			} else {
				die();
			}
			return false;
		}
		return true;
	}

	/**
	 * debug trace
	 * @return mixed
	 */
	public static function trace() {
		$trace = debug_backtrace();
		echo '<pre>';
		$sb = array();
		foreach ($trace as $item) {
			if (isset($item['file'])) {
				$sb[] = htmlspecialchars("$item[file]:$item[line]");
			} else {
				$sb[] = htmlspecialchars("$item[class]:$item[function]");
			}
		}
		echo implode("\n", $sb);
		echo '</pre>';
	}

	/**
	 * details
	 *
	 * @var mixed
	 * @access private
	 */
	private $details;

	/**
	 * profile function.
	 *
	 * @access public
	 * @param mixed $classname
	 * @param mixed $methodname
	 * @param mixed $methodargs
	 * @param int $invocations (default: 1)
	 * @return void
	 */
	public function profile($classname, $methodname, $methodargs, $invocations = 1) {
		if (class_exists($classname) != TRUE) {
			throw new Exception("{$classname} doesn't exist");
		}

		$method = new ReflectionMethod($classname, $methodname);

		$instance = NULL;
		if (!$method->isStatic()) {
			$class    = new ReflectionClass($classname);
			$instance = $class->newInstance();
		}

		$durations = array();
		for ($i = 0; $i < $invocations; $i++) {
			$start = microtime(true);
			$method->invokeArgs($instance, $methodargs);
			$durations[] = microtime(true)-$start;
		}

		$duration["total"]   = round(array_sum($durations), 4);
		$duration["average"] = round($duration["total"]/count($durations), 4);
		$duration["worst"]   = round(max($durations), 4);

		$this->details = array("class" => $classname,
			"method"                      => $methodname,
			"arguments"                   => $methodargs,
			"duration"                    => $duration,
			"invocations"                 => $invocations);

		return $duration["average"];
	}

	/**
	 * invokedMethod function.
	 *
	 * @access private
	 * @return void
	 */
	private function invokedMethod() {
		return "{$this->details["class"]}::{$this->details["method"]}(" .
		join(", ", $this->details["arguments"]).")";
	}

	/**
	 * printDetails function.
	 *
	 * @access public
	 * @return void
	 */
	public function printDetails() {
		$methodString = $this->invokedMethod();
		$numInvoked   = $this->details["invocations"];

		if ($numInvoked == 1) {
			echo "{$methodString} took {$this->details["duration"]["average"]}s\n";
		} else {
			echo "{$methodString} was invoked {$numInvoked} times\n";
			echo "Total duration:   {$this->details["duration"]["total"]}s\n";
			echo "Average duration: {$this->details["duration"]["average"]}s\n";
			echo "Worst duration:   {$this->details["duration"]["worst"]}s\n";
		}
	}

	/**
	 * check request from hostname
	 * @param  string  $hostname
	 * @return boolean
	 */
	public function isRequestFromHost($hostname = null) {
		if (is_null($hostname)) {$hostname = $_SERVER['SERVER_NAME'];
		}

		if (stristr($_SERVER['HTTP_REFERER'], $hostname)) {
			return true;
		}
		return false;
	}

	/**
	 * sslCheck function.
	 *
	 * @access public
	 * @return void
	 */
	function sslCheck() {
		if (SSL_ACTIVE) {
			if ($_SERVER['HTTPS'] != "on") {
				$redirect = "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
				header("Location:$redirect");
			}
		}
	}

	public function getClassName() {
		return get_called_class();
	}

	/**
	 * serverUrl function.
	 *
	 * @access public
	 * @return void
	 */
	public function serverUrl() {
		$protocol                 = isset($_SERVER['HTTPS']) && (strcasecmp('off', $_SERVER['HTTPS']) !== 0);
		if ($protocol) {$protocol = "https";
		} else {
			$protocol = "http";
		}

		$hostname                 = $_SERVER['SERVER_NAME'];
		$port                     = $_SERVER['SERVER_PORT'];
		if ($port == "80") {$port = "";
		} else {
			$port = ":".$port;
		}

		return $protocol."://".$hostname.$port;
	}

	/**
	 * show function.
	 *
	 * @access public
	 * @param mixed $msg (default: null)
	 * @param int $group (default: 0)
	 * @return void
	 */
	public static function showErrorMsg($msg = null, $group = 0) {
		switch ($group) {
			case 0:
				/* !warning */
				print("<div style='font-family:sans-serif; font-size: 16px; padding:10px; border: 1px solid #9CB3D9;margin: 5px;background: #E3D264; color: #FFFFFF; -webkit-border-radius: 10px; -moz-border-radius: 10px; border-radius: 5px; text-shadow: 1px 1px 1px rgba(150, 150, 150, 1); box-shadow: 1px 1px 2px 0px rgba(50, 50, 50, 0.75); -moz-box-shadow: 1px 1px 2px 0px rgba(50, 50, 50, 0.75); -webkit-box-shadow: 1px 1px 2px 0px rgba(50, 50, 50, 0.75);'><b> &#9888; ".$msg."</b></div>");
				break;
			case 1:
				/* !error */
				die("<div style='font-family:sans-serif; font-size: 16px; padding:10px; border: 1px solid #9CB3D9;margin: 5px;background: #CC4747; color: #FFFFFF; -webkit-border-radius: 10px; -moz-border-radius: 10px; border-radius: 5px; text-shadow: 1px 1px 1px rgba(150, 150, 150, 1); box-shadow: 1px 1px 2px 0px rgba(50, 50, 50, 0.75); -moz-box-shadow: 1px 1px 2px 0px rgba(50, 50, 50, 0.75); -webkit-box-shadow: 1px 1px 2px 0px rgba(50, 50, 50, 0.75);'><b> &#9888; ".$msg."</b></div>");
				break;
			case 3:
				die($msg);
				break;
			default:
				/* !warning */
				print("<div style='font-family:sans-serif; font-size: 16px; padding:10px; border: 1px solid #9CB3D9;margin: 5px;background: #E3D264; color: #FFFFFF; -webkit-border-radius: 10px; -moz-border-radius: 10px; border-radius: 5px; text-shadow: 1px 1px 1px rgba(150, 150, 150, 1); box-shadow: 1px 1px 2px 0px rgba(50, 50, 50, 0.75); -moz-box-shadow: 1px 1px 2px 0px rgba(50, 50, 50, 0.75); -webkit-box-shadow: 1px 1px 2px 0px rgba(50, 50, 50, 0.75);'><b> &#9888; ".$msg."</b></div>");
				break;
		}
	}

	public function __call($function_name, $args) {
		if (is_callable(self, $function_name)) {
			return call_user_func($function_name, $args);
		} else {
			return self;
		}
	}
}
