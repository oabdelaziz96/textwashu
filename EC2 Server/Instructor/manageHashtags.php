<?php
require "restrictedArea.php";
?>
<title>Manage Hashtags</title>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.0.0/jquery.min.js"></script>
<script src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css">

<H1 align="center">Manage Hashtags</H1>

<H3 align="left">Options</H3>

<a href="newHashtag.html"><input type="button" value="Create New Hashtag" style='float: left;'/></a>

<br><br><H3 align="left">All Hashtags</H3>
<table id="table" class="display" width="100%"></table>

<script src="showHashtagsTable.js"></script>