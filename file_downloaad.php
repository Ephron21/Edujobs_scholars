<?php
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    sendJsonResponse('error', 'Unauthorized access', 403);
}

// Get file ID from request
$fileId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$fileId) {
    sendJsonResponse('error', 'Invalid file ID', 400);
}

try {
    // Prepare statement to fetch file details
    $stmt = $conn->prepare("SELECT * FROM file_uploads WHERE id = :id");
    $stmt->execute([':id' => $fileId]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$file) {
        sendJsonResponse('error', 'File not found', 404);
    }

    // Check file access permissions
    if (!$file['is_public'] && !isAdminLoggedIn()) {
        sendJsonResponse('error', 'You do not have permission to download this file', 403);
    }

    // Check file exists
    if (!file_exists($file['file_path'])) {
        sendJsonResponse('error', 'File does not exist on server', 404);
    }

    // Download file
    header('Content-Type: ' . $file['file_type']);
    header('Content-Disposition: attachment; filename="' . $file['original_name'] . '"');
    header('Content-Length: ' . filesize($file['file_path']));
    readfile($file['file_path']);
    exit;

} catch (Exception $e) {
    sendJsonResponse('error', 'Download failed: ' . $e->getMessage(), 500);
}
?>