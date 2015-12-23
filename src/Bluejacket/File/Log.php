<?php
namespace Bluejacket\File;
/**
 * Log Class
 */
class Log
{
  /**
   * file def.
   * @var mixed
   */
  public $file;

  /**
   * Log Construct
   * @param array $properties Properties
   */
  public function __construct($properties = array()){
    foreach ($properties as $k => $v) {
      $this->{$k} = $v;
    }
    if(isset($this->errors)){
      ini_set('error_log',$this->errors.'.log');
    }
  }

  /**
   * Log write with text
   * @param  mixed $text
   */
  public function write($text){
    if(isset($this->file)){
      error_log($text,3,$this->file.".log");
    }else{
      error_log($text,3,$_SERVER['SCRIPT_FILENAME'].".log");
    }
  }
}
?>
