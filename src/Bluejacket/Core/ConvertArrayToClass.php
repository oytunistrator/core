<?php
namespace Bluejacket\Core;
/**
 * ConvertArrayToClass class
 */
class ConvertArrayToClass {
  function __construct($members = array()) {
    foreach ($members as $name => $value) {
      self::$name = $value;
    }
  }
  function __call($name, $args) {
    if (is_callable(self::$name)) {
      array_unshift($args, $this);
      return call_user_func_array(self::$name, $args);
    }
  }
}