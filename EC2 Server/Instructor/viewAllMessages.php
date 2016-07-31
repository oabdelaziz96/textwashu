<?php
require "restrictedArea.php";
?>

<title>All Messages</title>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.0.0/jquery.min.js"></script>
<script src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css">
<!--<link href="vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">-->

<H1 align="center">All Messages</H1>

<table id="table" class="display" width="100%"></table>

<?php

echo '<script> var searchField = "'.urldecode($_GET["search"]).'"; </script>';

?>

<script src="viewAllMessages.js"></script>
