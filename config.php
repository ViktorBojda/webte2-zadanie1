<?php
$hostname = "localhost";
$username = "xbojda";
$password = "iSyrh1QOJEbPLTT";
$dbname = "zadanie1";

$pdo = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>