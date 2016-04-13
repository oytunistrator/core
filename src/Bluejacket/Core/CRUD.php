<?php
namespace Bluejacket\Core;
/**
 * New CRUD Class
 */
use Bluejacket\Connectors\SQL;
use Bluejacket\Core\Core;

class CRUD {
	function __construct($objects = array()) {
		foreach ($objects as $key => $value) {
			$this->{ $key} = $value;
		}
	}

	public function setup() {
		$this->db = new SQL();

		if (!isset($this->db) && DEBUG == true) {
			Core::showErrorMsg("Database Error!", 1);
		}
	}

	/* insert data */
	public function create($data = array()) {
		return $this->db
		            ->insert($this->table)
			->set($data)
			->run();
	}

	/* read where */
	public function read($where = array(), $select = array()) {
		return $this->db
		            ->select($select)
		            ->from($this->table)
			->where($where)
			->query();
	}

	/* update where to data */
	public function update($where = array(), $data = array()) {
		return $this->db
		            ->update()
		            ->from($this->table)
			->set($data)
			->where($where)
			->query();
	}

	/* destroy where */
	public function destroy($where = array()) {
		return $this->db
		            ->delete()
		            ->from($this->table)
			->where($where)
			->run();
	}

	/* fetch single data with return contents */
	public function fetch($data = array()) {
		return $this->db->fetch($data);
	}

	/* fetch multiple data with return contents */
	public function fetchAll($data = array()) {
		return $this->db->fetchAll($data);
	}

	/* insert scheme string dump to database */
	public function insertScheme() {
		$this->scheme['id']         = "INT(55) UNSIGNED AUTO_INCREMENT";
		$this->scheme['created_at'] = "DATETIME DEFAULT NULL";
		$this->scheme['updated_at'] = "TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";

		$insert = $this->db
		               ->create("TABLE", $this->table)
			->createFor($this->scheme)
			->run();
		if ($insert) {
			return $this->db
			            ->alter("TABLE", $this->table)
				->addPrimary("id")
				->run();
		}
		return false;
	}

	/* update scheme string dump to database */
	public function updateScheme() {
		return $this->db
		            ->alter("TABLE", $this->table)
			->alterFor($this->scheme)
			->run();
	}

	/* destroy scheme string dump to database */
	public function destroyScheme() {
		return $this->db
		            ->drop("TABLE", $this->table);
	}
}