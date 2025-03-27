<?php
// This file contains a simplified consultation form that can be embedded directly

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Process form if submitted directly to this file
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Include database connection
    require_once 'config/database.php';
    
    // Sanitize inputs
    $name = trim(htmlspecialchars($_POST['name'] ?? ''));
    $email = trim(htmlspecialchars($_POST['email'] ?? ''));
    $phone = trim(htmlspecialchars($_POST['phone'] ?? ''));
    $service = trim(htmlspecialchars($_POST['service'] ?? ''));
    $message = trim(htmlspecialchars($_POST['message'] ?? ''));
    
    // Validate inputs (simple validation)
    $errors = [];
    if (empty($name) || empty($email) || empty($phone) || empty($service) || empty($message)) {
        $errors[] = "All fields are required";
    }
    
    // If no errors, save to database
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("INSERT INTO consultation_requests (name, email, phone, service_type, message) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $name, $email, $phone, $service, $message);
            
            if ($stmt->execute()) {
                $success = "Thank you for your consultation request! We will contact you shortly.";
            } else {
                $errors[] = "Database error: " . $stmt->error;
            }
        } catch (Exception $e) {
            $errors[] = "System error: " . $e->getMessage();
        }
    }
}

// Get saved form data if available
$formData = $_SESSION['consultation_form_data'] ?? [];
unset($_SESSION['consultation_form_data']); // Clear saved data
?>

<div class="consultation-form-container">
    <h3>Request Quick Consultation</h3>
    
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
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
    
    <!-- Simple form with minimal structure -->
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>#consulting-section" method="post">
        <div class="form-group">
            <label for="name">Full Name:</label>
            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($formData['name'] ?? ''); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="email">Email Address:</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="phone">Phone Number:</label>
            <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($formData['phone'] ?? ''); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="service">Service Interested In:</label>
            <select class="form-control" id="service" name="service" required>
                <option value="">Select a Service</option>
                <option value="cv_writing" <?php echo (isset($formData['service']) && $formData['service'] == 'cv_writing') ? 'selected' : ''; ?>>CV & Cover Letter Writing</option>
                <option value="university_application" <?php echo (isset($formData['service']) && $formData['service'] == 'university_application') ? 'selected' : ''; ?>>University Application Guidance</option>
                <option value="mifotra_setup" <?php echo (isset($formData['service']) && $formData['service'] == 'mifotra_setup') ? 'selected' : ''; ?>>MIFOTRA Account Setup</option>
                <option value="job_application" <?php echo (isset($formData['service']) && $formData['service'] == 'job_application') ? 'selected' : ''; ?>>Job Application Assistance</option>
                <option value="other" <?php echo (isset($formData['service']) && $formData['service'] == 'other') ? 'selected' : ''; ?>>Other Services</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="message">Your Message:</label>
            <textarea class="form-control" id="message" name="message" rows="4" required><?php echo htmlspecialchars($formData['message'] ?? ''); ?></textarea>
        </div>
        
        <div class="form-group text-center">
            <button type="submit" class="btn btn-primary">Submit Request</button>
        </div>
    </form>
</div> 