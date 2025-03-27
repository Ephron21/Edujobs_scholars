<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set content type to JSON
header('Content-Type: application/json');

// Get the raw POST data
$raw_data = file_get_contents('php://input');

// Log the request
file_put_contents('proxy_log.txt', date('Y-m-d H:i:s') . " - Request received\n", FILE_APPEND);
file_put_contents('proxy_log.txt', "Raw data: " . $raw_data . "\n", FILE_APPEND);

// Forward the request to the actual API endpoint
$ch = curl_init('http://' . $_SERVER['HTTP_HOST'] . '/Edujobs_scholars/backend/api/students_create.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $raw_data);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($raw_data)
]);

// Execute the request
$response = curl_exec($ch);
$error = curl_error($ch);
$info = curl_getinfo($ch);
curl_close($ch);

// Log the response
file_put_contents('proxy_log.txt', "Response status: " . $info['http_code'] . "\n", FILE_APPEND);
file_put_contents('proxy_log.txt', "Response: " . $response . "\n", FILE_APPEND);
if ($error) {
    file_put_contents('proxy_log.txt', "Error: " . $error . "\n", FILE_APPEND);
}

// If there was an error in the cURL request, return an error
if ($error) {
    echo json_encode([
        'success' => false,
        'message' => 'Error forwarding request: ' . $error,
        'curl_info' => $info
    ]);
    exit;
}

// Return the response from the API
echo $response;
?> 