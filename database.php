<?php
$hostname = "localhost";
$dbuser = "root";
$dbpassword = "";
$dbname = "advocon";

// Create connection
$conn = new mysqli($hostname, $dbuser, $dbpassword, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
