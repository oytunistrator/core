<?php
namespace Bluejacket\File;
/**
 * Image class.
 */
class Image
{
	/**
	 * watermark function.
	 *
	 * @access public
	 * @param array $option (default: array())
	 * @param array $option[wathermark] (default: null)
	 * @param array $option[image] (default: null)
	 * @param array $option[sag] (default: null)
	 * @param array $option[sol] (default: null)
	 * @return void
	 */
	function watermark(array $option = array()){
		$watermark = imagecreatefrompng($option['wathermark']);
		$image = imagecreatefromjpeg($option['image']);

		$sx = imagesx($watermark);
		$sy = imagesy($watermark);

		imagecopy($image, $watermark, imagesx($image) - $sx - $option['right'],
		                         imagesy($option['image']) - $sy - $option['alt'],
		                         0, 0, imagesx($watermark), imagesy($watermark));


		header('Content-type: image/png');
		imagepng($image);
		imagedestroy($image);
	}




	/**
	 * imageHalfSize function.
	 *
	 * @access public
	 * @param array $options (default: array())
	 * @return void
	 */
	function imageHalfSize(array $options = array()){
		$filename = $options['file'];
		$percent = $options['precent'];
		$type = $options['type'];


		switch($type){
			case "jpeg":
				header('Content-Type: image/jpeg');
				list($width, $height) = getimagesize($filename);
				$new_width = $width * $percent;
				$new_height = $height * $percent;

				$image_p = imagecreatetruecolor($new_width, $new_height);
				$image = imagecreatefromjpeg($filename);
				imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

				imagejpeg($image_p, null, 100);
				break;
			case "jpg":
				header('Content-Type: image/jpeg');
				list($width, $height) = getimagesize($filename);
				$new_width = $width * $percent;
				$new_height = $height * $percent;

				$image_p = imagecreatetruecolor($new_width, $new_height);
				$image = imagecreatefromjpeg($filename);
				imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

				imagejpeg($image_p, null, 100);
				break;
			case "png":
				header('Content-Type: image/png');
				list($width, $height) = getimagesize($filename);
				$new_width = $width * $percent;
				$new_height = $height * $percent;

				$image_p = imagecreatetruecolor($new_width, $new_height);
				$image = imagecreatefrompng($filename);
				imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

				imagepng($image_p, null, 100);
				break;
		}
	}

	function resize($src, $dst, $width, $height, $crop=0){
	  if(!list($w, $h) = getimagesize($src)) return "Unsupported picture type!";

	  $type = strtolower(substr(strrchr($src,"."),1));
	  if($type == 'jpeg') $type = 'jpg';
	  switch($type){
	    case 'bmp': $img = imagecreatefromwbmp($src); break;
	    case 'gif': $img = imagecreatefromgif($src); break;
	    case 'jpg': $img = imagecreatefromjpeg($src); break;
	    case 'png': $img = imagecreatefrompng($src); break;
	    default : return "Unsupported picture type!";
	  }

	  // resize
	  if($crop){
	    if($w < $width or $h < $height) return "Picture is too small!";
	    $ratio = max($width/$w, $height/$h);
	    $h = $height / $ratio;
	    $x = ($w - $width / $ratio) / 2;
	    $w = $width / $ratio;
	  }
	  else{
	    if($w < $width and $h < $height) return "Picture is too small!";
	    $ratio = min($width/$w, $height/$h);
	    $width = $w * $ratio;
	    $height = $h * $ratio;
	    $x = 0;
	  }

	  $new = imagecreatetruecolor($width, $height);

	  // preserve transparency
	  if($type == "gif" or $type == "png"){
	    imagecolortransparent($new, imagecolorallocatealpha($new, 0, 0, 0, 127));
	    imagealphablending($new, false);
	    imagesavealpha($new, true);
	  }

	  imagecopyresampled($new, $img, 0, 0, $x, 0, $width, $height, $w, $h);

	  switch($type){
	    case 'bmp': imagewbmp($new, $dst); break;
	    case 'gif': imagegif($new, $dst); break;
	    case 'jpg': imagejpeg($new, $dst); break;
	    case 'png': imagepng($new, $dst); break;
	  }
	  return true;
	}
}