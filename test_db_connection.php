<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Connection Test</h1>";

// Database configuration
$host = 'localhost';
$dbname = 'registration_system';
$username = 'root';
$password = 'Diano21@Esron21%';

try {
    echo "<p>Attempting to connect to MySQL server...</p>";
    
    // First try to connect to MySQL without specifying a database
    $conn = new PDO("mysql:host=$host", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p>Successfully connected to MySQL server.</p>";
    
    // Check if the database exists
    $stmt = $conn->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'");
    $dbExists = (bool) $stmt->fetchColumn();
    
    if ($dbExists) {
        echo "<p>Database '$dbname' exists.</p>";
        
        // Connect to the specific database
        $dbconn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $dbconn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo "<p>Successfully connected to '$dbname' database.</p>";
        
        // Check if the students table exists
        $stmt = $dbconn->query("SHOW TABLES LIKE 'students'");
        $tableExists = $stmt->rowCount() > 0;
        
        if ($tableExists) {
            echo "<p>Table 'students' exists.</p>";
            
            // Try to get table structure
            $stmt = $dbconn->query("DESCRIBE students");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            echo "<p>Table structure:</p>";
            echo "<ul>";
            foreach ($columns as $column) {
                echo "<li>" . htmlspecialchars($column) . "</li>";
            }
            echo "</ul>";
            
            // Check if there are any records
            $stmt = $dbconn->query("SELECT COUNT(*) FROM students");
            $count = $stmt->fetchColumn();
            
            echo "<p>Number of student records: $count</p>";
            
        } else {
            echo "<p style='color: red;'>Table 'students' does not exist!</p>";
            echo "<p>You may need to create the table using the setup_database.php script.</p>";
        }
    } else {
        echo "<p style='color: red;'>Database '$dbname' does not exist!</p>";
        echo "<p>You need to create the database first using the setup_database.php script.</p>";
    }
    
    echo "<div style='margin-top: 20px;'>";
    echo "<p><a href='setup_database.php' class='btn btn-primary'>Run Setup Script</a></p>";
    echo "<p><a href='form.html' class='btn btn-secondary'>Go to Registration Form</a></p>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; margin-top: 20px; border-radius: 5px;'>";
    echo "<h2>Connection Failed</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database configuration settings:</p>";
    echo "<ul>";
    echo "<li>Host: " . htmlspecialchars($host) . "</li>";
    echo "<li>Database Name: " . htmlspecialchars($dbname) . "</li>";
    echo "<li>Username: " . htmlspecialchars($username) . "</li>";
    echo "<li>Password: (hidden for security)</li>";
    echo "</ul>";
    echo "</div>";
}
?> 