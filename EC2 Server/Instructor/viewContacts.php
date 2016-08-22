<?php
require "restrictedArea.php";
?>

<title>Manage Contacts</title>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.0.0/jquery.min.js"></script>
<script src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css">

<H1 align="center">Manage Contacts</H1>

<H3 align="left">Options</H3>

<a href="editContactPrefForm.php"><input type="button" value="Contact Preferences" style='float: left;'/></a>

<br><br><H3 align="left">All Contacts</H3>
<table id="table" class="display" width="100%"></table>

<?php

echo '<script> var searchField = "'.urldecode($_GET["search"]).'"; </script>';

?>

<script src="viewContacts.js"></script>
