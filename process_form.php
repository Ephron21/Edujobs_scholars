<?php
// Initialize the session
session_start();
// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Authentication required']);
    exit;
}
// Check if the user is an admin
if (!isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] !== true) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Admin privileges required']);
    exit;
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to redirect back with error message
function redirect_with_error($message) {
    header('Location: form_alt.html?error=' . urlencode($message));
    exit;
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with_error('Invalid request method. Please use the form to submit data.');
}

// Validate required fields
$required_fields = ['first_name', 'last_name', 'email', 'grade_level', 'reg_number', 'password'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        redirect_with_error('Required field ' . $field . ' is missing.');
    }
}

// Create an array with all the submitted data
$data = [
    'first_name' => $_POST['first_name'],
    'last_name' => $_POST['last_name'],
    'email' => $_POST['email'],
    'grade_level' => $_POST['grade_level'],
    'reg_number' => $_POST['reg_number'],
    'password' => $_POST['password']
];

// Add optional fields if present
$optional_fields = ['phone', 'institution'];
foreach ($optional_fields as $field) {
    if (isset($_POST[$field]) && !empty($_POST[$field])) {
        $data[$field] = $_POST[$field];
    }
}

// Convert data to JSON
$json_data = json_encode($data);

// Initialize cURL session
$ch = curl_init('backend/api/students_create.php');

// Set cURL options
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($json_data)
]);

// Execute the cURL request
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Check for cURL errors
if (curl_errno($ch)) {
    redirect_with_error('Error sending data: ' . curl_error($ch));
}

curl_close($ch);

// Decode the response
$result = json_decode($response, true);

// Check if the API request was successful
if ($http_code == 200 && isset($result['success']) && $result['success'] === true) {
    // Redirect to success page
    header('Location: form_alt.html?success=true');
} else {
    // Extract error message
    $error_message = isset($result['message']) ? $result['message'] : 'Unknown error occurred';
    redirect_with_error($error_message);
}
?> 