<?php
/**
 * Bower class.
 */
namespace Bluejacket\Web;
class Bower {
	/**
	 * componentsFolder
	 *
	 * (default value: 'Application/bower_components')
	 *
	 * @var string
	 * @access public
	 */
	public $componentsFolder = 'bower_components';
	/**
	 * folder
	 *
	 * (default value: null)
	 *
	 * @var mixed
	 * @access public
	 */
	public $folder = null;
	/**
	 * html
	 *
	 * (default value: null)
	 *
	 * @var mixed
	 * @access public
	 */
	public $html = null;

	/**
	 * __construct function.
	 *
	 * @access public
	 * @param mixed $name
	 * @param mixed $target (default: null)
	 * @param mixed $componentsFolder (default: null)
	 * @return void
	 */
	function __construct($name, $target = null, $componentsFolder = null) {
		if (!is_null($componentsFolder)) {
			$this->folder .= $componentsFolder;
		} else {
			$this->folder .= $this->componentsFolder;
		}

		if (isset($name)) {
			$this->folder .= "/".$name;
		}

		if (!is_null($target)) {
			$this->folder .= "/".$target;
		}
	}

	/**
	 * generate function.
	 *
	 * @access public
	 * @param array $files (default: array())
	 * @param array $type (default: array())
	 * @return void
	 */
	public function generate($files = array(), $type = array()) {
		if (is_array($type)) {
			foreach ($type as $t) {
				if ($t == "css") {
					if (is_array($files)) {
						foreach ($files as $file) {
							$this->html .= $this->css($this->folder."/".$file.".css");
						}
					} else {
						$this->html .= $this->css($this->folder."/".$files.".css");
					}

				}

				if ($t == "js") {
					if (is_array($files)) {
						foreach ($files as $file) {
							$this->html .= $this->js($this->folder."/".$file.".js");
						}
					} else {
						$this->html .= $this->js($this->folder."/".$files.".js");
					}

				}
			}
		}
		return $this->html;
	}

	/**
	 * css function.
	 *
	 * @access public
	 * @param mixed $obje
	 * @return void
	 */
	public function css($obje) {
		if (isset($obje) && $obje != null) {
			return '<link rel="stylesheet" type="text/css" href="/'.$obje.'"/>';
		}
	}

	/**
	 * js function.
	 *
	 * @access public
	 * @param mixed $obje
	 * @return void
	 */
	public function js($obje) {
		if (isset($obje) && $obje != null) {
			return '<script type="text/javascript" src="/'.$obje.'"></script>';
		}
	}

}