<?php
session_start();
session_destroy();
header("Location: http://live.textwashu.com/logout");
exit;
?>