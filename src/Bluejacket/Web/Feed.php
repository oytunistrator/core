<?php
/**
 * Feed class.
 */
namespace Bluejacket\Framework\Web; 
class Feed
{
	/**
	 * items
	 *
	 * (default value: array())
	 *
	 * @var array
	 * @access private
	 */
	private $items        = array();
	/**
	 * channels
	 *
	 * (default value: array())
	 *
	 * @var array
	 * @access private
	 */
	private $channels     = array();
	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct(){
		$count         = func_num_args();
		$parameters    = func_get_args();

		if( $count == 3 ){
			$this->setFeedTitle($parameters[0]);
			$this->setFeedLink($parameters[1]);
			$this->setFeedDesc($parameters[2]);
		}

		else if ($count == 1 and is_array( $parameters[0]))
				foreach($parameters[0] as $key => $value)
					if( $key == 'title' )
						$this->setFeedTitle($value);
					else if($key == 'link')
							$this->setFeedLink( $value );
						else if($key == 'description')
								$this->setFeedDesc( $value );
							else if($key == 'generator')
									$this->setFeedGenerator($value);
								else if($key == 'language')
										$this->setFeedLang( $value );
									else if($key == 'image')
											$this->setFeedImage($value[0], $value[1], $value[2]);
										else
											$this->setChannelElm($key, $value);
	}


	/**
	 * meta_array function.
	 *
	 * @access private
	 * @param mixed $array
	 * @return void
	 */
	private function meta_array($array){
		$output = '';

		foreach($array as $key => $value)
			if(is_array($value))
				$output .=  PHP_EOL . "<$key>" . $this->meta_array( $value ) . "</$key>" . PHP_EOL;
			else
				$output .= PHP_EOL . "<$key>$value</$key>" . PHP_EOL;

			return $output;
	}


	/**
	 * setChannelElm function.
	 *
	 * @access public
	 * @param mixed $tagName
	 * @param mixed $content
	 * @return void
	 */
	public function setChannelElm($tagName, $content){
		$this->channels[$tagName] = $content ;
	}


	/**
	 * setFeedTitle function.
	 *
	 * @access public
	 * @param mixed $title
	 * @return void
	 */
	public function setFeedTitle($title){
		$this->setChannelElm('title', $title);
	}


	/**
	 * setFeedLink function.
	 *
	 * @access public
	 * @param mixed $link
	 * @return void
	 */
	public function setFeedLink($link){
		$this->setChannelElm('link', $link);
	}


	/**
	 * setFeedDesc function.
	 *
	 * @access public
	 * @param mixed $desc
	 * @return void
	 */
	public function setFeedDesc($desc){
		$this->setChannelElm('description', $desc);
	}


	/**
	 * setFeedLang function.
	 *
	 * @access public
	 * @param string $lang (default: 'en_en')
	 * @return void
	 */
	public function setFeedLang($lang='en_en'){
		$this->setChannelElm('language', $lang);
	}


	/**
	 * setFeedImage function.
	 *
	 * @access public
	 * @param mixed $title
	 * @param mixed $imag
	 * @param mixed $link
	 * @param string $width (default: '')
	 * @param string $height (default: '')
	 * @return void
	 */
	public function setFeedImage($title, $imag, $link, $width = '', $height = ''){
		$this->setChannelElm('image', array(
				'title' => $title,
				'link'=> $link,
				'url' => $imag,
				'width' => $width,
				'height' => $height
			) );
	}


	/**
	 * setFeedGenerator function.
	 *
	 * @access private
	 * @param string $desc (default: 'RSS Feed Generator - http://www.example.com/rss')
	 * @return void
	 */
	private function setFeedGenerator($desc = 'RSS Feed Generator - http://www.example.com/rss'){
		$this->setChannelElm('generator', $desc);
	}

	/**
	 * genHead function.
	 *
	 * @access private
	 * @return void
	 */
	private function genHead() {
		echo '<?xml version="1.0" encoding="utf-8"?>' . PHP_EOL . '<rss version="2.0">' . PHP_EOL;
	}


	/**
	 * genChannel function.
	 *
	 * @access private
	 * @return void
	 */
	private function genChannel(){
		echo '<channel>' . PHP_EOL;
		echo $this->meta_array($this->channels);
	}


	/**
	 * addItem function.
	 *
	 * @access public
	 * @param mixed $item
	 * @return void
	 */
	public function addItem($item ){
		if( is_array($item))
			foreach($item as $itm)
				$this->addItem($itm);

			array_push($this->items, $item);
	}

	/**
	 * genBody function.
	 *
	 * @access private
	 * @return void
	 */
	private function genBody(){
		foreach($this->items as $item)
			$item->parseItem();
	}

	/**
	 * genBottom function.
	 *
	 * @access private
	 * @return void
	 */
	private function genBottom(){
		echo '</channel>' . PHP_EOL . '</rss>';
	}


	/**
	 * genFeed function.
	 *
	 * @access public
	 * @return void
	 */
	public function genFeed(){
		header( "Content-type: text/xml" );

		$this->genHead();
		$this->genChannel();
		$this->genBody();
		$this->genBottom();
	}
}

?>
