<?php
/**
 * HTML class.
 */
namespace Framework\Web;
class HTML
{

	/**
	 * __construct function.
	 *
	 * @access public
	 * @param array $config (default: array())
	 * @return void
	 */
	function __construct(array $config = array()){
		foreach($config as $key => $val){
			$this->{$key} = $val;
		}

		if(isset($this->folder)){
			$this->baseFolder = "/".$this->folder."/";
		}else{
			$this->baseFolder = "/";
		}
	}

	/**
	 * title function.
	 *
	 * @access public
	 * @param mixed $title
	 * @return void
	 */
	public function title($title){
		return '<title>'.$title.'</title>'."";

	}

	/**
	 * keywords function.
	 *
	 * @access public
	 * @param mixed $keywords
	 * @return void
	 */
	public function keywords($keywords){
		return "<meta name=\"keywords\" content=\"".$keywords."\" />";
	}

	/**
	 * author function.
	 *
	 * @access public
	 * @param mixed $author
	 * @return void
	 */
	public function author($author){
		return "<meta name=\"author\" content=\"".$author."\" />";
	}

	/**
	 * description function.
	 *
	 * @access public
	 * @param mixed $desc
	 * @return void
	 */
	public function description($desc){
		return "<meta name=\"description\" content=\"".$desc."\" />";
	}

	public function meta($name,$content,$adds=array()){
		$add = null;
		if(is_array($adds)){
			foreach($adds as $addKey => $addValue){
				$add.="".$addKey."=\"".$addValue."\" ";
			}
		}
		return "<meta name=\"".$name."\" content=\"".$content."\" ".$add."/>";
	}

	/**
	 * html_start function.
	 *
	 * @access public
	 * @static
	 * @return void
	 */
	public static function html_start(){
		return '<!DOCTYPE html>'."\n".'<html>'."";
	}

	/**
	 * html_end function.
	 *
	 * @access public
	 * @static
	 * @return void
	 */
	public static function html_end(){
		return '</html>';
	}

	/**
	 * body_start function.
	 *
	 * @access public
	 * @static
	 * @return void
	 */
	public static function body_start(){
		return '<body>';
	}

	/**
	 * body_end function.
	 *
	 * @access public
	 * @static
	 * @return void
	 */
	public static function body_end(){
		return '</body>';
	}



	/**
	 * js function.
	 *
	 * @access public
	 * @param array $options (default: array())
	 * @return void
	 */
	public function js(array $options = array()){
		$path = $this->baseFolder;
		if(isset($options['folder'])) $path .= $options['folder'];
		if(isset($options['file'])) $path .= $options['file'];
		return '<script type="text/javascript" src="'.$path.'"></script>';
	}



	/**
	 * css function.
	 *
	 * @access public
	 * @param array $options (default: array())
	 * @return void
	 */
	public function css(array $options = array()){
		$path = $this->baseFolder;
		if(isset($options['folder'])) $path .= $options['folder'];
		if(isset($options['file'])) $path .= $options['file'];
		return'<link rel="stylesheet" type="text/css" href="'.$path.'"/>';
	}


	/**
	 * charset function.
	 *
	 * @access public
	 * @static
	 * @param mixed $type
	 * @return void
	 */
	public static function charset($type){
		if(isset($type) && $type!=null){
			return '<meta http-equiv="content-type" content="text/html;charset='.$type.'" />';
		}
	}


	/**
	 * p_start function.
	 *
	 * @access public
	 * @static
	 * @return void
	 */
	public static function p_start(){
		return "<p>\n";
	}

	/**
	 * p_end function.
	 *
	 * @access public
	 * @static
	 * @return void
	 */
	public static function p_end(){
		return "</p>\n";
	}

	/**
	 * b function.
	 *
	 * @access public
	 * @static
	 * @param mixed $text
	 * @return void
	 */
	public static function b($text){
		return "<strong>".$text."</strong>\n";
	}


	/**
	 * h function.
	 *
	 * @access public
	 * @static
	 * @param mixed $text
	 * @param mixed $w (default: null)
	 * @return void
	 */
	public static function h($text,$w=null){
		if(isset($w)){
			return "<h".$w.">".$text."</h".$w.">\n";
		}else{
			return "<h1>".$text."</h1>\n";
		}
	}


	/**
	 * getTextToHTML function.
	 *
	 * @access public
	 * @param mixed $file
	 * @return void
	 */
	public function getTextToHTML($file){
		if(file_exists($file)){
			$fo=fopen($file,"r");
			$fs=filesize($file);
			$fget=fread($fo,$fs);
			fclose($fo);
			return nl2br($fget);
		}
		return false;
	}

	/**
	 * favicon function.
	 *
	 * @access public
	 * @param mixed $filename
	 * @return void
	 */
	public function favicon(array $options = array()){
		return "<link rel='shortcut icon' href='/".$options['folder'].$options['file']."'  />\n";
	}

	/**
	 * redirect function.
	 *
	 * @access public
	 * @param mixed $url
	 * @param mixed $time
	 * @return void
	 */
	public function redirect($url,$time){
		return '<meta http-equiv="refresh" content="'.$time.';URL='.$url.'" />'."\n";
	}

	/**
	 * jsdirect function.
	 *
	 * @access public
	 * @param mixed $url
	 * @return void
	 */
	public function jsdirect($url){
		return '<script>window.location=\''.$url.'\'</script>';
	}

	/**
	 * direct function.
	 *
	 * @access public
	 * @param mixed $url
	 * @return void
	 */
	public function direct($url){
		header("Location: ".$url);
	}

	/**
	 * back function.
	 *
	 * @access public
	 * @param mixed $content
	 * @param mixed $class (default: null)
	 * @return void
	 */
	public function back($content,$class=null){
		echo "<a href='javascript:history.go(-1);' ".(!is_null($class) ? "class='".$class."'" : null).">".$content."</a>";
	}

	/**
	 * alert function.
	 *
	 * @access public
	 * @param mixed $msg
	 * @return void
	 */
	public function alert($msg){
		return "alert('".$msg."');";
	}

	/**
	 * load function.
	 *
	 * @access public
	 * @param mixed $url
	 * @return void
	 */
	public function load($url){
		return "<script src=\"".$url."\"></script>";
	}


	/**
	 * json function.
	 *
	 * @access public
	 * @static
	 * @param mixed $object
	 * @param bool $encode (default: true)
	 * @return void
	 */
	public static function json($object,$encode=true){
		if($encode){
			return json_encode($object);
		}else{
			return json_decode($object);
		}
	}

	/**
	 * generateTags function.
	 *
	 * @access public
	 * @param mixed $model
	 * @return void
	 */
	public function generateTags($model){
		$mod = new Model($model);
		$data = $mod->__oget(null,array("tag",true),null);
		$last_key=key(array_slice($data, -1,1, TRUE));

		$i=0;
		while($i<count($data)){
			$output.=$data[$i]['tag'];
			if($i!=$last_key){
				$output.=", ";
			}
			$i++;
		}
		return $output;
	}


	/**
	 * head function.
	 *
	 * @access public
	 * @param array $config (default: array())
	 * @return void
	 */
	public function head(array $config = array()){
		$html = null;
		$html .= "<head>";
		if(isset($config['charset'])){
			$html .= $this->charset($config['charset']);
		}
		if(isset($config['title'])){
			$html .= $this->title($config['title']);
		}
		if(isset($config['favicon'])){
			$html .= $this->favicon(array("file" => $config['favicon']));
		}
		if(is_array($config['keywords'])){
			foreach($config['keywords'] as $keyword){
				$kwGen .= $keyword;
				if(end($config['keywords']) != $keyword){
					$kwGen .= ",";
				}
			}
			$html .= $this->keywords($kwGen);
		}
		if(isset($config['description'])){
			$html .= $this->description($config['description']);
		}
		if(is_array($config['meta'])){
			foreach($config['meta'] as $metaKey => $metaContent){
				$html .= $this->meta($metaKey, $metaContent);
			}
		}
		if(is_array($config['author'])){
			$html .= $this->author($config['author']);
		}
		if(isset($config['before'])){
			$html .= $config['before'];
		}
		if(isset($config['bower'])){
			$html .= $this->bower($config['bower']);
		}
		if(is_array($config['autoLoad'])){
			foreach($config['autoLoad'] as $folder){
				$fl = scandir($folder);
				foreach($fl as $f){
					$info = pathinfo($folder.$f);
					if(isset($info['extension'])){
						switch($info['extension']){
							case "js":
								$html .= $this->js(array("file" => $folder.$f));
								break;
							case "css":
								$html .= $this->css(array("file" => $folder.$f));
								break;
							default:
								break;
						}
					}
				}
			}
		}
		if(isset($config['end'])){
			$html .= $config['end'];
		}

		$html .= "</head>";
		return $html;
	}


	/**
	 * bower function.
	 *
	 * @access public
	 * @param mixed $folder
	 * @return void
	 */
	public function bower($folder){
		if(is_array($folder)){
			$fol = $folder[0];
			$bowerArr = $folder[1];
		}else{
			$fol = $folder;
		}

		$html = null;
		if(is_array($bowerArr)){
			foreach($bowerArr as $f){
				if(is_file($fol.$f."/bower.json")){
					$mainFiles = json_decode(file_get_contents($fol.$f."/bower.json"));

					if(is_array($mainFiles->main)){
						foreach($mainFiles->main as $file){
							$fileName = $fol.$f."/".$file;

							$info = pathinfo($fileName);
							switch($info['extension']){
								case "js":
									$html .= $this->js(array("file" => $fileName));
									break;
								case "css":
									$html .= $this->css(array("file" => $fileName));
									break;
							}
						}
					}else{
						$fileName = $fol.$f."/".$mainFiles->main;

						$info = pathinfo($fileName);
						switch($info['extension']){
							case "js":
								$html .= $this->js(array("file" => $fileName));
								break;
							case "css":
								$html .= $this->css(array("file" => $fileName));
								break;
						}
					}
				}
			}
		}else{
			$fl = scandir($fol);
			foreach($fl as $f){
				if(is_file($fol.$f."/bower.json")){
					$mainFiles = json_decode(file_get_contents($fol.$f."/bower.json"));

					if(is_array($mainFiles->main)){
						foreach($mainFiles->main as $file){
							$fileName = $fol.$f."/".$file;

							$info = pathinfo($fileName);
							switch($info['extension']){
								case "js":
									$html .= $this->js(array("file" => $fileName));
									break;
								case "css":
									$html .= $this->css(array("file" => $fileName));
									break;
							}
						}
					}else{
						$fileName = $fol.$f."/".$mainFiles->main;

						$info = pathinfo($fileName);
						switch($info['extension']){
							case "js":
								$html .= $this->js(array("file" => $fileName));
								break;
							case "css":
								$html .= $this->css(array("file" => $fileName));
								break;
						}
					}
				}
			}
		}
		return $html;
	}
}
?>
