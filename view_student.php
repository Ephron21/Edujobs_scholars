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
    
    // Prepare and execute query
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

// Function to format the creation date nicely
function formatDate($dateString) {
    if (!$dateString) return 'N/A';
    $date = new DateTime($dateString);
    return $date->format('F j, Y \a\t g:i A');
}

// Map grade level to text description
$gradeLevelText = [
    1 => 'Level 1 (First Year)',
    2 => 'Level 2 (Second Year)',
    3 => 'Level 3 (Third Year)',
    4 => 'Level 4 (Final Year)'
][$student['grade_level']] ?? 'Unknown Level';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Student: <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 20px;
            padding-bottom: 20px;
        }
        .container {
            max-width: 800px;
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
        .student-info {
            background-color: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .info-label {
            color: #5a5c69;
            font-weight: 600;
            margin-bottom: 0.3rem;
        }
        .info-value {
            font-size: 1.1rem;
            margin-bottom: 1.2rem;
        }
        .badge-level {
            padding: 0.4rem 0.8rem;
            font-size: 0.9rem;
            border-radius: 0.5rem;
        }
        .level-1 { background-color: #36b9cc; }
        .level-2 { background-color: #1cc88a; }
        .level-3 { background-color: #f6c23e; }
        .level-4 { background-color: #e74a3b; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-user-graduate me-2"></i>Student Profile
                </h5>
                <a href="manage_students.php" class="btn btn-light btn-sm">
                    <i class="fas fa-arrow-left me-1"></i>Back to List
                </a>
            </div>
            
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></h4>
                    <span class="badge badge-level level-<?php echo $student['grade_level']; ?>">
                        <?php echo htmlspecialchars($gradeLevelText); ?>
                    </span>
                </div>
                
                <div class="student-info">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="info-label">Registration Number</p>
                            <p class="info-value"><?php echo htmlspecialchars($student['reg_number'] ?? 'N/A'); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="info-label">Email Address</p>
                            <p class="info-value"><?php echo htmlspecialchars($student['email']); ?></p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <p class="info-label">Phone Number</p>
                            <p class="info-value"><?php echo htmlspecialchars($student['phone'] ?? 'N/A'); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="info-label">Password/PIN</p>
                            <p class="info-value"><?php echo htmlspecialchars($student['pin'] ?? 'N/A'); ?></p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <p class="info-label">Registration Date</p>
                            <p class="info-value"><?php echo formatDate($student['created_at']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="info-label">Last Updated</p>
                            <p class="info-value"><?php echo formatDate($student['updated_at']); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-center gap-2">
                    <a href="edit_student.php?id=<?php echo $student['id']; ?>" class="btn btn-primary">
                        <i class="fas fa-edit me-1"></i>Edit Student
                    </a>
                    <a href="manage_students.php?action=delete&id=<?php echo $student['id']; ?>" 
                       class="btn btn-danger"
                       onclick="return confirm('Are you sure you want to delete this student?')">
                        <i class="fas fa-trash me-1"></i>Delete Student
                    </a>
                    <a href="student_print.php?id=<?php echo $student['id']; ?>&print=true" class="btn btn-success" target="_blank">
                        <i class="fas fa-print me-1"></i>Print Information
                    </a>
                </div>
            </div>
        </div>
        
        <div class="text-center">
            <a href="manage_students.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Students List
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 