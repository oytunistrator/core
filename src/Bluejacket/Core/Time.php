<?php
namespace Bluajacket\Core;
/**
 * Time conversion class
 */
class Time
{
	function __construct($date = array(),$timezone = null){
		if(!is_null($timezone)){
			date_default_timezone_set($timezone);
		}else{
			date_default_timezone_set('UTC');
		}

		if(is_array($date) && count($date) > 0){
			$this->date = $this->_set($date);
		}else{
			$this->date = date();
		}
	}

/**
 * date set from array
 * @param array $date
 */
	function _set($date = array()){
		if(is_array($date) && count($date) > 0){
			$day = $date['day'];
			$month = $date['month'];
			$year = $date['year'];
			$hour = $date['hour'];
			$minute = $date['minute'];
			$second = $date['second'];

			return mktime($hour,$minute, $second, $month, $day, $year);
		}
		return false;
	}

	/**
	 * remove second time string to first time string
	 * @param string $first
	 * @param string $second
	 * @return mixed
	 */
	function _remove($first = null, $second = null){
		if(!is_null($first) && !is_null($second)){
			$ft = strtotime($first);
			$st = strtotime($second);
			return $st - $ft;
		}
		return false;
	}

	/**
	 * add second time string to first time string
	 * @param string $first
	 * @param string $second
	 * @return mixed
	 */
	function _add($first = null, $second = null){
		if(!is_null($first) && !is_null($second)){
			$ft = strtotime($first);
			$st = strtotime($second);
			return $st + $ft;
		}
		return false;
	}

	/**
	 * setup timezone
	 * @param  string $timezone
	 * @return boolean
	 */
	function timezone($timezone = null){
		if(is_null($timezone)){
			date_default_timezone_set($timezone);
			return true;
		}
		return false;
	}

	/**
	 * convert string to date
	 * @param  string $string
	 * @return mixed
	 */
	function convert($string = null){
		if(!is_null($string)){
			return date($string, $this->date);
		}
		return false;
	}

	/**
	 * diffrents time second time to first time
	 * @param  string $result
	 * @param  string $first
	 * @param  string $second
	 * @return mixed
	 */
	function diff($result = null, $first = null, $second = null){
		if(!is_null($result) && !is_null($first) && !is_null($second)){
			return date($result, $this->_remove($first, $second));
		}
		return false;
	}

	/**
	 * adds time second time to first time
	 * @param  string $result
	 * @param  string $first
	 * @param  string $second
	 * @return mixed
	 */
	function add($result = null, $first = null, $second = null){
		if(!is_null($result) && !is_null($first) && !is_null($second)){
			return date($result, $this->_add($first, $second));
		}
		return false;
	}
}
?>
