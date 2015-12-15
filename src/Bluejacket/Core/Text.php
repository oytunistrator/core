<?php
/**
 * Text class.
 */
namespace Framework\Core;
class Text
{
	/**
	 * convert function.
	 *
	 * @access public
	 * @param mixed $text
	 * @param mixed $lang_input
	 * @param mixed $lang_output
	 * @param bool $strstat (default: false)
	 * @return void
	 */
	public function convert($text,$lang_input,$lang_output, $strstat=false) {
		switch($lang_input){
			case 'tr':
				$search = array('Ç','ç','Ğ','ğ','ı','İ','Ö','ö','Ş','ş','Ü','ü',' ','\'');
				break;
			case 'en':
				$search = array('c','c','g','g','i','i','o','o','s','s','u','u','_','_');
				break;
		}

		switch($lang_output){
			case 'tr':
				$replace = array('Ç','ç','Ğ','ğ','ı','İ','Ö','ö','Ş','ş','Ü','ü',' ','\'');
				break;
			case 'en':
				$replace = array('c','c','g','g','i','i','o','o','s','s','u','u','_','_');
				break;
		}
		$text = trim($text);
		$new_text = str_replace($search,$replace,$text);

		if($strstat){
			switch($strstat){
				case 'lower':
					$new_text = strtolower($new_text);
					break;
				case 'upper':
					$new_text = strtoupper($new_text);
					break;
			}
		}
		return $new_text;
	}

	/**
	 * tushish str lower
	 * @param  string $metin
	 * @return mixed
	 */
	public function strtolowertr($metin){
		$metin = str_replace('I','ı',$metin);
		$metin = str_replace('İ','i',$metin);
		$metin = str_replace('Ö','ö',$metin);
		$metin = str_replace('Ğ','ğ',$metin);
		$metin = str_replace('Ç','ç',$metin);
		$metin = str_replace('Ü','ü',$metin);
	    return mb_convert_case($metin, MB_CASE_LOWER, "UTF-8");
	}

	/**
	 * turkish strupper
	 * @param  string $metin
	 * @return mixed
	 */
	public function strtouppertr($metin){
		$metin = str_replace('i','İ',$metin);
		$metin = str_replace('ı','I',$metin);
		$metin = str_replace('ö','Ö',$metin);
		$metin = str_replace('ç','Ç',$metin);
		$metin = str_replace('ğ','Ğ',$metin);
		$metin = str_replace('ü','Ü',$metin);
	    return mb_convert_case($metin, MB_CASE_UPPER, "UTF-8");
	}

	/**
	 * ucwordstr turkish conversion
	 * @param  string $metin
	 * @return mixed
	 */
	public function ucwordstr($metin) {
	    return ltrim(mb_convert_case(str_replace(array(' I',' ı', ' İ', ' i', ' ö'),array(' I',' I',' İ',' İ',' Ö'),' '.$metin), MB_CASE_TITLE, "UTF-8"));
	}

	/**
	 * ucfirsttr turkish conversion
	 * @param  string $metin
	 * @return mixed
	 */
	public function ucfirsttr($metin) {
	    $metin = in_array(crc32($metin[0]),array(1309403428, -797999993, 957143474)) ? array(strtouppertr(substr($metin,0,2)),substr($metin,2)) : array(strtouppertr($metin[0]),substr($metin,1));
	return $metin[0].$metin[1];
	}

	/**
	 * substr conversion
	 * @param  string $text
	 * @param  integer $start
	 * @param  integer $count
	 * @return mixed
	 */
	public static function crop($text, $start, $count){
		return substr($text, $start, $count);
	}

	/**
	 * strip tags only
	 * @param  string $str
	 * @param  string $tags
	 * @param  string $stripContent
	 * @return string
	 */
	public static function strip_only($str, $tags, $stripContent = false) {
	    $content = '';
	    if(!is_array($tags)) {
	        $tags = (strpos($str, '>') !== false
	                 ? explode('>', str_replace('<', '', $tags))
	                 : array($tags));
	        if(end($tags) == '') array_pop($tags);
	    }
	    foreach($tags as $tag) {
	        if ($stripContent)
	             $content = '(.+</'.$tag.'[^>]*>|)';
	         $str = preg_replace('#</?'.$tag.'[^>]*>'.$content.'#is', '', $str);
	    }
    return $str;
}
}
?>
