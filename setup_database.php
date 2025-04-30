<?php
require_once 'config/config.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Display progress
echo "<h1>Database Setup</h1>";
echo "<p>Setting up database tables...</p>";

try {
    // First, try to connect to MySQL server
    echo "<p>Attempting to connect to MySQL server...</p>";
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p>Connected to MySQL server successfully.</p>";
    
    // Create database if it doesn't exist
    echo "<p>Creating database if it doesn't exist...</p>";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<p>Database created or already exists.</p>";
    
    // Create the edujobs_admin user and grant privileges
    echo "<p>Creating database user...</p>";
    $pdo->exec("CREATE USER IF NOT EXISTS 'edujobs_admin'@'localhost' IDENTIFIED BY 'Edujobs@2024'");
    $pdo->exec("GRANT ALL PRIVILEGES ON " . DB_NAME . ".* TO 'edujobs_admin'@'localhost'");
    $pdo->exec("FLUSH PRIVILEGES");
    echo "<p>Database user created and privileges granted.</p>";
    
    // Select the database
    echo "<p>Selecting database...</p>";
    $pdo->exec("USE " . DB_NAME);
    
    // Read and execute the SQL file
    echo "<p>Reading SQL file...</p>";
    if (file_exists('create_school_management_tables.sql')) {
        $sql = file_get_contents('create_school_management_tables.sql');
        echo "<p>Executing SQL commands...</p>";
        
        // Split SQL file into individual statements
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                try {
                    $pdo->exec($statement);
                } catch (PDOException $e) {
                    echo "<p style='color: orange;'>Warning: " . $e->getMessage() . "</p>";
                }
            }
        }
        
        echo "<p>SQL commands executed successfully.</p>";
    } else {
        throw new Exception("SQL file not found!");
    }
    
    echo "<div style='background-color: #d4edda; color: #155724; padding: 15px; margin-top: 20px; border-radius: 5px;'>";
    echo "<h2>Setup Complete</h2>";
    echo "<p>The database and required tables have been set up successfully.</p>";
    echo "<p>Default admin credentials:</p>";
    echo "<ul>";
    echo "<li>Username: admin</li>";
    echo "<li>Password: password</li>";
    echo "</ul>";
    echo "<p><a href='school_dashboard.php'>Go to School Dashboard</a></p>";
    echo "</div>";
} catch (PDOException $e) {
    echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; margin-top: 20px; border-radius: 5px;'>";
    echo "<h2>Database Connection Error</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database configuration:</p>";
    echo "<ul>";
    echo "<li>Make sure MySQL service is running in XAMPP</li>";
    echo "<li>Verify the database credentials in config/config.php</li>";
    echo "<li>Check if the root user has proper privileges</li>";
    echo "</ul>";
    echo "</div>";
} catch (Exception $e) {
    echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; margin-top: 20px; border-radius: 5px;'>";
    echo "<h2>Setup Error</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "</div>";
}
?> 