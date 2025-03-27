<?php
// Set response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

// Include database connection
require_once '../db_connection.php';

// Initialize response array
$response = ['success' => false, 'students' => [], 'message' => ''];

try {
    if (isset($_GET['ids']) && !empty($_GET['ids'])) {
        // Get specific students by IDs
        $ids = explode(',', $_GET['ids']);
        
        // Sanitize IDs (ensure they are integers)
        $ids = array_map(function($id) {
            return (int)trim($id);
        }, $ids);
        
        // Create placeholders for prepared statement
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        
        // Prepare and execute the query
        $stmt = $conn->prepare("SELECT * FROM students WHERE id IN ($placeholders) ORDER BY first_name, last_name");
        $stmt->execute($ids);
    } else {
        // Get all students
        $stmt = $conn->prepare("SELECT * FROM students ORDER BY first_name, last_name");
        $stmt->execute();
    }
    
    // Fetch students
    $students = $stmt->fetchAll();
    
    if (count($students) > 0) {
        // Mask sensitive data
        foreach ($students as &$student) {
            // Remove PIN from response
            unset($student['pin']);
        }
        
        $response['success'] = true;
        $response['students'] = $students;
        $response['count'] = count($students);
        $response['message'] = 'Students retrieved successfully';
    } else {
        $response['message'] = 'No students found';
    }
} catch (PDOException $e) {
    $response['message'] = 'Error retrieving students: ' . $e->getMessage();
}

// Return response
echo json_encode($response);
?> 