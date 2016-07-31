<?php

//Connects to given database
function setMysqlDatabase($databaseName) {
    $mysqli = new mysqli('localhost', 'participationUsr', 'WashU330', $databaseName);
 
    if($mysqli->connect_errno) {
        printf("Connection Failed: %s\n", $mysqli->connect_error);
        exit;
    }
    
    return $mysqli;
}

//Connects to mysql
function connectToMysql() {
    $mysqli = new mysqli('localhost', 'participationUsr', 'WashU330');
 
    if($mysqli->connect_errno) {
        printf("Connection Failed: %s\n", $mysqli->connect_error);
        exit;
    }
    
    return $mysqli;
}


?>