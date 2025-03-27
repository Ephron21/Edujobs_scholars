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

// Get the JSON body
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || !isset($data['enable'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}

// Get the maintenance mode status
$enable = (bool)$data['enable'];

// In a real application, you would save this to a database or configuration file
// For now, we'll just simulate a successful update

// Example of updating a configuration file
$config_file = 'maintenance_mode.txt';
file_put_contents($config_file, $enable ? '1' : '0');

// Log the change
$log_file = 'maintenance_mode.log';
$log_message = date('Y-m-d H:i:s') . ' - User ID: ' . $_SESSION["id"] . ' - Maintenance Mode: ' . ($enable ? 'Enabled' : 'Disabled') . PHP_EOL;
file_put_contents($log_file, $log_message, FILE_APPEND);

// Return success response
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => 'Maintenance mode ' . ($enable ? 'enabled' : 'disabled') . ' successfully',
    'enabled' => $enable
]);
?> 