<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    class xml {

    var $xmlResult;
    
    function __construct($rootNode){
        $rootNode = $rootNode[0];
        $this->xmlResult = new SimpleXMLElement("<$rootNode></$rootNode>");
    }
    
    private function iteratechildren($object,$xml){
        foreach ($object as $name=>$value) {
            if (is_string($value) || is_numeric($value)) {
                $xml->$name=$value;
            } else {
                $xml->$name=null;
                $this->iteratechildren($value,$xml->$name);
            }
        }
    }
    
    function toXml($object) {
        $this->iteratechildren($object,$this->xmlResult);
        return $this->xmlResult->asXML();
    }
}
?>