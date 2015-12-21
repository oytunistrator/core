<?php
namespace Bluejacket\Core;
class JSON
{
  public static function encode($data, $options = null){
    return json_encode($data, $options);
  }

  public static function decode($data, $options = null){
    return json_decode($data, $options);
  }
}
?>
