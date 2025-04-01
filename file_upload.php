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

// Check if it's an AJAX request
$isAjaxRequest = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                 strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Enhanced PDO connection with security settings
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci'
    ]);
} catch (PDOException $e) {
    if ($isAjaxRequest) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
        exit;
    }
    die('Database connection failed');
}

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

// Function to check if the admin is logged in and get user ID
function getCurrentUserId() {
    if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
        return null;
    }
    
    // Check if user_id exists in session
    if (!isset($_SESSION["admin_id"])) {
        // If not, try to fetch it from the database
        global $conn;
        try {
            $stmt = $conn->prepare("SELECT id FROM admins WHERE email = ?");
            $stmt->execute([$_SESSION["email"]]);
            $user = $stmt->fetch();
            
            if ($user) {
                $_SESSION["admin_id"] = $user['id'];
                return $user['id'];
            }
        } catch (PDOException $e) {
            error_log('Error fetching user ID: ' . $e->getMessage());
        }
        
        return null;
    }
    
    return $_SESSION["admin_id"];
}

// Function to check if the admin is logged in
function isAdminLoggedIn() {
    return isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true &&
           isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] === true;
}

// Create files table if not exists
function createFilesTable($conn) {
    $sql = "CREATE TABLE IF NOT EXISTS uploaded_files (
        id INT AUTO_INCREMENT PRIMARY KEY,
        filename VARCHAR(255) NOT NULL,
        original_name VARCHAR(255) NOT NULL,
        file_path VARCHAR(255) NOT NULL,
        file_type VARCHAR(100) NOT NULL,
        file_size INT NOT NULL,
        uploaded_by INT,
        is_public BOOLEAN DEFAULT FALSE,
        title VARCHAR(255),
        description TEXT,
        category VARCHAR(50),
        tags VARCHAR(255),
        download_count INT DEFAULT 0,
        featured BOOLEAN DEFAULT FALSE,
        display_order INT DEFAULT 0,
        thumbnail_path VARCHAR(255),
        status ENUM('active', 'inactive', 'archived') DEFAULT 'active',
        upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (uploaded_by) REFERENCES admins(id)
    )";
    $conn->exec($sql);
}

// Call the function to ensure table exists
createFilesTable($conn);

// Function to generate thumbnails for images
function generateThumbnail($sourcePath, $targetPath, $width = 200, $height = 200) {
    $sourceImage = null;
    $fileType = exif_imagetype($sourcePath);
    
    // Create image based on file type
    switch ($fileType) {
        case IMAGETYPE_JPEG:
            $sourceImage = imagecreatefromjpeg($sourcePath);
            break;
        case IMAGETYPE_PNG:
            $sourceImage = imagecreatefrompng($sourcePath);
            break;
        case IMAGETYPE_GIF:
            $sourceImage = imagecreatefromgif($sourcePath);
            break;
        default:
            return false;
    }
    
    if (!$sourceImage) {
        return false;
    }
    
    // Get original image dimensions
    $sourceWidth = imagesx($sourceImage);
    $sourceHeight = imagesy($sourceImage);
    
    // Calculate thumbnail dimensions (maintaining aspect ratio)
    $ratio = min($width / $sourceWidth, $height / $sourceHeight);
    $targetWidth = $sourceWidth * $ratio;
    $targetHeight = $sourceHeight * $ratio;
    
    // Create thumbnail image
    $thumbnail = imagecreatetruecolor($targetWidth, $targetHeight);
    
    // Preserve transparency for PNG images
    if ($fileType == IMAGETYPE_PNG) {
        imagealphablending($thumbnail, false);
        imagesavealpha($thumbnail, true);
        $transparent = imagecolorallocatealpha($thumbnail, 255, 255, 255, 127);
        imagefilledrectangle($thumbnail, 0, 0, $targetWidth, $targetHeight, $transparent);
    }
    
    // Resize image
    imagecopyresampled(
        $thumbnail, $sourceImage,
        0, 0, 0, 0,
        $targetWidth, $targetHeight,
        $sourceWidth, $sourceHeight
    );
    
    // Save thumbnail
    $result = false;
    switch ($fileType) {
        case IMAGETYPE_JPEG:
            $result = imagejpeg($thumbnail, $targetPath, 90);
            break;
        case IMAGETYPE_PNG:
            $result = imagepng($thumbnail, $targetPath, 9);
            break;
        case IMAGETYPE_GIF:
            $result = imagegif($thumbnail, $targetPath);
            break;
    }
    
    // Free memory
    imagedestroy($sourceImage);
    imagedestroy($thumbnail);
    
    return $result;
}

// Function to generate a PDF thumbnail (first page)
function generatePdfThumbnail($sourcePath, $targetPath) {
    // Check if Imagick extension is available
    if (!extension_loaded('imagick')) {
        return false;
    }
    
    try {
        $imagick = new Imagick();
        $imagick->setResolution(300, 300);
        $imagick->readImage($sourcePath . '[0]'); // Read first page
        $imagick->setImageFormat('jpg');
        $imagick->thumbnailImage(200, 200, true);
        $imagick->writeImage($targetPath);
        $imagick->clear();
        $imagick->destroy();
        return true;
    } catch (Exception $e) {
        error_log('PDF Thumbnail generation error: ' . $e->getMessage());
        return false;
    }
}

// Function to get files
function getFiles($conn, $filters = []) {
    try {
        // Default query
        $sql = "SELECT * FROM uploaded_files";
        $whereConditions = [];
        $params = [];
        
        // Apply filters
        if (!empty($filters)) {
            // Filter by category
            if (isset($filters['category']) && !empty($filters['category'])) {
                $whereConditions[] = "category = ?";
                $params[] = $filters['category'];
            }
            
            // Filter by is_public
            if (isset($filters['is_public'])) {
                $whereConditions[] = "is_public = ?";
                $params[] = $filters['is_public'] ? 1 : 0;
            }
            
            // Filter by featured
            if (isset($filters['featured'])) {
                $whereConditions[] = "featured = ?";
                $params[] = $filters['featured'] ? 1 : 0;
            }
            
            // Search in title or description
            if (isset($filters['search']) && !empty($filters['search'])) {
                $whereConditions[] = "(title LIKE ? OR description LIKE ?)";
                $searchTerm = "%" . $filters['search'] . "%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            // Add WHERE clause if conditions exist
            if (!empty($whereConditions)) {
                $sql .= " WHERE " . implode(" AND ", $whereConditions);
            }
            
            // Add ordering
            if (isset($filters['order_by']) && !empty($filters['order_by'])) {
                $allowedOrderFields = ['upload_date', 'title', 'file_size', 'display_order', 'download_count'];
                $orderField = in_array($filters['order_by'], $allowedOrderFields) ? $filters['order_by'] : 'upload_date';
                $orderDirection = (isset($filters['order_direction']) && strtoupper($filters['order_direction']) === 'ASC') ? 'ASC' : 'DESC';
                $sql .= " ORDER BY {$orderField} {$orderDirection}";
            } else {
                // Default order
                $sql .= " ORDER BY upload_date DESC";
            }
            
            // Add limit and offset for pagination
            if (isset($filters['limit']) && is_numeric($filters['limit'])) {
                $sql .= " LIMIT ?";
                $params[] = (int)$filters['limit'];
                
                if (isset($filters['offset']) && is_numeric($filters['offset'])) {
                    $sql .= " OFFSET ?";
                    $params[] = (int)$filters['offset'];
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
        $countParams = array_slice($params, 0, count($whereConditions)); // Only use the WHERE params, not LIMIT/OFFSET
        $countStmt->execute($countParams);
        $totalCount = $countStmt->fetch()['total'];
        
        // Format file data for response
        foreach ($files as &$file) {
            // Format file size
            $file['formatted_size'] = formatFileSize($file['file_size']);
            
            // Generate download URL
            $file['download_url'] = 'download.php?id=' . $file['id'];
            
            // Determine thumbnail or preview
            if (!empty($file['thumbnail_path']) && file_exists($file['thumbnail_path'])) {
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
        
        return [
            'files' => $files,
            'total' => $totalCount
        ];
    } catch (PDOException $e) {
        error_log('Error fetching files: ' . $e->getMessage());
        return [
            'files' => [],
            'total' => 0
        ];
    }
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

// Function to update download count
function incrementDownloadCount($conn, $fileId) {
    try {
        $stmt = $conn->prepare("UPDATE uploaded_files SET download_count = download_count + 1 WHERE id = ?");
        $stmt->execute([$fileId]);
        return true;
    } catch (PDOException $e) {
        error_log('Error updating download count: ' . $e->getMessage());
        return false;
    }
}

// Function to delete a file
function deleteFile($conn, $fileId) {
    try {
        // Get file info
        $stmt = $conn->prepare("SELECT file_path, thumbnail_path FROM uploaded_files WHERE id = ?");
        $stmt->execute([$fileId]);
        $file = $stmt->fetch();
        
        if (!$file) {
            return false;
        }
        
        // Delete from database
        $deleteStmt = $conn->prepare("DELETE FROM uploaded_files WHERE id = ?");
        $deleteStmt->execute([$fileId]);
        
        // Delete file from server
        if (file_exists($file['file_path'])) {
            unlink($file['file_path']);
        }
        
        // Delete thumbnail if exists
        if (!empty($file['thumbnail_path']) && file_exists($file['thumbnail_path'])) {
            unlink($file['thumbnail_path']);
        }
        
        return true;
    } catch (PDOException $e) {
        error_log('Error deleting file: ' . $e->getMessage());
        return false;
    }
}

// File upload handler
function uploadFile($conn) {
    // Check if it's an AJAX request
    $isAjaxRequest = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                     strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

    // Redirect non-AJAX requests to upload.php
    if (!$isAjaxRequest) {
        header('Location: upload.php');
        exit;
    }

    // For AJAX requests, forward to upload.php
    $ch = curl_init('upload.php');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $_POST);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    if (isset($_FILES['file'])) {
        $file = $_FILES['file'];
        curl_setopt($ch, CURLOPT_POSTFIELDS, array_merge($_POST, [
            'file' => new CURLFile($file['tmp_name'], $file['type'], $file['name'])
        ]));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    header('Content-Type: application/json');
    http_response_code($httpCode);
    echo $response;
    exit;
}

// Check if it's an AJAX upload request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    uploadFile($conn);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Management</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom styling -->
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 30px auto;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            border: none;
        }
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #eee;
            padding: 15px 20px;
            font-weight: 600;
            border-radius: 10px 10px 0 0 !important;
        }
        .card-body {
            padding: 20px;
        }
        .form-control, .form-select {
            border-radius: 5px;
            padding: 10px 15px;
            border: 1px solid #ddd;
        }
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
            border-color: #86b7fe;
        }
        .btn-primary {
            background-color: #0d6efd;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: 500;
        }
        .btn-primary:hover {
            background-color: #0b5ed7;
        }
        .file-card {
            position: relative;
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.2s;
        }
        .file-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        .file-thumbnail {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 10px 10px 0 0;
            background-color: #f1f1f1;
        }
        .file-info {
            padding: 15px;
        }
        .file-title {
            font-weight: 600;
            margin-bottom: 5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .file-meta {
            font-size: 0.85rem;
            color: #6c757d;
        }
        .file-actions {
            position: absolute;
            top: 10px;
            right: 10px;
            display: none;
        }
        .file-card:hover .file-actions {
            display: block;
        }
        .badge {
            font-weight: 500;
            padding: 5px 10px;
            border-radius: 20px;
        }
        .alert {
            border-radius: 5px;
            padding: 15px;
        }
        .filters {
            padding: 15px;
            background-color: #fff;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        .pagination {
            margin-top: 20px;
            justify-content: center;
        }
        .file-type-icon {
            font-size: 24px;
            margin-right: 10px;
        }
        .spinner-border {
            width: 1rem;
            height: 1rem;
            border-width: 2px;
        }
        .file-empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #6c757d;
        }
        .file-empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
        }
        .file-preview-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1050;
        }
        .file-preview-content {
            max-width: 90%;
            max-height: 90%;
        }
        .file-preview-content img {
            max-width: 100%;
            max-height: 90vh;
            border-radius: 5px;
        }
        .file-preview-close {
            position: absolute;
            top: 20px;
            right: 20px;
            color: white;
            font-size: 30px;
            cursor: pointer;
        }
        .dropzone {
            border: 2px dashed #0d6efd;
            border-radius: 10px;
            background-color: #f8f9fa;
            min-height: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .dropzone:hover {
            background-color: #e9ecef;
        }
        .dropzone-active {
            background-color: #e6f2ff;
            border-color: #0d6efd;
        }
        .file-upload-progress {
            width: 100%;
            margin-top: 15px;
        }
        .category-badge {
            background-color: #e9ecef;
            color: #495057;
            font-size: 0.8rem;
            margin-right: 5px;
            margin-bottom: 5px;
        }
        .status-toggle {
            width: 40px;
            cursor: pointer;
        }
        .btn-action {
            padding: 5px 10px;
            margin: 0 2px;
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .btn-action:hover {
            background-color: white;
        }
        .tag-badge {
            background-color: #e9ecef;
            color: #495057;
            border-radius: 20px;
            padding: 2px 10px;
            margin-right: 5px;
            margin-bottom: 5px;
            display: inline-block;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <?php
    // Check if user is logged in and is an admin
    if (!isAdminLoggedIn()) {
        echo '<div class="container"><div class="alert alert-danger">You must be logged in as an admin to access this page.</div></div>';
        exit;
    }
    ?>
    
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">File Management System</h1>
                
                <!-- Success/Error Messages -->
                <div id="alertsContainer"></div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-4">
                <!-- File Upload Card -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Upload New File</span>
                        <button class="btn btn-sm btn-outline-primary" id="toggleUploadForm">
                            <i class="fas fa-chevron-down"></i>
                        </button>
                    </div>
                    <div class="card-body" id="uploadFormContainer">
                        <form id="fileUploadForm" enctype="multipart/form-data">
                            <!-- Drag & Drop Zone -->
                            <div class="dropzone mb-3" id="dropzone">
                                <div class="text-center">
                                    <i class="fas fa-cloud-upload-alt fa-3x mb-3 text-primary"></i>
                                    <p class="mb-2">Drag & drop files here</p>
                                    <p class="small text-muted mb-3">or</p>
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="browseBtn">
                                        Browse Files
                                    </button>
                                    <input type="file" id="file" name="file" required class="d-none"
                                           accept="image/jpeg,image/png,image/gif,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,video/mp4,video/mpeg,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,text/plain,application/zip,application/x-rar-compressed">
                                </div>
                            </div>
                            
                            <!-- Upload Progress -->
                            <div class="file-upload-progress d-none" id="uploadProgress">
                                <div class="d-flex justify-content-between mb-1">
                                    <span id="selectedFileName">Uploading file...</span>
                                    <span id="uploadPercentage">0%</span>
                                </div>
                                <div class="progress mb-3">
                                    <div class="progress-bar" role="progressbar" style="width: 0%" id="progressBar"></div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="title" class="form-label">File Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="title" name="title" required>
                                </div>
                                
                                <div class="col-md-12 mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="category" class="form-label">Category</label>
                                    <select class="form-select" id="category" name="category">
                                        <option value="documents">Documents</option>
                                        <option value="images">Images</option>
                                        <option value="videos">Videos</option>
                                        <option value="presentations">Presentations</option>
                                        <option value="spreadsheets">Spreadsheets</option>
                                        <option value="archives">Archives</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="tags" class="form-label">Tags (comma separated)</label>
                                    <input type="text" class="form-control" id="tags" name="tags" placeholder="tag1, tag2, tag3">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_public" name="is_public" value="1">
                                        <label class="form-check-label" for="is_public">Make file public</label>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="featured" name="featured" value="1">
                                        <label class="form-check-label" for="featured">Featured file</label>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="display_order" class="form-label">Display Order</label>
                                    <input type="number" class="form-control" id="display_order" name="display_order" value="0" min="0">
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary" id="uploadBtn">
                                    <span id="uploadBtnText">Upload File</span>
                                    <span id="uploadSpinner" class="spinner-border spinner-border-sm ms-1 d-none" role="status"></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Filters Card -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Search & Filters</span>
                        <button class="btn btn-sm btn-outline-primary" id="toggleFilters">
                            <i class="fas fa-filter"></i>
                        </button>
                    </div>
                    <div class="card-body" id="filtersContainer">
                        <form id="filterForm">
                            <div class="mb-3">
                                <label for="searchTerm" class="form-label">Search</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="searchTerm" placeholder="Search in title or description">
                                    <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="filterCategory" class="form-label">Category</label>
                                <select class="form-select" id="filterCategory">
                                    <option value="">All Categories</option>
                                    <option value="documents">Documents</option>
                                    <option value="images">Images</option>
                                    <option value="videos">Videos</option>
                                    <option value="presentations">Presentations</option>
                                    <option value="spreadsheets">Spreadsheets</option>
                                    <option value="archives">Archives</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="sortBy" class="form-label">Sort By</label>
                                <select class="form-select" id="sortBy">
                                    <option value="upload_date">Upload Date</option>
                                    <option value="title">Title</option>
                                    <option value="file_size">File Size</option>
                                    <option value="download_count">Download Count</option>
                                    <option value="display_order">Display Order</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="sortOrder" class="form-label">Sort Order</label>
                                <select class="form-select" id="sortOrder">
                                    <option value="DESC">Descending</option>
                                    <option value="ASC">Ascending</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="filterFeatured">
                                    <label class="form-check-label" for="filterFeatured">Featured files only</label>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="filterPublic">
                                    <label class="form-check-label" for="filterPublic">Public files only</label>
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="button" class="btn btn-outline-primary" id="applyFilters">Apply Filters</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-8">
                <!-- Files List Card -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Uploaded Files</span>
                        <div>
                            <button class="btn btn-sm btn-outline-secondary me-2" id="refreshFilesList">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                            <select class="form-select form-select-sm d-inline-block w-auto" id="filesPerPage">
                                <option value="10">10 per page</option>
                                <option value="25">25 per page</option>
                                <option value="50">50 per page</option>
                                <option value="100">100 per page</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="filesList">
                            <!-- Files will be loaded here -->
                            <div class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2">Loading files...</p>
                            </div>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div id="fileStats">
                                <span id="totalFiles">0</span> files found
                            </div>
                            <nav aria-label="Files pagination">
                                <ul class="pagination" id="pagination">
                                    <!-- Pagination links will be added here -->
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- File Preview Modal -->
    <div class="modal fade" id="filePreviewModal" tabindex="-1" aria-labelledby="filePreviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="filePreviewModalLabel">File Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="filePreviewContent">
                    <!-- Preview content will be loaded here -->
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn btn-primary" id="downloadBtn" target="_blank">Download</a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this file? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    // DOM Elements
    const fileUploadForm = document.getElementById('fileUploadForm');
    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('file');
    const browseBtn = document.getElementById('browseBtn');
    const uploadProgress = document.getElementById('uploadProgress');
    const progressBar = document.getElementById('progressBar');
    const uploadPercentage = document.getElementById('uploadPercentage');
    const selectedFileName = document.getElementById('selectedFileName');
    const uploadBtn = document.getElementById('uploadBtn');
    const uploadBtnText = document.getElementById('uploadBtnText');
    const uploadSpinner = document.getElementById('uploadSpinner');
    const alertsContainer = document.getElementById('alertsContainer');
    const filesList = document.getElementById('filesList');
    const totalFiles = document.getElementById('totalFiles');
    const pagination = document.getElementById('pagination');
    const filesPerPage = document.getElementById('filesPerPage');
    const searchBtn = document.getElementById('searchBtn');
    const refreshFilesList = document.getElementById('refreshFilesList');
    const applyFilters = document.getElementById('applyFilters');
    const toggleUploadForm = document.getElementById('toggleUploadForm');
    const uploadFormContainer = document.getElementById('uploadFormContainer');
    const toggleFilters = document.getElementById('toggleFilters');
    const filtersContainer = document.getElementById('filtersContainer');
    const filePreviewModal = new bootstrap.Modal(document.getElementById('filePreviewModal'));
    const filePreviewContent = document.getElementById('filePreviewContent');
    const filePreviewModalLabel = document.getElementById('filePreviewModalLabel');
    const downloadBtn = document.getElementById('downloadBtn');
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const confirmDelete = document.getElementById('confirmDelete');
    
    // Pagination state
    let currentPage = 1;
    let totalPages = 1;
    let itemsPerPage = parseInt(filesPerPage.value);
    
    // Filters state
    let filters = {
        search: '',
        category: '',
        featured: false,
        is_public: false,
        order_by: 'upload_date',
        order_direction: 'DESC',
        limit: itemsPerPage,
        offset: 0
    };
    
    // File being deleted
    let fileToDelete = null;
    
    // Initialize tooltips
    const initTooltips = () => {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    };
    
    // Show alert message
    const showAlert = (message, type = 'success', autoDismiss = true) => {
        const alertId = 'alert-' + new Date().getTime();
        const alertHtml = `
            <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        alertsContainer.innerHTML = alertHtml + alertsContainer.innerHTML;
        
        if (autoDismiss) {
            setTimeout(() => {
                const alert = document.getElementById(alertId);
                if (alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            }, 5000);
        }
    };
    
    // Handle file selection
    const handleFileSelect = (e) => {
        const file = e.target.files[0];
        if (file) {
            selectedFileName.textContent = file.name;
            // Show file name in dropzone
            const fileNameDisplay = document.createElement('p');
            fileNameDisplay.className = 'mt-2 small text-primary';
            fileNameDisplay.textContent = `Selected: ${file.name} (${formatFileSize(file.size)})`;
            const existingFileNameDisplay = dropzone.querySelector('.text-primary');
            if (existingFileNameDisplay) {
                existingFileNameDisplay.remove();
            }
            dropzone.querySelector('div').appendChild(fileNameDisplay);
        }
    };
    
    // Format file size
    const formatFileSize = (bytes) => {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    };
    
    // Upload file
    fileUploadForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData(fileUploadForm);
        const file = fileInput.files[0];
        
        if (!file) {
            showAlert('Please select a file', 'danger');
            return;
        }
        
        if (!formData.get('title')) {
            showAlert('File title is required', 'danger');
            return;
        }
        
        // Show progress bar
        uploadProgress.classList.remove('d-none');
        progressBar.style.width = '0%';
        uploadPercentage.textContent = '0%';
        
        // Disable upload button
        uploadBtn.disabled = true;
        uploadBtnText.textContent = 'Uploading...';
        uploadSpinner.classList.remove('d-none');
        
        try {
            const response = await fetch('upload.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            
            if (data.status === 'success') {
                showAlert('File uploaded successfully', 'success');
                fileUploadForm.reset();
                
                // Remove the file name display
                const fileNameDisplay = dropzone.querySelector('.text-primary');
                if (fileNameDisplay) {
                    fileNameDisplay.remove();
                }
                
                // Refresh the files list
                loadFiles();
            } else {
                showAlert(data.message || 'Upload failed', 'danger');
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('An error occurred during upload', 'danger');
        } finally {
            // Hide progress bar
            uploadProgress.classList.add('d-none');
            progressBar.style.width = '0%';
            uploadPercentage.textContent = '0%';
            
            // Re-enable upload button
            uploadBtn.disabled = false;
            uploadBtnText.textContent = 'Upload File';
            uploadSpinner.classList.add('d-none');
        }
    });
    
    // Load files from the server
    const loadFiles = () => {
        filesList.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading files...</p>
            </div>
        `;
        
        // Set offset based on current page
        filters.offset = (currentPage - 1) * filters.limit;
        
        // Build query string
        const queryParams = new URLSearchParams();
        for (const key in filters) {
            if (filters[key] !== '' && filters[key] !== false) {
                queryParams.append(key, filters[key]);
            }
        }
        
        // Fetch files
        fetch(`get_files.php?${queryParams.toString()}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success' && data.data) {
                    renderFiles(data.data.files);
                    updatePagination(data.data.total);
                    totalFiles.textContent = data.data.total;
                } else {
                    showNoFiles();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Failed to load files', 'danger');
                showNoFiles();
            });
    };
    
    // Render files to the file list
    const renderFiles = (files) => {
        if (!files || files.length === 0) {
            showNoFiles();
            return;
        }
        
        const gridView = `
            <div class="row">
                ${files.map(file => `
                    <div class="col-md-4 col-sm-6 mb-4">
                        <div class="card file-card h-100">
                            <div class="file-actions">
                                <button class="btn btn-action" onclick="previewFile(${file.id}, '${file.file_path}', '${file.file_type}', '${file.title}')" data-bs-toggle="tooltip" title="Preview">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <a href="${file.download_url}" class="btn btn-action" data-bs-toggle="tooltip" title="Download">
                                    <i class="fas fa-download"></i>
                                </a>
                                <button class="btn btn-action text-danger" onclick="confirmFileDelete(${file.id})" data-bs-toggle="tooltip" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <div class="text-center pt-3">
                                ${getFileTypeIcon(file.file_type, file.extension)}
                            </div>
                            <div class="file-info">
                                <h5 class="file-title">${file.title}</h5>
                                <div class="file-meta d-flex justify-content-between">
                                    <span>${file.formatted_size}</span>
                                    <span>${new Date(file.upload_date).toLocaleDateString()}</span>
                                </div>
                                <div class="mt-2">
                                    ${file.category ? `<span class="badge category-badge">${file.category}</span>` : ''}
                                    ${file.featured ? '<span class="badge bg-warning text-dark">Featured</span>' : ''}
                                    ${file.is_public ? '<span class="badge bg-success">Public</span>' : '<span class="badge bg-secondary">Private</span>'}
                                </div>
                                ${file.tags ? `
                                <div class="mt-2">
                                    ${file.tags.split(',').map(tag => `
                                        <span class="tag-badge">${tag.trim()}</span>
                                    `).join('')}
                                </div>
                                ` : ''}
                                <div class="mt-2">
                                    <small class="text-muted">Downloads: ${file.download_count}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
        
        filesList.innerHTML = gridView;
        initTooltips();
    };
    
    // Show "no files" message
    const showNoFiles = () => {
        filesList.innerHTML = `
            <div class="file-empty-state">
                <i class="fas fa-file-alt"></i>
                <h4>No files found</h4>
                <p>Upload your first file or change your filters to see files.</p>
            </div>
        `;
    };
    
    // Get file type icon
    const getFileTypeIcon = (fileType, extension) => {
        let iconClass = 'fa-file';
        let colorClass = 'text-secondary';
        
        if (fileType.includes('image/')) {
            iconClass = 'fa-file-image';
            colorClass = 'text-primary';
        } else if (fileType.includes('pdf')) {
            iconClass = 'fa-file-pdf';
            colorClass = 'text-danger';
        } else if (fileType.includes('word') || extension === 'doc' || extension === 'docx') {
            iconClass = 'fa-file-word';
            colorClass = 'text-primary';
        } else if (fileType.includes('excel') || extension === 'xls' || extension === 'xlsx') {
            iconClass = 'fa-file-excel';
            colorClass = 'text-success';
        } else if (fileType.includes('powerpoint') || extension === 'ppt' || extension === 'pptx') {
            iconClass = 'fa-file-powerpoint';
            colorClass = 'text-warning';
        } else if (fileType.includes('video/')) {
            iconClass = 'fa-file-video';
            colorClass = 'text-danger';
        } else if (fileType.includes('text/')) {
            iconClass = 'fa-file-alt';
            colorClass = 'text-info';
        } else if (fileType.includes('zip') || fileType.includes('rar') || fileType.includes('compressed')) {
            iconClass = 'fa-file-archive';
            colorClass = 'text-warning';
        }
        
        return `<i class="fas ${iconClass} fa-3x ${colorClass}"></i>`;
    };
    
    // Update pagination
    const updatePagination = (total) => {
        totalPages = Math.ceil(total / filters.limit);
        
        // Generate pagination links
        let paginationHtml = '';
        
        // Previous button
        paginationHtml += `
            <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="changePage(${currentPage - 1}); return false;">Previous</a>
            </li>
        `;
        
        // Page numbers
        const startPage = Math.max(1, currentPage - 2);
        const endPage = Math.min(totalPages, startPage + 4);
        
        for (let i = startPage; i <= endPage; i++) {
            paginationHtml += `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="changePage(${i}); return false;">${i}</a>
                </li>
            `;
        }
        
        // Next button
        paginationHtml += `
            <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="changePage(${currentPage + 1}); return false;">Next</a>
            </li>
        `;
        
        pagination.innerHTML = paginationHtml;
    };
    
    // Change page
    const changePage = (page) => {
        if (page < 1 || page > totalPages || page === currentPage) {
            return;
        }
        
        currentPage = page;
        loadFiles();
    };
    
    // Initialize filters from form values
    const initFilters = () => {
        filters.search = document.getElementById('searchTerm').value.trim();
        filters.category = document.getElementById('filterCategory').value;
        filters.featured = document.getElementById('filterFeatured').checked;
        filters.is_public = document.getElementById('filterPublic').checked;
        filters.order_by = document.getElementById('sortBy').value;
        filters.order_direction = document.getElementById('sortOrder').value;
        
        // Reset pagination
        currentPage = 1;
        filters.offset = 0;
    };
    
    // Preview file
    const previewFile = (id, filePath, fileType, title) => {
        filePreviewModalLabel.textContent = title;
        downloadBtn.href = `download.php?id=${id}`;
        
        let previewHtml = '';
        
        if (fileType.includes('image/')) {
            previewHtml = `<img src="${filePath}" class="img-fluid" alt="${title}">`;
        } else if (fileType.includes('pdf')) {
            previewHtml = `<iframe src="${filePath}" width="100%" height="500" frameborder="0"></iframe>`;
        } else if (fileType.includes('video/')) {
            previewHtml = `
                <video controls width="100%">
                    <source src="${filePath}" type="${fileType}">
                    Your browser does not support video playback.
                </video>
            `;
        } else {
            previewHtml = `
                <div class="text-center py-5">
                    ${getFileTypeIcon(fileType, filePath.split('.').pop())}
                    <h4 class="mt-3">Preview not available</h4>
                    <p>This file type cannot be previewed. Please download the file to view its contents.</p>
                </div>
            `;
        }
        
        filePreviewContent.innerHTML = previewHtml;
        filePreviewModal.show();
    };
    
    // Confirm file deletion
    const confirmFileDelete = (id) => {
        fileToDelete = id;
        deleteModal.show();
    };
    
    // Delete file
    const deleteFile = () => {
        if (!fileToDelete) return;
        
        fetch(`delete_file.php?id=${fileToDelete}`, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showAlert('File deleted successfully');
                loadFiles();
            } else {
                showAlert(data.message || 'Failed to delete file', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An error occurred while deleting the file', 'danger');
        })
        .finally(() => {
            deleteModal.hide();
            fileToDelete = null;
        });
    };
    
    // Event listeners
    
    // File input change
    fileInput.addEventListener('change', handleFileSelect);
    
    // Browse button click
    browseBtn.addEventListener('click', () => fileInput.click());
    
    // Drag and drop
    dropzone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropzone.classList.add('dropzone-active');
    });
    
    dropzone.addEventListener('dragleave', () => {
        dropzone.classList.remove('dropzone-active');
    });
    
    dropzone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropzone.classList.remove('dropzone-active');
        
        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            handleFileSelect({ target: { files: e.dataTransfer.files } });
        }
    });
    
    // Apply filters
    applyFilters.addEventListener('click', () => {
        initFilters();
        loadFiles();
    });
    
    // Search button click
    searchBtn.addEventListener('click', () => {
        initFilters();
        loadFiles();
    });
    
    // Enter key in search box
    document.getElementById('searchTerm').addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            initFilters();
            loadFiles();
        }
    });
    
    // Change items per page
    filesPerPage.addEventListener('change', () => {
        filters.limit = parseInt(filesPerPage.value);
        currentPage = 1;
        loadFiles();
    });
    
    // Refresh button click
    refreshFilesList.addEventListener('click', () => {
        loadFiles();
    });
    
    // Toggle upload form
    toggleUploadForm.addEventListener('click', () => {
        if (uploadFormContainer.style.display === 'none') {
            uploadFormContainer.style.display = 'block';
            toggleUploadForm.innerHTML = '<i class="fas fa-chevron-up"></i>';
        } else {
            uploadFormContainer.style.display = 'none';
            toggleUploadForm.innerHTML = '<i class="fas fa-chevron-down"></i>';
        }
    });
    
    // Toggle filters
    toggleFilters.addEventListener('click', () => {
        if (filtersContainer.style.display === 'none') {
            filtersContainer.style.display = 'block';
            toggleFilters.innerHTML = '<i class="fas fa-times"></i>';
        } else {
            filtersContainer.style.display = 'none';
            toggleFilters.innerHTML = '<i class="fas fa-filter"></i>';
        }
    });
    
    // Confirm delete button
    confirmDelete.addEventListener('click', deleteFile);
    
    // Make functions available globally
    window.changePage = changePage;
    window.previewFile = previewFile;
    window.confirmFileDelete = confirmFileDelete;
    
    // Initialize
    document.addEventListener('DOMContentLoaded', () => {
        // Load files
        loadFiles();
    });
    </script>
</body>
</html>