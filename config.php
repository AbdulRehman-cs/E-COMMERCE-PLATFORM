<?php

$servername = "localhost";  // Database server
$username = "root";         // Database username
$password = "";             // Default for XAMPP/MAMP is empty
$dbname = "razababa";       // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}

// Set character encoding (important for handling special characters)
$conn->set_charset("utf8");

// Success message

?>
