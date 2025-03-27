<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$host = 'localhost';
$dbname = 'registration_system';
$username = 'root';
$password = 'Diano21@Esron21%';

echo "<h1>Students Table Check</h1>";

try {
    // Connect to the database
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p>Connected to database successfully.</p>";
    
    // Get student count
    $stmt = $conn->query("SELECT COUNT(*) FROM students");
    $count = $stmt->fetchColumn();
    echo "<p>Total students in the database: <strong>$count</strong></p>";
    
    // Get all students
    $stmt = $conn->query("SELECT * FROM students ORDER BY id DESC");
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($students) > 0) {
        echo "<h3>Students in the Database:</h3>";
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        
        // Table headers
        echo "<tr style='background-color: #f2f2f2;'>";
        foreach (array_keys($students[0]) as $column) {
            echo "<th>$column</th>";
        }
        echo "</tr>";
        
        // Table rows
        foreach ($students as $student) {
            echo "<tr>";
            foreach ($student as $key => $value) {
                // Mask the PIN value if it exists
                if ($key === 'pin' && !empty($value)) {
                    echo "<td>[HASHED]</td>";
                } else {
                    echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                }
            }
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>No students found in the database.</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

echo "<div style='margin-top: 20px;'>";
echo "<a href='form.html' style='display: inline-block; padding: 10px 20px; background-color: #4e73df; color: white; text-decoration: none; border-radius: 5px;'>Back to Registration Form</a>";
echo "</div>";
?> 