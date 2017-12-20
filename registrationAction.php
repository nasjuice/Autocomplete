
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
 * When the form is submitted, compare the tokens to see if they are equal. 
 * It will check if userid and password are valid , if that is the case then
 * it will go in the validate method otherwise it shows an error msg.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['token']))
        echo "<h3> No token from post </h3>";
    else if (!isset($_SESSION['token']))
        echo "<h3> No token from session </h3>";
    else if ($_POST['token'] != $_SESSION['token'])
        echo "<h3> Tokens are not equal </h3>";

    else {

        //have to trim otherwise you can enter whitespaces
        if (!isset($_POST['userid']) || empty(trim($_POST['userid']))) {
            displayForm();
            echo '<div id="centerValidate"><h3 class="validateStyle"> Enter a userid </h3></div>';
        } else if (!isset($_POST['password']) || empty(trim($_POST['password']))) {
            displayForm();
            echo '<div id="centerValidate"><h3 class="validateStyle"> Enter a password </h3></div>';
        } else if (strlen($_POST['password']) < 6) {
            displayForm();
            echo '<div id="centerValidate"><h3 class="validateStyle">Password must be 6 characters</h3></div>';
        } else if ($_POST['password'] != $_POST['confirmedPassword'])
        {
            displayForm();
            echo '<div id="centerValidate"><h3 class="validateStyle"> Passwords do not match </h3></div>';
        }
        
        else {
            validateData();
        }
    }
}

/**
 * This functions takes care of generating the token and displaying the form.
 */
function displayForm() {


    $token = hash("sha512", mt_rand(0, mt_getrandmax()));

    $_SESSION['token'] = $token;


    echo <<<REGISTRATION
        <!DOCTYPE html>
        <html>
            <head>
            <link rel="stylesheet" href="design/autostyle.css">
            <title> Registration </title>
            </head>
            <body>
                    <h1> Registration </h1>
                    <form action="" method="POST">
                            <div id="register">
                                <label> Username: </label><br>
                                <input type = "text" name="userid" class="inputStyle">
                                <br>
                                <label> Password: </label><br>
                                <input type = "password" name = "password" class="inputStyle">
                                <br>
                                <label> Confirmation Password: </label><br>
                                <input type= "password" name ="confirmedPassword" class="inputStyle">
                                <input type= "hidden" name ="token" value =$token>
                            </div>
                            <div id="centerBtn">
                                <input type= "submit" value="Submit" id="button">
                            </div>
                    </form>
                    <div id="centerLogin">
                        <a id="loginStyle" href="loginAction.php"> Login </a>
                    </div>
            </body>
        </html>
REGISTRATION;
}

/**
 * If a user exists then it will display a generic error msg otherwise 
 * it will add them to the db.
 */
function validateData() {
    try {
        $user = $_POST['userid'];
        $pdoObject = new PDOObject();
        $pdo = $pdoObject->getPDO();
        $stmt = checkUserExists($user, $pdo);
        if ($stmt->execute()) {
            $row = $stmt->fetch();
            if ($row) {
                displayForm();
                echo '<div id="centerValidate"><h3 class="validateStyle"> Bad information </h3></div>';
            } else 
                insertIntoDatabase($user, $pdo);
        }
    } catch (PDOException $e) {
        $e->getMessage();
        exit;
    } finally {
        unset($pdo);
    }
}
/**
 * This function checks if a user exists. If they exist then they cant be added
 * to the database.
 * @param type $user
 * @param type $pdo
 * @return type $stmt
 */
function checkUserExists($user, $pdo) {
    $query = "SELECT userid from users where userid = ?";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(1, $user);

    return $stmt;
}

/**
 * If the user does not exist then add them to the database. If they were
 * successfully added then redirect them to the index.php page.
 * @param type $user
 * @param type $pdo
 */
function insertIntoDatabase($user, $pdo) {

    $password = $_POST['password'];
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $query = "INSERT into users(userid,hashedPassword) values(?,?);";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(1, $user);

    $stmt->bindParam(2, $hash);


    if ($stmt->execute()) {
        $_SESSION['userid'] = $user;
        header("Location: index.php");
        exit;
    }
}
?>