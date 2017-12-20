<?php

require('database/PDOObject.php');
session_start();
session_regenerate_id();


/**
 * Check if the user is logged in. If that is the case then send them to the index
 * page. Otherwise, display the form so they can log in.
 */
if (isset($_SESSION['userid'])) {
    header("Location: index.php");
    exit;
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {

        displayForm();
    }
}

/**
 * When the form is submitted, compare the tokens to see if they are equal. If 
 * that's the case then validate if they input a user and password. If user and
 * password are inputed then let the user log in otherwise and error msg will
 * be prompted. Also checks if confirmation password is equal to password.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['token']))
        echo "<h3> No token from post </h3>";
    else if (!isset($_SESSION['token']))
        echo "<h3> No token from session </h3>";
    else if ($_POST['token'] != $_SESSION['token'])
        echo "<h3> Tokens are not equal </h3>";
    else {
        if (!isset($_POST['userid']) || empty($_POST['userid'])) {
            displayForm();
            echo "<h3> Enter a userid </h3>";
        } else {
            if (!isset($_POST['password']) || empty($_POST['password'])) {
                displayForm();
                echo "<h3> Enter a password </h3>";
            } else {
                login();
            }
        }
    }
}
/**
 * This function generates a token and displays the form to the user.
 */
function displayForm() {
    $token = hash("sha512", mt_rand(0, mt_getrandmax()));
    $_SESSION['token'] = $token;
    echo <<<REGISTRATION
        <!DOCTYPE html>
        <html>
            <head>
            <link rel="stylesheet" href="design/autostyle.css">
            <title> Login </title>
            </head>
            <body>
                    <h1> Login </h1>
                    <form action="" method="POST">
                        <div id="register">
                            <label> Username: </label> <br>
                            <input type = "text" name="userid" class="inputStyle">
                            <br>
                            <label> Password: </label> <br>
                            <input type = "password" name = "password" class="inputStyle">
                            <input type= "hidden" name ="token" value =$token>
                        </div>
                        <div id="centerBtn">
                            <input type= "submit" value="Submit" id="button2">
                        </div>
                    </form>
                    <div id="centerReg">
                        <a id="indexReg" href="registrationAction.php"> Registration </a>
                    </div>
            </body>
        </html>
REGISTRATION;
}
/**
 * This function will select data specific to a userid from the db. If the user
 * exists then it will add them to the db otherwise it will inc the attemptCounter
 * if user exists but wrong password. If the user does not exist 
 * then it will display an error msg.
 */
function login() {
    $user = $_POST['userid'];
    $password = $_POST['password'];
    $query = "Select userid, hashedPassword, attemptCounter from users where userid = ?;";
    try
    {
        $pdoObject = new PDOObject();
        $pdo = $pdoObject->getPDO();
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(1, $user);

        if ($stmt->execute()) {
            $row = $stmt->fetch();
            if ($row && $row['attemptCounter'] < 5) {
                if (password_verify($password, $row['hashedPassword'])) 
                    displayIndex($pdo,$user);
                else 
                    updateCounter($row, $pdo, $user);
            } else 
                updateCounter($row, $pdo, $user);
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
 * This function will display an error msg if the counter is 5, else it will
 * display an error msg if the userid does not exist in the db, else it will
 * increment the counter and add it to the db; if the user exists but invalid pwd.
 * @param type $row
 * @param type $pdo
 * @param type $user
 */
function updateCounter($row, $pdo, $user) {

    if ($row['attemptCounter'] == 5) {
        displayForm();
        echo "<h3>Account banned</h3>";
    } else if (!$row) {
        displayForm();
        echo "<h3>Invalid Information</h3>";
    } else {
        displayForm();
        echo "<h3>Invalid information </h3>";
        $value = ++$row['attemptCounter'];
        $query = "UPDATE users set attemptCounter=? where userid = ?;";

        $stmt = $pdo->prepare($query);
        $stmt->bindValue(1, $value);
        $stmt->bindParam(2, $user);

        $stmt->execute();
    }
}

/**
 * This function will reset the user attempt counter to 0 and redirect it to the 
 * index.php page.
 * @param type $pdo
 * @param type $user
 */
function displayIndex($pdo,$user)
{
    $query = "UPDATE users set attemptCounter=0 where userid = ?;";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(1, $user);


    $stmt->execute();

    $_SESSION['userid'] = $user;

    header("Location: index.php");
    exit;
}

?>