<?php

require('database/PDOObject.php');

session_start();
session_regenerate_id();
//checks if the user is logged in
if (isset($_SESSION['userid'])) {
    //executed when the page is loaded
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        echo
        <<<INDEX
        <!DOCTYPE html>
<html>
    <head>
    <title> Index </title>
    <link rel="stylesheet" href="design/autostyle.css">
            <script
  src="https://code.jquery.com/jquery-3.1.1.min.js"
  integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8="
  crossorigin="anonymous"></script>
    <script src= "loading.js"></script>
    </head>
    <body>
            <h1> Search Any City </h1>
            <form action="" method="GET">
                    <label> Keyword: </label> <br>
                    <input id="keywordInput" autocomplete="off" type = "text" name="keywordInput" list="suggestions">
                    <datalist id='suggestions'>
                    <option class="options" value =""></option>
                    <option class="options" value =""></option>
                    <option class="options" value =""></option>
                    <option class="options" value =""></option>
                    <option class="options" value =""></option>
                    </datalist>
                    <br><br>
                    <input type= "submit" value="Submit" name="submit" id="button">
            </form>
            <br>
            <a id="loginStyle" href="loginAction.php"> Login </a><br><br>
            <a id= "indexReg" href="registrationAction.php"> Registration </a><br><br>
            <a id="loginStyle" href="logout.php"> Logout </a>
    </body>
 </html>
INDEX;


        //create a PDO object
        $pdoObject = new PDOObject();
        $pdo = $pdoObject->getPDO();
       
           

        //if the user clicks on submit and there is input in the keyword textbox
        //then handle the input
        
        if (isset($_GET['submit']) && isset($_GET['keywordInput']) && !empty($_GET['keywordInput']))
            handleUserInput($pdo);
    }
} 
//when the user is not logged in redirect them
else {
    echo "<!DOCTYPE html>";
    echo '<html><head><title> Index </title><link rel="stylesheet" href="design/autostyle.css"></head><body>';
    echo "<h1> Login or Register</h1><br>";
    echo '<div id="centerIndex"><a id="loginStyle" href=loginAction.php> Login </a></div><br>';
    echo '<div id="centerIndex"><a id="indexReg" href=registrationAction.php> Registration </a></div></body></html>';
}


/**
 * This function takes care of checking if a keyword already exists. If thats
 * the case then it updates the keyword's timestamp otherwise it adds it into
 * the database if its a valid city/country combination.
 * @param type $pdo
 */
function handleUserInput($pdo) {
    $userInput = $_GET['keywordInput'];
    $user = $_SESSION['userid'];
try{
    $stmt = checkIfKeywordExists($userInput, $user, $pdo);

    if ($stmt->execute()) {
        $row = $stmt->fetch();
        if ($row)
            updateSuggestion($userInput, $user, $pdo);
        else
        {
            if(checkIsAValidCountry($userInput,$pdo))
                addIntoDatabase($userInput, $user, $pdo);
            else
                echo "<h3> Not a valid city </h3>";
        }
    }
}
catch (PDOException $e) {
    $e->getMessage();
    exit;
    } finally {
        unset($pdo);
    }
}

/**
 * This function checks if the user inputted a valid city/country combination.
 * If its not the case then it won't be added to the database.
 * @param type $userInput
 * @param type $pdo
 * @return type
 */
function checkIsAValidCountry($userInput,$pdo)
{
    try{
    $query = "SELECT cityAndCountry from cities where cityAndCountry =?";

    $stmt = $pdo->prepare($query);
    
    $trimmedInput = trim($userInput);

    $stmt->bindParam(1, $trimmedInput);
    
     if($stmt -> execute())
     {
         $row = $stmt -> fetch();
     }
     
     return $row;
        
        
    }
    catch (PDOException $e) {
    $e->getMessage();
    exit;
    } finally {
        unset($pdo);
    }
}

/**
 * This function checks if a keyword is already in the database and returns
 * a $stmt object that will be used in handleUserInput.
 * @param type $userInput
 * @param type $user
 * @param type $pdo
 * @return type
 */
function checkIfKeywordExists($userInput, $user, $pdo) {
    try{
    $query = "SELECT suggestion from userHistory where userid = ? AND suggestion =?";

    $stmt = $pdo->prepare($query);

    $stmt->bindParam(1, $user);
    $stmt->bindParam(2, $userInput);

    return $stmt;
    }
    catch (PDOException $e) {
    $e->getMessage();
    exit;
    } finally {
        unset($pdo);
    }
    
}

/**
 * This function takes care of updating the suggestion's timestamp.
 * @param type $userInput
 * @param type $user
 * @param type $pdo
 */
function updateSuggestion($userInput, $user, $pdo) {
try{
    $query = "UPDATE userHistory set timeAdded=CURRENT_TIMESTAMP where userid = ? AND suggestion = ?";

    $stmt = $pdo->prepare($query);

    $stmt->bindParam(1, $user);
    $stmt->bindParam(2, $userInput);
    
    $stmt -> execute();
}
catch (PDOException $e) {
    $e->getMessage();
    exit;
    } finally {
        unset($pdo);
    }
}
/**
 * This function will add a suggestion to the database in the userHistory table.
 * @param type $userInput
 * @param type $user
 * @param type $pdo
 */
function addIntoDatabase($userInput, $user, $pdo) {
    try{
    $query = "Insert into userHistory(userid,suggestion) values (?,?)";
    $stmt = $pdo->prepare($query);
    
    $stmt->bindParam(1, $user);
    $stmt->bindParam(2, $userInput);

    $stmt -> execute();
    
    }
    catch (PDOException $e) {
    $e->getMessage();
    exit;
    } finally {
        unset($pdo);
    }

}

?>