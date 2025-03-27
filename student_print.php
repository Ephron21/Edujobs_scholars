<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$host = 'localhost';
$dbname = 'registration_system';
$username = 'root';
$password = 'Diano21@Esron21%';

// Check if print is requested
if (isset($_GET['print']) && $_GET['print'] === 'true') {
    // Determine which students to print
    $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
    $students = [];
    
    try {
        // Connect to the database
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        if ($id) {
            // Fetch a specific student
            $stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
            $stmt->execute([$id]);
        } else {
            // Query all students
            $stmt = $conn->query("SELECT * FROM students ORDER BY first_name, last_name");
        }
        
        // Fetch all matching students
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
    
    // Function to format date
    function formatDate($dateString) {
        if (!$dateString) return 'N/A';
        $date = new DateTime($dateString);
        return $date->format('F j, Y');
    }
    
    // Map grade levels to text description
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
    <title><?php echo $id ? 'Student Card' : 'All Students Information'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #4e73df;
            padding-bottom: 15px;
        }
        .header h1 {
            color: #4e73df;
        }
        .student-card {
            background-color: white;
            border: none;
            border-radius: 10px;
            margin-bottom: 25px;
            padding: 20px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            break-inside: avoid;
        }
        .student-card h3 {
            color: #4e73df;
            border-bottom: 1px solid #e3e6f0;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .student-info {
            margin-bottom: 20px;
        }
        .info-row {
            display: flex;
            margin-bottom: 8px;
        }
        .label {
            font-weight: 600;
            min-width: 180px;
            color: #5a5c69;
        }
        .value {
            flex: 1;
        }
        .print-btn {
            margin-bottom: 20px;
            background-color: #4e73df;
            border: none;
        }
        .print-btn:hover {
            background-color: #2e59d9;
        }
        .badge-level {
            padding: 5px 10px;
            border-radius: 5px;
            color: white;
            font-weight: normal;
            font-size: 0.9em;
        }
        .level-1 { background-color: #36b9cc; }
        .level-2 { background-color: #1cc88a; }
        .level-3 { background-color: #f6c23e; }
        .level-4 { background-color: #e74a3b; }
        .signature-box {
            margin-top: 40px;
            border-top: 1px dashed #ddd;
            padding-top: 15px;
        }
        .signature-line {
            border-top: 1px solid #000;
            width: 200px;
            margin-top: 40px;
            margin-bottom: 5px;
        }
        @media print {
            .print-btn, .no-print {
                display: none;
            }
            body {
                padding: 0;
                background-color: white;
            }
            .container {
                width: 100%;
                max-width: 100%;
            }
            .student-card {
                box-shadow: none;
                border: 1px solid #ddd;
            }
            .header h1 {
                color: #000;
            }
            .student-card h3 {
                color: #000;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-graduation-cap me-2"></i><?php echo $id ? 'Student Identification Card' : 'Student Records'; ?></h1>
            <p class="text-muted">Generated on: <?php echo date('F j, Y \a\t g:i A'); ?></p>
        </div>
        
        <button class="btn btn-primary print-btn" onclick="window.print()">
            <i class="fas fa-print me-1"></i>Print this page
        </button>
        
        <?php if (empty($students)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>No student records found.
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($students as $student): ?>
                    <div class="col-12">
                        <div class="student-card">
                            <h3>
                                <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                                <span class="badge badge-level level-<?php echo $student['grade_level']; ?> float-end">
                                    <?php echo htmlspecialchars($gradeLevelMap[$student['grade_level']] ?? 'Unknown Level'); ?>
                                </span>
                            </h3>
                            
                            <div class="student-info">
                                <div class="info-row">
                                    <span class="label">Registration Number:</span>
                                    <span class="value"><?php echo htmlspecialchars($student['reg_number'] ?? 'N/A'); ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="label">Email Address:</span>
                                    <span class="value"><?php echo htmlspecialchars($student['email']); ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="label">Phone Number:</span>
                                    <span class="value"><?php echo htmlspecialchars($student['phone'] ?? 'N/A'); ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="label">Password/PIN:</span>
                                    <span class="value"><?php echo htmlspecialchars($student['pin'] ?? 'N/A'); ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="label">Registration Date:</span>
                                    <span class="value"><?php echo formatDate($student['created_at']); ?></span>
                                </div>
                            </div>
                            
                            <?php if ($id): // Show signature box only for individual student cards ?>
                            <div class="signature-box text-center">
                                <p class="mb-5">This card certifies that the above named person is a registered student.</p>
                                <div class="row">
                                    <div class="col-6 text-center">
                                        <div class="signature-line mx-auto"></div>
                                        <p>Student Signature</p>
                                    </div>
                                    <div class="col-6 text-center">
                                        <div class="signature-line mx-auto"></div>
                                        <p>Administrator Signature</p>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div class="mt-4 no-print">
            <a href="manage_students.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Students List
            </a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
} else {
    // Redirect back to the form if print is not requested
    header('Location: manage_students.php');
    exit;
}
?> 