<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$host = 'localhost';
$dbname = 'registration_system';
$username = 'root';
$password = 'Diano21@Esron21%';

echo "<h1>Simple Create Students Table</h1>";

try {
    // Connect to MySQL
    echo "<p>Connecting to MySQL...</p>";
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p>Connected to MySQL.</p>";
    
    // Create the database if it doesn't exist
    echo "<p>Creating database if it doesn't exist...</p>";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname`");
    echo "<p>Database created or already exists.</p>";
    
    // Switch to the database
    echo "<p>Switching to database '$dbname'...</p>";
    $pdo->exec("USE `$dbname`");
    echo "<p>Now using database '$dbname'.</p>";
    
    // Drop the table if it exists
    echo "<p>Dropping existing students table if it exists...</p>";
    $pdo->exec("DROP TABLE IF EXISTS `students`");
    echo "<p>Table dropped (if it existed).</p>";
    
    // Create a simplified students table
    echo "<p>Creating a simplified students table...</p>";
    $sql = "CREATE TABLE `students` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `first_name` varchar(100) NOT NULL,
        `last_name` varchar(100) NOT NULL,
        `reg_number` varchar(50) DEFAULT NULL,
        `pin` varchar(255) DEFAULT NULL,
        `dob` date NOT NULL,
        `gender` varchar(10) NOT NULL,
        `email` varchar(100) NOT NULL,
        `phone` varchar(20) DEFAULT NULL,
        `address` text,
        `institution` varchar(150) DEFAULT NULL,
        `grade_level` varchar(20) NOT NULL,
        `admission_date` date NOT NULL,
        `status` varchar(20) DEFAULT 'Pending',
        `national_id` varchar(50) DEFAULT NULL,
        `parent_name` varchar(150) DEFAULT NULL,
        `parent_phone` varchar(20) DEFAULT NULL,
        `parent_email` varchar(100) DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `email` (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $pdo->exec($sql);
    echo "<p style='color: green; font-weight: bold;'>Table 'students' created successfully!</p>";
    
    // Test inserting a record
    echo "<p>Testing with a sample record insertion...</p>";
    $insert = $pdo->prepare("
        INSERT INTO students (
            first_name, last_name, dob, gender, email, 
            grade_level, admission_date
        ) VALUES (
            'Test', 'Student', '2000-01-01', 'male', 'test@example.com',
            '10th', '2023-01-01'
        )
    ");
    $insert->execute();
    
    if ($insert->rowCount() > 0) {
        echo "<p style='color: green;'>Sample record inserted successfully!</p>";
    } else {
        echo "<p style='color: red;'>Failed to insert sample record.</p>";
    }
    
    // Show table contents
    $select = $pdo->query("SELECT * FROM students");
    $students = $select->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($students) > 0) {
        echo "<h3>Current Table Contents:</h3>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr>";
        foreach (array_keys($students[0]) as $column) {
            echo "<th>$column</th>";
        }
        echo "</tr>";
        
        foreach ($students as $student) {
            echo "<tr>";
            foreach ($student as $value) {
                echo "<td>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

echo "<div style='margin-top: 20px;'>";
echo "<a href='form.html' style='display: inline-block; padding: 10px 20px; background-color: #4e73df; color: white; text-decoration: none; border-radius: 5px;'>Go to Registration Form</a>";
echo "</div>";
?> 