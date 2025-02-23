<?php
$db_host = '78.47.245.88';
$db_name = 'lan_test';
$db_user = 'matic';
$db_password = 'Nogomet2015!';

// Create connection
$link = new mysqli($db_host, $db_user, $db_password, $db_name);

// Check connection
if ($link->connect_error) {
    die("Connection failed: " . $link->connect_error);
}
mysqli_set_charset($link, "utf8mb4");

// Treba ustavit bazo
?>

