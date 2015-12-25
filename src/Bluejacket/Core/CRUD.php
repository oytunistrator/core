<?php
namespace Bluejacket\Database;
/**
 * New CRUD Class
 */
class CRUD
{
    function __construct($objects = array()){
      foreach($objects as $key => $value){
        $this->{$key} = $value;
      }
    }

    public function setup(){
      if(DB_DRIVER == preg_match('/(sql)/i')){
        use Bluejacket\Connectors\SQL;
        $this->db = new SQL();
      }
    }

    /* insert data */
    public function create($data = array()){

    }

    /* read where */
    public function read($where = array()){

    }

    /* update where to data */
    public function update($where = array(), $data = array()){

    }

    /* destroy where */
    public function destroy($where = array()){

    }


    /* fetch single data with return contents */
    public function fetch($data = array(), $retr = array()){

    }

  	/* fetch multiple data with return contents */
    public function fetchAll($data = array(), $retr = array()){

    }
}
?>
