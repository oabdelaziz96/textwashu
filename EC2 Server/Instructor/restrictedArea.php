<?php

session_start();

if (isset($_SESSION['name'])) {
    echo "Hello ".htmlspecialchars(ucfirst($_SESSION['name']))."!";
    
    
?>

<a href="logout.php"><input type="button" value="Logout" style='float: right;'/></a>
<a href="mainMenu.php"><input type="button" value="Main Menu" style='float: right;'/></a>

<?php


} else {
    echo "Log in to enter this area";
    header("refresh: 2; url=login.html");
    exit;
}

?>