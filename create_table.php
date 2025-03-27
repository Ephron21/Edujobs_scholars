<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$host = 'localhost';
$dbname = 'registration_system';
$username = 'root';
$password = 'Diano21@Esron21%';

echo "<h1>Create Students Table</h1>";

try {
    // Connect to the database
    echo "<p>Connecting to database...</p>";
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p>Connected successfully.</p>";
    
    // Drop the table if it exists
    echo "<p>Dropping existing table if it exists...</p>";
    $conn->exec("DROP TABLE IF EXISTS `students`");
    echo "<p>Table dropped (if it existed).</p>";
    
    // Create the students table
    echo "<p>Creating students table...</p>";
    $sql = "CREATE TABLE `students` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `first_name` varchar(100) NOT NULL,
      `last_name` varchar(100) NOT NULL,
      `reg_number` varchar(50) DEFAULT NULL,
      `pin` varchar(255) DEFAULT NULL,
      `dob` date NOT NULL,
      `gender` enum('male','female','other') NOT NULL,
      `email` varchar(100) NOT NULL,
      `phone` varchar(20) DEFAULT NULL,
      `address` text,
      `institution` varchar(150) DEFAULT NULL,
      `grade_level` varchar(20) NOT NULL,
      `admission_date` date NOT NULL,
      `status` enum('Pending','Admitted','Not Admitted') DEFAULT 'Pending',
      `national_id` varchar(50) DEFAULT NULL,
      `parent_name` varchar(150) DEFAULT NULL,
      `parent_phone` varchar(20) DEFAULT NULL,
      `parent_email` varchar(100) DEFAULT NULL,
      `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
      `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `email` (`email`),
      UNIQUE KEY `reg_number` (`reg_number`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($sql);
    echo "<p style='color: green; font-weight: bold;'>Table 'students' created successfully.</p>";
    
    // Check if the table was created
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
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

echo "<div style='margin-top: 20px;'>";
echo "<p><a href='form.html' class='btn btn-primary'>Go to Registration Form</a></p>";
echo "</div>";
?> 