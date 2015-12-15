<?php
/**
 * Debug class.
 */
namespace Framework\Core;
class Debug
{
	/**
	 * isFunction function.
	 *
	 * @access public
	 * @param mixed $c
	 * @return void
	 */
	public function isFunction($c){
		try{
			if(is_array($c)){
				if(!method_exists($c[1],$c[0])){
					throw new \Exception("Function doesnt exists!");
					return false;
				}else{
					throw new \Exception("Function exists!");
					return true;
				}
			}else{
				if(!function_exists($c)){
					throw new \Exception("Function doesnt exists!");
					return false;
				}else{
					throw new \Exception("Function exists!");
					return true;
				}
			}
		}catch(\Exception $e){
			echo $e->getMessage();
		}
	}

	/**
	 * debug trace
	 * @return mixed
	 */
	public static function trace() {
			$trace = debug_backtrace();
			echo '<pre>';
			$sb = array();
			foreach($trace as $item) {
					if(isset($item['file'])) {
							$sb[] = htmlspecialchars("$item[file]:$item[line]");
					} else {
							$sb[] = htmlspecialchars("$item[class]:$item[function]");
					}
			}
			echo implode("\n",$sb);
			echo '</pre>';
	}
}
?>
