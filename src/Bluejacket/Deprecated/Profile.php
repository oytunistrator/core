<?php
/**
 * Profile class.
 */
namespace Bluejacket\Deprecated;
class Profile
{

	/**
	 * details
	 *
	 * @var mixed
	 * @access private
	 */
	private $details;

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {}


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
		if(class_exists($classname) != TRUE) {
			throw new Exception("{$classname} doesn't exist");
		}

		$method = new ReflectionMethod($classname, $methodname);

		$instance = NULL;
		if(!$method->isStatic()){
			$class = new ReflectionClass($classname);
			$instance = $class->newInstance();
		}

		$durations = array();
		for($i = 0; $i < $invocations; $i++) {
			$start = microtime(true);
			$method->invokeArgs($instance, $methodargs);
			$durations[] = microtime(true) - $start;
		}

		$duration["total"] = round(array_sum($durations), 4);
		$duration["average"] = round($duration["total"] / count($durations), 4);
		$duration["worst"] = round(max($durations), 4);

		$this->details = array(  "class" => $classname,
			"method" => $methodname,
			"arguments" => $methodargs,
			"duration" => $duration,
			"invocations" => $invocations);

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
			join(", ", $this->details["arguments"]) . ")";
	}


	/**
	 * printDetails function.
	 *
	 * @access public
	 * @return void
	 */
	public function printDetails() {
		$methodString = $this->invokedMethod();
		$numInvoked = $this->details["invocations"];

		if($numInvoked == 1) {
			echo "{$methodString} took {$this->details["duration"]["average"]}s\n";
		}

		else {
			echo "{$methodString} was invoked {$numInvoked} times\n";
			echo "Total duration:   {$this->details["duration"]["total"]}s\n";
			echo "Average duration: {$this->details["duration"]["average"]}s\n";
			echo "Worst duration:   {$this->details["duration"]["worst"]}s\n";
		}
	}
}

?>
