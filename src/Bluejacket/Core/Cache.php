<?php
/**
 * Cache class.
 */
namespace Bluejacket\Core;
class Cache
{
	/**
	 * _folder
	 *
	 * (default value: CACHE_FOLDER)
	 *
	 * @var mixed
	 * @access public
	 */
	public $_folder = CACHE_FOLDER;
	/**
	 * _time
	 *
	 * (default value: CACHE_TIMER)
	 *
	 * @var mixed
	 * @access public
	 */
	public $_time = CACHE_TIMER;
	/**
	 * _filename
	 *
	 * @var mixed
	 * @access public
	 */
	public $_filename;
	/**
	 * _ctime
	 *
	 * @var mixed
	 * @access public
	 */
	public $_ctime;
	/**
	 * _cname
	 *
	 * @var mixed
	 * @access public
	 */
	public $_cname;
	/**
	 * fb
	 *
	 * @var mixed
	 * @access private
	 */
	private $fb;

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	function __construct(){
		$this->_filename = md5($_SERVER['REQUEST_URI']).".html";
		$this->_cname = $this->_folder."/".$this->_filename;
		$this->_ctime = $this->_time * 60 * 60;

		if (file_exists($this->_cname)){
			if(time() - $this->_ctime < filemtime($this->_cname)){
				readfile($this->_cname);
				exit();
			}else{
				unlink($this->_cname);
			}
		}
		ob_start();
	}

	/**
	 * end function.
	 *
	 * @access public
	 * @return void
	 */
	function end(){
		$this->fp = fopen($this->_cname, 'w+');
		fwrite($this->fp, ob_get_contents());
		fclose($this->fp);
		ob_end_flush();
	}
}