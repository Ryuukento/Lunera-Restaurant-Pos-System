<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "Paldo"; // palitan kung iba ang database name mo

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
