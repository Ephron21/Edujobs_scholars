<?php
// Form validation functions

// Validate email with stricter regex
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) &&
           preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email);
}

// Validate phone number (stricter validation)
function validate_phone($phone) {
    return preg_match('/^\+?[0-9]{7,15}$/', $phone); // Allows international numbers
}

// Check if email exists in database securely
function email_exists($conn, $email) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $exists = $stmt->num_rows > 0;
    $stmt->close();
    return $exists;
}

// Validate file type, extension, and size
function validate_file_upload($file, $allowed_types, $allowed_extensions, $max_size = 2097152) { // 2MB limit
    $file_type = mime_content_type($file['tmp_name']);
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($file_type, $allowed_types) || !in_array($file_extension, $allowed_extensions)) {
        return false;
    }
    if ($file['size'] > $max_size) {
        return false;
    }
    return true;
}
?>
