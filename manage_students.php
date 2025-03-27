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
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Connect to the database
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Handle delete requests
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $deleteStmt = $conn->prepare("DELETE FROM students WHERE id = ?");
        $deleteStmt->execute([$id]);
        
        // Redirect to remove the action from URL
        header("Location: manage_students.php");
        exit;
    }
    
    // Count total students for pagination
    if (!empty($searchTerm)) {
        $countStmt = $conn->prepare("SELECT COUNT(*) FROM students WHERE 
            first_name LIKE ? OR 
            last_name LIKE ? OR 
            email LIKE ? OR 
            reg_number LIKE ?");
        $searchPattern = "%$searchTerm%";
        $countStmt->execute([$searchPattern, $searchPattern, $searchPattern, $searchPattern]);
    } else {
        $countStmt = $conn->query("SELECT COUNT(*) FROM students");
    }
    
    $totalStudents = $countStmt->fetchColumn();
    $totalPages = ceil($totalStudents / $perPage);
    
    // Ensure page is within valid range
    $page = max(1, min($page, $totalPages));
    $offset = ($page - 1) * $perPage;
    
    // Get students with pagination and search
    if (!empty($searchTerm)) {
        $stmt = $conn->prepare("SELECT * FROM students WHERE 
            first_name LIKE ? OR 
            last_name LIKE ? OR 
            email LIKE ? OR 
            reg_number LIKE ? 
            ORDER BY id DESC LIMIT ?, ?");
        $searchPattern = "%$searchTerm%";
        $stmt->bindParam(1, $searchPattern, PDO::PARAM_STR);
        $stmt->bindParam(2, $searchPattern, PDO::PARAM_STR);
        $stmt->bindParam(3, $searchPattern, PDO::PARAM_STR);
        $stmt->bindParam(4, $searchPattern, PDO::PARAM_STR);
        $stmt->bindParam(5, $offset, PDO::PARAM_INT);
        $stmt->bindParam(6, $perPage, PDO::PARAM_INT);
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("SELECT * FROM students ORDER BY id DESC LIMIT ?, ?");
        $stmt->bindParam(1, $offset, PDO::PARAM_INT);
        $stmt->bindParam(2, $perPage, PDO::PARAM_INT);
        $stmt->execute();
    }
    
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 20px;
            padding-bottom: 20px;
        }
        .container {
            max-width: 1200px;
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
        .table th {
            background-color: #f8f9fa;
            color: #5a5c69;
            font-weight: 500;
        }
        .action-btn {
            font-size: 0.85rem;
            padding: 0.25rem 0.5rem;
        }
        .badge-level {
            padding: 0.4rem 0.6rem;
            font-size: 0.85rem;
        }
        .level-1 { background-color: #36b9cc; }
        .level-2 { background-color: #1cc88a; }
        .level-3 { background-color: #f6c23e; }
        .level-4 { background-color: #e74a3b; }
        .search-box {
            position: relative;
        }
        .search-box .fa-search {
            position: absolute;
            top: 10px;
            left: 10px;
            color: #6c757d;
        }
        .search-box input {
            padding-left: 30px;
        }
        .pagination {
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-users me-2"></i>Manage Students</h5>
                <a href="add_student.php" class="btn btn-light btn-sm">
                    <i class="fas fa-plus me-1"></i>Add New Student
                </a>
            </div>
            
            <div class="card-body">
                <?php if (isset($_GET['deleted'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        Successfully deleted <?php echo (int)$_GET['deleted']; ?> student<?php echo (int)$_GET['deleted'] !== 1 ? 's' : ''; ?>.
                    </div>
                <?php endif; ?>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <form action="" method="GET" class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" name="search" class="form-control" placeholder="Search by name, email or reg number..." value="<?php echo htmlspecialchars($searchTerm); ?>">
                        </form>
                    </div>
                    <div class="col-md-6 text-md-end mt-3 mt-md-0">
                        <span class="text-muted">
                            <?php echo $totalStudents; ?> student<?php echo $totalStudents !== 1 ? 's' : ''; ?> found
                        </span>
                    </div>
                </div>
                
                <?php if (count($students) > 0): ?>
                    <form id="bulkActionForm" method="POST">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <button type="button" class="btn btn-sm btn-outline-secondary me-2" id="selectAllBtn">
                                        <i class="fas fa-check-square me-1"></i>Select All
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAllBtn">
                                        <i class="fas fa-square me-1"></i>Deselect All
                                    </button>
                                </div>
                                <div class="bulk-actions">
                                    <button type="button" class="btn btn-sm btn-danger" id="bulkDeleteBtn" disabled>
                                        <i class="fas fa-trash me-1"></i>Delete Selected
                                    </button>
                                </div>
                            </div>
                        </div>
                    
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered">
                                <thead>
                                    <tr>
                                        <th width="40px">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="checkAll">
                                            </div>
                                        </th>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Reg Number</th>
                                        <th>Level</th>
                                        <th>Password</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $student): ?>
                                    <tr>
                                        <td>
                                            <div class="form-check">
                                                <input class="form-check-input student-checkbox" type="checkbox" name="student_ids[]" value="<?php echo $student['id']; ?>">
                                            </div>
                                        </td>
                                        <td><?php echo $student['id']; ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                                        <td><?php echo htmlspecialchars($student['reg_number'] ?? 'N/A'); ?></td>
                                        <td>
                                            <span class="badge level-<?php echo $student['grade_level']; ?>">
                                                Level <?php echo $student['grade_level']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($student['pin'] ?? 'N/A'); ?></td>
                                        <td>
                                            <a href="view_student.php?id=<?php echo $student['id']; ?>" class="btn btn-info btn-sm action-btn">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="edit_student.php?id=<?php echo $student['id']; ?>" class="btn btn-primary btn-sm action-btn">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="manage_students.php?action=delete&id=<?php echo $student['id']; ?>" 
                                            class="btn btn-danger btn-sm action-btn"
                                            onclick="return confirm('Are you sure you want to delete this student?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </form>
                    
                    <?php if ($totalPages > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($searchTerm); ?>">
                                    Previous
                                </a>
                            </li>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($page + 2, $totalPages); $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($searchTerm); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                            <?php endfor; ?>
                            
                            <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($searchTerm); ?>">
                                    Next
                                </a>
                            </li>
                        </ul>
                    </nav>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        No students found. <?php echo !empty($searchTerm) ? 'Try a different search term or ' : ''; ?>
                        <a href="add_student.php" class="alert-link">add a new student</a>.
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <a href="add_student.php" class="btn btn-secondary me-2">
                <i class="fas fa-user-plus me-1"></i>Add Student
            </a>
            <a href="student_print.php?print=true" class="btn btn-success" target="_blank">
                <i class="fas fa-print me-1"></i>Print All Students
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get elements
            const checkAll = document.getElementById('checkAll');
            const studentCheckboxes = document.querySelectorAll('.student-checkbox');
            const selectAllBtn = document.getElementById('selectAllBtn');
            const deselectAllBtn = document.getElementById('deselectAllBtn');
            const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
            
            // Function to update bulk action button state
            function updateBulkActions() {
                const checkedCount = document.querySelectorAll('.student-checkbox:checked').length;
                if (checkedCount > 0) {
                    bulkDeleteBtn.removeAttribute('disabled');
                } else {
                    bulkDeleteBtn.setAttribute('disabled', 'disabled');
                }
            }
            
            // Check/Uncheck all checkboxes
            if (checkAll) {
                checkAll.addEventListener('change', function() {
                    const isChecked = this.checked;
                    studentCheckboxes.forEach(checkbox => {
                        checkbox.checked = isChecked;
                    });
                    updateBulkActions();
                });
            }
            
            // Update "check all" state based on individual checkboxes
            studentCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const allChecked = document.querySelectorAll('.student-checkbox').length === 
                                     document.querySelectorAll('.student-checkbox:checked').length;
                    if (checkAll) {
                        checkAll.checked = allChecked;
                    }
                    updateBulkActions();
                });
            });
            
            // Select All button
            if (selectAllBtn) {
                selectAllBtn.addEventListener('click', function() {
                    studentCheckboxes.forEach(checkbox => {
                        checkbox.checked = true;
                    });
                    if (checkAll) {
                        checkAll.checked = true;
                    }
                    updateBulkActions();
                });
            }
            
            // Deselect All button
            if (deselectAllBtn) {
                deselectAllBtn.addEventListener('click', function() {
                    studentCheckboxes.forEach(checkbox => {
                        checkbox.checked = false;
                    });
                    if (checkAll) {
                        checkAll.checked = false;
                    }
                    updateBulkActions();
                });
            }
            
            // Bulk Delete button
            if (bulkDeleteBtn) {
                bulkDeleteBtn.addEventListener('click', function() {
                    const checkedIds = Array.from(document.querySelectorAll('.student-checkbox:checked'))
                        .map(checkbox => checkbox.value);
                    
                    if (checkedIds.length > 0) {
                        window.location.href = 'batch_operations.php?action=delete&ids=' + checkedIds.join(',');
                    }
                });
            }
            
            // Initialize bulk action button state
            updateBulkActions();
        });
    </script>
</body>
</html> 