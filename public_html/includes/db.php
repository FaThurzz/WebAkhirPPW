<?php
$servername = "localhost";
$username   = "u169077025_fathur";
$password   = "D0&ilTMS@h";
$database   = "u169077025_db_fathurshop";

$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>