<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$host = 'localhost';
$dbname = 'registration_system';
$username = 'root';
$password = 'Diano21@Esron21%';

echo "<h1>Update Students Table Structure</h1>";

try {
    // Connect to the database
    echo "<p>Connecting to database...</p>";
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p>Connected successfully.</p>";
    
    // Update grade_level to use numeric values
    echo "<p>Modifying grade_level column to use numeric values...</p>";
    $conn->exec("ALTER TABLE students CHANGE grade_level grade_level INT(2) NOT NULL COMMENT 'Levels 1-4'");
    echo "<p>Grade level column modified.</p>";
    
    // Update existing records to convert text grade levels to numbers
    echo "<p>Updating existing grade level values...</p>";
    
    // First, create a temporary mapping table for grade level conversions
    $gradeLevelMapping = [
        '1st' => 1, '2nd' => 1, '3rd' => 1,  // Level 1
        '4th' => 1, '5th' => 1, '6th' => 1,
        '7th' => 2, '8th' => 2, '9th' => 2,  // Level 2
        '10th' => 3, '11th' => 3,            // Level 3
        '12th' => 4                          // Level 4
    ];
    
    // Get all students
    $stmt = $conn->query("SELECT id, grade_level FROM students");
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Update each student's grade level
    $updateStmt = $conn->prepare("UPDATE students SET grade_level = ? WHERE id = ?");
    
    foreach ($students as $student) {
        $oldGradeLevel = $student['grade_level'];
        $id = $student['id'];
        
        // If the current grade level is already a number, no need to convert
        if (is_numeric($oldGradeLevel)) {
            $newGradeLevel = min(max((int)$oldGradeLevel, 1), 4); // Ensure between 1-4
        } else {
            // Convert text grade level to number using mapping
            $newGradeLevel = isset($gradeLevelMapping[$oldGradeLevel]) ? 
                             $gradeLevelMapping[$oldGradeLevel] : 1; // Default to level 1
        }
        
        // Update the record
        $updateStmt->execute([$newGradeLevel, $id]);
        
        echo "<p>Updated student ID $id: Grade level '$oldGradeLevel' → $newGradeLevel</p>";
    }
    
    // Update PIN column to store plaintext passwords
    echo "<p>Modifying pin column to store plaintext passwords...</p>";
    $conn->exec("ALTER TABLE students CHANGE pin password VARCHAR(50) DEFAULT NULL COMMENT 'Plaintext password'");
    echo "<p>PIN column renamed to password and set to store plaintext values.</p>";
    
    echo "<div style='background-color: #d4edda; color: #155724; padding: 15px; margin-top: 20px; border-radius: 5px;'>";
    echo "<h2>Update Complete</h2>";
    echo "<p>The students table has been successfully updated:</p>";
    echo "<ul>";
    echo "<li>Grade levels changed to numeric values (1-4)</li>";
    echo "<li>PIN column renamed to password for plaintext storage</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; margin-top: 20px; border-radius: 5px;'>";
    echo "<h2>Update Failed</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "<div style='margin-top: 20px;'>";
echo "<a href='manage_students.php' style='display: inline-block; padding: 10px 20px; background-color: #4e73df; color: white; text-decoration: none; border-radius: 5px; margin-right: 10px;'>Go to Student Management</a>";
echo "<a href='form.html' style='display: inline-block; padding: 10px 20px; background-color: #6c757d; color: white; text-decoration: none; border-radius: 5px;'>Back to Registration Form</a>";
echo "</div>";
?> 