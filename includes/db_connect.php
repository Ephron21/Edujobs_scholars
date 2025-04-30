<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$host = 'localhost';
$dbname = 'edujobs_scholars';
$username = 'root';  // Default XAMPP username
$password = '';      // Default XAMPP password

try {
    // Create PDO connection
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    
    // Set PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch(PDOException $e) {
    // Log the error and set session message
    error_log("Connection failed: " . $e->getMessage());
    $_SESSION['application_error'] = "Database connection failed. Please try again later.";
    die("Connection failed: " . $e->getMessage());
}
?> 