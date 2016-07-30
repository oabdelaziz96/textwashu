<?php
require "restrictedArea.php";
?>
<title>Manage Hashtags</title>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.0.0/jquery.min.js"></script>
<script src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css">
<!--<link href="vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">-->

<br><br>Manage Hashtags<br><br>

<a href="newHashtag.html" class="btn btn-default">Create New Hashtag</a>

<br><br><br>All Hashtags:<br><br>
<table id="table" class="display" width="100%"></table>

<script src="showHashtagsTable.js"></script>