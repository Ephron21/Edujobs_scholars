<?php
// Initialize the session
session_start();
// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
// Check if the user is an admin
if (!isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] !== true) {
    // Not an admin, redirect to access denied page
    header("location: access_denied.php");
    exit;
}

// Get admin information (replace with actual database query in production)
$adminInfo = [
    'id' => isset($_SESSION["id"]) ? $_SESSION["id"] : 1,
    'name' => isset($_SESSION["username"]) ? $_SESSION["username"] : 'Admin User',
    'email' => isset($_SESSION["email"]) ? $_SESSION["email"] : 'admin@example.com',
    'avatar' => 'assets/img/admin-avatar.jpg', // Default path - replace with actual path
    'phone' => '123-456-7890',
    'role' => 'System Administrator',
    'bio' => 'Experienced system administrator with a passion for maintaining and improving educational platforms.'
];

// Include header file
$includesPath = __DIR__ . '/includes/';
$pageTitle = "My Profile";
require_once($includesPath . 'admin_header.php');

// Process form submission for profile updates
$updateMessage = '';
$updateSuccess = false;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_profile"])) {
    // In a real application, validate and sanitize inputs
    // Then update the database with the new values
    
    // Simulate successful update
    $updateSuccess = true;
    $updateMessage = "Profile updated successfully!";
    
    // In a real application, you would update the session variables with new values
    // $_SESSION["username"] = $_POST["name"];
    // $_SESSION["email"] = $_POST["email"];
}

// Process password change
$passwordMessage = '';
$passwordSuccess = false;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["change_password"])) {
    $currentPassword = $_POST["current_password"];
    $newPassword = $_POST["new_password"];
    $confirmPassword = $_POST["confirm_password"];
    
    // In a real application, verify current password against database
    // Check if new password meets requirements
    // Check if new password and confirm password match
    
    // Simulate successful password change
    $passwordSuccess = true;
    $passwordMessage = "Password changed successfully!";
}

// Process avatar upload
$avatarMessage = '';
$avatarSuccess = false;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["upload_avatar"])) {
    if (isset($_FILES["avatar"]) && $_FILES["avatar"]["error"] == 0) {
        $allowed = ["jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png"];
        $filename = $_FILES["avatar"]["name"];
        $filetype = $_FILES["avatar"]["type"];
        $filesize = $_FILES["avatar"]["size"];
        
        // Validate file extension
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (!array_key_exists($ext, $allowed)) {
            $avatarMessage = "Error: Please select a valid file format.";
        }
        
        // Validate file size - 5MB maximum
        $maxsize = 5 * 1024 * 1024;
        if ($filesize > $maxsize) {
            $avatarMessage = "Error: File size is larger than the allowed limit.";
        }
        
        // Validate MIME type of the file
        if (in_array($filetype, $allowed)) {
            // Check if file exists before uploading
            $target_dir = "uploads/avatars/";
            
            // Create directory if it doesn't exist
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $target_file = $target_dir . basename($filename);
            
            // Simulate successful upload
            $avatarSuccess = true;
            $avatarMessage = "Avatar uploaded successfully!";
            $adminInfo['avatar'] = $target_file;
            
            // In a real application, move the uploaded file and update the database
            // move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_file);
            // Update database with new avatar path
        } else {
            $avatarMessage = "Error: There was a problem uploading your file. Please try again.";
        }
    } else {
        $avatarMessage = "Error: " . $_FILES["avatar"]["error"];
    }
}
?>

<div class="container-fluid mt-4">
    <div class="row">
        <!-- Left sidebar with admin navigation -->
        <div class="col-md-3">
            <div class="card dashboard-card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user-cog"></i> Profile Settings</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="#profile-info" class="list-group-item list-group-item-action active" data-bs-toggle="list">
                            <i class="fas fa-user me-2"></i> Personal Information
                        </a>
                        <a href="#change-password" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="fas fa-key me-2"></i> Change Password
                        </a>
                        <a href="#avatar-upload" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="fas fa-image me-2"></i> Profile Picture
                        </a>
                        <a href="#notification-settings" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="fas fa-bell me-2"></i> Notification Settings
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="card dashboard-card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-question-circle"></i> Need Help?</h5>
                </div>
                <div class="card-body">
                    <p>If you have any questions or need assistance with your profile settings, please contact our support team.</p>
                    <a href="contact.php" class="btn btn-info w-100">Contact Support</a>
                </div>
            </div>
        </div>
        
        <!-- Main content area -->
        <div class="col-md-9">
            <div class="tab-content">
                <!-- Personal Information Tab -->
                <div class="tab-pane fade show active" id="profile-info">
                    <div class="card dashboard-card mb-4">
                        <div class="card-header">
                            <h5 class="page-title"><i class="fas fa-user"></i> Personal Information</h5>
                        </div>
                        <div class="card-body">
                            <?php if($updateMessage): ?>
                                <div class="alert alert-<?php echo $updateSuccess ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                                    <?php echo $updateMessage; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>
                            
                            <form id="profileForm" method="post" action="profile.php">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="name" class="form-label">Full Name</label>
                                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($adminInfo['name']); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Email Address</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($adminInfo['email']); ?>" required>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="phone" class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($adminInfo['phone']); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="role" class="form-label">Role</label>
                                        <input type="text" class="form-control" id="role" name="role" value="<?php echo htmlspecialchars($adminInfo['role']); ?>" readonly>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="bio" class="form-label">Bio</label>
                                    <textarea class="form-control" id="bio" name="bio" rows="4"><?php echo htmlspecialchars($adminInfo['bio']); ?></textarea>
                                </div>
                                <input type="hidden" name="update_profile" value="1">
                                <button type="submit" class="btn btn-primary">Update Profile</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Change Password Tab -->
                <div class="tab-pane fade" id="change-password">
                    <div class="card dashboard-card mb-4">
                        <div class="card-header">
                            <h5 class="page-title"><i class="fas fa-key"></i> Change Password</h5>
                        </div>
                        <div class="card-body">
                            <?php if($passwordMessage): ?>
                                <div class="alert alert-<?php echo $passwordSuccess ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                                    <?php echo $passwordMessage; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>
                            
                            <form id="passwordForm" method="post" action="profile.php">
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                    <div class="form-text">Password must be at least 8 characters long and include uppercase, lowercase, numbers, and special characters.</div>
                                </div>
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                <input type="hidden" name="change_password" value="1">
                                <button type="submit" class="btn btn-primary">Change Password</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Avatar Upload Tab -->
                <div class="tab-pane fade" id="avatar-upload">
                    <div class="card dashboard-card mb-4">
                        <div class="card-header">
                            <h5 class="page-title"><i class="fas fa-image"></i> Profile Picture</h5>
                        </div>
                        <div class="card-body">
                            <?php if($avatarMessage): ?>
                                <div class="alert alert-<?php echo $avatarSuccess ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                                    <?php echo $avatarMessage; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>
                            
                            <div class="row">
                                <div class="col-md-4 text-center mb-4">
                                    <div class="current-avatar-container">
                                        <img src="<?php echo $adminInfo['avatar']; ?>" alt="Current Avatar" class="img-thumbnail current-avatar" onerror="this.src='https://via.placeholder.com/150'">
                                    </div>
                                    <p class="text-muted mt-2">Current Profile Picture</p>
                                </div>
                                <div class="col-md-8">
                                    <form id="avatarForm" method="post" action="profile.php" enctype="multipart/form-data">
                                        <div class="mb-3">
                                            <label for="avatar" class="form-label">Select New Profile Picture</label>
                                            <input class="form-control" type="file" id="avatar" name="avatar" required>
                                            <div class="form-text">Allowed formats: JPG, JPEG, PNG, GIF. Max size: 5MB.</div>
                                        </div>
                                        <div class="mb-3">
                                            <div id="imagePreview" class="mt-3 d-none">
                                                <h6>Preview:</h6>
                                                <img src="" alt="Preview" class="img-thumbnail preview-avatar">
                                            </div>
                                        </div>
                                        <input type="hidden" name="upload_avatar" value="1">
                                        <button type="submit" class="btn btn-primary">Upload New Picture</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Notification Settings Tab -->
                <div class="tab-pane fade" id="notification-settings">
                    <div class="card dashboard-card mb-4">
                        <div class="card-header">
                            <h5 class="page-title"><i class="fas fa-bell"></i> Notification Settings</h5>
                        </div>
                        <div class="card-body">
                            <form id="notificationForm">
                                <div class="mb-3">
                                    <h6>Email Notifications</h6>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="emailNewUser" checked>
                                        <label class="form-check-label" for="emailNewUser">New user registrations</label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="emailSystemAlert" checked>
                                        <label class="form-check-label" for="emailSystemAlert">System alerts</label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="emailNewApplication">
                                        <label class="form-check-label" for="emailNewApplication">New applications</label>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <h6>Browser Notifications</h6>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="browserNewUser" checked>
                                        <label class="form-check-label" for="browserNewUser">New user registrations</label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="browserSystemAlert" checked>
                                        <label class="form-check-label" for="browserSystemAlert">System alerts</label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="browserNewApplication" checked>
                                        <label class="form-check-label" for="browserNewApplication">New applications</label>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Save Notification Settings</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Preview avatar image before upload
document.getElementById('avatar').addEventListener('change', function(e) {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        const preview = document.getElementById('imagePreview');
        const previewImg = preview.querySelector('.preview-avatar');
        
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.classList.remove('d-none');
        }
        
        reader.readAsDataURL(file);
    }
});

// Password validation
document.getElementById('passwordForm').addEventListener('submit', function(e) {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (newPassword !== confirmPassword) {
        e.preventDefault();
        alert('New password and confirm password do not match!');
        return false;
    }
    
    // Check password strength
    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
    if (!passwordRegex.test(newPassword)) {
        e.preventDefault();
        alert('Password must be at least 8 characters long and include uppercase, lowercase, numbers, and special characters!');
        return false;
    }
});

// Save notification settings with AJAX
document.getElementById('notificationForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Collect all notification settings
    const settings = {
        email: {
            newUser: document.getElementById('emailNewUser').checked,
            systemAlert: document.getElementById('emailSystemAlert').checked,
            newApplication: document.getElementById('emailNewApplication').checked
        },
        browser: {
            newUser: document.getElementById('browserNewUser').checked,
            systemAlert: document.getElementById('browserSystemAlert').checked,
            newApplication: document.getElementById('browserNewApplication').checked
        }
    };
    
    // Send to server via AJAX
    fetch('update_notification_settings.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(settings)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Notification settings updated successfully!');
        } else {
            alert('Failed to update notification settings!');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating notification settings!');
    });
});
</script>

<?php require_once($includesPath . 'admin_footer.php'); ?> 