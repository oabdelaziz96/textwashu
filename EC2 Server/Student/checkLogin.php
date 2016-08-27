<?php

session_start();

if (isset($_SESSION['studNumber'])) { //user logged in
    header('Location: checkClasses.php');
    exit;
} else { //user not logged in
    header('Location: login.html');
    exit;
}

?>