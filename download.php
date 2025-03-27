<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session securely
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');
session_start();

// Database configuration (Move credentials to an environment file for security)
$host = 'localhost';
$dbname = 'registration_system';
$username = 'root';
$password = 'Diano21@Esron21%';

// Function to check if the user is logged in
function isUserLoggedIn() {
    return isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;
}

// Function to check if the admin is logged in
function isAdminLoggedIn() {
    return isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true &&
           isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] === true;
}

// Enhanced PDO connection with security settings
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die('Database connection failed');
}

// Check if file ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('HTTP/1.1 400 Bad Request');
    echo 'Invalid file ID';
    exit;
}

$fileId = (int)$_GET['id'];

// Get file information
try {
    $stmt = $conn->prepare("SELECT * FROM uploaded_files WHERE id = ?");
    $stmt->execute([$fileId]);
    $file = $stmt->fetch();
    
    if (!$file) {
        header('HTTP/1.1 404 Not Found');
        echo 'File not found';
        exit;
    }
    
    // Check if user has access to the file
    // Public files can be accessed by anyone, private files only by logged-in users/admins
    if (!$file['is_public'] && !isUserLoggedIn()) {
        header('HTTP/1.1 403 Forbidden');
        echo 'Access denied';
        exit;
    }
    
    // Check if file exists on server
    if (!file_exists($file['file_path'])) {
        header('HTTP/1.1 404 Not Found');
        echo 'File not found on server';
        exit;
    }
    
    // Check if download_count column exists and create it if it doesn't
    try {
        $checkColumn = $conn->query("SHOW COLUMNS FROM uploaded_files LIKE 'download_count'");
        if ($checkColumn->rowCount() === 0) {
            // Column doesn't exist, create it
            $conn->exec("ALTER TABLE uploaded_files ADD COLUMN download_count INT DEFAULT 0");
        }
        
        // Increment download count
        $updateStmt = $conn->prepare("UPDATE uploaded_files SET download_count = download_count + 1 WHERE id = ?");
        $updateStmt->execute([$fileId]);
    } catch (PDOException $e) {
        // Log the error but continue with the download
        error_log('Error updating download count: ' . $e->getMessage());
    }
    
    // Set appropriate headers for file download
    header('Content-Description: File Transfer');
    header('Content-Type: ' . $file['file_type']);
    header('Content-Disposition: attachment; filename="' . $file['original_name'] . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file['file_path']));
    
    // Clear output buffer
    ob_clean();
    flush();
    
    // Read file and output it to the browser
    readfile($file['file_path']);
    exit;
} catch (PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo 'Database error: ' . $e->getMessage();
    exit;
} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo 'Error: ' . $e->getMessage();
    exit;
}
?>