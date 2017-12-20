<?php
/**
 * This php file will destroy the session and redirect to loginAction.php
 * There the user can either login or register
 */
session_start();
session_regenerate_id();
if (isset($_SESSION['userid'])) {

    $_SESSION = [];
    session_destroy();
    header("Location: loginAction.php");
    exit;
} else
    echo"<h3>Cannot logout</h3>";
?>

