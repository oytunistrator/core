<?php
/**
 * Error class.
 *
 * @extends Exception
 */
namespace Framework\Core;
class Error
{
	/**
	 * Message text
	 * @var string
	 */
	public $message;

	/**
	 * Error construct
	 * @param mixed $message Message
	 */
	function __construct($message=null){
		$this->message = $message;
	}

	/**
	 * checkClass function.
	 *
	 * @access public
	 * @param mixed $class
	 * @return void
	 */
	public function checkClass($class){
		if (!class_exists($class)) {
			if(APP_DEBUGING){
				print $this->show("Class not exist: ".$class);
			}else{
				die();
			}
			return false;
		}
		return true;
	}


	/**
	 * show function.
	 *
	 * @access public
	 * @param mixed $msg (default: null)
	 * @param int $group (default: 0)
	 * @return void
	 */
	public function show($msg=null,$group=0){
		switch($group){
			case 0:
				/* !warning */
				print("<div style='font-family:sans-serif; font-size: 16px; padding:10px; border: 1px solid #9CB3D9;margin: 5px;background: #E3D264; color: #FFFFFF; -webkit-border-radius: 10px; -moz-border-radius: 10px; border-radius: 5px; text-shadow: 1px 1px 1px rgba(150, 150, 150, 1); box-shadow: 1px 1px 2px 0px rgba(50, 50, 50, 0.75); -moz-box-shadow: 1px 1px 2px 0px rgba(50, 50, 50, 0.75); -webkit-box-shadow: 1px 1px 2px 0px rgba(50, 50, 50, 0.75);'><b> &#9888; ".$msg."</b></div>");
				break;
			case 1:
				/* !error */
				die("<div style='font-family:sans-serif; font-size: 16px; padding:10px; border: 1px solid #9CB3D9;margin: 5px;background: #CC4747; color: #FFFFFF; -webkit-border-radius: 10px; -moz-border-radius: 10px; border-radius: 5px; text-shadow: 1px 1px 1px rgba(150, 150, 150, 1); box-shadow: 1px 1px 2px 0px rgba(50, 50, 50, 0.75); -moz-box-shadow: 1px 1px 2px 0px rgba(50, 50, 50, 0.75); -webkit-box-shadow: 1px 1px 2px 0px rgba(50, 50, 50, 0.75);'><b> &#9888; ".$msg."</b></div>");
				break;
			default:
				/* !warning */
				print("<div style='font-family:sans-serif; font-size: 16px; padding:10px; border: 1px solid #9CB3D9;margin: 5px;background: #E3D264; color: #FFFFFF; -webkit-border-radius: 10px; -moz-border-radius: 10px; border-radius: 5px; text-shadow: 1px 1px 1px rgba(150, 150, 150, 1); box-shadow: 1px 1px 2px 0px rgba(50, 50, 50, 0.75); -moz-box-shadow: 1px 1px 2px 0px rgba(50, 50, 50, 0.75); -webkit-box-shadow: 1px 1px 2px 0px rgba(50, 50, 50, 0.75);'><b> &#9888; ".$msg."</b></div>");
				break;
		}
	}

	/**
	 * Get Exeption message
	 * @param integer $type type of message
	 */
	public function getMessage($type=0){
		$this->show($this->message,$type);
	}
}
?>
