<?php
namespace Bluajacket\Framework\Database;
const STATUS_REPLY = '+';
const ERROR_REPLY = '-';
const INTEGER_REPLY = ':';
const BULK_REPLY = '$';
const MULTI_BULK_REPLY = '*';
/**
 * SocketException class.
 */
class SocketException extends \Exception { }

/**
 * ProtocolException class.
 */
class ProtocolException extends \Exception { }


/**
 * Redis class.
 */
class Redis
{
	/**
	 * client function.
	 *
	 * @access public
	 * @param string $host (default: '127.0.0.1')
	 * @param int $port (default: 6379)
	 * @param mixed $timeout (default: NULL)
	 * @return void
	 */
	function client($host='127.0.0.1', $port=6379, $timeout=NULL)
	{
		$timeout = $timeout ?: ini_get("default_socket_timeout");
		$fp = fsockopen($host, $port, $errno, $errstr, $timeout);
		if (!$fp) throw new SocketException($errstr, $errno);
		return function ($cmd) use ($fp)
		{
			$cmd = trim($cmd);
			if ('quit' == strtolower($cmd)) return fclose($fp);
			$return = fwrite($fp, $this->_multi_bulk_reply($cmd));
			if ($return === FALSE) 	throw new SocketException();
			$reply = $this->_reply($fp);
			if ('hgetall' === substr(strtolower($cmd), 0, 7))
			{
				$reply_count = count($reply);
				$hash_reply = array();
				for ($i = 0; $i < $reply_count; $i += 2)
				{
					$hash_reply[$reply[$i]] = $reply[$i+1];
				}
				return $hash_reply;
			}
			return $reply;
		};
	}


	/**
	 * _multi_bulk_reply function.
	 *
	 * @access private
	 * @param mixed $cmd
	 * @return void
	 */
	function _multi_bulk_reply($cmd)
	{
		$tokens = str_getcsv($cmd, ' ', '"');
		$number_of_arguments = count($tokens);
		$multi_bulk_reply = "*$number_of_arguments\r\n";
		foreach ($tokens as $token) $multi_bulk_reply .= $this->_bulk_reply($token);
		return $multi_bulk_reply;
	}

	/**
	 * _bulk_reply function.
	 *
	 * @access private
	 * @param mixed $arg
	 * @return void
	 */
	function _bulk_reply($arg)
	{
		return '$'.strlen($arg)."\r\n".$arg."\r\n";
	}


	/**
	 * _reply function.
	 *
	 * @access private
	 * @param mixed $fp
	 * @return void
	 */
	function _reply($fp)
	{
		$reply = fgets($fp);
		if (FALSE === $reply) throw new SocketException('Error Reading Reply');
		$reply = trim($reply);
		$reply_type = $reply[0];
		$data = substr($reply, 1);
		switch($reply_type)
		{
			case STATUS_REPLY:
				if ('ok' == strtolower($data)) return true;
				return $data;
			case ERROR_REPLY:
				throw new ProtocolException(substr($data, 4));
			case INTEGER_REPLY:
				return $data;
			case BULK_REPLY:
				$data_length = intval($data);
				if ($data_length < 0) return NULL;
				$bulk_reply = stream_get_contents($fp, $data_length + strlen("\r\n"));
				if (FALSE === $bulk_reply) throw new SocketException('Error Reading Bulk Reply');
				return trim($bulk_reply);
			case MULTI_BULK_REPLY:
				$bulk_reply_count = intval($data);
				if ($bulk_reply_count < 0) return NULL;
				$multi_bulk_reply = array();
				for($i = 0; $i < $bulk_reply_count; $i++) $multi_bulk_reply[] = $this->_reply($fp);
				return $multi_bulk_reply;
			default:
				throw new ProtocolException("Unknown Reply Type: $reply");
		}
	}
}

?>
