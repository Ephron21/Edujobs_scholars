<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Disable display errors to prevent breaking JSON response

// Start session securely
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');
session_start();

// Database configuration
$host = 'localhost';
$dbname = 'registration_system';
$username = 'root';
$password = 'Diano21@Esron21%';

// Function to send JSON response
function sendJsonResponse($status, $message, $data = null) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Check if user is admin
if (!isset($_SESSION["loggedin"]) || !$_SESSION["loggedin"] || !isset($_SESSION["is_admin"]) || !$_SESSION["is_admin"]) {
    sendJsonResponse('error', 'Unauthorized access');
}

// Check if file was uploaded
if (!isset($_FILES['file']) || !isset($_FILES['file']['error']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $error = isset($_FILES['file']['error']) ? $_FILES['file']['error'] : 'No file uploaded';
    sendJsonResponse('error', 'Upload failed: ' . $error);
}

try {
    // Connect to database
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    $file = $_FILES['file'];
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $isPublic = isset($_POST['is_public']) ? 1 : 0;

    // Validate file size (50MB max)
    $maxSize = 50 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        sendJsonResponse('error', 'File size exceeds 50MB limit');
    }

    // Validate file type
    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'application/pdf' => 'pdf',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'video/mp4' => 'mp4',
        'video/mpeg' => 'mpeg',
        'application/vnd.ms-excel' => 'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        'application/vnd.ms-powerpoint' => 'ppt',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx'
    ];

    $fileType = $file['type'];
    if (!array_key_exists($fileType, $allowedTypes)) {
        sendJsonResponse('error', 'Invalid file type. Allowed types: images, PDFs, Word docs, Excel, PowerPoint, and videos');
    }

    // Create upload directory if it doesn't exist
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Generate unique filename
    $extension = $allowedTypes[$fileType];
    $filename = uniqid('file_') . '.' . $extension;
    $uploadPath = $uploadDir . $filename;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        sendJsonResponse('error', 'Failed to move uploaded file');
    }

    // Insert file info into database
    $stmt = $conn->prepare("
        INSERT INTO uploaded_files (
            filename, 
            original_name, 
            file_path, 
            file_type, 
            file_size, 
            title, 
            description, 
            is_public, 
            uploaded_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $filename,
        $file['name'],
        $uploadPath,
        $fileType,
        $file['size'],
        $title,
        $description,
        $isPublic,
        $_SESSION['admin_id']
    ]);

    sendJsonResponse('success', 'File uploaded successfully', [
        'id' => $conn->lastInsertId(),
        'filename' => $filename,
        'title' => $title
    ]);

} catch (Exception $e) {
    error_log('Upload error: ' . $e->getMessage());
    sendJsonResponse('error', 'An error occurred during upload');
}
?> 