<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration Form (Traditional)</title>
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
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="form-container">
                    <div class="form-header">
                        <h2><i class="fas fa-user-graduate me-2"></i>Student Registration Form (Traditional)</h2>
                        <p class="text-muted">Please fill in all required fields marked with an asterisk (*)</p>
                        <div>
                            <a href="add_student.php" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-exchange-alt me-1"></i>Switch to Traditional Form
                            </a>
                            <a href="manage_students.php" class="btn btn-outline-secondary btn-sm ms-2">
                                <i class="fas fa-list me-1"></i>Manage Students
                            </a>
                        </div>
                    </div>

                    <!-- Display error message if set in URL parameter -->
                    <?php if(isset($_GET['error'])): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i> <?php echo htmlspecialchars(urldecode($_GET['error'])); ?>
                    </div>
                    <?php endif; ?>

                    <!-- Display success message if set in URL parameter -->
                    <?php if(isset($_GET['success'])): ?>
                    <div class="alert alert-success" role="alert">
                        <i class="fas fa-check-circle me-2"></i> Student registered successfully!
                    </div>
                    <?php endif; ?>

                    <form action="process_form.php" method="POST">
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
                                    <input type="password" class="form-control" id="password" name="password" required>
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
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="reset" class="btn btn-secondary me-md-2">Reset</button>
                            <button type="submit" class="btn btn-primary btn-submit">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 