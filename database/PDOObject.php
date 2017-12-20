<?php

/**
 * This class creates a PDOObject which has a pdoObject as a field to do tasks
 * necessary for the setup.
 */
class PDOObject {

    private $pdoObject;

    /**
     * This function takes care of creating the PDO object
     * @param type $url
     * @param type $username
     * @param type $password
     */
    function __construct($url = "mysql:dbname=CS1433545;host=korra.dawsoncollege.qc.ca", $username = "CS1433545", $password = "ermanent") {

        $this->pdoObject = new PDO($url, $username, $password);
    }

    /**
     * This function takes care of creating the cities and users tables that 
     * are necessary for application to function.
     */
    function createTables() {
        
        $tables= fopen('tables.sql', 'r');

        while (!feof($tables)) {
            $table = fgetcsv($tables, 0, ';');
            
        $this -> pdoObject -> exec($table[0]);
    }
 
    }

    /**
     * This method returns a PDO object which can then be used to perform sql operations.
     * Not necessary, used to shrink the amount of code.
     * @return type PDO object type
     */
    function getPDO() {
        return ($this->pdoObject);
    }

}

?>