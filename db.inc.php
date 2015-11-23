<?php

$SERVER_MAGIC = "TechItHa6RuzeM8";

function pdo_connect() {
    try {
        // Production server
        $dbhost="mysql:host=mysql-user.cse.msu.edu;dbname=chuppthe";
        $user = "chuppthe";
        $password = "7n4qk5q3";
        return new PDO($dbhost, $user, $password);
    } catch(PDOException $e) {
        die( "Unable to select database");
    }
}