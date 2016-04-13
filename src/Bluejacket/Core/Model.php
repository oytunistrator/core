<?php
/**
 * Model class.
 */
namespace Bluejacket\Core;
abstract class Model extends CRUD {
	/**
	 * __construct function.
	 *
	 * @access public
	 * @param Array $properties (default: array())
	 * @return void
	 */
	public function __construct(array $properties = array()) {
		parent::__construct($properties);
	}
}