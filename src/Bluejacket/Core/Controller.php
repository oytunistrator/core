<?php
/**
 * Controller class.
 */
namespace Bluejacket\Core;
class Controller extends Core
{
	/**
	 * html
	 *
	 * @var mixed
	 * @access public
	 */
	public $html;
	/**
	 * view
	 *
	 * @var mixed
	 * @access public
	 */
	public $view;
	/**
	 * form
	 *
	 * @var mixed
	 * @access public
	 */
	public $form;
	/**
	 * url
	 *
	 * @var mixed
	 * @access public
	 */
	public $url;
	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct(){
            $this->loadUri();
	}


	/**
	 * uri function.
	 *
	 * @access public
	 * @param mixed $id
	 * @return void
	 */
	public function loadUri(){
		$uri = parse_url($_SERVER['REQUEST_URI']);
		$query = isset($uri['query']) ? $uri['query'] : '';
		$uri = isset($uri['path']) ? rawurldecode($uri['path']) : '';


		if (strpos($uri, $_SERVER['SCRIPT_NAME']) === 0)
		{
			$uri = (string) substr($uri, strlen($_SERVER['SCRIPT_NAME']));
		}
		elseif (strpos($uri, dirname($_SERVER['SCRIPT_NAME'])) === 0)
		{
			$uri = (string) substr($uri, strlen(dirname($_SERVER['SCRIPT_NAME'])));
		}

		$this->_url = explode('/',$uri);

		if(isset($this->_url[0])
			&& $this->_url[0] == "index"
			|| $this->_url[0] == "index.php"
			|| $this->_url[0] == ""){
			unset($this->_url[0]);
		}
	}

	/**
	 * uri function.
	 *
	 * @access public
	 * @param mixed $id
	 * @return void
	 */
	function uri($id){
		if(isset($id)){
			return $this->_url[$id];
		}
		return false;
	}

	function getUri(){
		return $this->_url;
	}

    /**
     * call user function from method and arguments
     * @param  array $method    method strings
     * @param  array $arguments function array to arguments
     * @return mixed            return user function
     */
    public function __call($method,$arguments) {
        if(method_exists($this, $method)) {
            $this->totalArguments = func_num_args();
            $this->arguments = func_get_args();
            return call_user_func_array(array($this,$method),$arguments);
        }
    }
    public function getClassName() {
        $class = get_called_class();
        $explode = explode("\\",$class);
        
        return $explode[self::_getLastKey($explode)];
    }


   static function _getLastKey($data){
        if(!is_array($data)){
            return false;
        }
        return key(array_slice($data, -1,1, TRUE));
   }    
}