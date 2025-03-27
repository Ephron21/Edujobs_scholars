<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Allow from any origin
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         
    
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    exit(0);
}

// Set response headers
header('Content-Type: application/json');

// Include database connection
require_once '../db_connection.php';

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Only POST requests are allowed. You sent: ' . $_SERVER['REQUEST_METHOD'],
        'server_info' => [
            'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'],
            'CONTENT_TYPE' => $_SERVER['CONTENT_TYPE'] ?? 'Not set'
        ]
    ]);
    exit;
}

// Get the posted data
$raw_data = file_get_contents('php://input');
if (empty($raw_data)) {
    echo json_encode([
        'success' => false,
        'message' => 'No data received. Please submit the form data.'
    ]);
    exit;
}

try {
    $data = json_decode($raw_data, true);
    
    // Check if JSON decode failed
    if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid JSON data: ' . json_last_error_msg(),
            'received_data' => $raw_data
        ]);
        exit;
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error parsing JSON data: ' . $e->getMessage(),
        'received_data' => $raw_data
    ]);
    exit;
}

// Check if required data is present
if (!$data || !isset($data['first_name']) || !isset($data['last_name']) || 
    !isset($data['email']) || !isset($data['grade_level']) || !isset($data['reg_number']) ||
    !isset($data['password'])) {
    
    echo json_encode([
        'success' => false,
        'message' => 'Required fields are missing. Please provide all required information.',
        'received_data' => $data
    ]);
    exit;
}

// Sanitize and validate inputs
$first_name = filter_var(trim($data['first_name']), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$last_name = filter_var(trim($data['last_name']), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$reg_number = filter_var(trim($data['reg_number']), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$pin = filter_var(trim($data['password']), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$dob = isset($data['dob']) ? filter_var(trim($data['dob']), FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null;
$gender = isset($data['gender']) ? filter_var(trim($data['gender']), FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null;
$email = filter_var(trim($data['email']), FILTER_VALIDATE_EMAIL);
$phone = isset($data['phone']) ? filter_var(trim($data['phone']), FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null;
$address = isset($data['address']) ? filter_var(trim($data['address']), FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null;
$institution = isset($data['institution']) ? filter_var(trim($data['institution']), FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null;
$grade_level = (int)trim($data['grade_level']);
$admission_date = isset($data['admission_date']) ? filter_var(trim($data['admission_date']), FILTER_SANITIZE_FULL_SPECIAL_CHARS) : date('Y-m-d');
$status = isset($data['status']) ? filter_var(trim($data['status']), FILTER_SANITIZE_FULL_SPECIAL_CHARS) : 'Pending';
$national_id = isset($data['national_id']) ? filter_var(trim($data['national_id']), FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null;
$parent_name = isset($data['parent_name']) ? filter_var(trim($data['parent_name']), FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null;
$parent_phone = isset($data['parent_phone']) ? filter_var(trim($data['parent_phone']), FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null;
$parent_email = isset($data['parent_email']) ? filter_var(trim($data['parent_email']), FILTER_VALIDATE_EMAIL) : null;

// Email validation
if (!$email) {
    echo json_encode([
        'success' => false,
        'message' => 'Please provide a valid email address.'
    ]);
    exit;
}

// Grade level validation (1-4)
if ($grade_level < 1 || $grade_level > 4) {
    echo json_encode([
        'success' => false,
        'message' => 'Grade level must be between 1 and 4.'
    ]);
    exit;
}

// Check if email already exists
try {
    $check_stmt = $conn->prepare("SELECT id FROM students WHERE email = ?");
    $check_stmt->execute([$email]);
    
    if ($check_stmt->rowCount() > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'This email is already registered. Please use a different email.'
        ]);
        exit;
    }
    
    // Check if registration number exists
    $check_reg_stmt = $conn->prepare("SELECT id FROM students WHERE reg_number = ?");
    $check_reg_stmt->execute([$reg_number]);
    
    if ($check_reg_stmt->rowCount() > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'This registration number is already in use. Please use a different registration number.'
        ]);
        exit;
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error checking existing records: ' . $e->getMessage()
    ]);
    exit;
}

// Insert the data
try {
    $stmt = $conn->prepare("
        INSERT INTO students 
        (first_name, last_name, reg_number, pin, dob, gender, email, phone, address, 
        institution, grade_level, admission_date, status, national_id, 
        parent_name, parent_phone, parent_email, created_at, updated_at) 
        VALUES 
        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    ");
    
    $stmt->execute([
        $first_name, $last_name, $reg_number, $pin, $dob, $gender, $email, $phone, $address,
        $institution, $grade_level, $admission_date, $status, $national_id,
        $parent_name, $parent_phone, $parent_email
    ]);
    
    if ($stmt->rowCount()) {
        $student_id = $conn->lastInsertId();
        echo json_encode([
            'success' => true,
            'message' => 'Student registered successfully!',
            'student_id' => $student_id
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to register student. Please try again.'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error registering student: ' . $e->getMessage(),
        'query_error' => $e->getTraceAsString()
    ]);
}
?> 