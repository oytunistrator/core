<?php
namespace Bluejacket\Connectors;
/**
 * MDB is MongoDB class
 */
class MDB
{
  /**
   * mongo class connection
   * @var object
   */
  public $mongo;
  /**
   * db is connection db
   * @var mixed
   */
  public $db;
  /**
   * collection name
   * @var mixed
   */
  public $collectionName;
  /**
   * output array
   * @var array
   */
  public $_array;
  /**
   * where outputs
   * @var array
   */
  public $_where;
  /**
   * config outputs
   * @var array
   */
  public $_config;
  /**
   * result outputs
   * @var mixed
   */
  public $result;

  function __construct($result=null){
    $this->result = $result;
  }

  /**
   * init mongodb class
   * @param  array  $config
   * @return mixed
   */
  function init($config = array()){
    $this->error = new \Framework\Core\Error();
    if(is_array($config) && count($config) > 0){
      $this->_config = $config;
      foreach ($config as $k => $v) {
        $this->{$k} = $v;
      }
    }

    $this->_connect();
    if(isset($this->database)){
      $this->_setDb($this->database);
    }
    if(isset($this->collectionName)){
      $this->_setCollection($this->collectionName);
    }
  }

  /**
   * connect mongodb
   * @return mixed
   */
  function _connect(){
    if(isset($this->username) && isset($this->password)){
      $user = $this->username.":".$this->password."@";
    }else{
      $user = "";
    }
    try{
      if(isset($this->server) && isset($this->port)){
        @$this->mongo = new \Mongo('mongodb://'.$user.$this->server.':'.$this->port);
      }else{
        @$this->mongo = new \Mongo('mongodb://localhost:27017');
      }
    }catch(\MongoConnectionException $e){
      if(DEBUG){
        $this->error->show("Bağlantı kurulamadı:".$e->getMessage(),1);
        return false;
      }
    }
    return true;
  }

  /**
   * database setup
   * @param string $dbName
   */
  function _setDb($dbName = null){
    if(!is_null($dbName)){
      $this->db = $this->mongo->selectDB($dbName);
      return true;
    }
    return false;
  }

  /**
   * set collection
   * @param string $collectionName
   */
  function _setCollection($collectionName = null){
    if(!is_null($collectionName)){
      $this->collection = new \MongoCollection($this->db, $collectionName);
      return true;
    }
    return false;
  }

  /**
   * find from array and results to array or false
   * @param  array  $array
   * @return mixed
   */
  function find($array=array()){
    $this->init($this->_config);
    $this->_array = $array;
    if(is_array($array)){
      return new self($this->collection->find($array));
    }
    return false;
  }

  /**
   * insert from array
   * @param  array  $array
   * @return mixed
   */
  function insert($array=array()){
    $this->init($this->_config);
    $this->_array = $array;
    if(is_array($array) && count($array) > 0){
      return new self($this->collection->insert($array));
    }
    return false;
  }

  /**
   * update array from where array
   * @param  array  $where
   * @param  array  $array
   * @return mixed
   */
  function update($where=array(),$array=array()){
    $this->init($this->_config);
    $this->_array = $array;
    $this->_where = $where;
    if(is_array($where) && count($where) > 0 && is_array($array) && count($array) > 0){
      return new self($this->collection->update($where,$array));
    }
    return false;
  }

  /**
   * delete from array
   * @param  array  $where
   * @return mixed
   */
  function delete($array=array()){
    $this->init($this->_config);
    $this->_array = $array;
    if(is_array($array) && count($array) > 0){
      return new self($this->collection->remove($array));
    }
    return false;
  }

  /**
   * convert result to array
   * @return mixed
   */
  function toArray(){
      return iterator_to_array($this->result);
  }

  /**
   * convert result to json
   * @return mixed
   */
  function toJson(){
    return json_encode(iterator_to_array($this->result));
  }
}
?>
