<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Check if the user is an admin
if(!isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] !== true) {
    // Not an admin, redirect to access denied page or home
    header("location: access_denied.php");
    exit;
}

// Include database connection file
require_once "config/database.php";

// Include header file
$includesPath = __DIR__ . '/includes/';
if (file_exists($includesPath . 'header.php')) {
    include $includesPath . 'header.php';
} else {
    die("Header file not found. Path: " . $includesPath . 'header.php');
}

// Check connection
if (!isset($conn) || $conn->connect_error) {
    die("<div class='alert alert-danger'>Connection failed: " . ($conn ? $conn->connect_error : "Database connection not established") . "</div>");
}

// Process form submission for adding a new applicant
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_user'])) {
    // Create uploads directory if it doesn't exist
    $uploadDir = "uploads/";
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            $error_message = "Failed to create uploads directory. Please check the server permissions.";
        } else {
            // Create .htaccess file to allow access to files if directory was just created
            $htaccess = $uploadDir . ".htaccess";
            file_put_contents($htaccess, "Options -Indexes\n\n# Allow access to files\n<FilesMatch \"\.(jpg|jpeg|png|gif|pdf)$\">\n    Order Allow,Deny\n    Allow from all\n</FilesMatch>\n\n# Deny access to PHP files\n<FilesMatch \"\.php$\">\n    Order Allow,Deny\n    Deny from all\n</FilesMatch>\n\n# Allow index.php\n<Files \"index.php\">\n    Order Allow,Deny\n    Allow from all\n</Files>");
            chmod($htaccess, 0644);
        }
    }
    
    // Ensure the uploads directory is writable
    if (!is_writable($uploadDir)) {
        chmod($uploadDir, 0755);
        if (!is_writable($uploadDir)) {
            $error_message = "Uploads directory is not writable. Please check the server permissions.";
        }
    }
    
    // Capture form data
    $firstname = $_POST['firstname'] ?? "";
    $lastname = $_POST['lastname'] ?? "";
    $email = $_POST['email'] ?? "";
    $phone = $_POST['phone'] ?? "";
    $gender = $_POST['gender'] ?? "";
    $father_name = $_POST['father_name'] ?? "";
    $father_phone = $_POST['father_phone'] ?? "";
    $mother_name = $_POST['mother_name'] ?? "";
    $mother_phone = $_POST['mother_phone'] ?? "";
    $province = $_POST['province'] ?? "";
    $district = $_POST['district'] ?? "";
    $sector = $_POST['sector'] ?? "";
    $cell = $_POST['cell'] ?? "";
    $village = $_POST['village'] ?? "";

    // File upload handling - use the previously defined uploadDir
    function uploadFile($fileInputName) {
        global $uploadDir;
        // Default max size is 2MB
        $maxSize = 2 * 1024 * 1024; 
        
        // Set specific max size for profile image (1MB)
        if ($fileInputName === 'profile_image') {
            $maxSize = 1 * 1024 * 1024;
            $allowedTypes = ['image/jpeg', 'image/png']; // Only allow images for profile
        } else {
            $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf']; // Allow PDF for documents
        }
        
        $uploadError = null;
        
        // Check if a file was uploaded
        if (empty($_FILES[$fileInputName]["name"])) {
            return ["path" => ""];
        }
        
        // Check file size
        if ($_FILES[$fileInputName]["size"] > $maxSize) {
            $maxSizeMB = $maxSize / (1024 * 1024);
            return ["error" => "File is too large. Maximum size is {$maxSizeMB}MB."];
        }
        
        // Check file type
        $fileType = $_FILES[$fileInputName]["type"];
        if (!in_array($fileType, $allowedTypes)) {
            if ($fileInputName === 'profile_image') {
                return ["error" => "Invalid file type. Only JPG and PNG files are allowed for profile images."];
            } else {
                return ["error" => "Invalid file type. Only JPG, PNG and PDF files are allowed."];
            }
        }
        
        // Sanitize filename and create a unique filename
        $originalName = basename($_FILES[$fileInputName]["name"]);
        $fileExt = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $newFileName = time() . '_' . md5(uniqid()) . '.' . $fileExt;
        $targetFilePath = $uploadDir . $newFileName;
        
        if (move_uploaded_file($_FILES[$fileInputName]["tmp_name"], $targetFilePath)) {
            // Make sure file is readable by web server
            chmod($targetFilePath, 0644);
            return ["path" => $targetFilePath];
        } else {
            return ["error" => "Failed to upload file. Please try again."];
        }
    }

    $uploadErrors = [];
    $idDocumentResult = uploadFile("id_document");
    $diplomaResult = uploadFile("diploma");
    $profileImageResult = uploadFile("profile_image");
    
    // Check for upload errors
    if (isset($idDocumentResult["error"])) {
        $uploadErrors[] = "ID Document: " . $idDocumentResult["error"];
        $id_document = "";
    } else {
        $id_document = $idDocumentResult["path"];
    }
    
    if (isset($diplomaResult["error"])) {
        $uploadErrors[] = "Diploma: " . $diplomaResult["error"];
        $diploma = "";
    } else {
        $diploma = $diplomaResult["path"];
    }
    
    if (isset($profileImageResult["error"])) {
        $uploadErrors[] = "Profile Image: " . $profileImageResult["error"];
        $profile_image = "";
    } else {
        $profile_image = $profileImageResult["path"];
    }
    
    // Combine upload errors if any
    if (!empty($uploadErrors)) {
        $error_message = "File upload issues: " . implode(", ", $uploadErrors);
    }
    
    // Only proceed if there are no upload errors
    if (empty($uploadErrors)) {
        // Prepare and insert statement
        $stmt = $conn->prepare("INSERT INTO applicants 
            (firstname, lastname, email, phone, gender, 
             father_name, father_phone, mother_name, mother_phone, 
             province, district, sector, cell, village, 
             id_document, diploma, profile_image) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        if (!$stmt) {
            $error_message = "Error preparing statement: " . $conn->error;
        } else {
            $stmt->bind_param(
                "sssssssssssssssss",
                $firstname, $lastname, $email, $phone, $gender,
                $father_name, $father_phone, $mother_name, $mother_phone,
                $province, $district, $sector, $cell, $village,
                $id_document, $diploma, $profile_image
            );

            if ($stmt->execute()) {
                $success_message = "Applicant added successfully!";
            } else {
                $error_message = "Error adding applicant: " . $stmt->error;
            }

            $stmt->close();
        }
    }
}

// Delete applicant if delete_id is provided in the URL
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']); // Ensure the ID is an integer
    
    if ($delete_id > 0) {
        // First, get the file paths to delete them
        $stmt = $conn->prepare("SELECT id_document, diploma, profile_image FROM applicants WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
        $stmt->bind_result($id_document, $diploma, $profile_image);
        $stmt->fetch();
        $stmt->close();
        
        // Delete the files if they exist
        $files_to_delete = [$id_document, $diploma, $profile_image];
        foreach ($files_to_delete as $file) {
            if (!empty($file) && file_exists($file)) {
                unlink($file);
            }
        }
        
        // Now delete the record
        $stmt = $conn->prepare("DELETE FROM applicants WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        
        if ($stmt->execute()) {
            $success_message = "Applicant deleted successfully!";
        } else {
            $error_message = "Error deleting applicant: " . $stmt->error;
        }
        
        $stmt->close();
    } else {
        $error_message = "Invalid applicant ID for deletion.";
    }
}

// Check if the applicants table exists
$tableExists = false;
$checkTable = $conn->query("SHOW TABLES LIKE 'applicants'");
if ($checkTable && $checkTable->num_rows > 0) {
    $tableExists = true;
}

// Get all applicants with their files
if ($tableExists) {
    $sql = "SELECT a.*, 
            a.id_document,
            a.diploma,
            a.profile_image
            FROM applicants a
            ORDER BY a.id DESC";
    $result = $conn->query($sql);
} else {
    $error_message = "The applicants table does not exist in the database. Please create it first.";
    $result = null;
}
?>

<div class="container">
<link rel="stylesheet" href="public/css/style.css">
    <h2>Applicant Management</h2>
    
    <div class="mb-3">
        <a href="admin_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
    
    <?php if(isset($success_message)): ?>
        <div class="alert alert-success">
            <?php echo $success_message; ?>
            <?php if(strpos($success_message, "added successfully") !== false): ?>
                <p>You can <a href="#view-users" class="view-applicants-link">view all applicants</a> or add another one.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <?php if(isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>
    
    <!-- File Preview Modal -->
    <div id="filePreviewModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <div id="filePreviewContent"></div>
        </div>
    </div>
    
    <div class="tab-container">
        <div class="tabs">
            <button class="tab-btn active" data-tab="view-users">View Applicants</button>
            <button class="tab-btn" data-tab="add-user">Add New Applicant</button>
        </div>
        
        <div id="view-users" class="tab-content active">
            <?php if ($tableExists && $result && $result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Gender</th>
                                <th>Location</th>
                                <th>Parents</th>
                                <th>Files</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td><?php echo htmlspecialchars($row['firstname'] . ' ' . $row['lastname']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                    <td><?php echo htmlspecialchars(ucfirst($row['gender'])); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($row['province'] . ', ' . $row['district'] . ', ' . $row['sector'] . ', ' . $row['cell'] . ', ' . $row['village']); ?>
                                    </td>
                                    <td>
                                        <strong>Father:</strong> <?php echo htmlspecialchars($row['father_name']); ?> (<?php echo htmlspecialchars($row['father_phone']); ?>)<br>
                                        <strong>Mother:</strong> <?php echo htmlspecialchars($row['mother_name']); ?> (<?php echo htmlspecialchars($row['mother_phone']); ?>)
                                    </td>
                                    <td class="user-files">
                                        <?php if (!empty($row['id_document'])): ?>
                                            <?php 
                                                $file_extension = strtolower(pathinfo($row['id_document'], PATHINFO_EXTENSION));
                                                $icon_class = $file_extension == 'pdf' ? 'fa-file-pdf' : 'fa-file-image';
                                                $file_type = $file_extension == 'pdf' ? 'PDF' : strtoupper($file_extension);
                                            ?>
                                            <a href="view_file.php?file=<?php echo urlencode($row['id_document']); ?>" target="_blank" class="file-link id-file">
                                                <i class="fa-solid <?php echo $icon_class; ?>"></i> ID Document (<?php echo $file_type; ?>)
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($row['diploma'])): ?>
                                            <?php 
                                                $file_extension = strtolower(pathinfo($row['diploma'], PATHINFO_EXTENSION));
                                                $icon_class = $file_extension == 'pdf' ? 'fa-file-pdf' : 'fa-file-image';
                                                $file_type = $file_extension == 'pdf' ? 'PDF' : strtoupper($file_extension);
                                            ?>
                                            <a href="view_file.php?file=<?php echo urlencode($row['diploma']); ?>" target="_blank" class="file-link diploma-file">
                                                <i class="fa-solid <?php echo $icon_class; ?>"></i> Diploma (<?php echo $file_type; ?>)
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($row['profile_image'])): ?>
                                            <a href="view_file.php?file=<?php echo urlencode($row['profile_image']); ?>" target="_blank">
                                                <img src="view_file.php?file=<?php echo urlencode($row['profile_image']); ?>" alt="Profile Image" class="profile-thumbnail" style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;">
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <!-- Delete button with confirmation -->
                                        <a href="view_applicants.php?delete_id=<?php echo $row['id']; ?>" 
                                           onclick="return confirm('Are you sure you want to delete this applicant?');" 
                                           class="btn btn-danger">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="no-data">
                    <p>No applicants registered yet.</p>
                    <a href="#add-user" class="btn btn-primary add-first-applicant">Add Your First Applicant</a>
                </div>
            <?php endif; ?>
        </div>
        
        <div id="add-user" class="tab-content">
            <form action="view_applicants.php" method="post" enctype="multipart/form-data" class="registration-form">
                <fieldset>
                    <legend>Personal Information</legend>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="firstname">First Name *</label>
                            <input type="text" id="firstname" name="firstname" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="lastname">Last Name *</label>
                            <input type="text" id="lastname" name="lastname" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number *</label>
                            <input type="tel" id="phone" name="phone" class="form-control" required>
                        </div>
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
                
                <fieldset>
                    <legend>Parent Information</legend>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="father_name">Father's Name *</label>
                            <input type="text" id="father_name" name="father_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="father_phone">Father's Phone Number *</label>
                            <input type="tel" id="father_phone" name="father_phone" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="mother_name">Mother's Name *</label>
                            <input type="text" id="mother_name" name="mother_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="mother_phone">Mother's Phone Number *</label>
                            <input type="tel" id="mother_phone" name="mother_phone" class="form-control" required>
                        </div>
                    </div>
                </fieldset>
                
                <fieldset>
                    <legend>Place of Issue</legend>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="province">Province *</label>
                            <input type="text" id="province" name="province" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="district">District *</label>
                            <input type="text" id="district" name="district" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="sector">Sector *</label>
                            <input type="text" id="sector" name="sector" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="cell">Cell *</label>
                            <input type="text" id="cell" name="cell" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="village">Village *</label>
                            <input type="text" id="village" name="village" class="form-control" required>
                        </div>
                    </div>
                </fieldset>
                
                <fieldset>
                    <legend>Document Upload</legend>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="id_document">ID Document (PDF, JPG, PNG) *</label>
                            <input type="file" id="id_document" name="id_document" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                            <small class="form-text">Maximum file size: 2MB</small>
                        </div>
                        <div class="form-group">
                            <label for="diploma">Diploma/Certificate (PDF, JPG, PNG) *</label>
                            <input type="file" id="diploma" name="diploma" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                            <small class="form-text">Maximum file size: 2MB</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="profile_image">Profile Image (JPG, PNG) *</label>
                        <input type="file" id="profile_image" name="profile_image" class="form-control" accept=".jpg,.jpeg,.png" required>
                        <small class="form-text">Maximum file size: 1MB</small>
                        <div id="image-preview" class="mt-2" style="display: none;">
                            <img id="preview-img" src="#" alt="Preview" style="max-width: 100px; max-height: 100px;">
                        </div>
                    </div>
                </fieldset>
                
                <input type="hidden" name="add_user" value="1">
                <div class="form-group submit-group">
                    <button type="submit" class="btn btn-primary">Add Applicant</button>
                    <button type="reset" class="btn btn-secondary">Reset Form</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openTab(tabId) {
        // Hide all tab contents
        var tabContents = document.getElementsByClassName('tab-content');
        for (var i = 0; i < tabContents.length; i++) {
            tabContents[i].classList.remove('active');
        }
        
        // Deactivate all tabs
        var tabButtons = document.getElementsByClassName('tab-btn');
        for (var i = 0; i < tabButtons.length; i++) {
            tabButtons[i].classList.remove('active');
        }
        
        // Show the selected tab content and activate the tab
        document.getElementById(tabId).classList.add('active');
        document.querySelector('button[data-tab="' + tabId + '"]').classList.add('active');
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        // Check if submission had errors or is a new form
        <?php if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_user'])): ?>
            <?php if(isset($error_message)): ?>
                // Form had errors, stay on add-user tab
                openTab('add-user');
            <?php elseif(isset($success_message)): ?>
                // Form was successful, default behavior (stay on current tab)
                // The success message includes a link to switch tabs if desired
            <?php endif; ?>
        <?php else: ?>
            // Check URL hash for tab selection on normal page load
            var hash = window.location.hash.substring(1);
            if (hash === 'add-user') {
                openTab('add-user');
            }
        <?php endif; ?>
        
        // Add click handlers for tab buttons
        var tabButtons = document.getElementsByClassName('tab-btn');
        for (var i = 0; i < tabButtons.length; i++) {
            tabButtons[i].addEventListener('click', function() {
                var tabId = this.getAttribute('data-tab');
                openTab(tabId);
                window.location.hash = tabId;
            });
        }
        
        // Add click handler for "Add Your First Applicant" link
        var addFirstLink = document.querySelector('.add-first-applicant');
        if (addFirstLink) {
            addFirstLink.addEventListener('click', function(e) {
                e.preventDefault();
                openTab('add-user');
                window.location.hash = 'add-user';
            });
        }
        
        // Add click handler for "view all applicants" link in success message
        var viewApplicantsLink = document.querySelector('.view-applicants-link');
        if (viewApplicantsLink) {
            viewApplicantsLink.addEventListener('click', function(e) {
                e.preventDefault();
                openTab('view-users');
                window.location.hash = 'view-users';
            });
        }
        
        // Add form validation to ensure required fields are filled
        var addUserForm = document.querySelector('form.registration-form');
        if (addUserForm) {
            addUserForm.addEventListener('submit', function(e) {
                var isValid = this.checkValidity();
                if (!isValid) {
                    e.preventDefault();
                    // Highlight the first invalid field
                    var invalidField = this.querySelector(':invalid');
                    if (invalidField) {
                        invalidField.focus();
                    }
                    // Make sure we stay on the add-user tab
                    openTab('add-user');
                    window.location.hash = 'add-user';
                }
            });
        }
    });
    
    // File preview modal functions
    function previewFile(filePath, fileType) {
        var modal = document.getElementById("filePreviewModal");
        var previewContent = document.getElementById("filePreviewContent");
        
        // Clear previous content
        previewContent.innerHTML = '';
        
        if (fileType === 'pdf') {
            // PDF viewer
            previewContent.innerHTML = '<iframe src="' + filePath + '" width="100%" height="500px"></iframe>';
        } else {
            // Image viewer
            previewContent.innerHTML = '<img src="' + filePath + '" style="max-width: 100%; max-height: 80vh;">';
        }
        
        modal.style.display = "block";
        
        // Add error handling for file loading
        var contentElement = previewContent.querySelector('iframe, img');
        if (contentElement) {
            contentElement.onerror = function() {
                previewContent.innerHTML = '<div class="alert alert-danger">Error loading file. The file may be missing or inaccessible.</div>';
            };
        }
    }
    
    // Close modal when clicking the X
    document.querySelector(".close-modal").addEventListener("click", function() {
        document.getElementById("filePreviewModal").style.display = "none";
    });
    
    // Close modal when clicking outside the content
    window.addEventListener("click", function(event) {
        var modal = document.getElementById("filePreviewModal");
        if (event.target == modal) {
            modal.style.display = "none";
        }
    });
    
    // Add image preview functionality
    document.getElementById('profile_image').addEventListener('change', function(e) {
        var preview = document.getElementById('image-preview');
        var previewImg = document.getElementById('preview-img');
        var file = this.files[0];
        
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(file);
        } else {
            preview.style.display = 'none';
        }
    });
</script>

<style>
.user-files {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.file-link {
    display: inline-flex;
    align-items: center;
    padding: 3px 8px;
    background-color: #f0f0f0;
    border-radius: 4px;
    color: #333;
    text-decoration: none;
    font-size: 0.9em;
    transition: background-color 0.2s;
}

.file-link:hover {
    background-color: #e0e0e0;
    text-decoration: none;
}

.id-file {
    background-color: #e6f7ff;
    color: #0066cc;
}

.diploma-file {
    background-color: #f0f7e6;
    color: #5c8a00;
}

.profile-thumbnail {
    border: 2px solid #fff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    transition: transform 0.2s;
}

.profile-thumbnail:hover {
    transform: scale(1.1);
}

/* Modal styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.7);
}

.modal-content {
    position: relative;
    background-color: #fefefe;
    margin: 5% auto;
    padding: 20px;
    border-radius: 5px;
    width: 80%;
    max-width: 900px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.close-modal {
    position: absolute;
    top: 10px;
    right: 15px;
    color: #aaa;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close-modal:hover {
    color: #333;
}

#filePreviewContent {
    margin-top: 20px;
    text-align: center;
}
</style>

<?php
// Close connection
$conn->close();

// Include footer file
if (file_exists($includesPath . 'footer.php')) {
    include $includesPath . 'footer.php';
} else {
    echo "</body></html>";
}
?>