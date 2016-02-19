<?php
namespace Bluejacket\Connector;
/**
 * Curl class
 */
class Curl
{
	function __construct($array = array()){
		if(count($array)>0){
			foreach($array as $k => $v){
				$this->{$k} = $v;
			}
		}


		$this->_connection = curl_init();
		/* Extend */
        if(isset($this->url)) curl_setopt($this->_connection, CURLOPT_URL,$this->url);
        if(isset($this->returnTransfer) && $this->returnTransfer == true) curl_setopt($this->_connection, CURLOPT_RETURNTRANSFER, 1);
        if(isset($this->headers) && is_array(headers)) curl_setopt($this->_connection, CURLOPT_HTTPHEADER, $this->headers);
        if(isset($this->port)) curl_setopt($this->_connection,CURLOPT_PORT,$this->port);
        if(isset($this->customRequest)) curl_setopt($this->_connection,CURLOPT_CUSTOMREQUEST,$this->customRequest);
        if(isset($this->encoding)) curl_setopt($this->_connection, CURLOPT_ENCODING, $this->encoding);
	}

	/**
	 * set options
	 * @param mixed $key
	 * @param mixed $value
	 */
	function setOpt($key,$value){
		curl_setopt($this->_connection, $key, $value);
	}

	/**
	 * set to send post data
	 * @param  array $data setup post data
	 * @return mixed
	 */
	function post($data){
		$this->_data = (is_array($data) ? http_build_query($data) : $data);
		curl_setopt($this->_connection, CURLOPT_POST, 1);
		curl_setopt($this->_connection, CURLOPT_POSTFIELDS, $this->_data);
	}

	/**
	 * get curl info from headers
	 * @param  mixed $header
	 * @return mixed
	 */
	function info($header){
		return curl_getinfo($this->_connection, $header);
	}

	/**
	 * execute all setups
	 * @return mixed return array, content or false
	 */
	function execute(){
		$res = curl_exec($this->_connection);
		curl_close($this->_connection);
		if($res === false){
			///$res = 'Curl error: '.curl_error($ch);
			return false;
		}else{
			return $res;
		}
	}
}