<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

// Function to send a secure JSON response
function sendJsonResponse($status, $message, $data = null, $httpCode = 200) {
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    http_response_code($httpCode);
    $response = ['status' => $status, 'message' => $message];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// Function to check if the admin is logged in
function isAdminLoggedIn() {
    return isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true &&
           isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] === true;
}

// Check if the user is admin
if (!isAdminLoggedIn()) {
    sendJsonResponse('error', 'Unauthorized access - Admin privileges required', null, 403);
}

// Check if file ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    sendJsonResponse('error', 'Invalid file ID', null, 400);
}

$fileId = (int)$_GET['id'];

// Enhanced PDO connection with security settings
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    error_log('Database connection error: ' . $e->getMessage());
    sendJsonResponse('error', 'Database connection failed', null, 500);
}

// Process file deletion
try {
    // Check if thumbnail_path column exists
    $checkColumn = $conn->query("SHOW COLUMNS FROM uploaded_files LIKE 'thumbnail_path'");
    $hasThumbnailColumn = $checkColumn->rowCount() > 0;
    
    // First, get the file information to know which files to delete
    $stmt = null;
    if ($hasThumbnailColumn) {
        $stmt = $conn->prepare("SELECT file_path, thumbnail_path FROM uploaded_files WHERE id = ?");
    } else {
        $stmt = $conn->prepare("SELECT file_path FROM uploaded_files WHERE id = ?");
    }
    
    $stmt->execute([$fileId]);
    $file = $stmt->fetch();
    
    if (!$file) {
        sendJsonResponse('error', 'File not found', null, 404);
    }
    
    // Begin transaction for deletion
    $conn->beginTransaction();
    
    // Delete from database
    $deleteStmt = $conn->prepare("DELETE FROM uploaded_files WHERE id = ?");
    $deleteResult = $deleteStmt->execute([$fileId]);
    
    if (!$deleteResult) {
        // If database delete fails, rollback and return error
        $conn->rollBack();
        sendJsonResponse('error', 'Failed to delete file record from database', null, 500);
    }
    
    // If database delete was successful, commit the transaction
    $conn->commit();
    
    // Now delete the physical files
    $errors = [];
    
    // Delete main file
    if (!empty($file['file_path']) && file_exists($file['file_path'])) {
        if (!unlink($file['file_path'])) {
            $errors[] = 'Failed to delete the physical file';
            error_log('Failed to delete file: ' . $file['file_path']);
        }
    }
    
    // Delete thumbnail if exists and column exists
    if ($hasThumbnailColumn && !empty($file['thumbnail_path']) && file_exists($file['thumbnail_path'])) {
        if (!unlink($file['thumbnail_path'])) {
            $errors[] = 'Failed to delete the thumbnail file';
            error_log('Failed to delete thumbnail: ' . $file['thumbnail_path']);
        }
    }
    
    // If we had errors deleting files, report warning but still consider it a success
    // since the database record is gone
    if (!empty($errors)) {
        sendJsonResponse('warning', 'File record deleted, but: ' . implode(', ', $errors), null, 200);
    } else {
        sendJsonResponse('success', 'File deleted successfully', null, 200);
    }
    
} catch (PDOException $e) {
    error_log('Database error when deleting file: ' . $e->getMessage());
    sendJsonResponse('error', 'Database error: ' . $e->getMessage(), null, 500);
} catch (Exception $e) {
    error_log('Error when deleting file: ' . $e->getMessage());
    sendJsonResponse('error', 'Error: ' . $e->getMessage(), null, 500);
}

