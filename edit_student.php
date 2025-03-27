<?php
// Include authentication check
require_once 'auth_check.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$host = 'localhost';
$dbname = 'registration_system';
$username = 'root';
$password = 'Diano21@Esron21%';

// Initialize variables
$errors = [];
$success = false;

// Check if the ID parameter exists
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage_students.php");
    exit;
}

$id = (int)$_GET['id'];

try {
    // Connect to the database
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Handle form submission for updating student
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $regNumber = trim($_POST['reg_number'] ?? '');
        $gradeLevel = (int)($_POST['grade_level'] ?? 1);
        $pin = trim($_POST['pin'] ?? '');
        
        // Basic validation
        if (empty($firstName)) $errors[] = "First name is required";
        if (empty($lastName)) $errors[] = "Last name is required";
        if (empty($email)) $errors[] = "Email is required";
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email format is invalid";
        if (empty($regNumber)) $errors[] = "Registration number is required";
        if ($gradeLevel < 1 || $gradeLevel > 4) $errors[] = "Grade level must be between 1 and 4";
        if (empty($pin)) $errors[] = "Password is required";
        
        // Check if email exists (other than current student)
        if (!empty($email)) {
            $checkStmt = $conn->prepare("SELECT id FROM students WHERE email = ? AND id != ?");
            $checkStmt->execute([$email, $id]);
            if ($checkStmt->fetchColumn()) {
                $errors[] = "Email already exists for another student";
            }
        }
        
        // Check if registration number exists (other than current student)
        if (!empty($regNumber)) {
            $checkStmt = $conn->prepare("SELECT id FROM students WHERE reg_number = ? AND id != ?");
            $checkStmt->execute([$regNumber, $id]);
            if ($checkStmt->fetchColumn()) {
                $errors[] = "Registration number already exists for another student";
            }
        }
        
        // If no errors, update the student
        if (empty($errors)) {
            $updateStmt = $conn->prepare("
                UPDATE students SET 
                    first_name = ?, 
                    last_name = ?, 
                    email = ?, 
                    phone = ?, 
                    reg_number = ?, 
                    grade_level = ?,
                    pin = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            
            $result = $updateStmt->execute([
                $firstName, 
                $lastName, 
                $email, 
                $phone, 
                $regNumber, 
                $gradeLevel,
                $pin,
                $id
            ]);
            
            if ($result) {
                $success = true;
            } else {
                $errors[] = "Failed to update student information";
            }
        }
    }
    
    // Get student data for the form
    $stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$id]);
    
    // Check if student exists
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$student) {
        header("Location: manage_students.php");
        exit;
    }
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student: <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 20px;
            padding-bottom: 20px;
        }
        .container {
            max-width: 900px;
        }
        .card {
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            border: none;
            border-radius: 0.5rem;
            overflow: hidden;
        }
        .card-header {
            background-color: #4e73df;
            color: white;
            padding: 1rem;
            border-bottom: none;
        }
        .form-label {
            font-weight: 500;
            color: #5a5c69;
        }
        .required::after {
            content: "*";
            color: #e74a3b;
            margin-left: 2px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-user-edit me-2"></i>Edit Student Information
                </h5>
                <a href="manage_students.php" class="btn btn-light btn-sm">
                    <i class="fas fa-arrow-left me-1"></i>Back to List
                </a>
            </div>
            
            <div class="card-body p-4">
                <?php if ($success): ?>
                    <div class="alert alert-success" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        Student information has been updated successfully!
                        <div class="mt-3">
                            <a href="view_student.php?id=<?php echo $id; ?>" class="alert-link">View Student Profile</a> or <a href="manage_students.php" class="alert-link">Return to Student List</a>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="edit_student.php?id=<?php echo $id; ?>">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label required">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($student['first_name']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="last_name" class="form-label required">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($student['last_name']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="email" class="form-label required">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($student['phone'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="reg_number" class="form-label required">Registration Number</label>
                            <input type="text" class="form-control" id="reg_number" name="reg_number" value="<?php echo htmlspecialchars($student['reg_number'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="grade_level" class="form-label required">Grade Level</label>
                            <select class="form-select" id="grade_level" name="grade_level" required>
                                <option value="1" <?php echo $student['grade_level'] == 1 ? 'selected' : ''; ?>>Level 1 (First Year)</option>
                                <option value="2" <?php echo $student['grade_level'] == 2 ? 'selected' : ''; ?>>Level 2 (Second Year)</option>
                                <option value="3" <?php echo $student['grade_level'] == 3 ? 'selected' : ''; ?>>Level 3 (Third Year)</option>
                                <option value="4" <?php echo $student['grade_level'] == 4 ? 'selected' : ''; ?>>Level 4 (Final Year)</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="pin" class="form-label required">Password/PIN</label>
                            <input type="text" class="form-control" id="pin" name="pin" value="<?php echo htmlspecialchars($student['pin'] ?? ''); ?>" required>
                            <div class="form-text">This is stored as plaintext for demonstration purposes.</div>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Save Changes
                        </button>
                        <a href="view_student.php?id=<?php echo $id; ?>" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 