<?php
namespace Framework\Web;
/**
 * JSObject class
 */
class JSObject {
  function __construct($members = array()) {
    foreach ($members as $name => $value) {
      $this->$name = $value;
    }
  }
  function __call($name, $args) {
    if (is_callable($this->$name)) {
      array_unshift($args, $this);
      return call_user_func_array($this->$name, $args);
    }
  }
}
?>
