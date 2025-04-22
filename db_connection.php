<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "ems";  // Make sure this matches your database name

$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
