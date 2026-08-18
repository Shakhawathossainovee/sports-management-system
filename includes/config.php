<?php
// =============================================
// KHELA HOBEE - Database Configuration
// =============================================
// 📌 INFINITYFREE DATABASE CREDENTIALS
// =============================================

// Database connection settings for InfinityFree
$host = 'sql201.infinityfree.com';          // ✅ InfinityFree MySQL Hostname
$user = 'if0_42633959';                     // ✅ InfinityFree MySQL Username
$pass = '128CSE3200';                       // ✅ YOUR MySQL Password
$dbname = 'if0_42633959_khela_hobee';       // ✅ InfinityFree Database Name

// =============================================
// CREATE DATABASE CONNECTION
// =============================================

// Create connection using MySQLi
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    // If connection fails, show error and stop
    die("❌ Connection failed: " . $conn->connect_error);
}

// =============================================
// CONFIGURE CONNECTION SETTINGS
// =============================================

// Set charset to UTF-8 (supports Bengali and special characters)
$conn->set_charset("utf8mb4");

// Set timezone to Bangladesh Standard Time
date_default_timezone_set('Asia/Dhaka');

// =============================================
// OPTIONAL: CHECK CONNECTION (Remove after testing)
// =============================================

// Uncomment the line below to test if the database connection is working
// echo "✅ Connected successfully to khela_hobee database on InfinityFree!";

// =============================================
// END OF CONFIGURATION
// =============================================
?>