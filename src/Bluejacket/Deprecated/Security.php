<?php
/**
 * Security class.
 */
namespace Bluejacket\Deprecated;
class Security
{
	public $blacklist=array();

	/**
	 * getBrowser function.
	 *
	 * @access public
	 * @return void
	 */
	public function getBrowser(){
		$u_agent = $_SERVER['HTTP_USER_AGENT'];
		$bname = 'Unknown';
		$platform = 'Unknown';
		$version= "";

		//First get the platform?
		if (preg_match('/linux/i', $u_agent)) {
			$platform = 'linux';
		}
		elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
			$platform = 'mac';
		}
		elseif (preg_match('/windows|win32/i', $u_agent)) {
			$platform = 'windows';
		}else{
			$platform = 'other';
		}

		// Next get the name of the useragent yes seperately and for good reason
		if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent))
		{
			$bname = 'Internet Explorer';
			$ub = "MSIE";
		}
		elseif(preg_match('/Firefox/i',$u_agent))
		{
			$bname = 'Mozilla Firefox';
			$ub = "Firefox";
		}
		elseif(preg_match('/Chrome/i',$u_agent))
		{
			$bname = 'Google Chrome';
			$ub = "Chrome";
		}
		elseif(preg_match('/Safari/i',$u_agent))
		{
			$bname = 'Apple Safari';
			$ub = "Safari";
		}
		elseif(preg_match('/Opera/i',$u_agent))
		{
			$bname = 'Opera';
			$ub = "Opera";
		}
		elseif(preg_match('/Netscape/i',$u_agent))
		{
			$bname = 'Netscape';
			$ub = "Netscape";
		}

		// finally get the correct version number
		$known = array('Version', $ub, 'other');
		$pattern = '#(?<browser>' . join('|', $known) .
			')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
		if (!preg_match_all($pattern, $u_agent, $matches)) {
			// we have no matching number just continue
		}

		// see how many we have
		$i = count($matches['browser']);
		if ($i != 1) {
			//we will have two since we are not using 'other' argument yet
			//see if version is before or after the name
			if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
				$version= $matches['version'][0];
			}
			else {
				$version= $matches['version'][1];
			}
		}
		else {
			$version= $matches['version'][0];
		}

		// check if we have a number
		if ($version==null || $version=="") {$version="?";}

		return array(
			'userAgent' => $u_agent,
			'name'      => $bname,
			'version'   => $version,
			'platform'  => $platform,
			'pattern'    => $pattern
		);
	}

	public function _check($type,$text){
		switch($type){
			case "html":
				if($text) return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
				break;
			case "cslashes":
				if($text) return stripcslashes($text);
				break;
			case "slashes":
				if($text) return stripslashes($text);
				break;
			case "get":
				foreach ($_GET as $get => $content){
					$content=preg_replace("/&#?[a-z0-9]{2,8};/i","",$content);
					$_GET[$get]=htmlspecialchars(stripcslashes(stripslashes($content)));
				}
				if(APP_DEBUGING) error_log("Get Data Cleaned.");
				break;
			case "post":
				foreach ($_POST as $post => $content){
					$content=preg_replace("/&#?[a-z0-9]{2,8};/i","",$content);
					$_POST[$post]=htmlspecialchars(stripcslashes(stripslashes($content)),ENT_QUOTES);
				}
				if(APP_DEBUGING) error_log("Post Data Cleaned.");
				break;
			default:
				if($properties['text']) return htmlspecialchars(stripcslashes(stripslashes($text)), ENT_QUOTES, 'UTF-8');
				break;
		}
	}

	/**
	 * Filter with properties
	 * @param  array  $properties properties(type:array and text:mixed)
	 * @return boolean
	 */
	public function filter($properties = array()){
		if(isset($properties) && is_array($properties)){
			if(is_array($properties['type'])){
				foreach($properties['type'] as $prop){
					$properties['text'] = $this->_check($prop,$properties['text']);
				}
			}else{
				$properties['text'] = $this->_check($properties['type'],$properties['text']);
			}
			return $properties['text'];
		}
		return false;
	}

	public function encode($string){
		$entities = array('%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%2B', '%24', '%2C', '%2F', '%3F', '%25', '%23', '%5B', '%5D','%7C','%5C','%7B','%7D');
		$replacements = array('!', '*', "'", "(", ")", ";", ":", "@", "&", "=", "+", "$", ",", "/", "?", "%", "#", "[", "]","|","\\","{","}");
		return str_replace($entities, $replacements, urlencode($string));
	}


	/**
	 * checkEmail function.
	 *
	 * @access public
	 * @param mixed $email
	 * @return void
	 */
	public function checkEmail($email) {
		if(preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/",$email)){
			list($username,$domain)=split('@',$email);
			if(!checkdnsrr($domain,'MX')) {
				return false;
			}
			return true;
		}
		return false;
	}

	/**
	 * blockAndroid function.
	 *
	 * @access public
	 * @param mixed $msg (default: null)
	 * @param mixed $site (default: null)
	 * @return void
	 */
	public function blockAndroid($msg=null,$site=null){
		$ua = strtolower($_SERVER['HTTP_USER_AGENT']);
		if(stripos($ua,'android') !== false) { // && stripos($ua,'mobile') !== false) {
			if(!is_null($msg)){
				echo $msg;
			}
			if(!is_null($site)){
				header("Location: ".$site);
			}
			exit();
		}
	}

	/**
	 * checkOldIE function.
	 *
	 * @access public
	 * @param mixed $msg (default: null)
	 * @param mixed $site (default: null)
	 * @return void
	 */
	public function checkOldIE(){
		$ua = strtolower($_SERVER['HTTP_USER_AGENT']);
		if(stripos($ua,'msie') !== false) { // && stripos($ua,'mobile') !== false) {
			if(!is_null($msg)){
				print_r($msg);
			}
			if(!is_null($site)){
				header("Location: ".$site);
			}
			exit();
		}
	}

	/**
	 * check request from hostname
	 * @param  string  $hostname
	 * @return boolean
	 */
	public function isRequestFromHost($hostname = null){
		if(is_null($hostname)) $hostname = $_SERVER['SERVER_NAME'];

		if(stristr($_SERVER['HTTP_REFERER'], $hostname)){
			return true;
		}
		return false;
	}
}
?>
