<?php
session_start();

// Check if user is admin
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || 
    !isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] !== true) {
    http_response_code(403);
    die("Access Denied");
}

// Include database connection
require_once 'includes/db_connection.php';

// File upload handling
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // File upload configuration
    $uploadDir = 'uploads/';
    
    // Create uploads directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Validate file
    if (!isset($_FILES['file_upload']) || $_FILES['file_upload']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['upload_error'] = "File upload failed. Please try again.";
        header("Location: admin_dashboard.php");
        exit;
    }

    $file = $_FILES['file_upload'];
    
    // Validate file size (max 50MB)
    $maxFileSize = 50 * 1024 * 1024; // 50MB
    if ($file['size'] > $maxFileSize) {
        $_SESSION['upload_error'] = "File is too large. Maximum file size is 50MB.";
        header("Location: admin_dashboard.php");
        exit;
    }

    // Validate file type
    $allowedTypes = [
        'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'image/jpeg', 'image/png', 'image/gif', 'image/webp',
        'video/mp4', 'video/mpeg', 'video/quicktime',
        'application/zip', 'text/plain'
    ];
    
    if (!in_array($file['type'], $allowedTypes)) {
        $_SESSION['upload_error'] = "Invalid file type. Allowed types: PDF, DOCX, JPG, PNG, GIF, MP4, ZIP, TXT.";
        header("Location: admin_dashboard.php");
        exit;
    }

    // Generate unique filename
    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = uniqid('upload_', true) . '.' . $fileExtension;
    $uploadPath = $uploadDir . $fileName;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        $_SESSION['upload_error'] = "Failed to move uploaded file.";
        header("Location: admin_dashboard.php");
        exit;
    }

    // Prepare database insert
    $fileTitle = mysqli_real_escape_string($conn, $_POST['file_title']);
    $fileDescription = mysqli_real_escape_string($conn, $_POST['file_description'] ?? '');
    $fileCategory = mysqli_real_escape_string($conn, $_POST['file_category']);
    $isPublic = isset($_POST['is_public']) ? 1 : 0;
    $uploadedBy = $_SESSION['username'];

    // Insert file record into database
    $insertQuery = "INSERT INTO uploaded_files (
        file_name, 
        file_path, 
        file_title, 
        file_description, 
        file_category, 
        is_public, 
        uploaded_by, 
        upload_date
    ) VALUES (
        '$fileName', 
        '$uploadPath', 
        '$fileTitle', 
        '$fileDescription', 
        '$fileCategory', 
        $isPublic, 
        '$uploadedBy', 
        NOW()
    )";

    if (mysqli_query($conn, $insertQuery)) {
        $_SESSION['upload_success'] = "File uploaded successfully!";
    } else {
        // Delete file if database insert fails
        unlink($uploadPath);
        $_SESSION['upload_error'] = "Database error: " . mysqli_error($conn);
    }

    header("Location: admin_dashboard.php");
    exit;
}
?>