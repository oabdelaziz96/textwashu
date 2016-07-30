<?php

session_start();

if (isset($_SESSION['name'])) { //user logged in
    header('Location: mainMenu.php');
    exit;
} else { //user not logged in
    header('Location: login.html');
    exit;
}

?>