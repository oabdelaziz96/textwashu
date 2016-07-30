<?php
session_start();
session_destroy();
echo "You're logged out!";
header("refresh: 1; url=login.html");
exit;
?>