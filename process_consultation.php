<?php
// Start a session to store error messages
session_start();

// For debugging
$debug_log = [];
$debug_log[] = "Script started at " . date('Y-m-d H:i:s');

// Include database connection
require_once 'config/database.php';
$debug_log[] = "Database connection included";

// Check if database connection is valid
if ($conn->connect_error) {
    $debug_log[] = "Database connection failed: " . $conn->connect_error;
} else {
    $debug_log[] = "Database connection successful";
}

// Function to sanitize user inputs
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// If this script is accessed directly via the URL with test=true param, run a test insertion
if (isset($_GET['test']) && $_GET['test'] === 'true') {
    echo "<h2>Running Consultation Request Form Test</h2>";
    
    // Display the debug log
    echo "<h3>Debug Log:</h3>";
    echo "<pre>";
    foreach ($debug_log as $log) {
        echo htmlspecialchars($log) . "\n";
    }
    echo "</pre>";
    
    // Test database connection
    echo "<h3>Database Connection Test:</h3>";
    if (!$conn->connect_error) {
        echo "<p style='color:green'>Database connection is working!</p>";
        
        // Check if table exists
        $tableCheck = $conn->query("SHOW TABLES LIKE 'consultation_requests'");
        if ($tableCheck->num_rows > 0) {
            echo "<p style='color:green'>Table 'consultation_requests' exists!</p>";
            
            // Try a test insertion
            echo "<h3>Test Insertion:</h3>";
            try {
                $testName = "Test User";
                $testEmail = "test@example.com";
                $testPhone = "123456789";
                $testService = "test_service";
                $testMessage = "This is a test message.";
                
                $stmt = $conn->prepare("INSERT INTO consultation_requests (name, email, phone, service_type, message) VALUES (?, ?, ?, ?, ?)");
                
                if (!$stmt) {
                    echo "<p style='color:red'>Prepare statement failed: " . $conn->error . "</p>";
                } else {
                    $stmt->bind_param("sssss", $testName, $testEmail, $testPhone, $testService, $testMessage);
                    
                    if ($stmt->execute()) {
                        $insertId = $conn->insert_id;
                        echo "<p style='color:green'>Test data inserted successfully with ID: $insertId</p>";
                        
                        // Display inserted data
                        $result = $conn->query("SELECT * FROM consultation_requests WHERE id = $insertId");
                        if ($result && $row = $result->fetch_assoc()) {
                            echo "<p>Inserted Data:</p>";
                            echo "<pre>";
                            print_r($row);
                            echo "</pre>";
                        }
                    } else {
                        echo "<p style='color:red'>Execute failed: " . $stmt->error . "</p>";
                    }
                }
            } catch (Exception $e) {
                echo "<p style='color:red'>Exception: " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p style='color:red'>Table 'consultation_requests' does not exist!</p>";
        }
    } else {
        echo "<p style='color:red'>Database connection failed: " . $conn->connect_error . "</p>";
    }
    
    // Link back to homepage
    echo "<p><a href='index.php'>Return to Homepage</a></p>";
    exit();
}

// Process consultation form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $debug_log[] = "POST method detected";
    
    // Log all POST data for debugging
    $debug_log[] = "POST data: " . print_r($_POST, true);
    
    // Get form data and sanitize inputs
    $name = sanitize_input($_POST['name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    $service = sanitize_input($_POST['service'] ?? '');
    $message = sanitize_input($_POST['message'] ?? '');
    
    $debug_log[] = "Sanitized inputs - Name: $name, Email: $email, Phone: $phone, Service: $service";
    
    // Validate required fields
    $errors = [];
    if (empty($name)) {
        $errors[] = "Full name is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($phone)) {
        $errors[] = "Phone number is required";
    }
    
    if (empty($service)) {
        $errors[] = "Please select a service";
    }
    
    if (empty($message)) {
        $errors[] = "Message is required";
    }
    
    $debug_log[] = "Validation errors: " . (empty($errors) ? "None" : implode(", ", $errors));
    
    // If no errors, proceed with database insertion
    if (empty($errors)) {
        try {
            $debug_log[] = "Attempting database insertion";
            
            // Check if the consultation_requests table exists
            $tableCheck = $conn->query("SHOW TABLES LIKE 'consultation_requests'");
            if ($tableCheck->num_rows == 0) {
                $debug_log[] = "ERROR: Table 'consultation_requests' does not exist!";
                throw new Exception("Table 'consultation_requests' does not exist");
            }
            
            // Using prepared statements to prevent SQL injection
            $stmt = $conn->prepare("INSERT INTO consultation_requests (name, email, phone, service_type, message) VALUES (?, ?, ?, ?, ?)");
            
            if (!$stmt) {
                $debug_log[] = "Prepare statement failed: " . $conn->error;
                throw new Exception("Prepare statement failed: " . $conn->error);
            }
            
            $debug_log[] = "Statement prepared successfully";
            
            $stmt->bind_param("sssss", $name, $email, $phone, $service, $message);
            $debug_log[] = "Parameters bound to statement";
            
            if ($stmt->execute()) {
                $debug_log[] = "Execute successful! Data inserted with ID: " . $conn->insert_id;
                
                // Log success to a text file for debugging
                file_put_contents('consultation_debug.log', implode("\n", $debug_log) . "\n\n", FILE_APPEND);
                
                // Redirect with success message
                header("Location: ./index.php?consultation=success#consulting-section");
                exit();
            } else {
                $debug_log[] = "Execute failed: " . $stmt->error;
                throw new Exception("Execute failed: " . $stmt->error);
            }
        } catch (Exception $e) {
            // Log the error
            $debug_log[] = "Exception caught: " . $e->getMessage();
            error_log("Database error in consultation form: " . $e->getMessage());
            
            // Save debug log to file
            file_put_contents('consultation_debug.log', implode("\n", $debug_log) . "\n\n", FILE_APPEND);
            
            // Store error in session
            $_SESSION['consultation_errors'] = ["System error: " . $e->getMessage()];
            
            // Redirect with error message
            header("Location: ./index.php?consultation=error#consulting-section");
            exit();
        }
    } else {
        $debug_log[] = "Validation errors found, returning to form";
        
        // Store errors in session to display them on the form
        $_SESSION['consultation_errors'] = $errors;
        $_SESSION['consultation_form_data'] = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'service' => $service,
            'message' => $message
        ];
        
        // Save debug log to file
        file_put_contents('consultation_debug.log', implode("\n", $debug_log) . "\n\n", FILE_APPEND);
        
        // Redirect back to the form with errors
        header("Location: ./index.php?consultation=validation_error#consulting-section");
        exit();
    }
} else {
    $debug_log[] = "Not a POST request, redirecting to homepage";
    
    // Save debug log to file for non-POST requests too
    file_put_contents('consultation_debug.log', implode("\n", $debug_log) . "\n\n", FILE_APPEND);
    
    // Redirect to the homepage if accessed directly
    header("Location: ./index.php");
    exit();
}
?> 