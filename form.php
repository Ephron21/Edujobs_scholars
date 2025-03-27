<?php
// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include the header
include_once 'includes/header.php';

// Process form submission
$success = $errors = null;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Include database connection
    require_once 'config/database.php';
    
    // Sanitize inputs
    function sanitize_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
    
    $name = sanitize_input($_POST['name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    $service = sanitize_input($_POST['service'] ?? '');
    $message = sanitize_input($_POST['message'] ?? '');
    
    // Validate inputs
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
    
    // If no errors, save to database
    if (empty($errors)) {
        try {
            // Check if table exists
            $tableCheck = $conn->query("SHOW TABLES LIKE 'consultation_requests'");
            if ($tableCheck->num_rows == 0) {
                throw new Exception("The consultation_requests table does not exist");
            }
            
            // Prepare and execute the insertion
            $stmt = $conn->prepare("INSERT INTO consultation_requests (name, email, phone, service_type, message) VALUES (?, ?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }
            
            $stmt->bind_param("sssss", $name, $email, $phone, $service, $message);
            
            if ($stmt->execute()) {
                $success = "Thank you for your consultation request! We will contact you shortly.";
                // Clear form data
                $name = $email = $phone = $service = $message = '';
            } else {
                throw new Exception("Execute failed: " . $stmt->error);
            }
        } catch (Exception $e) {
            $errors[] = "System error: " . $e->getMessage();
            
            // Log the error for debugging
            error_log("Consultation form error: " . $e->getMessage());
        }
    }
    
    // Save form data in case of errors
    if (!empty($errors)) {
        $_SESSION['consultation_form_data'] = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'service' => $service,
            'message' => $message
        ];
    }
}

// Get saved form data if available
$formData = $_SESSION['consultation_form_data'] ?? [];
unset($_SESSION['consultation_form_data']); // Clear saved data

// Check if a service was specified in the URL and set it
if (isset($_GET['service']) && empty($formData['service']) && !isset($service)) {
    $service = $_GET['service'];
}
?>

<div class="container">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card mt-5 mb-5">
                <div class="card-header bg-primary text-white">
                    <h3 class="text-center mb-0">Request Quick Consultation</h3>
                </div>
                <div class="card-body">
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <?php echo $success; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul>
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form action="<?php 
                        // Keep the service parameter if it was in the URL
                        echo htmlspecialchars($_SERVER['PHP_SELF']);
                        if (isset($_GET['service'])) {
                            echo '?service=' . htmlspecialchars($_GET['service']);
                        }
                    ?>" method="post">
                        <div class="form-group mb-3">
                            <label for="name">Full Name:</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($formData['name'] ?? $name ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="email">Email Address:</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($formData['email'] ?? $email ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="phone">Phone Number:</label>
                            <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($formData['phone'] ?? $phone ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="service">Service Interested In:</label>
                            <select class="form-control" id="service" name="service" required>
                                <option value="">Select a Service</option>
                                <option value="cv_writing" <?php echo (isset($formData['service']) && $formData['service'] == 'cv_writing') || (isset($service) && $service == 'cv_writing') ? 'selected' : ''; ?>>CV & Cover Letter Writing</option>
                                <option value="university_application" <?php echo (isset($formData['service']) && $formData['service'] == 'university_application') || (isset($service) && $service == 'university_application') ? 'selected' : ''; ?>>University Application Guidance</option>
                                <option value="mifotra_setup" <?php echo (isset($formData['service']) && $formData['service'] == 'mifotra_setup') || (isset($service) && $service == 'mifotra_setup') ? 'selected' : ''; ?>>MIFOTRA Account Setup</option>
                                <option value="job_application" <?php echo (isset($formData['service']) && $formData['service'] == 'job_application') || (isset($service) && $service == 'job_application') ? 'selected' : ''; ?>>Job Application Assistance</option>
                                <option value="other" <?php echo (isset($formData['service']) && $formData['service'] == 'other') || (isset($service) && $service == 'other') ? 'selected' : ''; ?>>Other Services</option>
                            </select>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="message">Your Message:</label>
                            <textarea class="form-control" id="message" name="message" rows="4" required><?php echo htmlspecialchars($formData['message'] ?? $message ?? ''); ?></textarea>
                        </div>
                        
                        <div class="form-group text-center mb-3">
                            <button type="submit" class="btn btn-primary">Submit Request</button>
                        </div>
                    </form>
                </div>
                <div class="card-footer">
                    <p class="text-center mb-0">We'll get back to you within 24 hours</p>
                </div>
            </div>
            
            <div class="text-center">
                <a href="index.php" class="btn btn-secondary">Back to Homepage</a>
            </div>
        </div>
    </div>
</div>

<?php
// Include the footer
include_once 'includes/footer.php';
?> 