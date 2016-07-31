<?php
require "restrictedArea.php";
?>
<title>Preferences</title>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.0.0/jquery.min.js"></script>

<H1 align="center">Preferences</H1>

<br><br><br><br>

<style>
table, td, th {
    border: 1px solid black;
}

table {
    border-collapse: collapse;
    width: 70%;
}

th, td {
    text-align: center;
}
</style>

<table id="table" align="center">
    <tr>
        <th>Name</th>
        <th>On/Off</th> 
        <th>Argument 1</th>
        <th>Argument 2</th>
    </tr>
</table>

<script src="showPreferencesTable.js"></script>