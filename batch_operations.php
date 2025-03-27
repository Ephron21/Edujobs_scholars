<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$host = 'localhost';
$dbname = 'registration_system';
$username = 'root';
$password = 'Diano21@Esron21%';

// Determine action type
$action = isset($_GET['action']) ? $_GET['action'] : '';
$ids = isset($_GET['ids']) ? $_GET['ids'] : '';
$confirm = isset($_GET['confirm']) && $_GET['confirm'] === 'yes';

// Parse IDs into an array
$id_array = [];
if (!empty($ids)) {
    $id_array = array_map('intval', explode(',', $ids));
}

// Connect to the database
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Handle delete action if confirmed
    if ($action === 'delete' && !empty($id_array) && $confirm) {
        // Create placeholders for the prepared statement
        $placeholders = implode(',', array_fill(0, count($id_array), '?'));
        
        // Delete the students
        $stmt = $conn->prepare("DELETE FROM students WHERE id IN ($placeholders)");
        $stmt->execute($id_array);
        
        $count = $stmt->rowCount();
        
        // Redirect back to the manage page with success message
        header("Location: manage_students.php?deleted={$count}");
        exit;
    }
    
    // Fetch data about the selected students for confirmation
    if (!empty($id_array) && !$confirm) {
        // Create placeholders for the prepared statement
        $placeholders = implode(',', array_fill(0, count($id_array), '?'));
        
        // Query the selected students
        $stmt = $conn->prepare("SELECT id, first_name, last_name, email, reg_number, grade_level FROM students WHERE id IN ($placeholders)");
        $stmt->execute($id_array);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Map grade levels to text descriptions
$gradeLevelMap = [
    1 => 'Level 1 (First Year)',
    2 => 'Level 2 (Second Year)',
    3 => 'Level 3 (Third Year)',
    4 => 'Level 4 (Final Year)'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Batch Operations - Student Registration System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 20px;
            padding-bottom: 20px;
        }
        .container {
            max-width: 1000px;
        }
        .card {
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            border: none;
            border-radius: 0.5rem;
            overflow: hidden;
        }
        .card-header {
            background-color: #e74a3b;
            color: white;
            padding: 1rem;
            border-bottom: none;
        }
        .table th {
            background-color: #f8f9fa;
            color: #5a5c69;
        }
        .badge-level {
            padding: 0.4rem 0.6rem;
            font-size: 0.85rem;
        }
        .level-1 { background-color: #36b9cc; }
        .level-2 { background-color: #1cc88a; }
        .level-3 { background-color: #f6c23e; }
        .level-4 { background-color: #e74a3b; }
        .warning-icon {
            font-size: 3rem;
            color: #e74a3b;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo ($action === 'delete') ? 'Confirm Deletion' : 'Batch Operation'; ?>
                </h5>
            </div>
            
            <div class="card-body p-4">
                <?php if ($action === 'delete' && !empty($students)): ?>
                    <div class="text-center mb-4">
                        <i class="fas fa-trash-alt warning-icon mb-3"></i>
                        <h4>Are you sure you want to delete these <?php echo count($students); ?> students?</h4>
                        <p class="text-muted">This action cannot be undone. All data related to these students will be permanently removed.</p>
                    </div>
                    
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Registration Number</th>
                                    <th>Grade Level</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?php echo $student['id']; ?></td>
                                    <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($student['email']); ?></td>
                                    <td><?php echo htmlspecialchars($student['reg_number']); ?></td>
                                    <td>
                                        <span class="badge badge-level level-<?php echo $student['grade_level']; ?>">
                                            <?php echo htmlspecialchars($gradeLevelMap[$student['grade_level']] ?? 'Unknown Level'); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="d-flex justify-content-center gap-3">
                        <a href="batch_operations.php?action=delete&ids=<?php echo $ids; ?>&confirm=yes" class="btn btn-danger">
                            <i class="fas fa-trash-alt me-1"></i>Yes, Delete All
                        </a>
                        <a href="manage_students.php" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>Cancel
                        </a>
                    </div>
                <?php elseif ($action === 'delete' && empty($students)): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        No students found with the provided IDs.
                    </div>
                    <div class="text-center">
                        <a href="manage_students.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Back to Students List
                        </a>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        No valid operation specified.
                    </div>
                    <div class="text-center">
                        <a href="manage_students.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Back to Students List
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 