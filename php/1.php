<?php
ini_set('display_errors', 1); 
ini_set('display_startup_errors', 1); 
error_reporting(E_ALL);

$dblocation = "localhost";
$dbuser = ""; //username
$dbpasswd = ""; //password
$dbcn= mysqli_connect($dblocation, $dbuser, $dbpasswd);

//Перевірте з'єднання
if (mysqli_connect_errno()) {
    die("Failed to connect to MySQL: " . mysqli_connect_error());
}
?>
