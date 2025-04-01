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

// Function to check if user is logged in
function isUserLoggedIn() {
    return isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;
}

// Function to check if the admin is logged in
function isAdminLoggedIn() {
    return isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true &&
           isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] === true;
}

// Function to send JSON response
function sendJsonResponse($status, $message, $data = null, $httpCode = 200) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($httpCode);
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Enhanced PDO connection with security settings
try {
    // Database connection
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // Check if file ID is provided
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            echo json_encode(['status' => 'error', 'message' => 'Invalid file ID']);
            exit;
        }
        die('Invalid file ID');
    }

    // Get file information
    $stmt = $conn->prepare("SELECT * FROM uploaded_files WHERE id = :id");
    $stmt->bindParam(':id', $_GET['id'], PDO::PARAM_INT);
    $stmt->execute();
    $file = $stmt->fetch();

    if (!$file) {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            echo json_encode(['status' => 'error', 'message' => 'File not found']);
            exit;
        }
        die('File not found');
    }

    // For AJAX validation requests
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
        echo json_encode(['status' => 'success']);
        exit;
    }

    // For actual file download
    $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads';
    
    // Ensure the uploads directory exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Construct the full file path
    $filePath = $uploadDir . DIRECTORY_SEPARATOR . $file['file_name'];
    
    // Debug information
    error_log("Attempting to download file: " . $filePath);
    
    // Check if file exists
    if (!file_exists($filePath)) {
        error_log("File not found at path: " . $filePath);
        die('File not found on server');
    }

    // Update download count
    $updateStmt = $conn->prepare("UPDATE uploaded_files SET download_count = download_count + 1 WHERE id = :id");
    $updateStmt->bindParam(':id', $_GET['id'], PDO::PARAM_INT);
    $updateStmt->execute();

    // Get file extension
    $fileExtension = pathinfo($file['file_name'], PATHINFO_EXTENSION);
    
    // Clean the filename and add extension
    $downloadFilename = $file['title'];
    if (!empty($fileExtension)) {
        $downloadFilename .= '.' . $fileExtension;
    }

    // Set proper content type
    $contentType = $file['file_type'];
    if (empty($contentType)) {
        $contentType = 'application/octet-stream';
    }

    // Clear any output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }

    // Set headers for file download
    header('Content-Type: ' . $contentType);
    header('Content-Disposition: attachment; filename="' . basename($downloadFilename) . '"');
    header('Content-Length: ' . filesize($filePath));
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: public');
    header('Expires: 0');

    // Output file content
    if ($fp = @fopen($filePath, 'rb')) {
        while (!feof($fp) && connection_status() == 0) {
            echo fread($fp, 8192);
            flush();
        }
        fclose($fp);
    } else {
        error_log("Failed to open file: " . $filePath);
        die('Failed to open file');
    }
    exit;

} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
        echo json_encode(['status' => 'error', 'message' => 'An error occurred while processing your request']);
        exit;
    }
    die('An error occurred while processing your request');
}
?>