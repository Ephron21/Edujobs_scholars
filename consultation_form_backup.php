<?php
// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if this is an AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Include the header (only for non-AJAX requests)
if (!$isAjax) {
    include_once 'includes/header.php';
}

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
    $urgency = sanitize_input($_POST['urgency'] ?? 'normal');
    $newsletter = isset($_POST['newsletter']) ? 1 : 0;
    
    // Get service-specific fields
    $additionalData = [];
    
    if ($service === 'cv_writing') {
        $additionalData['cv_level'] = sanitize_input($_POST['cv_level'] ?? '');
        $additionalData['industry'] = sanitize_input($_POST['industry'] ?? '');
    } elseif ($service === 'university_application') {
        $additionalData['university'] = sanitize_input($_POST['university'] ?? '');
        $additionalData['program'] = sanitize_input($_POST['program'] ?? '');
        $additionalData['application_deadline'] = sanitize_input($_POST['application_deadline'] ?? '');
    } elseif ($service === 'mifotra_setup') {
        $additionalData['current_status'] = sanitize_input($_POST['current_status'] ?? '');
        $additionalData['education_level'] = sanitize_input($_POST['education_level'] ?? '');
    } elseif ($service === 'job_application') {
        $additionalData['job_title'] = sanitize_input($_POST['job_title'] ?? '');
        $additionalData['company'] = sanitize_input($_POST['company'] ?? '');
        $additionalData['job_deadline'] = sanitize_input($_POST['job_deadline'] ?? '');
    }
    
    // Serialize additional data
    $additionalDataJson = !empty($additionalData) ? json_encode($additionalData) : null;
    
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
                // Create the table if it doesn't exist
                $createTable = "CREATE TABLE IF NOT EXISTS consultation_requests (
                    id INT(11) NOT NULL AUTO_INCREMENT,
                    name VARCHAR(255) NOT NULL,
                    email VARCHAR(255) NOT NULL,
                    phone VARCHAR(50) NOT NULL,
                    service_type VARCHAR(50) NOT NULL,
                    message TEXT NOT NULL,
                    urgency VARCHAR(20) NOT NULL DEFAULT 'normal',
                    newsletter TINYINT(1) NOT NULL DEFAULT 0,
                    additional_data JSON DEFAULT NULL,
                    status VARCHAR(20) NOT NULL DEFAULT 'pending',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
                
                if (!$conn->query($createTable)) {
                    throw new Exception("Table creation failed: " . $conn->error);
                }
            } else {
                // Check if we need to add new columns
                $result = $conn->query("SHOW COLUMNS FROM consultation_requests LIKE 'urgency'");
                if ($result->num_rows == 0) {
                    // Add new columns if they don't exist
                    $alterTable = "ALTER TABLE consultation_requests 
                        ADD COLUMN urgency VARCHAR(20) NOT NULL DEFAULT 'normal' AFTER message,
                        ADD COLUMN newsletter TINYINT(1) NOT NULL DEFAULT 0 AFTER urgency,
                        ADD COLUMN additional_data JSON DEFAULT NULL AFTER newsletter,
                        ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'pending' AFTER additional_data;";
                    
                    if (!$conn->query($alterTable)) {
                        throw new Exception("Table alteration failed: " . $conn->error);
                    }
                }
            }
            
            // Prepare and execute the insertion
            $stmt = $conn->prepare("INSERT INTO consultation_requests (name, email, phone, service_type, message, urgency, newsletter, additional_data) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }
            
            $stmt->bind_param("ssssssis", $name, $email, $phone, $service, $message, $urgency, $newsletter, $additionalDataJson);
            
            if ($stmt->execute()) {
                $success = "Thank you for your consultation request! We will contact you shortly.";
                
                // Clear form data
                $name = $email = $phone = $service = $message = '';
                $additionalData = [];
                
                // Send confirmation email (this is just a placeholder - implement actual email sending as needed)
                // mail($email, "Consultation Request Received", "Thank you for your consultation request. We will contact you shortly.");
                
                // Return JSON response for AJAX requests
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => $success]);
                    exit;
                }
            } else {
                throw new Exception("Execute failed: " . $stmt->error);
            }
        } catch (Exception $e) {
            $errors[] = "System error: " . $e->getMessage();
            
            // Log the error for debugging
            error_log("Consultation form error: " . $e->getMessage());
            
            // Return JSON response for AJAX requests
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'errors' => $errors]);
                exit;
            }
        }
    } else {
        // Return JSON response for AJAX requests
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit;
        }
        
        // Save form data in case of errors (for non-AJAX submissions)
        $_SESSION['consultation_form_data'] = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'service' => $service,
            'message' => $message
        ];
    }
}

// Only proceed with these parts for non-AJAX requests
if (!$isAjax) {
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
            <!-- Progress steps -->
            <div class="progress-container mt-5">
                <div class="progress mb-3">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%"></div>
                </div>
                <div class="step-indicators d-flex justify-content-between mb-4">
                    <div class="step active">Personal Info</div>
                    <div class="step">Service Details</div>
                    <div class="step">Message</div>
                    <div class="step">Review</div>
                </div>
            </div>

            <div class="card mt-3 mb-5">
                <div class="card-header bg-primary text-white">
                    <h3 class="text-center mb-0">Request Quick Consultation</h3>
                </div>
                <div class="card-body">
                    <div id="form-response" style="display: none;">
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
                    </div>
                    
                    <form id="consultationForm" action="<?php 
                        // Keep the service parameter if it was in the URL
                        echo htmlspecialchars($_SERVER['PHP_SELF']);
                        if (isset($_GET['service'])) {
                            echo '?service=' . htmlspecialchars($_GET['service']);
                        }
                    ?>" method="post">
                        <!-- Step 1: Personal Information -->
                        <div class="form-step" id="step1">
                            <h4 class="mb-3">Personal Information</h4>
                            <div class="form-group mb-3">
                                <label for="name">Full Name:</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($formData['name'] ?? $name ?? ''); ?>" required>
                                <div class="invalid-feedback">Please enter your full name</div>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="email">Email Address:</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($formData['email'] ?? $email ?? ''); ?>" required>
                                <div class="invalid-feedback">Please enter a valid email address</div>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="phone">Phone Number:</label>
                                <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($formData['phone'] ?? $phone ?? ''); ?>" required>
                                <div class="invalid-feedback">Please enter your phone number</div>
                            </div>
                            
                            <div class="d-flex justify-content-end mt-4">
                                <button type="button" class="btn btn-primary next-step">Next <i class="fas fa-arrow-right"></i></button>
                            </div>
                        </div>
                        
                        <!-- Step 2: Service Selection -->
                        <div class="form-step" id="step2" style="display: none;">
                            <h4 class="mb-3">Select Service</h4>
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
                                <div class="invalid-feedback">Please select a service</div>
                            </div>
                            
                            <!-- Dynamic fields based on service selection -->
                            <div id="dynamic-fields" class="mt-4">
                                <!-- Fields will be populated dynamically based on service selection -->
                            </div>
                            
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-secondary prev-step"><i class="fas fa-arrow-left"></i> Previous</button>
                                <button type="button" class="btn btn-primary next-step">Next <i class="fas fa-arrow-right"></i></button>
                            </div>
                        </div>
                        
                        <!-- Step 3: Message -->
                        <div class="form-step" id="step3" style="display: none;">
                            <h4 class="mb-3">Your Message</h4>
                            <div class="form-group mb-3">
                                <label for="message">Your Message:</label>
                                <textarea class="form-control" id="message" name="message" rows="4" required><?php echo htmlspecialchars($formData['message'] ?? $message ?? ''); ?></textarea>
                                <div class="invalid-feedback">Please enter your message</div>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="urgency">How urgent is your request?</label>
                                <select class="form-control" id="urgency" name="urgency">
                                    <option value="normal">Normal - Response within 24 hours</option>
                                    <option value="urgent">Urgent - Response within 12 hours</option>
                                    <option value="very_urgent">Very Urgent - Response within 6 hours</option>
                                </select>
                            </div>
                            
                            <div class="form-group mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="newsletter" name="newsletter" value="1">
                                    <label class="form-check-label" for="newsletter">
                                        Subscribe to our newsletter for job tips and opportunities
                                    </label>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-secondary prev-step"><i class="fas fa-arrow-left"></i> Previous</button>
                                <button type="button" class="btn btn-primary next-step">Review <i class="fas fa-arrow-right"></i></button>
                            </div>
                        </div>
                        
                        <!-- Step 4: Review and Submit -->
                        <div class="form-step" id="step4" style="display: none;">
                            <h4 class="mb-3">Review Your Information</h4>
                            <div class="review-data p-3 bg-light rounded mb-4">
                                <div class="row mb-2">
                                    <div class="col-md-4 font-weight-bold">Name:</div>
                                    <div class="col-md-8" id="review-name"></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-4 font-weight-bold">Email:</div>
                                    <div class="col-md-8" id="review-email"></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-4 font-weight-bold">Phone:</div>
                                    <div class="col-md-8" id="review-phone"></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-4 font-weight-bold">Service:</div>
                                    <div class="col-md-8" id="review-service"></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-4 font-weight-bold">Message:</div>
                                    <div class="col-md-8" id="review-message"></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-4 font-weight-bold">Urgency:</div>
                                    <div class="col-md-8" id="review-urgency"></div>
                                </div>
                            </div>
                            
                            <div class="form-group mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                    <label class="form-check-label" for="terms">
                                        I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">terms and conditions</a>
                                    </label>
                                    <div class="invalid-feedback">You must agree to the terms and conditions</div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-secondary prev-step"><i class="fas fa-arrow-left"></i> Previous</button>
                                <button type="submit" class="btn btn-primary" id="submit-btn">Submit Request</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-footer">
                    <p class="text-center mb-0">We'll get back to you within 24 hours</p>
                </div>
            </div>
            
            <!-- Success Modal -->
            <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title" id="successModalLabel">Consultation Request Submitted</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="text-center mb-4">
                                <div class="success-checkmark"></div>
                            </div>
                            <h4 class="text-center mb-3">Thank you for your consultation request!</h4>
                            <p class="text-center">We will contact you shortly based on your selected urgency level.</p>
                            <p class="text-center">A confirmation email has been sent to your email address.</p>
                            
                            <div class="alert alert-info mt-4">
                                <p class="mb-0"><i class="fas fa-info-circle"></i> What happens next?</p>
                                <ol class="mb-0 mt-2">
                                    <li>Our team will review your request</li>
                                    <li>A consultant will be assigned to your case</li>
                                    <li>You'll receive a call/email to schedule your consultation</li>
                                </ol>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <a href="index.php" class="btn btn-primary">Back to Homepage</a>
                            <button type="button" class="btn btn-outline-success" id="submit-another-btn">Submit Another Request</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Terms Modal -->
            <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="termsModalLabel">Terms and Conditions</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <h5>Consultation Service Terms</h5>
                            <p>By submitting this form, you agree to the following terms:</p>
                            <ul>
                                <li>We will use your contact information to respond to your consultation request.</li>
                                <li>Response times are estimates and may vary based on demand.</li>
                                <li>Your information will be kept confidential and will not be shared with third parties.</li>
                                <li>If you opt in to our newsletter, you may receive periodic emails about job opportunities and career tips.</li>
                            </ul>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" id="accept-terms" data-bs-dismiss="modal">Accept Terms</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center">
                <a href="index.php" class="btn btn-secondary">Back to Homepage</a>
            </div>
        </div>
    </div>
</div>

<!-- Add Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<!-- Add Flatpickr for better date picking -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<style>
    .progress-container {
        margin-bottom: 20px;
    }
    
    .step-indicators {
        position: relative;
    }
    
    .step {
        position: relative;
        text-align: center;
        flex: 1;
        color: #6c757d;
    }
    
    .step.active {
        color: #007bff;
        font-weight: bold;
    }
    
    .step.completed {
        color: #28a745;
    }
    
    .form-step {
        transition: all 0.3s ease;
    }
    
    .review-data {
        border: 1px solid #ddd;
    }
    
    /* Service option card styles */
    .service-option {
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid #dee2e6;
    }
    
    .service-option:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        border-color: #adb5bd;
    }
    
    .service-option.selected {
        border-color: #007bff;
        background-color: #f0f7ff;
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,123,255,0.15);
    }
    
    .service-option i {
        color: #6c757d;
        transition: all 0.3s ease;
    }
    
    .service-option.selected i {
        color: #007bff;
    }
    
    /* File upload area */
    .cv-upload-area {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.5s ease;
        opacity: 0;
    }
    
    .cv-upload-area.show {
        max-height: 200px;
        opacity: 1;
    }
    
    /* Character counter */
    .char-counter {
        transition: color 0.3s ease;
    }
    
    /* Progress bar custom styles */
    .progress {
        height: 0.5rem;
        background-color: #e9ecef;
        overflow: hidden;
        border-radius: 1rem;
    }
    
    .progress-bar {
        background-color: #007bff;
        transition: width 0.6s ease;
    }
    
    /* Success checkmark */
    .success-checkmark {
        display: block;
        margin: 0 auto;
        width: 80px;
        height: 80px;
        border-radius: 50%;
        box-sizing: content-box;
        border: 4px solid #4CAF50;
        --color: #4CAF50;
        position: relative;
        margin-bottom: 20px;
        animation: checkmark-circle 0.8s ease;
    }
    
    .success-checkmark::before {
        content: '';
        position: absolute;
        right: 28px;
        top: 15px;
        width: 20px;
        height: 40px;
        border-right: 4px solid var(--color);
        border-bottom: 4px solid var(--color);
        transform: rotate(45deg);
        opacity: 0;
        animation: checkmark-check 0.3s ease 0.8s forwards;
    }
    
    @keyframes checkmark-circle {
        0% { transform: scale(0.8); opacity: 0.5; }
        50% { transform: scale(1.1); opacity: 1; }
        100% { transform: scale(1); opacity: 1; }
    }
    
    @keyframes checkmark-check {
        0% { opacity: 0; transform: scale(0) rotate(45deg); }
        100% { opacity: 1; transform: scale(1) rotate(45deg); }
    }
    
    /* Tooltip style */
    .custom-tooltip {
        position: relative;
        display: inline-block;
    }
    
    .custom-tooltip .tooltip-text {
        visibility: hidden;
        width: 200px;
        background-color: #333;
        color: #fff;
        text-align: center;
        border-radius: 6px;
        padding: 5px;
        position: absolute;
        z-index: 1;
        bottom: 125%;
        left: 50%;
        margin-left: -100px;
        opacity: 0;
        transition: opacity 0.3s;
    }
    
    .custom-tooltip .tooltip-text::after {
        content: "";
        position: absolute;
        top: 100%;
        left: 50%;
        margin-left: -5px;
        border-width: 5px;
        border-style: solid;
        border-color: #333 transparent transparent transparent;
    }
    
    .custom-tooltip:hover .tooltip-text {
        visibility: visible;
        opacity: 1;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('consultationForm');
        const steps = document.querySelectorAll('.form-step');
        const nextButtons = document.querySelectorAll('.next-step');
        const prevButtons = document.querySelectorAll('.prev-step');
        const progressBar = document.querySelector('.progress-bar');
        const stepIndicators = document.querySelectorAll('.step');
        const serviceSelect = document.getElementById('service');
        const dynamicFields = document.getElementById('dynamic-fields');
        const submitBtn = document.getElementById('submit-btn');
        const termsCheckbox = document.getElementById('terms');
        const acceptTermsBtn = document.getElementById('accept-terms');
        
        let currentStep = 0;
        
        // Update progress bar and step indicators
        function updateProgress(step) {
            const progress = (step / (steps.length - 1)) * 100;
            progressBar.style.width = `${progress}%`;
            progressBar.setAttribute('aria-valuenow', progress);
            
            stepIndicators.forEach((indicator, idx) => {
                if (idx < step) {
                    indicator.classList.add('completed');
                    indicator.classList.remove('active');
                } else if (idx === step) {
                    indicator.classList.add('active');
                    indicator.classList.remove('completed');
                } else {
                    indicator.classList.remove('active', 'completed');
                }
            });
        }
        
        // Show the specified step
        function showStep(step) {
            steps.forEach((el, idx) => {
                el.style.display = idx === step ? 'block' : 'none';
            });
            
            updateProgress(step);
            currentStep = step;
            
            // If we're on the review step, populate the review data
            if (step === 3) {
                populateReviewData();
            }
        }
        
        // Validate the current step
        function validateStep(step) {
            let isValid = true;
            const inputs = steps[step].querySelectorAll('input[required], select[required], textarea[required]');
            
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    input.classList.add('is-invalid');
                    isValid = false;
                } else {
                    input.classList.remove('is-invalid');
                }
                
                // For email field, validate format
                if (input.type === 'email' && input.value.trim()) {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(input.value.trim())) {
                        input.classList.add('is-invalid');
                        isValid = false;
                    }
                }
            });
            
            return isValid;
        }
        
        // Next button event listeners
        nextButtons.forEach(button => {
            button.addEventListener('click', function() {
                if (validateStep(currentStep)) {
                    showStep(currentStep + 1);
                }
            });
        });
        
        // Previous button event listeners
        prevButtons.forEach(button => {
            button.addEventListener('click', function() {
                showStep(currentStep - 1);
            });
        });
        
        // Dynamic fields based on service selection
        serviceSelect.addEventListener('change', function() {
            const selectedService = this.value;
            dynamicFields.innerHTML = '';
            
            if (selectedService === 'cv_writing') {
                dynamicFields.innerHTML = `
                    <div class="form-group mb-3">
                        <label for="cv_level">CV Level:</label>
                        <select class="form-control" id="cv_level" name="cv_level">
                            <option value="entry">Entry Level</option>
                            <option value="professional">Professional</option>
                            <option value="executive">Executive</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label for="industry">Industry:</label>
                        <input type="text" class="form-control" id="industry" name="industry" placeholder="e.g. IT, Healthcare, Finance">
                    </div>
                `;
            } else if (selectedService === 'university_application') {
                dynamicFields.innerHTML = `
                    <div class="form-group mb-3">
                        <label for="university">Target University:</label>
                        <input type="text" class="form-control" id="university" name="university">
                    </div>
                    <div class="form-group mb-3">
                        <label for="program">Program of Interest:</label>
                        <input type="text" class="form-control" id="program" name="program">
                    </div>
                    <div class="form-group mb-3">
                        <label for="application_deadline">Application Deadline:</label>
                        <input type="date" class="form-control" id="application_deadline" name="application_deadline">
                    </div>
                `;
            } else if (selectedService === 'mifotra_setup') {
                dynamicFields.innerHTML = `
                    <div class="form-group mb-3">
                        <label for="current_status">Current Status:</label>
                        <select class="form-control" id="current_status" name="current_status">
                            <option value="new">New to MIFOTRA</option>
                            <option value="existing">Have existing account with issues</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label for="education_level">Highest Education Level:</label>
                        <select class="form-control" id="education_level" name="education_level">
                            <option value="high_school">High School</option>
                            <option value="bachelor">Bachelor's Degree</option>
                            <option value="master">Master's Degree</option>
                            <option value="phd">PhD</option>
                        </select>
                    </div>
                `;
            } else if (selectedService === 'job_application') {
                dynamicFields.innerHTML = `
                    <div class="form-group mb-3">
                        <label for="job_title">Job Title:</label>
                        <input type="text" class="form-control" id="job_title" name="job_title">
                    </div>
                    <div class="form-group mb-3">
                        <label for="company">Company:</label>
                        <input type="text" class="form-control" id="company" name="company">
                    </div>
                    <div class="form-group mb-3">
                        <label for="job_deadline">Application Deadline:</label>
                        <input type="date" class="form-control" id="job_deadline" name="job_deadline">
                    </div>
                `;
            }
        });
        
        // Populate review data
        function populateReviewData() {
            document.getElementById('review-name').textContent = document.getElementById('name').value;
            document.getElementById('review-email').textContent = document.getElementById('email').value;
            document.getElementById('review-phone').textContent = document.getElementById('phone').value;
            
            const serviceSelect = document.getElementById('service');
            document.getElementById('review-service').textContent = serviceSelect.options[serviceSelect.selectedIndex].text;
            
            document.getElementById('review-message').textContent = document.getElementById('message').value;
            
            const urgencySelect = document.getElementById('urgency');
            document.getElementById('review-urgency').textContent = urgencySelect.options[urgencySelect.selectedIndex].text;
        }
        
        // Terms and conditions
        acceptTermsBtn.addEventListener('click', function() {
            termsCheckbox.checked = true;
            termsCheckbox.classList.remove('is-invalid');
        });
        
        // AJAX form submission
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!validateStep(currentStep)) {
                return;
            }
            
            // Create FormData object
            const formData = new FormData(form);
            
            // Disable submit button to prevent double submission
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Submitting...';
            
            // AJAX request
            fetch(form.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success modal
                    const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                    successModal.show();
                    
                    // Reset form
                    form.reset();
                    showStep(0);
                } else {
                    // Show errors with animation
                    const formResponse = document.getElementById('form-response');
                    formResponse.innerHTML = `
                        <div class="alert alert-danger">
                            <ul>
                                ${data.errors.map(error => `<li>${error}</li>`).join('')}
                            </ul>
                        </div>
                    `;
                    showFormResponse(formResponse);
                    
                    // Scroll to top of form
                    form.scrollIntoView({ behavior: 'smooth' });
                }
            })
            .catch(error => {
                const formResponse = document.getElementById('form-response');
                formResponse.innerHTML = `
                    <div class="alert alert-danger">
                        <p>There was an error submitting your form. Please try again later.</p>
                        <p><small>Technical details: ${error.message}</small></p>
                    </div>
                `;
                showFormResponse(formResponse);
            })
            .finally(() => {
                // Re-enable submit button
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Submit Request';
            });
        });
        
        // Initialize first step
        showStep(0);
        
        // Initialize service fields if a service is already selected
        if (serviceSelect.value) {
            serviceSelect.dispatchEvent(new Event('change'));
        }
        
        // Initialize Flatpickr for date inputs
        function initializeDatepickers() {
            document.querySelectorAll('input[type="date"]').forEach(dateInput => {
                flatpickr(dateInput, {
                    dateFormat: "Y-m-d",
                    minDate: "today",
                    disableMobile: true,
                    onChange: function(selectedDates, dateStr, instance) {
                        // Add valid class when date is selected
                        if (dateStr) {
                            instance.element.classList.add('is-valid');
                            instance.element.classList.remove('is-invalid');
                        }
                    }
                });
            });
        }
        
        // Add character counter for textarea
        const messageTextarea = document.getElementById('message');
        if (messageTextarea) {
            // Create and append character counter
            const counterDiv = document.createElement('div');
            counterDiv.className = 'char-counter text-muted small text-end';
            counterDiv.innerHTML = '0/500 characters';
            messageTextarea.parentNode.appendChild(counterDiv);
            
            // Update counter on input
            messageTextarea.addEventListener('input', function() {
                const count = this.value.length;
                const maxCount = 500;
                counterDiv.innerHTML = `${count}/${maxCount} characters`;
                
                // Change color when approaching limit
                if (count > maxCount * 0.8) {
                    counterDiv.classList.add('text-warning');
                    counterDiv.classList.remove('text-muted', 'text-danger');
                } else if (count > maxCount) {
                    counterDiv.classList.add('text-danger');
                    counterDiv.classList.remove('text-muted', 'text-warning');
                } else {
                    counterDiv.classList.add('text-muted');
                    counterDiv.classList.remove('text-warning', 'text-danger');
                }
            });
        }
        
        // Add file upload option for CV/Resume
        function setupFileUpload() {
            const haveResumeCheckbox = document.getElementById('have_resume');
            if (haveResumeCheckbox) {
                // Create file upload area
                const fileUploadArea = document.createElement('div');
                fileUploadArea.className = 'form-group mb-3 cv-upload-area';
                fileUploadArea.style.display = 'none';
                fileUploadArea.innerHTML = `
                    <label for="resume_file">Upload your existing CV/Resume:</label>
                    <input type="file" class="form-control" id="resume_file" name="resume_file" accept=".pdf,.doc,.docx">
                    <small class="form-text text-muted">Accepted formats: PDF, DOC, DOCX (Max 2MB)</small>
                `;
                
                // Add after the checkbox
                haveResumeCheckbox.closest('.form-group').after(fileUploadArea);
                
                // Toggle display based on checkbox
                haveResumeCheckbox.addEventListener('change', function() {
                    fileUploadArea.style.display = this.checked ? 'block' : 'none';
                });
            }
        }
        
        // Enhanced service selection with icons
        function enhanceServiceSelection() {
            const serviceSelect = document.getElementById('service');
            if (serviceSelect) {
                // Get the current select element's parent
                const selectContainer = serviceSelect.parentNode;
                
                // Create the custom radio button group
                const radioGroup = document.createElement('div');
                radioGroup.className = 'service-radio-group row mb-3';
                
                // Add radio buttons for each service
                const services = [
                    { value: 'cv_writing', label: 'CV & Cover Letter', icon: 'fa-file-alt' },
                    { value: 'university_application', label: 'University Application', icon: 'fa-university' },
                    { value: 'mifotra_setup', label: 'MIFOTRA Setup', icon: 'fa-id-card' },
                    { value: 'job_application', label: 'Job Application', icon: 'fa-briefcase' },
                    { value: 'other', label: 'Other Services', icon: 'fa-plus-circle' }
                ];
                
                services.forEach(service => {
                    const isSelected = serviceSelect.value === service.value;
                    
                    const radioCol = document.createElement('div');
                    radioCol.className = 'col-md-4 col-sm-6 mb-3';
                    radioCol.innerHTML = `
                        <div class="service-option card p-3 text-center ${isSelected ? 'selected' : ''}">
                            <input type="radio" name="service_radio" id="service_${service.value}" 
                                   value="${service.value}" ${isSelected ? 'checked' : ''} class="d-none">
                            <label for="service_${service.value}" class="mb-0 cursor-pointer">
                                <i class="fas ${service.icon} fa-2x mb-2"></i>
                                <p class="mb-0">${service.label}</p>
                            </label>
                        </div>
                    `;
                    
                    radioGroup.appendChild(radioCol);
                });
                
                // Insert the radio group before the select element
                selectContainer.insertBefore(radioGroup, serviceSelect);
                
                // Hide the original select but keep it for form submission
                serviceSelect.style.display = 'none';
                
                // Add click handler for the radio options
                document.querySelectorAll('.service-option').forEach(option => {
                    option.addEventListener('click', function() {
                        // Remove selected class from all options
                        document.querySelectorAll('.service-option').forEach(opt => {
                            opt.classList.remove('selected');
                        });
                        
                        // Add selected class to clicked option
                        this.classList.add('selected');
                        
                        // Get the radio input and mark it as checked
                        const radio = this.querySelector('input[type="radio"]');
                        radio.checked = true;
                        
                        // Update the hidden select value
                        serviceSelect.value = radio.value;
                        
                        // Trigger change event to load dynamic fields
                        serviceSelect.dispatchEvent(new Event('change'));
                    });
                });
            }
        }
        
        // Function to add animated progress when moving between steps
        function updateProgressWithAnimation(step) {
            const progress = (step / (steps.length - 1)) * 100;
            const currentWidth = parseInt(progressBar.style.width);
            const targetWidth = progress;
            
            // Animate progress bar
            let width = currentWidth;
            const interval = setInterval(() => {
                if (width >= targetWidth) {
                    clearInterval(interval);
                } else {
                    width += 2;
                    progressBar.style.width = width + '%';
                    progressBar.setAttribute('aria-valuenow', width);
                }
            }, 10);
            
            stepIndicators.forEach((indicator, idx) => {
                if (idx < step) {
                    indicator.classList.add('completed');
                    indicator.classList.remove('active');
                } else if (idx === step) {
                    indicator.classList.add('active');
                    indicator.classList.remove('completed');
                } else {
                    indicator.classList.remove('active', 'completed');
                }
            });
        }
        
        // Override the updateProgress function with the animated version
        updateProgress = updateProgressWithAnimation;
        
        // Call to initialize custom features
        initializeDatepickers();
        enhanceServiceSelection();
        
        // Listen for dynamic field changes to set up file upload
        const originalServiceChange = serviceSelect.onchange;
        serviceSelect.addEventListener('change', function() {
            // Wait for the dynamic fields to be created
            setTimeout(() => {
                setupFileUpload();
                initializeDatepickers();
            }, 400);
        });
        
        // Show the form response with animation
        function showFormResponse(responseElement) {
            responseElement.style.display = 'block';
            // Force a reflow
            responseElement.offsetHeight;
            responseElement.classList.add('show');
        }
        
        // Handle "Submit Another Request" button in success modal
        document.getElementById('submit-another-btn').addEventListener('click', function() {
            // Hide the modal
            const successModal = bootstrap.Modal.getInstance(document.getElementById('successModal'));
            successModal.hide();
            
            // Reset form and go to first step
            form.reset();
            showStep(0);
            
            // Clear validation classes
            form.querySelectorAll('.is-valid, .is-invalid').forEach(el => {
                el.classList.remove('is-valid', 'is-invalid');
            });
            
            // Clear dynamic fields
            dynamicFields.innerHTML = '';
        });
        
        // Add tooltips to fields that might need explanation
        const tooltips = {
            'urgency': 'Select urgency based on how soon you need our assistance',
            'newsletter': 'Get weekly job tips and exclusive opportunities',
            'terms': 'Our terms outline how we handle your information and consultation process'
        };
        
        Object.keys(tooltips).forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                const label = element.closest('.form-group').querySelector('label') || 
                             element.closest('.form-check').querySelector('label');
                
                if (label) {
                    // Add info icon with tooltip
                    const infoIcon = document.createElement('span');
                    infoIcon.className = 'custom-tooltip ms-2';
                    infoIcon.innerHTML = `<i class="fas fa-info-circle text-info"></i><span class="tooltip-text">${tooltips[id]}</span>`;
                    label.appendChild(infoIcon);
                }
            }
        });
        
        // Add form auto-save to localStorage
        function saveFormState() {
            const formData = {};
            form.querySelectorAll('input, select, textarea').forEach(field => {
                if (field.type === 'checkbox' || field.type === 'radio') {
                    formData[field.name] = field.checked;
                } else {
                    formData[field.name] = field.value;
                }
            });
            
            localStorage.setItem('consultationFormData', JSON.stringify(formData));
            localStorage.setItem('consultationFormStep', currentStep.toString());
        }
        
        // Load saved form state
        function loadFormState() {
            const savedData = localStorage.getItem('consultationFormData');
            const savedStep = localStorage.getItem('consultationFormStep');
            
            if (savedData) {
                const formData = JSON.parse(savedData);
                
                form.querySelectorAll('input, select, textarea').forEach(field => {
                    if (formData[field.name] !== undefined) {
                        if (field.type === 'checkbox' || field.type === 'radio') {
                            field.checked = formData[field.name];
                        } else {
                            field.value = formData[field.name];
                        }
                    }
                });
                
                // Trigger service select change to load dynamic fields
                if (serviceSelect.value) {
                    serviceSelect.dispatchEvent(new Event('change'));
                }
                
                // Go to saved step if available
                if (savedStep) {
                    showStep(parseInt(savedStep));
                }
                
                // Show notification
                const notification = document.createElement('div');
                notification.className = 'alert alert-info alert-dismissible fade show';
                notification.innerHTML = `
                    <i class="fas fa-sync-alt"></i> We've restored your previous form data.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                form.insertBefore(notification, form.firstChild);
            }
        }
        
        // Save form on input changes
        form.addEventListener('input', saveFormState);
        
        // Save form state when moving between steps
        const originalShowStep = showStep;
        showStep = function(step) {
            originalShowStep(step);
            saveFormState();
        };
        
        // Clear saved data on successful submission
        form.addEventListener('submit', function() {
            localStorage.removeItem('consultationFormData');
            localStorage.removeItem('consultationFormStep');
        });
        
        // Load saved form state on page load
        loadFormState();
    });
</script>

<?php
// Include the footer
include_once 'includes/footer.php';
?> 
