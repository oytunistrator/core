<?php
namespace Bluejacket\Connector\WebSocket;

use Bluejacket\Core\Exception as Error;
class WebSocket
{
	private static $mode = 'server';
	private static $ip;
	private static $port;
	private static $sock;
	
	public function __construct($config){
		if(isset($config)){
			foreach($config as $k => $v){
				self::$k = $v;
			}
		}
		self::server();
	}
	
	public static function server(){
		try{
			if(self::$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) === false){
				throw new Error("socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n");
			}
			if (socket_bind(self::$sock, $address, $port) === false) {
				throw new Error("socket_bind() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n");
			}
			if (socket_listen(self::$sock, 5) === false) {
				throw new Error("socket_listen() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n");
			}
			do {
				if (($msgsock = socket_accept($sock)) === false) {
					throw new Error("socket_accept() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n");
					break;
				}
				$msg = "\nWelcome to the PHP Test Server. \n" .
						"To quit, type 'quit'. To shut down the server type 'shutdown'.\n";
				socket_write($msgsock, $msg, strlen($msg));
			
				do {
					if (false === (self::$buf = socket_read($msgsock, 2048, PHP_NORMAL_READ))) {
						throw new Error("socket_read() failed: reason: " . socket_strerror(socket_last_error($msgsock)) . "\n");
						break 2;
					}
					if (!$buf = trim($buf)) {
						continue;
					}
					if(isset(self::$commands)){
						foreach (self::$commands as $k => $v){
							if(self::$buf == $k && is_callable($v)){
								$talkback = call_user_func($v, func_get_args($v));
							}
						}
					}
					socket_write($msgsock, $talkback, strlen($talkback));
				} while (true);
				
				socket_close($msgsock);
			} while (true);
		}catch (Error $e){
			printf("LOG: %s",$e->getMessage());	
		}
	}
	
	public static function connect(){
		try{
			/* Create a TCP/IP socket. */
			$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
			if ($socket === false) {
				throw new Error("socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n");
			}
			
			$result = socket_connect($socket, $address, $service_port);
			if ($result === false) {
				throw new Error("socket_connect() failed.\nReason: ($result) " . socket_strerror(socket_last_error($socket)) . "\n");
			}
			
			$in = "HEAD / HTTP/1.1\r\n";
			$in .= "Host: ".$_SERVER['REMOTE_ADDR']."\r\n";
			$in .= "Connection: Close\r\n\r\n";
			$out = '';
			if(isset(self::$input) && is_array(self::$input)){
				foreach (self::$input as $k => $v){
					if(is_callable($v)){
						$in = call_user_func($v, func_get_args($v));
					}
				}
			}
			$out = '';
			
			socket_write($socket, $in, strlen($in));
			while ($out = socket_read($socket, 2048)) {
				if(is_callable(self::$success)){
					call_user_func(self::$success, func_get_args(self::$success));
				}else{
					printf("LOG: %s",$e->getMessage());
				}
			}
			
			socket_close($socket);
		}catch(Error $e){
			if(is_callable(self::$error)){
				call_user_func(self::$error, func_get_args(self::$error));
			}else{
				printf("LOG: %s",$e->getMessage());
			}
		}
	}
}