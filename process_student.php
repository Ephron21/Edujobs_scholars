<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once 'config.php';

// Function to sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'redirect' => 'form_alt.html'  // Default redirect to the alternative form
];

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize form data
    $first_name = isset($_POST['first_name']) ? sanitize_input($_POST['first_name']) : '';
    $last_name = isset($_POST['last_name']) ? sanitize_input($_POST['last_name']) : '';
    $reg_number = isset($_POST['reg_number']) ? sanitize_input($_POST['reg_number']) : null;
    $pin = isset($_POST['pin']) ? sanitize_input($_POST['pin']) : null;
    $dob = isset($_POST['dob']) ? sanitize_input($_POST['dob']) : '';
    $gender = isset($_POST['gender']) ? sanitize_input($_POST['gender']) : '';
    $email = isset($_POST['email']) ? filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) : '';
    $phone = isset($_POST['phone']) ? sanitize_input($_POST['phone']) : null;
    $address = isset($_POST['address']) ? sanitize_input($_POST['address']) : null;
    $institution = isset($_POST['institution']) ? sanitize_input($_POST['institution']) : null;
    $grade_level = isset($_POST['grade_level']) ? sanitize_input($_POST['grade_level']) : '';
    $admission_date = isset($_POST['admission_date']) ? sanitize_input($_POST['admission_date']) : '';
    $status = isset($_POST['status']) ? sanitize_input($_POST['status']) : 'Pending';
    $national_id = isset($_POST['national_id']) ? sanitize_input($_POST['national_id']) : null;
    $parent_name = isset($_POST['parent_name']) ? sanitize_input($_POST['parent_name']) : null;
    $parent_phone = isset($_POST['parent_phone']) ? sanitize_input($_POST['parent_phone']) : null;
    $parent_email = isset($_POST['parent_email']) ? filter_var($_POST['parent_email'], FILTER_VALIDATE_EMAIL) : null;

    // Validate required fields
    if (empty($first_name) || empty($last_name) || empty($dob) || empty($gender) || 
        empty($email) || empty($grade_level) || empty($admission_date)) {
        $response['message'] = 'Required fields are missing. Please fill in all required fields.';
    } else {
        try {
            // Hash password if provided
            if (!empty($pin)) {
                $pin = password_hash($pin, PASSWORD_DEFAULT);
            }
            
            // Check if email already exists
            $check_stmt = $conn->prepare("SELECT id FROM students WHERE email = ?");
            $check_stmt->execute([$email]);
            
            if ($check_stmt->rowCount() > 0) {
                $response['message'] = 'This email is already registered. Please use a different email.';
            } else {
                // Check if registration number exists (if provided)
                if (!empty($reg_number)) {
                    $check_reg_stmt = $conn->prepare("SELECT id FROM students WHERE reg_number = ?");
                    $check_reg_stmt->execute([$reg_number]);
                    
                    if ($check_reg_stmt->rowCount() > 0) {
                        $response['message'] = 'This registration number is already in use. Please use a different one.';
                        goto end_processing;
                    }
                }
                
                // Insert student data
                $stmt = $conn->prepare("
                    INSERT INTO students 
                    (first_name, last_name, reg_number, pin, dob, gender, email, phone, address, 
                    institution, grade_level, admission_date, status, national_id, 
                    parent_name, parent_phone, parent_email) 
                    VALUES 
                    (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $first_name, $last_name, $reg_number, $pin, $dob, $gender, $email, $phone, $address,
                    $institution, $grade_level, $admission_date, $status, $national_id,
                    $parent_name, $parent_phone, $parent_email
                ]);
                
                if ($stmt->rowCount() > 0) {
                    $response['success'] = true;
                    $response['message'] = 'Student registered successfully!';
                    $response['student_id'] = $conn->lastInsertId();
                } else {
                    $response['message'] = 'Failed to register student. Please try again.';
                }
            }
        } catch (PDOException $e) {
            $response['message'] = 'Database error: ' . $e->getMessage();
        }
    }
    
    end_processing:
    
    // Return JSON response for AJAX requests
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    
    // For regular form submissions, redirect with status in the URL
    $redirect_url = $response['redirect'] . '?success=' . ($response['success'] ? 'true' : 'false');
    if (!empty($response['message'])) {
        $redirect_url .= '&message=' . urlencode($response['message']);
    }
    if (isset($response['student_id'])) {
        $redirect_url .= '&student_id=' . $response['student_id'];
    }
    
    header("Location: $redirect_url");
    exit;
} else {
    // Not a POST request, redirect to form
    header("Location: " . $response['redirect']);
    exit;
}
?> 