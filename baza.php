<?php
$db_host = 'localhost';
$db_name = 'lan_primerjava';
$db_user = 'root';
$db_password = '';

// Create connection
$link = new mysqli($db_host, $db_user, $db_password, $db_name);

// Check connection
if ($link->connect_error) {
    die("Connection failed: " . $link->connect_error);
}
mysqli_set_charset($link, "utf8mb4");

// Treba ustavit bazo
?>

