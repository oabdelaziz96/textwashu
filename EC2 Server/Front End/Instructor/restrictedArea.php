<?php

session_start();

if (isset($_SESSION['name'])) {
    echo "Hello ".htmlspecialchars(ucfirst($_SESSION['name']))."!";
    
    
?>
<link href="vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
<br><br>
<a href="logout.php" class="btn btn-default">Log Out</a>
<br>
<a href="mainMenu.php" class="btn btn-default">Main Menu</a>

<?php


} else {
    echo "Log in to enter this area";
    header("refresh: 2; url=login.html");
    exit;
}

?>