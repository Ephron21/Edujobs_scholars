<?php
// Initialize the session
session_start();
// Check if the user is logged in, if not then respond with error
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}
// Check if the user is an admin
if (!isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Process form data
$theme_color = isset($_POST['theme_color']) ? $_POST['theme_color'] : '#0d6efd';
$secondary_color = isset($_POST['secondary_color']) ? $_POST['secondary_color'] : '#6c757d';
$enable_dark_mode = isset($_POST['enable_dark_mode']) ? filter_var($_POST['enable_dark_mode'], FILTER_VALIDATE_BOOLEAN) : true;

// Handle file uploads
$logo_path = '';
$favicon_path = '';

// Process logo upload
if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
    $allowed = ["png" => "image/png", "jpg" => "image/jpeg", "jpeg" => "image/jpeg", "svg" => "image/svg+xml"];
    $filename = $_FILES["logo"]["name"];
    $filetype = $_FILES["logo"]["type"];
    $filesize = $_FILES["logo"]["size"];
    
    // Validate file extension
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    if (!array_key_exists($ext, $allowed)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid logo file format']);
        exit;
    }
    
    // Validate file size - 5MB maximum
    $maxsize = 5 * 1024 * 1024;
    if ($filesize > $maxsize) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Logo file size exceeds 5MB limit']);
        exit;
    }
    
    // Create upload directory if it doesn't exist
    $target_dir = "uploads/appearance/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    // Generate a unique filename
    $new_filename = 'logo_' . time() . '.' . $ext;
    $target_file = $target_dir . $new_filename;
    
    // In a real application, move the uploaded file
    // move_uploaded_file($_FILES["logo"]["tmp_name"], $target_file);
    
    $logo_path = $target_file;
}

// Process favicon upload
if (isset($_FILES['favicon']) && $_FILES['favicon']['error'] == 0) {
    $allowed = ["png" => "image/png", "ico" => "image/x-icon"];
    $filename = $_FILES["favicon"]["name"];
    $filetype = $_FILES["favicon"]["type"];
    $filesize = $_FILES["favicon"]["size"];
    
    // Validate file extension
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    if (!array_key_exists($ext, $allowed)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid favicon file format']);
        exit;
    }
    
    // Validate file size - 1MB maximum
    $maxsize = 1 * 1024 * 1024;
    if ($filesize > $maxsize) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Favicon file size exceeds 1MB limit']);
        exit;
    }
    
    // Create upload directory if it doesn't exist
    $target_dir = "uploads/appearance/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    // Generate a unique filename
    $new_filename = 'favicon_' . time() . '.' . $ext;
    $target_file = $target_dir . $new_filename;
    
    // In a real application, move the uploaded file
    // move_uploaded_file($_FILES["favicon"]["tmp_name"], $target_file);
    
    $favicon_path = $target_file;
}

// In a real application, you would save these settings to a database or configuration file
// For now, we'll just simulate a successful update

// Save settings to a file for demonstration
$appearance_settings = [
    'theme_color' => $theme_color,
    'secondary_color' => $secondary_color,
    'enable_dark_mode' => $enable_dark_mode,
    'logo_path' => $logo_path,
    'favicon_path' => $favicon_path,
    'updated_at' => date('Y-m-d H:i:s'),
    'updated_by' => $_SESSION["id"]
];

// Save to a JSON file
$settings_file = 'appearance_settings.json';
file_put_contents($settings_file, json_encode($appearance_settings, JSON_PRETTY_PRINT));

// Log the update
$log_file = 'appearance_settings.log';
$log_message = date('Y-m-d H:i:s') . ' - User ID: ' . $_SESSION["id"] . ' - Updated appearance settings' . PHP_EOL;
file_put_contents($log_file, $log_message, FILE_APPEND);

// Return success response
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => 'Appearance settings updated successfully',
    'settings' => $appearance_settings
]);
?> 