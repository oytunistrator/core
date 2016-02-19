<?php
/**
 * Upload class.
 */
namespace Bluejacket\File;
class Upload
{
	/**
	 * file
	 *
	 * @var mixed
	 * @access public
	 */
	public $file;

	/**
	 * __construct function.
	 *
	 * @access public
	 * @param array $file (default: array())
	 * @return void
	 */
	public function __construct($file=array()){
		$this->file = $file;
	}

	/**
	 * upload function.
	 *
	 * @access public
	 * @param array $fileInput (default: array())
	 * @return void
	 */
	public function single($fileInput){
		if($this->check($fileInput,$this->file['maxSize'],$this->file['allowedTypes'])){
			$fileExt = explode(".", $fileInput["name"]);
			$fileName = $fileExt[0];
			$fileExt = end($fileExt);

			$newFileName = $this->fixName($fileName).".".$fileExt;
			if(file_exists($this->file['uploadFolder'].$newFileName)){
				$newFileName = $this->fixName($fileName)."_".rand(0,9999999).".".$fileExt;
			}
			$normalPath = $this->file['uploadFolder'].$newFileName;
			$movePath = $this->file['uploadFolder'].$newFileName;
			move_uploaded_file($fileInput["tmp_name"],$movePath);
			$fileExt = explode(".", $fileInput['name']);
			$fileExt = strtolower(end($fileExt));

			$fileInput["name"] = $fileInput['name'];
			$fileInput["path"] = $movePath;
			$fileInput["folder"] = $movePath;
			$fileInput["ext"] = $fileExt;
			return $fileInput;
		}
		return false;
	}


	/**
	 * multiUpload function.
	 *
	 * @access public
	 * @param mixed $fileInput
	 * @return void
	 */
	function multi($fileInput){
		foreach($fileInput as $object => $array){
			foreach($array as $id => $value){
				$f[$id][$object] = $value;
			}
		}

		$i=0;
		while($i<count($f)){
			if($this->check($f[$i],$this->file['maxSize'],$this->file['allowedTypes'])){
				$fileExt = explode(".", $f[$i]["name"]);
				$fileName = $fileExt[0];
				$fileExt = end($fileExt);

				$newFileName = $this->fixName($fileName)."_".rand(0,9999999).".".$fileExt;
				$movePath = $this->file['uploadFolder'].$newFileName;
				move_uploaded_file($f[$i]["tmp_name"],$movePath);
				$f[$i]['path'] = $movePath;
				$uploaded[]=$f[$i];
			}

			$i++;
		}
		return $uploaded;
	}

	/**
	 * check function.
	 *
	 * @access public
	 * @param mixed $fileInput
	 * @param mixed $fileSize
	 * @param array $fileType (default: array())
	 * @return void
	 */
	public function check($fileInput,$fileSize,$fileType=array()){
		$extention = false;
		$size = false;


		$fileSize = $fileSize * (1024 * 1024);
		$fileExt = explode(".", $fileInput['name']);
		$fileExt = strtolower(end($fileExt));

		if(is_array($fileType)){
			$allowedTypes = $fileType;
			if(in_array($fileExt, $allowedTypes)){
				$extention = true;
			}
		}

		if($fileInput['size'] <= $fileSize){
			$size = true;
		}


		if($extention && $size){
			return true;
		}
		return false;

	}

	/**
	 * fixName function.
	 *
	 * @access public
	 * @param mixed $text
	 * @return void
	 */
	public function fixName($text) {
		$text = trim($text);
		$search = array('Ç','ç','Ğ','ğ','ı','İ','Ö','ö','Ş','ş','Ü','ü',' ',')','(','#');
		$replace = array('c','c','g','g','i','i','o','o','s','s','u','u','_','_','_','_');
		$new_text = str_replace($search,$replace,$text);
		return $new_text;
	}
}