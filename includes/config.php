<?php
// =============================================
// KHELA HOBEE - Database Configuration
// =============================================

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'khela_hobee';

// Create connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to UTF-8
$conn->set_charset("utf8mb4");

// Set timezone
date_default_timezone_set('Asia/Dhaka');

// For testing (remove after testing)
// echo "✅ Connected successfully to khela_hobee database";
?>