<?php

require('PDOObject.php');

createDatabase();
/**
 * This function takes care of creating the tables necessary for the application.
 * Then sends the PDO object to the openFile function.
 */
function createDatabase() {
    try {
        $pdoObject = new PDOObject();
        $pdoObject->createTables();
        $pdo = $pdoObject->getPDO();

        openFile($pdo);
    } catch (PDOException $e) {
        $e->getMessage();
        exit;
    } finally {
        unset($pdo);
    }
}

/**
 * This function gets the data from the cities file and adds it to the database.
 * @param type $pdo PDO object
 */
function openFile($pdo) {
    $cities = fopen('cities.txt', 'r');

    $ctr=1;
    while (!feof($cities)) {
        $city = fgetcsv($cities, 0, ';');

        insertDatabase($city[0], $city[1], $pdo, $ctr);
        $ctr++;
    }
}

/**
 * This function will add the weights and city/country to the db.
 * @param type $weights
 * @param type $cityAndCountry
 * @param type $pdo
 */
function insertDatabase($weights, $cityAndCountry, $pdo, $ctr) {
    try {

        $query = 'INSERT INTO cities(weights, cityAndCountry) VALUES (?,?);';
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(1, $weights);
        $stmt->bindParam(2, $cityAndCountry);

        
        if ($stmt->execute())
            echo "($ctr/93827) Added to database\n";
    } catch (PDOException $e) {
        echo $e->getMessage();
        exit;
    } finally {
        unset($pdo);
    }
}

?>