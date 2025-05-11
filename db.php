<?php
$host = "localhost";
$user = "root"; // Change if needed
$password = ""; // Your MySQL password
$database = "movie_db"; // Your database name

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
