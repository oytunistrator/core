<?php
namespace Bluejacket\Formatter;
/**
 * XML generator class
 */
class XML
{
  function __construct(){
    $this->xml = new \SimpleXMLElement('<?xml version="1.0"?><data></data>');
  }

  /**
   * array convert to xml
   * @param array  $data
   * @param mixed  $xml_data
   * @return mixed
   */
  function array_to_xml( $data, &$xml_data ) {
      foreach( $data as $key => $value ) {
          $key = str_replace("$","",$key);
          $key = str_replace("_","",$key);
          if( is_array($value) ) {
              if( is_numeric($key) ){
                  $key = 'item'.$key; //dealing with <0/>..<n/> issues
              }
              $subnode = $this->xml->addChild($key);
              $this->array_to_xml($value, $subnode);
          } else {
              $this->xml->addChild("$key",htmlspecialchars("$value"));
          }
       }
       return $this->xml->asXML();
  }
}
?>
