<?php
// Initialize the session
session_start();
// Check if the user is logged in, if not then redirect to login page
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

if (!$data) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}

// Validate the data
if (!isset($data['email']) || !isset($data['browser'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Missing required data']);
    exit;
}

// In a real application, you would save this to a database
// For now, we'll just simulate a successful update

// Database connection would go here
// $conn = new mysqli($servername, $username, $password, $dbname);

// UPDATE query would go here
// $stmt = $conn->prepare("UPDATE user_preferences SET email_notifications = ?, browser_notifications = ? WHERE user_id = ?");
// $stmt->bind_param("ssi", json_encode($data['email']), json_encode($data['browser']), $_SESSION["id"]);
// $stmt->execute();

// Log the update for demonstration
$log_file = 'notification_settings.log';
$log_message = date('Y-m-d H:i:s') . ' - User ID: ' . $_SESSION["id"] . ' - Settings: ' . $json . PHP_EOL;
file_put_contents($log_file, $log_message, FILE_APPEND);

// Return success response
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => 'Notification settings updated successfully',
    'data' => $data
]);
?> 