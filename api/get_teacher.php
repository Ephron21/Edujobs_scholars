<?php
require_once '../config/config.php';
require_once '../includes/AttendanceManager.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Teacher ID is required'
    ]);
    exit;
}

$teacherId = intval($_GET['id']);

// Initialize AttendanceManager
$attendanceManager = new AttendanceManager($db);

// Get teacher data
$result = $attendanceManager->getTeacher($teacherId);

// Return the response
echo json_encode($result); 