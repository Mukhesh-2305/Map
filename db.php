<?php
$host = "localhost";
$username = "root";
$password = "";
$dbname = "map"; // <-- Replace this with your actual DB name

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
