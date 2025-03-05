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

    // File upload handling
    $uploadDir = "uploads/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    function uploadFile($fileInputName) {
        global $uploadDir;
        if (!empty($_FILES[$fileInputName]["name"])) {
            $fileName = time() . "_" . basename($_FILES[$fileInputName]["name"]);
            $targetFilePath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES[$fileInputName]["tmp_name"], $targetFilePath)) {
                return $targetFilePath;
            } else {
                return false;
            }
        }
        return "";
    }

    $id_document = uploadFile("id_document");
    $diploma = uploadFile("diploma");
    $profile_image = uploadFile("profile_image");

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
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    
    <?php if(isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>
    
    <div class="tab-container">
        <div class="tabs">
            <button class="tab-btn active" onclick="openTab('view-users')">View Applicants</button>
            <button class="tab-btn" onclick="openTab('add-user')">Add New Applicant</button>
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
                                            <a href="<?php echo htmlspecialchars($row['id_document']); ?>" target="_blank" class="file-link id-file">ID</a>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($row['diploma'])): ?>
                                            <a href="<?php echo htmlspecialchars($row['diploma']); ?>" target="_blank" class="file-link diploma-file">Diploma</a>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($row['profile_image'])): ?>
                                            <a href="<?php echo htmlspecialchars($row['profile_image']); ?>" target="_blank">
                                                <img src="<?php echo htmlspecialchars($row['profile_image']); ?>" alt="Profile Image" class="profile-thumbnail">
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
                    <a href="#" onclick="openTab('add-user')" class="btn btn-primary">Add Your First Applicant</a>
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
                            <input type="file" id="id_document" name="id_document" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                            <small class="form-text">Maximum file size: 2MB</small>
                        </div>
                        <div class="form-group">
                            <label for="diploma">Diploma/Certificate (PDF, JPG, PNG) *</label>
                            <input type="file" id="diploma" name="diploma" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                            <small class="form-text">Maximum file size: 2MB</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="profile_image">Profile Image (JPG, PNG) *</label>
                        <input type="file" id="profile_image" name="profile_image" class="form-control" accept=".jpg,.jpeg,.png">
                        <small class="form-text">Maximum file size: 1MB</small>
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
        document.querySelector('button[onclick="openTab(\'' + tabId + '\')"]').classList.add('active');
    }
</script>

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