<?php
// Initialize the session
session_start();
// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: admin_login.php");
    exit;
}
// Check if the user is an admin
if (!isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] !== true) {
    // Not an admin, redirect to access denied page
    header("location: access_denied.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .form-container {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-top: 20px;
            margin-bottom: 20px;
        }
        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .form-header h2 {
            color: #3c4b64;
        }
        .form-section {
            margin-bottom: 25px;
        }
        .form-section-title {
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 10px;
            margin-bottom: 20px;
            color: #3c4b64;
        }
        .btn-submit {
            background-color: #4e73df;
            border-color: #4e73df;
        }
        .btn-submit:hover {
            background-color: #2e59d9;
            border-color: #2e59d9;
        }
        .required-field::after {
            content: " *";
            color: red;
        }
        #successMessage, #errorMessage {
            display: none;
        }
        .print-section {
            background-color: #e9ecef;
            border-radius: 5px;
            padding: 20px;
            margin-top: 30px;
        }
        .field-error {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="form-container">
                    <div class="form-header">
                        <h2><i class="fas fa-user-graduate me-2"></i>Student Registration Form</h2>
                        <p class="text-muted">Please fill in all required fields marked with an asterisk (*)</p>
                        <div>
                            <a href="form_alt.html" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-exchange-alt me-1"></i>Switch to Traditional Form
                            </a>
                            <a href="api_test.html" class="btn btn-outline-secondary btn-sm ms-2">
                                <i class="fas fa-bug me-1"></i>API Test
                            </a>
                        </div>
                    </div>

                    <div class="alert alert-success" id="successMessage">
                        Student registered successfully!
                    </div>
                    <div class="alert alert-danger" id="errorMessage">
                        Error occurred while registering student.
                    </div>

                    <form id="studentForm">
                        <!-- Personal Information Section -->
                        <div class="form-section">
                            <h4 class="form-section-title"><i class="fas fa-user me-2"></i>Personal Information</h4>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="firstName" class="form-label required-field">First Name</label>
                                    <input type="text" class="form-control" id="firstName" name="first_name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="lastName" class="form-label required-field">Last Name</label>
                                    <input type="text" class="form-control" id="lastName" name="last_name" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="regNumber" class="form-label required-field">Registration Number</label>
                                    <input type="text" class="form-control" id="regNumber" name="reg_number" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label required-field">Password</label>
                                    <input type="password" class="form-control" id="password" name="pin" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="dob" class="form-label required-field">Date of Birth</label>
                                    <input type="date" class="form-control" id="dob" name="dob" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="gender" class="form-label required-field">Gender</label>
                                    <select class="form-select" id="gender" name="gender" required>
                                        <option value="" selected disabled>Select Gender</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Information Section -->
                        <div class="form-section">
                            <h4 class="form-section-title"><i class="fas fa-address-card me-2"></i>Contact Information</h4>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label required-field">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone" name="phone">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea class="form-control" id="address" name="address" rows="3"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Academic Information Section -->
                        <div class="form-section">
                            <h4 class="form-section-title"><i class="fas fa-school me-2"></i>Academic Information</h4>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="institution" class="form-label">Institution</label>
                                    <input type="text" class="form-control" id="institution" name="institution">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="gradeLevel" class="form-label required-field">Grade Level</label>
                                    <select class="form-select" id="gradeLevel" name="grade_level" required>
                                        <option value="" selected disabled>Select Grade Level</option>
                                        <option value="1">Level 1 (First Year)</option>
                                        <option value="2">Level 2 (Second Year)</option>
                                        <option value="3">Level 3 (Third Year)</option>
                                        <option value="4">Level 4 (Final Year)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="admissionDate" class="form-label required-field">Admission Date</label>
                                    <input type="date" class="form-control" id="admissionDate" name="admission_date" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="Pending" selected>Pending</option>
                                        <option value="Admitted">Admitted</option>
                                        <option value="Not Admitted">Not Admitted</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nationalId" class="form-label">National ID</label>
                                    <input type="text" class="form-control" id="nationalId" name="national_id">
                                </div>
                            </div>
                        </div>

                        <!-- Parent/Guardian Information Section -->
                        <div class="form-section">
                            <h4 class="form-section-title"><i class="fas fa-users me-2"></i>Parent/Guardian Information</h4>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="parentName" class="form-label">Parent/Guardian Name</label>
                                    <input type="text" class="form-control" id="parentName" name="parent_name">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="parentPhone" class="form-label">Parent/Guardian Phone</label>
                                    <input type="tel" class="form-control" id="parentPhone" name="parent_phone">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="parentEmail" class="form-label">Parent/Guardian Email</label>
                                    <input type="email" class="form-control" id="parentEmail" name="parent_email">
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="reset" class="btn btn-secondary me-md-2">Reset</button>
                            <button type="submit" class="btn btn-primary btn-submit">Submit</button>
                        </div>
                    </form>

                    <!-- Print Section -->
                    <div class="print-section">
                        <h4 class="form-section-title"><i class="fas fa-print me-2"></i>Print Student Information</h4>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="printType" class="form-label">Select Print Option</label>
                                <select class="form-select" id="printType">
                                    <option value="all" selected>All Students</option>
                                    <option value="selected">Selected Students</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3" id="studentIdsContainer" style="display: none;">
                                <label for="studentIds" class="form-label">Student IDs (comma-separated)</label>
                                <input type="text" class="form-control" id="studentIds" placeholder="e.g., 1,2,3">
                            </div>
                        </div>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="button" class="btn btn-success" id="printBtn">
                                <i class="fas fa-print me-2"></i>Print
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Student form submission
            const studentForm = document.getElementById('studentForm');
            const successMessage = document.getElementById('successMessage');
            const errorMessage = document.getElementById('errorMessage');

            studentForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Hide previous messages
                successMessage.style.display = 'none';
                errorMessage.style.display = 'none';
                
                // Validate all required fields
                const requiredFields = [
                    { id: 'firstName', name: 'First Name' },
                    { id: 'lastName', name: 'Last Name' },
                    { id: 'regNumber', name: 'Registration Number' },
                    { id: 'password', name: 'Password' },
                    { id: 'dob', name: 'Date of Birth' },
                    { id: 'gender', name: 'Gender' },
                    { id: 'email', name: 'Email' },
                    { id: 'gradeLevel', name: 'Grade Level' },
                    { id: 'admissionDate', name: 'Admission Date' }
                ];
                
                let missingFields = [];
                requiredFields.forEach(field => {
                    const element = document.getElementById(field.id);
                    if (!element.value.trim()) {
                        missingFields.push(field.name);
                        // Highlight missing field
                        element.classList.add('field-error');
                    } else {
                        // Remove error highlighting
                        element.classList.remove('field-error');
                    }
                });
                
                if (missingFields.length > 0) {
                    errorMessage.textContent = 'Please fill in the following required fields: ' + missingFields.join(', ');
                    errorMessage.style.display = 'block';
                    errorMessage.scrollIntoView({ behavior: 'smooth' });
                    return;
                }
                
                // Create form data object
                const formData = new FormData(studentForm);
                const data = {};
                formData.forEach((value, key) => {
                    data[key] = value;
                });
                
                // Make sure password field is correctly named - the backend expects 'password' but form uses 'pin'
                if (data.pin) {
                    data.password = data.pin;
                }
                
                // Ensure admission_date is provided if required
                if (!data.admission_date && document.getElementById('admissionDate').value) {
                    data.admission_date = document.getElementById('admissionDate').value;
                }
                
                // Ensure other required fields have the correct field names
                data.first_name = data.first_name || document.getElementById('firstName').value;
                data.last_name = data.last_name || document.getElementById('lastName').value;
                data.reg_number = data.reg_number || document.getElementById('regNumber').value;
                data.email = data.email || document.getElementById('email').value;
                data.grade_level = data.grade_level || document.getElementById('gradeLevel').value;
                
                // Convert to JSON
                const jsonData = JSON.stringify(data);
                
                // Log data to console for debugging
                console.log('Submitting form data:', data);
                
                // Show loading message
                errorMessage.textContent = 'Processing your request...';
                errorMessage.style.display = 'block';
                
                // Send API request through the proxy
                fetch('proxy.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: jsonData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok. Status: ' + response.status);
                    }
                    return response.json();
                })
                .then(result => {
                    // Hide loading message
                    errorMessage.style.display = 'none';
                    
                    console.log('API response:', result);
                    
                    if (result.success) {
                        successMessage.textContent = result.message;
                        successMessage.style.display = 'block';
                        studentForm.reset();
                        
                        // Scroll to the message
                        successMessage.scrollIntoView({ behavior: 'smooth' });
                    } else {
                        errorMessage.textContent = result.message || 'Error registering student.';
                        errorMessage.style.display = 'block';
                        
                        // Add debug info if available
                        if (result.received_data) {
                            console.log('Server received data:', result.received_data);
                            
                            // Check which required fields are missing
                            const requiredServerFields = ['first_name', 'last_name', 'email', 'grade_level', 'reg_number', 'password'];
                            const missingServerFields = [];
                            
                            requiredServerFields.forEach(field => {
                                if (!result.received_data[field]) {
                                    missingServerFields.push(field);
                                }
                            });
                            
                            if (missingServerFields.length > 0) {
                                console.error('Missing required fields on server:', missingServerFields);
                                errorMessage.textContent += '\nMissing server fields: ' + missingServerFields.join(', ');
                            }
                        }
                        
                        if (result.query_error || result.error_details) {
                            console.error('Server error details:', result.query_error || result.error_details);
                        }
                        
                        // Scroll to the message
                        errorMessage.scrollIntoView({ behavior: 'smooth' });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    errorMessage.textContent = 'An error occurred while processing your request: ' + error.message;
                    errorMessage.style.display = 'block';
                    errorMessage.scrollIntoView({ behavior: 'smooth' });
                });
            });
            
            // Print functionality
            const printType = document.getElementById('printType');
            const studentIdsContainer = document.getElementById('studentIdsContainer');
            const studentIds = document.getElementById('studentIds');
            const printBtn = document.getElementById('printBtn');
            
            printType.addEventListener('change', function() {
                if (this.value === 'selected') {
                    studentIdsContainer.style.display = 'block';
                } else {
                    studentIdsContainer.style.display = 'none';
                }
            });
            
            printBtn.addEventListener('click', function() {
                if (printType.value === 'selected' && studentIds.value.trim() !== '') {
                    // If the user entered a single ID, navigate to individual student view
                    const idValue = studentIds.value.trim();
                    if (/^\d+$/.test(idValue)) {
                        window.open('student_print.php?id=' + idValue + '&print=true', '_blank');
                    } else {
                        alert('Please enter a valid student ID number');
                    }
                } else {
                    // Print all students
                    window.open('student_print.php?print=true', '_blank');
                }
            });
        });
    </script>
</body>
</html>
