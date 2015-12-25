<?php
/**
 * Model class.
 */
namespace Bluejacket\Core;
abstract class Model extends CRUD
{
	/**
	 * __construct function.
	 *
	 * @access public
	 * @param Array $properties (default: array())
	 * @return void
	 */
	public function __construct(array $properties=array()){
    parent::__construct($properties);
	}

	/* insert scheme string dump to database */
	public function insertScheme(){

	}

	/* update scheme string dump to database */
	public function updateScheme(){

	}

	/* destroy scheme string dump to database */
	public function destroyScheme(){

	}

	/* search table with options and return */
	public function search($options = array(), $retr = array()){

	}
}
?>
