<?php
namespace Bluejacket\Core;
/**
 * AbstractClass class
 */
abstract class AbstractClass {
  function __construct($members = array()) {
    foreach ($members as $name => $value) {
      $this->{$name} = $value;
    }
  }
}