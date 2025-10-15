<?php
// Database connection parameters
$servername = "localhost";
$username = "root"; // Use your actual database username
$password = "";     // Use your actual database password
$dbname = "vignan_feedback";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start session to pass data between pages
session_start();
?>