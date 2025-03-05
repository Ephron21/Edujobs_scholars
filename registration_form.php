<?php
// Start session
session_start();

// Check if header file exists and include it, otherwise create a basic HTML header
if (file_exists('includes/header.php')) {
    include 'includes/header.php';
} else {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <style>
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
        }
        fieldset {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        legend {
            font-weight: bold;
            padding: 0 10px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .radio-group {
            display: flex;
            gap: 15px;
        }
        .submit-group {
            margin-top: 20px;
        }
        .btn {
            padding: 8px 20px;
            border-radius: 4px;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            color: white;
        }
        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
            color: white;
        }

         /* Back to Home Button */
         .back-to-home {
            text-align: center;
            margin-top: 20px;
        }

        .back-to-home a {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            transition: background-color 0.3s ease-in-out;
        }

        .back-to-home a:hover {
            background-color: #0056b3;
        }

    </style>
</head>
<body>
    <header class="bg-dark text-white p-3">
        <div class="container">
            <h1>Registration System</h1>
            <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
                <ul class="navbar-nav ms-auto">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="signin.php">Sign In</a></li>
                        <li class="nav-item"><a class="nav-link" href="signup.php">Sign Up</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
<?php
}
?>

<div class="container">
    <h2>User Registration Form</h2>
    
    <form action="process_registration.php" method="post" enctype="multipart/form-data">
        <!-- Personal Information -->
        <fieldset>
            <legend>Personal Information</legend>
            
            <div class="form-group">
                <label for="firstname">First Name *</label>
                <input type="text" name="firstname" id="firstname" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="lastname">Last Name *</label>
                <input type="text" name="lastname" id="lastname" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="phone">Phone Number *</label>
                <input type="tel" name="phone" id="phone" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>Gender *</label>
                <div class="radio-group">
                    <label><input type="radio" name="gender" value="male" required> Male</label>
                    <label><input type="radio" name="gender" value="female"> Female</label>
                    <label><input type="radio" name="gender" value="other"> Other</label>
                </div>
            </div>
        </fieldset>

        <!-- Select Role (Admin or User) -->
        <!-- <fieldset>
            <legend>Role Selection</legend>
            <div class="form-group">
                <label for="role">Select Role *</label>
                <select name="role" id="role" class="form-control" required>
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
        </fieldset> -->
        
        <!-- Parent Information -->
        <fieldset>
            <legend>Parent Information</legend>
            <div class="form-group">
                <label for="father_name">Father's Name *</label>
                <input type="text" name="father_name" id="father_name" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="father_phone">Father's Phone Number *</label>
                <input type="tel" name="father_phone" id="father_phone" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="mother_name">Mother's Name *</label>
                <input type="text" name="mother_name" id="mother_name" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="mother_phone">Mother's Phone Number *</label>
                <input type="tel" name="mother_phone" id="mother_phone" class="form-control" required>
            </div>
        </fieldset>
        
        <!-- Place of Issue -->
        <fieldset>
            <legend>Place of Issue</legend>
            
            <div class="form-group">
                <label for="province">Province *</label>
                <input type="text" name="province" id="province" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="district">District *</label>
                <input type="text" name="district" id="district" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="sector">Sector *</label>
                <input type="text" name="sector" id="sector" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="cell">Cell *</label>
                <input type="text" name="cell" id="cell" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="village">Village *</label>
                <input type="text" name="village" id="village" class="form-control" required>
            </div>
        </fieldset>
        
        <!-- Documents Upload -->
        <fieldset>
            <legend>Document Upload</legend>
            
            <div class="form-group">
                <label for="id_document">ID Document (PDF, JPG, PNG) *</label>
                <input type="file" name="id_document" id="id_document" class="form-control" required accept=".pdf,.jpg,.jpeg,.png">
                <small class="form-text text-muted">Maximum file size: 2MB</small>
            </div>
            
            <div class="form-group">
                <label for="diploma">Diploma/Certificate (PDF, JPG, PNG) *</label>
                <input type="file" name="diploma" id="diploma" class="form-control" required accept=".pdf,.jpg,.jpeg,.png">
                <small class="form-text text-muted">Maximum file size: 2MB</small>
            </div>
            
            <div class="form-group">
                <label for="profile_image">Profile Image (JPG, PNG) *</label>
                <input type="file" name="profile_image" id="profile_image" class="form-control" required accept=".jpg,.jpeg,.png">
                <small class="form-text text-muted">Maximum file size: 1MB</small>
            </div>
        </fieldset>
        
        <div class="form-group submit-group">
            <button type="submit" name="submit" class="btn btn-primary">Submit Registration</button>
            <button type="reset" class="btn btn-secondary">Reset Form</button>
        </div>
    </form>

     <!-- Back to Home Button -->
     <div class="back-to-home">
            <a href="index.php">Back to Home</a>
        </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Simple form validation
    const form = document.querySelector('form');
    
    form.addEventListener('submit', function(event) {
        let isValid = true;
        
        // Validate file size for uploads
        const idDocument = document.getElementById('id_document').files[0];
        const diploma = document.getElementById('diploma').files[0];
        const profileImage = document.getElementById('profile_image').files[0];
        
        if (idDocument && idDocument.size > 2 * 1024 * 1024) {
            alert('ID Document file size exceeds 2MB limit');
            isValid = false;
        }
        
        if (diploma && diploma.size > 2 * 1024 * 1024) {
            alert('Diploma/Certificate file size exceeds 2MB limit');
            isValid = false;
        }
        
        if (profileImage && profileImage.size > 1 * 1024 * 1024) {
            alert('Profile Image file size exceeds 1MB limit');
            isValid = false;
        }
        
        // Validate phone number format
        const phoneRegex = /^\+?[0-9]{10,15}$/;
        const phone = document.getElementById('phone').value;
        const fatherPhone = document.getElementById('father_phone').value;
        const motherPhone = document.getElementById('mother_phone').value;
        
        if (!phoneRegex.test(phone)) {
            alert('Please enter a valid phone number (10-15 digits)');
            isValid = false;
        }
        
        if (!phoneRegex.test(fatherPhone)) {
            alert('Please enter a valid phone number for father');
            isValid = false;
        }
        
        if (!phoneRegex.test(motherPhone)) {
            alert('Please enter a valid phone number for mother');
            isValid = false;
        }
        
        if (!isValid) {
            event.preventDefault();
        }
    });
});
</script>

<?php
// Check if footer file exists and include it, otherwise create a basic HTML footer
if (file_exists('includes/footer.php')) {
    include 'includes/footer.php';
} else {
?>
    <footer class="bg-dark text-white text-center p-3 mt-4">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Registration System. All rights reserved.</p>
        </div>
    </footer>
    </body>
    </html>
<?php
}
?>
