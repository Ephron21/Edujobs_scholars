<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug: Log the start of processing
error_log("Starting application processing");

require_once 'includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Debug: Log POST data (excluding sensitive information)
        error_log("POST data received: " . print_r($_POST, true));
        error_log("FILES data received: " . print_r($_FILES, true));

        // Validate required fields
        $required_fields = ['job_id', 'full_name', 'email', 'phone', 'education', 'experience', 'cover_letter'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("$field is required");
            }
        }

        // Get form data
        $job_id = $_POST['job_id'];
        $full_name = $_POST['full_name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $education = $_POST['education'];
        $experience = $_POST['experience'];
        $cover_letter = $_POST['cover_letter'];

        // Debug: Log form data
        error_log("Form data validated successfully");

        // Handle CV file upload
        if (!isset($_FILES['cv']) || $_FILES['cv']['error'] !== 0) {
            error_log("CV file error: " . print_r($_FILES['cv']['error'], true));
            throw new Exception("CV file is required");
        }

        $allowed = ['pdf'];
        $filename = $_FILES['cv']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        if (!in_array(strtolower($filetype), $allowed)) {
            throw new Exception("Only PDF files are allowed");
        }

        // Create uploads directory if it doesn't exist
        $upload_dir = __DIR__ . '/uploads/cvs/';
        if (!file_exists($upload_dir)) {
            error_log("Creating upload directory: " . $upload_dir);
            if (!mkdir($upload_dir, 0777, true)) {
                error_log("Failed to create directory. Error: " . error_get_last()['message']);
                throw new Exception("Failed to create upload directory");
            }
        }

        // Debug: Check directory permissions
        error_log("Upload directory exists: " . (file_exists($upload_dir) ? 'yes' : 'no'));
        error_log("Upload directory writable: " . (is_writable($upload_dir) ? 'yes' : 'no'));

        // Generate unique filename
        $new_filename = uniqid() . '_' . $filename;
        $target_file = $upload_dir . $new_filename;

        // Debug: Log file upload attempt
        error_log("Attempting to upload file to: " . $target_file);

        // Move uploaded file
        if (!move_uploaded_file($_FILES['cv']['tmp_name'], $target_file)) {
            error_log("Upload error: " . error_get_last()['message']);
            throw new Exception("Failed to upload CV file");
        }

        error_log("File uploaded successfully to: " . $target_file);
        $cv_path = 'uploads/cvs/' . $new_filename;

        // Begin transaction
        $conn->beginTransaction();
        error_log("Database transaction started");

        // Prepare SQL statement
        $sql = "INSERT INTO job_applications (job_id, full_name, email, phone, education, experience, cv_path, cover_letter) 
                VALUES (:job_id, :full_name, :email, :phone, :education, :experience, :cv_path, :cover_letter)";
        
        $stmt = $conn->prepare($sql);
        
        // Bind parameters
        $params = [
            ':job_id' => $job_id,
            ':full_name' => $full_name,
            ':email' => $email,
            ':phone' => $phone,
            ':education' => $education,
            ':experience' => $experience,
            ':cv_path' => $cv_path,
            ':cover_letter' => $cover_letter
        ];

        // Debug: Log SQL and parameters
        error_log("Executing SQL: " . $sql);
        error_log("Parameters: " . print_r($params, true));
        
        // Execute the statement
        if (!$stmt->execute($params)) {
            error_log("Database error: " . print_r($stmt->errorInfo(), true));
            throw new Exception("Failed to insert data into database");
        }

        // Commit transaction
        $conn->commit();
        error_log("Database transaction committed successfully");

        // Set success message
        $_SESSION['application_success'] = true;
        
        // Redirect back to home page
        header("Location: index.php#jobs-section");
        exit();

    } catch(Exception $e) {
        error_log("Error in application process: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());

        // Rollback transaction if active
        if ($conn && $conn->inTransaction()) {
            $conn->rollBack();
            error_log("Database transaction rolled back");
        }

        // Delete uploaded file if it exists
        if (isset($target_file) && file_exists($target_file)) {
            if (unlink($target_file)) {
                error_log("Uploaded file deleted successfully");
            } else {
                error_log("Failed to delete uploaded file");
            }
        }

        // Set error message
        $_SESSION['application_error'] = "Error submitting application: " . $e->getMessage();
        
        // Redirect back to home page
        header("Location: index.php#jobs-section");
        exit();
    }
} else {
    error_log("Invalid request method: " . $_SERVER["REQUEST_METHOD"]);
    header("Location: index.php");
    exit();
}
?> 