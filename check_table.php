<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$host = 'localhost';
$dbname = 'registration_system';
$username = 'root';
$password = 'Diano21@Esron21%';

echo "<h1>Table Check</h1>";

try {
    // Connect to the database
    echo "<p>Attempting to connect to database...</p>";
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p>Database connection successful.</p>";
    
    // Check if the students table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'students'");
    $tableExists = $stmt->rowCount() > 0;
    
    if ($tableExists) {
        echo "<p style='color: green;'>The 'students' table exists in the database.</p>";
        
        // Check table structure
        $stmt = $conn->query("DESCRIBE students");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Table Structure:</h3>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>{$column['Field']}</td>";
            echo "<td>{$column['Type']}</td>";
            echo "<td>{$column['Null']}</td>";
            echo "<td>{$column['Key']}</td>";
            echo "<td>{$column['Default']}</td>";
            echo "<td>{$column['Extra']}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p style='color: red;'>The 'students' table does NOT exist in the database!</p>";
        
        // Check if the database exists
        $stmt = $conn->query("SELECT DATABASE()");
        $dbName = $stmt->fetchColumn();
        echo "<p>Current database: $dbName</p>";
        
        // Show all tables
        $stmt = $conn->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (count($tables) > 0) {
            echo "<p>Existing tables in the database:</p>";
            echo "<ul>";
            foreach ($tables as $table) {
                echo "<li>$table</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>No tables found in the database.</p>";
        }
        
        // Create the table now
        echo "<h3>Creating students table...</h3>";
        
        try {
            // Check if SQL file exists
            if (file_exists('students.sql')) {
                $sql = file_get_contents('students.sql');
                echo "<p>SQL file loaded.</p>";
                echo "<pre>" . htmlspecialchars($sql) . "</pre>";
                
                // Execute the SQL
                $conn->exec($sql);
                echo "<p style='color: green;'>Students table created successfully!</p>";
            } else {
                echo "<p style='color: red;'>Error: students.sql file not found!</p>";
            }
        } catch (PDOException $e) {
            echo "<p style='color: red;'>Error creating table: " . $e->getMessage() . "</p>";
        }
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Connection failed: " . $e->getMessage() . "</p>";
}

echo "<p><a href='form.html'>Go to Registration Form</a></p>";
?> 