<?php
/**
 * View class.
 */
namespace Bluajacket\Framework\Core;
class View{
	/**
	 * _cfg
	 *
	 * @var mixed
	 * @access private
	 * @static
	 */
	private static $_cfg = array(
		"cache_header" => null,
		"cache_lifetime" => 3600,
		"caching" => false,
		"dir_cache" => "Application/cache/",
		"dir_tpl" => "Application/template/",
		"error_type" => E_USER_ERROR,
		"ext_cache" => ".cache.html",
		"ext_tpl" => ".html",
		"security" => false
	);

	/**
	 * _tpl
	 *
	 * @var mixed
	 * @access private
	 */
	private $_tpl = array(
		"cached_files" => array(),
		"compiled" => null,
		"name" => null
	);

	/**
	 * _vars
	 *
	 * (default value: array())
	 *
	 * @var array
	 * @access private
	 */
	private $_vars = array();


	/**
	 * __construct function.
	 *
	 * @access public
	 * @param mixed $controller (default: null)
	 * @return void
	 */
	function __construct($controller=null){
		if(!is_null($controller)){
			$this->setConfig("dir_tpl", TEMPLATE_FOLDER.$controller."/");
		}
		if(CACHE_EXTENTION){
			$this->setConfig("caching",true);
			$this->setConfig("dir_cache", CACHE_FOLDER."/");
		}
		if(SECURITY_EXTENSION){
			$this->setConfig("security",true);
		}
	}

	/**
	 * _cacheWrite function.
	 *
	 * @access private
	 * @param mixed $cache_filename (default: null)
	 * @return void
	 */
	private function _cacheWrite($cache_filename = null) {
		if((int)self::$_cfg["cache_lifetime"] > 0 && $this->_tpl["compiled"] !== null) {
			if(!is_writable(self::$_cfg["dir_cache"])) {
				$this->_error("Failed to write cache file \"{$cache_filename}\" "
					. "(cache directory \"" . self::$_cfg["dir_cache"] . "\" is not writable)");
			}

			file_put_contents($cache_filename, self::$_cfg["cache_header"] . $this->_tpl["compiled"]);
		}
	}


	/**
	 * _compile function.
	 *
	 * @access private
	 * @param mixed $template (default: null)
	 * @param bool $init_only (default: false)
	 * @return void
	 */
	private function _compile($template = null, $init_only = false) {
		$filename = $cache_filename = null;

		if($this->_tpl["compiled"] === null || $template != $this->_tpl["name"]) {
			$this->_tpl["name"] = $template;

			$filename = self::$_cfg["dir_tpl"] . $template . self::$_cfg["ext_tpl"];

			if(self::$_cfg["caching"] && (int)self::$_cfg["cache_lifetime"] > 0) {
				if(isset($this->_tpl["cached_files"][$template])) {
					$this->_tpl["compiled"] = file_get_contents($this->_tpl["cached_files"][$template]);

					return;
				}

				$cache_filename = self::$_cfg["dir_cache"] . rawurlencode(md5($template). self::$_cfg["ext_cache"]);

				if(is_file($cache_filename)){
					$cache_filename = self::$_cfg["dir_cache"] . rawurlencode(md5($template."_".rand(0,9999)). self::$_cfg["ext_cache"]);
				}

				if(file_exists($cache_filename)) {
					if( (time() - filemtime($cache_filename)) < self::$_cfg["cache_lifetime"]) {
						$this->_tpl["compiled"] = file_get_contents($cache_filename);

						$this->_tpl["cached_files"][$template] = $cache_filename;

						return;
					} else {
						unlink($cache_filename);
					}
				}
			}

			if(!file_exists($filename)) {
				if(APP_DEBUGING){
					$error = new Error();
					$error->show("Failed to load template: \"{$filename}\" (template file not found)");
				}
			}
		} else {
			return;
		}

		if($init_only) {
			return;
		}

		$regex_tags = array(
			"(\{\*(?:.*?))",
			"((?:.*?)\*\})", // eof comment

			"(\{func=(?:\w+\(.*?\))\})",

			"(\{if.*?\}.*?\{\/if\})",

			"(\{include=(?:.*?)\})",

			"(\{literal\})",
			"(\{\/literal\})", // eof literal

			"(\{loop=\\$(?:\w+)\})",
			"(\{\/loop\})" // eof loop
		);

		$regex_tags = "/" . implode("|", $regex_tags) . "/";

		$template_parts = preg_split($regex_tags, file_get_contents($filename), -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

		$in_comment = $in_loop = 0;
		$in_literal = false;
		$in_loops = array();

		while($raw = array_shift($template_parts)) {
			if($in_comment && preg_match("/(?:.*?)\*\}/", $raw)) {
				$in_comment--;
			} else if($in_comment) {
					if(preg_match("/\{\*(?:.*?)/", $raw)) {
						$in_comment++;
					}
				} else if(preg_match("/\{\*(?:.*?)/", $raw)) {
					$in_comment++;
				} else if($in_literal && preg_match("/\{\/literal\}/", $raw)) {
					$in_literal = false;

					$this->_tpl["compiled"] .= str_replace("{/literal}", null, $raw);
				} else if($in_literal) {
					$this->_tpl["compiled"] .= $raw;
				} else if(preg_match("/\{literal\}/", $raw)) {
					$in_literal = true;

					$this->_tpl["compiled"] .= str_replace("{literal}", null, $raw);
				} else if(preg_match("/\{func=(\w+)\((.*?)\)\}/", $raw, $matches)) {
					if(isset($matches[1])) {
						if(is_callable($matches[1])) {
							$this->_tpl["compiled"] .= isset($matches[2]) ? call_user_func_array($matches[1], explode(",", $matches[2]))
							: call_user_func($matches[1]);
						}
					}
				} else if(preg_match("/(?:\{include=(.*?)\})/", $raw, $matches)) {
					if(count($matches) && isset($matches[1])) {
						$tmp = new self;
						$tmp->set($this->_vars);

						$this->_tpl["compiled"] .= $tmp->fetch($matches[1]);

						unset($tmp);
					}
				} else if(preg_match("/\{loop=\\$(\w+)\}/", $raw, $matches)) {
					if(isset($matches[1])) {
						$in_loop++;

						$in_loops[$in_loop] = $matches[1];
					}
				} else if($in_loop && preg_match("/\{\/loop\}/", $raw, $matches)) {
					if(isset($in_loops[$in_loop])) {
						unset($in_loops[$in_loop]);
					}

					$in_loop--;
				} else if($in_loop && isset($in_loops[$in_loop])) {
					if(isset($this->_vars[$in_loops[$in_loop]]) && is_array($this->_vars[$in_loops[$in_loop]])) {
						$i = 0;

						foreach($this->_vars[$in_loops[$in_loop]] as $k => $v) {
							if(!is_array($v)) {
								$this->_tpl["compiled"] .= str_replace("{\$counter}", $i, str_replace("{\$key}", $k, str_replace("{\$value}",
											$this->_formatCompiledValue($v), $raw)));
							} elseif(preg_match_all("/\{\\$(\w+)\.(\w+)\}/", $raw, $matches)) {
								if(!empty($matches[2])) {
									$tmp = $raw;

									foreach($matches[2] as $el) {
										if(isset($this->_vars[$in_loops[$in_loop]][$k][$el])) {
											$tmp = str_replace("{\$counter}", $i, str_replace("{\$key}", $k, preg_replace("/\{\\\$value\.{$el}\}/",
														$this->_formatCompiledValue($this->_vars[$in_loops[$in_loop]][$k][$el]), $tmp)));
										}
									}

									$this->_tpl["compiled"] .= preg_replace("/\{\\$\w+\.\w+\}/", null, $tmp);
								}

							}

							$i++;
						}
					}
				} else if(preg_match("#\{if.*?\}.*?\{\/if\}#", $raw, $matches)) {
					preg_match("#\{if \\$(\w+)\=?.*?\}#", $matches[0], $var);
					$var = isset($var[1]) ? $var[1] : null;

					preg_match("#\{if \\$\w+\=?.*?\}(.*?)(?:\{else\}|\{\/if\})#", $matches[0], $if);
					$if = isset($if[1]) ? $if[1] : "";

					$else = null;
					if(strpos($matches[0], "{else}") !== false) {
						preg_match("#\{else\}(.*?)\{\/if\}#", $matches[0], $else);
						$else = isset($else[1]) ? $else[1] : "";
					}

					if(preg_match("#\{if \\$\w+\}.*?\{\/if\}#", $matches[0])) {
						$this->_tpl["compiled"] .= $this->_replaceVars( isset($this->_vars[$var]) && $this->_vars[$var] ? $if : $else );
					} else if(preg_match("#\{if \\$\w+(?:\=\=|\!\=|\>\=|\<\=|\>|\<).*?\}.*?\{\/if\}#", $matches[0])) {
							preg_match("#\{if \\$\w+(\=\=|\!\=|\>\=|\<\=|\>|\<)(.*?)\}#", $matches[0], $condition);

							if(isset($condition[1]) && isset($condition[2]) && isset($this->_vars[$var])) {
								$true = false;

								eval(" \$true = \$this->_vars[\$var] {$condition[1]} \$condition[2]; ");

								$this->_tpl["compiled"] .= $this->_replaceVars( $true ? $if : $else );
							} else {
								$this->_tpl["compiled"] .= $this->_replaceVars($else);
							}
						}
				} else {
				$this->_tpl["compiled"] .= $this->_replaceVars($raw);
			}
		}

		if($cache_filename) {
			$this->_cacheWrite($cache_filename);
		}
	}


	/**
	 * _error function.
	 *
	 * @access private
	 * @param mixed $error_message (default: null)
	 * @return void
	 */
	private function _error($error_message = null) {
		if(self::$_cfg["error_type"]) {
			trigger_error($error_message, self::$_cfg["error_type"]);
		}
	}

	/**
	 * _formatCompiledValue function.
	 *
	 * @access private
	 * @param mixed $value (default: null)
	 * @param mixed $tag (default: null)
	 * @return void
	 */
	private function _formatCompiledValue($value = null, $tag = null) {
		if(!is_scalar($value)) {
			return null;
		}

		preg_match_all("/\{\\$(?:[\w\.].*?)\|(\w+)\}/", $tag, $matches);

		if(isset($matches[1][0])) {
			$matches[1][0] = strtolower($matches[1][0]);

			switch($matches[1][0]) {
			case "b":
			case "i":
			case "u":
				$value = "<{$matches[1][0]}>{$value}</{$matches[1][0]}>";
				break;
			case "capitalize":
				$value = ucwords($value);
				break;
			case "escape":
				$value = rawurlencode($value);
				break;
			case "lower":
				$value = strtolower($value);
				break;
			case "upper":
				$value = strtoupper($value);
				break;
			}
		}

		return $value;
	}

	/**
	 * _replaceVars function.
	 *
	 * @access private
	 * @param mixed $html (default: null)
	 * @return void
	 */
	private function _replaceVars($html = null) {
		preg_match_all("/\{\\$(\w+)=(.*?)\}/", $html, $matches);

		for($i = 0; $i < count($matches[0]); $i++) {
			if(isset($matches[1][$i])) {
				$this->_vars[$matches[1][$i]] = isset($matches[2][$i]) ? $this->_formatCompiledValue($matches[2][$i]) : null;
			}

			$c = 0;
			$html = str_replace($matches[0][$i], null, $html, $c);
		}

		preg_match_all("/\{\\$(\w+)(?:\|(\w+))?\}/", $html, $matches);

		for($i = 0; $i < count($matches[0]); $i++) {
			$val = null;

			if(isset($matches[1][$i], $this->_vars[$matches[1][$i]])) {
				$val = $this->_vars[$matches[1][$i]];
			}

			$html = str_replace($matches[0][$i], $this->_formatCompiledValue($val, $matches[0][$i]), $html);
		}

		preg_match_all("/\{\\$\.(\w+)\.(\w+)(?:\|(\w+))?\}/", $html, $matches);

		for($i = 0; $i < count($matches[0]); $i++) {
			$val = null;

			if(isset($matches[1][$i], $matches[2][$i])) {
				$var = $matches[2][$i];


				if(self::$_cfg['security']){
					foreach ($_GET as $get => $content){
						$_GET[$get]=preg_replace("/&#?[a-z0-9]{2,8};/i","",$content);
					}
					foreach ($_POST as $post => $content){
						$_POST[$post]=preg_replace("/&#?[a-z0-9]{2,8};/i","",$content);
					}
				}

				switch($matches[1][$i]) {
				case "const":
					$val = defined($var) ? constant($var) : null;
					break;
				case "cookie":
					$val = isset($_COOKIE[$var]) ? $_COOKIE[$var] : null;
					break;
				case "get":
					$val = isset($_GET[$var]) ? $_GET[$var] : null;
					break;
				case "post":
					$val = isset($_POST[$var]) ? $_POST[$var] : null;
					break;
				case "session":
					$val = isset($_SESSION[$var]) ? $_SESSION[$var] : null;
					break;
				case "server":
					$val = isset($_SERVER[$var]) ? $_SERVER[$var] : null;
					break;
				}
			}

			$html = str_replace($matches[0][$i], $this->_formatCompiledValue($val, $matches[0][$i]), $html);
		}
		unset($var);

		preg_match_all("/\{\\$(\w+)\.(\w+)(?:\|(\w+))?\}/", $html, $matches);

		for($i = 0; $i < count($matches[0]); $i++) {
			$val = null;

			if(isset($matches[1][$i], $matches[2][$i], $this->_vars[$matches[1][$i]]) && is_array($this->_vars[$matches[1][$i]])
				&& array_key_exists($matches[2][$i], $this->_vars[$matches[1][$i]])) {
				$val = $this->_vars[$matches[1][$i]][$matches[2][$i]];
			}

			$html = str_replace($matches[0][$i], $this->_formatCompiledValue($val, $matches[0][$i]), $html);
		}

		preg_match_all("/\{\\$(\w+)\-\>(\w+)(?:\|(\w+))?\}/", $html, $matches);

		for($i = 0; $i < count($matches[0]); $i++) {
			$val = null;

			if(isset($matches[1][$i], $matches[2][$i], $this->_vars[$matches[1][$i]]) && is_object($this->_vars[$matches[1][$i]])
				&& property_exists($this->_vars[$matches[1][$i]], $matches[2][$i])) {
				$val = $this->_vars[$matches[1][$i]]->$matches[2][$i];
			}

			$html = str_replace($matches[0][$i], $this->_formatCompiledValue($val, $matches[0][$i]), $html);
		}

		preg_match_all("/\{\\$(\w+)\-\>([a-zA-Z_]+)\((.*?)\)\}/", $html, $matches);

		for($i = 0; $i < count($matches[0]); $i++) {
			$val = null;

			if(isset($matches[1][$i], $matches[2][$i], $this->_vars[$matches[1][$i]]) && is_object($this->_vars[$matches[1][$i]])
				&& method_exists($this->_vars[$matches[1][$i]], "{$matches[2][$i]}")) {
				$val = isset($matches[3][$i]) ? $this->_vars[$matches[1][$i]]->$matches[2][$i]($matches[3][$i])
				: $this->_vars[$matches[1][$i]]->$matches[2][$i]();
			}

			$html = str_replace($matches[0][$i], $this->_formatCompiledValue($val), $html);
		}

		return $html;
	}

	/**
	 * cache function.
	 *
	 * @access public
	 * @param mixed $template (default: null)
	 * @param int $cache_lifetime (default: 0)
	 * @return void
	 */
	public function cache($template = null, $cache_lifetime = 0) {
		if((int)$cache_lifetime > 0) {
			self::setConfig("cache_lifetime", $cache_lifetime);
		}

		$this->_compile($template, true);

		return isset($this->_tpl["cached_files"][$template]);
	}

	/**
	 * cacheFlush function.
	 *
	 * @access public
	 * @return void
	 */
	public function cacheFlush() {
		array_map("unlink", glob(self::$_cfg["dir_cache"] . "*" . self::$_cfg["ext_cache"]));
	}

	/**
	 * load function.
	 *
	 * @access public
	 * @param mixed $template (default: null)
	 * @return void
	 */
	public function load($template = null) {
		$this->_compile($template);

		print $this->_tpl["compiled"];
	}

	/**
	 * engine function.
	 *
	 * @access public
	 * @static
	 * @return void
	 */
	public static function engine() {
		static $view = null;

		if($view === null) {
			$view = new self;
		}

		return $view;
	}

	/**
	 * fetch function.
	 *
	 * @access public
	 * @param mixed $template (default: null)
	 * @return void
	 */
	public function fetch($template = null) {
		$this->_compile($template);

		return $this->_tpl["compiled"];
	}

	/**
	 * get function.
	 *
	 * @access public
	 * @param mixed $var (default: null)
	 * @return void
	 */
	public function get($var = null) {
		if(isset($this->_vars[$var])) {
			return $this->_vars[$var];
		}
	}

	/**
	 * set function.
	 *
	 * @access public
	 * @param mixed $var (default: null)
	 * @param mixed $value (default: null)
	 * @return void
	 */
	public function set($var = null, $value = null) {
		if(!is_array($var)) {
			$this->_vars[$var] = $value;
		} else {
			$this->_vars += $var;
		}
	}

	/**
	 * setConfig function.
	 *
	 * @access public
	 * @static
	 * @param mixed $key (default: null)
	 * @param mixed $value (default: null)
	 * @return void
	 */
	public static function setConfig($key = null, $value = null) {
		if(is_array($key)) {
			foreach($k as $v) {
				$this->setConfig($k, $v);
			}
		} else {
			if(array_key_exists($key, self::$_cfg)) {
				self::$_cfg[$key] = trim($value);
			}
		}
	}
}
?>
