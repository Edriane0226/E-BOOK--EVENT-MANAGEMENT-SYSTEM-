<?php
$servername = "localhost";
$username = "root";
$password = "admin";
$database = "ebook_planner";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset for proper character handling
$conn->set_charset("utf8");

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
