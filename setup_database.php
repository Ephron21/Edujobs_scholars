<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$host = 'localhost';
$dbname = 'registration_system';
$username = 'root';
$password = 'Diano21@Esron21%';

// Display progress
echo "<h1>Database Setup</h1>";
echo "<p>Setting up database tables...</p>";

try {
    // Connect to MySQL server without selecting a database
    echo "<p>Attempting to connect to MySQL server...</p>";
    $conn = new PDO("mysql:host=$host", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "<p>Connected to MySQL server successfully.</p>";
    
    // Check if database exists, if not create it
    echo "<p>Checking if database '$dbname' exists...</p>";
    $conn->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<p>Database '$dbname' created or already exists.</p>";
    
    // Select the database
    echo "<p>Selecting database '$dbname'...</p>";
    $conn->exec("USE `$dbname`");
    echo "<p>Database selected successfully.</p>";
    
    // Get the SQL content from the file
    echo "<p>Reading SQL file...</p>";
    if (file_exists('students.sql')) {
        $sql = file_get_contents('students.sql');
        echo "<p>SQL file read successfully.</p>";
        
        // Execute the SQL
        echo "<p>Creating students table...</p>";
        $conn->exec($sql);
        echo "<p>Students table created successfully.</p>";
    } else {
        echo "<p style='color: red;'>Error: students.sql file not found!</p>";
    }
    
    echo "<div style='background-color: #d4edda; color: #155724; padding: 15px; margin-top: 20px; border-radius: 5px;'>";
    echo "<h2>Setup Complete</h2>";
    echo "<p>The database and required tables have been set up successfully.</p>";
    echo "<p><a href='form.html'>Go to the Student Registration Form</a></p>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; margin-top: 20px; border-radius: 5px;'>";
    echo "<h2>Setup Failed</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database configuration and try again.</p>";
    echo "</div>";
}
?> 