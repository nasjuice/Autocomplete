<?php
/**
 *  This class takes care of creating Suggestions object that will be used
 *  by javascript in displaying the them in the option tags. 
 * 
 */
class Suggestions implements JsonSerializable{
    
    private $suggestions;
    
    function __construct() {
        
    }
    
  
    /**
     * This function adds a city to an array of suggestions that will be parsed
     * in the jscript.
     * @param type $citySuggestion
     */
    function add($citySuggestion)
    {
        
        $this->suggestions[] = $citySuggestion; 
    }
    
    /**
     * This function takes care of converting the array of Suggestions objects
     * into Strings.
     * @return type
     */
    function jsonSerialize(){
        return $this-> suggestions;
    }
    
    function getArray()
    {
        return $this -> suggestions;
    }
    
}

?>