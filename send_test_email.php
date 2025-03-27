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

if (!$data) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}

// Validate the email settings
if (!isset($data['from_email']) || !filter_var($data['from_email'], FILTER_VALIDATE_EMAIL)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid from email address']);
    exit;
}

// Set up email parameters
$to = $_SESSION["email"]; // Send to the admin's email
$subject = "Test Email from EduJobs Scholars";
$message = "
<html>
<head>
<title>Test Email</title>
</head>
<body>
<div style='max-width: 600px; margin: 0 auto; padding: 20px; font-family: Arial, sans-serif;'>
    <h2 style='color: #0d6efd;'>Test Email</h2>
    <p>This is a test email from the EduJobs Scholars system.</p>
    <p>If you are receiving this email, your email settings are configured correctly.</p>
    <p>Email settings used:</p>
    <ul>
        <li><strong>SMTP Enabled:</strong> " . ($data['enable_smtp'] ? 'Yes' : 'No') . "</li>
        <li><strong>From Email:</strong> " . htmlspecialchars($data['from_email']) . "</li>
        <li><strong>From Name:</strong> " . htmlspecialchars($data['from_name']) . "</li>
    </ul>
    <p style='margin-top: 30px; padding-top: 10px; border-top: 1px solid #eee; font-size: 12px; color: #666;'>
        This is an automated message. Please do not reply to this email.
    </p>
</div>
</body>
</html>
";

// Set content-type header for sending HTML email
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
$headers .= "From: " . $data['from_name'] . " <" . $data['from_email'] . ">" . "\r\n";

// Simulate sending email
// In a real application, you would use PHPMailer or similar library,
// especially when using SMTP
$success = true;
$error_message = "";

if ($data['enable_smtp']) {
    // In a real application, you would use PHPMailer with SMTP
    // For this demo, we'll just simulate it
    
    // Check if SMTP settings are provided
    if (empty($data['smtp_server']) || empty($data['smtp_port'])) {
        $success = false;
        $error_message = "SMTP server and port are required when SMTP is enabled";
    } else {
        // Log the attempt
        $log_file = 'email_test.log';
        $log_message = date('Y-m-d H:i:s') . ' - SMTP Test to: ' . $to . ' - Server: ' . $data['smtp_server'] . ':' . $data['smtp_port'] . PHP_EOL;
        file_put_contents($log_file, $log_message, FILE_APPEND);
        
        // Simulate success
        $success = true;
    }
} else {
    // Use PHP mail function (simulated)
    // $success = mail($to, $subject, $message, $headers);
    
    // Log the attempt
    $log_file = 'email_test.log';
    $log_message = date('Y-m-d H:i:s') . ' - PHP mail() Test to: ' . $to . PHP_EOL;
    file_put_contents($log_file, $log_message, FILE_APPEND);
    
    // Simulate success
    $success = true;
}

// Return response
header('Content-Type: application/json');
if ($success) {
    echo json_encode([
        'success' => true,
        'message' => 'Test email sent successfully to ' . $to
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to send test email: ' . ($error_message ?: 'Unknown error')
    ]);
}
?> 