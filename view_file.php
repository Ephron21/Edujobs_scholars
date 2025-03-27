<?php
// Initialize the session
session_start();

// Check if the user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("HTTP/1.1 403 Forbidden");
    echo "Access denied. Please log in.";
    exit;
}

// Get file path from the query string
$file = isset($_GET['file']) ? $_GET['file'] : '';

// Basic security check - only allow accessing files in the uploads directory
if (empty($file) || strpos($file, 'uploads/') !== 0 || strpos($file, '..') !== false) {
    header("HTTP/1.1 403 Forbidden");
    echo "Invalid file path.";
    exit;
}

// Check if file exists
if (!file_exists($file)) {
    header("HTTP/1.1 404 Not Found");
    echo "File not found.";
    exit;
}

// Get file extension
$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

// Set content type based on file extension
switch ($ext) {
    case 'pdf':
        $contentType = 'application/pdf';
        break;
    case 'jpg':
    case 'jpeg':
        $contentType = 'image/jpeg';
        break;
    case 'png':
        $contentType = 'image/png';
        break;
    default:
        $contentType = 'application/octet-stream';
}

// Set headers
header('Content-Type: ' . $contentType);
header('Content-Disposition: inline; filename="' . basename($file) . '"');
header('Content-Length: ' . filesize($file));
header('Cache-Control: max-age=86400');

// Output file
readfile($file);
exit;