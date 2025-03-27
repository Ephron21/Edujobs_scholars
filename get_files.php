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

// Check if it's an AJAX request
$isAjaxRequest = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                 strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Function to check if user is logged in
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
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed'
    ]);
    exit;
}

// Check and create required columns if they don't exist
try {
    // Check for thumbnail_path column
    $checkColumn = $conn->query("SHOW COLUMNS FROM uploaded_files LIKE 'thumbnail_path'");
    if ($checkColumn->rowCount() === 0) {
        // Column doesn't exist, create it
        $conn->exec("ALTER TABLE uploaded_files ADD COLUMN thumbnail_path VARCHAR(255)");
    }
    
    // Check for download_count column
    $checkColumn = $conn->query("SHOW COLUMNS FROM uploaded_files LIKE 'download_count'");
    if ($checkColumn->rowCount() === 0) {
        // Column doesn't exist, create it
        $conn->exec("ALTER TABLE uploaded_files ADD COLUMN download_count INT DEFAULT 0");
    }
} catch (PDOException $e) {
    error_log('Error checking/creating columns: ' . $e->getMessage());
}

// Helper function to format file size
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, 2) . ' ' . $units[$pow];
}

// Helper function to get an icon for file type
function getFileIcon($fileType) {
    $icons = [
        'image/' => 'assets/icons/image.png',
        'application/pdf' => 'assets/icons/pdf.png',
        'application/msword' => 'assets/icons/doc.png',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'assets/icons/doc.png',
        'video/' => 'assets/icons/video.png',
        'application/vnd.ms-excel' => 'assets/icons/excel.png',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'assets/icons/excel.png',
        'application/vnd.ms-powerpoint' => 'assets/icons/powerpoint.png',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'assets/icons/powerpoint.png',
        'text/plain' => 'assets/icons/text.png',
        'application/zip' => 'assets/icons/archive.png',
        'application/x-rar-compressed' => 'assets/icons/archive.png'
    ];
    
    foreach ($icons as $type => $icon) {
        if (strpos($fileType, $type) === 0) {
            return $icon;
        }
    }
    
    return 'assets/icons/file.png'; // Default icon
}

// Get files with filters
try {
    // Default query
    $sql = "SELECT * FROM uploaded_files";
    $whereConditions = [];
    $params = [];
    
    // Apply filters
    if (!empty($_GET)) {
        // Filter by category
        if (isset($_GET['category']) && !empty($_GET['category'])) {
            $whereConditions[] = "category = ?";
            $params[] = $_GET['category'];
        }
        
        // Filter by is_public
        if (isset($_GET['is_public'])) {
            $whereConditions[] = "is_public = ?";
            $params[] = $_GET['is_public'] ? 1 : 0;
        }
        
        // Filter by featured
        if (isset($_GET['featured'])) {
            $whereConditions[] = "featured = ?";
            $params[] = $_GET['featured'] ? 1 : 0;
        }
        
        // Search in title or description
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $whereConditions[] = "(title LIKE ? OR description LIKE ?)";
            $searchTerm = "%" . $_GET['search'] . "%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Add WHERE clause if conditions exist
        if (!empty($whereConditions)) {
            $sql .= " WHERE " . implode(" AND ", $whereConditions);
        }
        
        // Add ordering
        if (isset($_GET['order_by']) && !empty($_GET['order_by'])) {
            $allowedOrderFields = ['upload_date', 'title', 'file_size', 'display_order', 'download_count'];
            $orderField = in_array($_GET['order_by'], $allowedOrderFields) ? $_GET['order_by'] : 'upload_date';
            $orderDirection = (isset($_GET['order_direction']) && strtoupper($_GET['order_direction']) === 'ASC') ? 'ASC' : 'DESC';
            $sql .= " ORDER BY {$orderField} {$orderDirection}";
        } else {
            // Default order
            $sql .= " ORDER BY upload_date DESC";
        }
        
        // Add limit and offset for pagination
        if (isset($_GET['limit']) && is_numeric($_GET['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = (int)$_GET['limit'];
            
            if (isset($_GET['offset']) && is_numeric($_GET['offset'])) {
                $sql .= " OFFSET ?";
                $params[] = (int)$_GET['offset'];
            }
        }
    } else {
        // Default ordering
        $sql .= " ORDER BY upload_date DESC";
    }
    
    // Execute query
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    
    // Fetch files
    $files = $stmt->fetchAll();
    
    // Get total count (for pagination)
    $countSql = "SELECT COUNT(*) as total FROM uploaded_files";
    
    // Apply the same filters to count query
    if (!empty($whereConditions)) {
        $countSql .= " WHERE " . implode(" AND ", $whereConditions);
    }
    
    $countStmt = $conn->prepare($countSql);
    $countParams = array_slice($params, 0, count($whereConditions) * 2); // Only use the WHERE params, not LIMIT/OFFSET
    $countStmt->execute($countParams);
    $totalCount = $countStmt->fetch()['total'];
    
    // Format file data for response
    foreach ($files as &$file) {
        // Format file size
        $file['formatted_size'] = formatFileSize($file['file_size']);
        
        // Generate download URL
        $file['download_url'] = 'download.php?id=' . $file['id'];
        
        // Determine thumbnail or preview
        if (isset($file['thumbnail_path']) && !empty($file['thumbnail_path']) && file_exists($file['thumbnail_path'])) {
            $file['thumbnail_url'] = $file['thumbnail_path'];
        } elseif (strpos($file['file_type'], 'image/') === 0) {
            // Use the actual image as its own thumbnail
            $file['thumbnail_url'] = $file['file_path'];
        } else {
            // Assign default icon based on file type
            $file['thumbnail_url'] = getFileIcon($file['file_type']);
        }
        
        // Add file extension
        $file['extension'] = pathinfo($file['original_name'], PATHINFO_EXTENSION);
    }
    
    // Build response data
    $responseData = [
        'files' => $files,
        'total' => $totalCount
    ];
    
    // Return successful response
    echo json_encode([
        'status' => 'success',
        'message' => 'Files retrieved successfully',
        'data' => $responseData
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error: ' . $e->getMessage()
    ]);
} 