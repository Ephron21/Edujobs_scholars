<?php
require_once 'config/config.php';
require_once 'includes/SMSService.php';
require_once 'includes/StudentCardGenerator.php';
require_once 'includes/AttendanceManager.php';

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

// Initialize services
$smsService = new SMSService($db);
$cardGenerator = new StudentCardGenerator($db);
$attendanceManager = new AttendanceManager($db);

// Handle section parameter
$section = isset($_GET['section']) ? $_GET['section'] : 'dashboard';

// Valid sections
$validSections = ['dashboard', 'attendance', 'cards', 'sms', 'students', 'teachers', 'staff', 'reports', 'timetable', 'exams', 'finance', 'resources', 'communication'];
if (!in_array($section, $validSections)) {
    $section = 'dashboard';
}

// Initialize result message
$resultMessage = '';
$resultType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'mark_attendance':
                try {
                    if (isset($_POST['student_id'])) {
                        $attendanceManager->markStudentAttendance(
                            $_POST['student_id'],
                            $_POST['status'],
                            $_POST['date'] ?? null,
                            $_POST['remarks'] ?? ''
                        );
                        $resultMessage = "Student attendance marked successfully!";
                        $resultType = "success";
                    } elseif (isset($_POST['teacher_id'])) {
                        $attendanceManager->markTeacherAttendance(
                            $_POST['teacher_id'],
                            $_POST['status'],
                            $_POST['date'] ?? null,
                            $_POST['remarks'] ?? ''
                        );
                        $resultMessage = "Teacher attendance marked successfully!";
                        $resultType = "success";
                    }
                } catch (Exception $e) {
                    $resultMessage = "Error marking attendance: " . $e->getMessage();
                    $resultType = "danger";
                }
                break;

            case 'generate_report':
                try {
                    if (isset($_POST['student_id']) && isset($_POST['report_type'])) {
                        $studentId = $_POST['student_id'];
                        $reportType = $_POST['report_type'];
                        $startDate = $_POST['start_date'] ?? null;
                        $endDate = $_POST['end_date'] ?? null;
                        
                        $result = $attendanceManager->generateStudentReport($studentId, $reportType, $startDate, $endDate);
                        
                        if ($result['success']) {
                            $reportData = $result['data'];
                            $resultMessage = "Report generated successfully!";
                            $resultType = "success";
                            // Store report data in session for rendering
                            $_SESSION['report_data'] = $reportData;
                            $_SESSION['report_type'] = $reportType;
                            $_SESSION['student_info'] = $result['student_info'];
                            
                            // Redirect to prevent form resubmission
                            header("Location: school_dashboard.php?section=reports&success=1&message=" . urlencode($resultMessage));
                            exit;
                        } else {
                            $resultMessage = $result['message'];
                            $resultType = "danger";
                        }
                    } else {
                        $resultMessage = "Student ID and report type are required";
                        $resultType = "danger";
                    }
                } catch (Exception $e) {
                    $resultMessage = "Error generating report: " . $e->getMessage();
                    $resultType = "danger";
                }
                break;
                
            case 'generate_rwanda_report':
                try {
                    if (isset($_POST['student_id']) && isset($_POST['education_level'])) {
                        $studentId = $_POST['student_id'];
                        $level = $_POST['education_level'];
                        $term = $_POST['term'] ?? '1';
                        $academicYear = $_POST['academic_year'] ?? null;
                        
                        $result = $attendanceManager->generateRwandaReport($studentId, $level, $term, $academicYear);
                        
                        if ($result['success']) {
                            $resultMessage = "Rwanda-style report generated successfully!";
                            $resultType = "success";
                            // Store report data in session for rendering
                            $_SESSION['rwanda_report'] = $result['report'];
                            
                            // Redirect to prevent form resubmission
                            header("Location: school_dashboard.php?section=reports&tab=rwanda&success=1&message=" . urlencode($resultMessage));
                            exit;
                        } else {
                            $resultMessage = $result['message'];
                            $resultType = "danger";
                        }
                    } else {
                        $resultMessage = "Student ID and education level are required";
                        $resultType = "danger";
                    }
                } catch (Exception $e) {
                    $resultMessage = "Error generating Rwanda report: " . $e->getMessage();
                    $resultType = "danger";
                }
                break;

            case 'generate_card':
                try {
                    if (isset($_POST['student_id'])) {
                        $cardGenerator->generateCard($_POST['student_id']);
                        $resultMessage = "Student ID card generated successfully!";
                        $resultType = "success";
                    }
                } catch (Exception $e) {
                    $resultMessage = "Error generating ID card: " . $e->getMessage();
                    $resultType = "danger";
                }
                break;

            case 'send_sms':
                try {
                    if (isset($_POST['recipient_type']) && isset($_POST['recipient_id']) && isset($_POST['phone_number']) && isset($_POST['message'])) {
                        $smsService->sendSMS(
                            $_POST['phone_number'],
                            $_POST['message'],
                            $_POST['recipient_type'],
                            $_POST['recipient_id']
                        );
                        $resultMessage = "SMS sent successfully!";
                        $resultType = "success";
                    }
                } catch (Exception $e) {
                    $resultMessage = "Error sending SMS: " . $e->getMessage();
                    $resultType = "danger";
                }
                break;
                
            case 'add_student':
                try {
                    $studentData = [
                        'first_name' => $_POST['first_name'],
                        'last_name' => $_POST['last_name'],
                        'gender' => $_POST['gender'],
                        'date_of_birth' => $_POST['date_of_birth'],
                        'admission_number' => $_POST['admission_number'],
                        'class_id' => $_POST['class_id'] ?? null,
                        'grade_level' => $_POST['grade_level'] ?? null,
                        'parent_name' => $_POST['parent_name'] ?? null,
                        'parent_phone' => $_POST['parent_phone'] ?? null,
                        'parent_email' => $_POST['parent_email'] ?? null,
                        'address' => $_POST['address'] ?? null
                    ];
                    
                    $result = $attendanceManager->addStudent($studentData);
                    if ($result['success']) {
                        $resultMessage = $result['message'];
                        $resultType = "success";
                        // Redirect to prevent form resubmission
                        header("Location: school_dashboard.php?section=students&success=1&message=" . urlencode($resultMessage));
                        exit;
                    } else {
                        $resultMessage = $result['message'];
                        $resultType = "danger";
                    }
                } catch (Exception $e) {
                    $resultMessage = "Error adding student: " . $e->getMessage();
                    $resultType = "danger";
                }
                break;
                
            case 'update_student':
                try {
                    if (isset($_POST['student_id'])) {
                        $studentId = $_POST['student_id'];
                        $studentData = [
                            'first_name' => $_POST['first_name'],
                            'last_name' => $_POST['last_name'],
                            'gender' => $_POST['gender'],
                            'date_of_birth' => $_POST['date_of_birth'],
                            'admission_number' => $_POST['admission_number'],
                            'class_id' => $_POST['class_id'] ?? null,
                            'grade_level' => $_POST['grade_level'] ?? null,
                            'parent_name' => $_POST['parent_name'] ?? null,
                            'parent_phone' => $_POST['parent_phone'] ?? null,
                            'parent_email' => $_POST['parent_email'] ?? null,
                            'address' => $_POST['address'] ?? null
                        ];
                        
                        $result = $attendanceManager->updateStudent($studentId, $studentData);
                        if ($result['success']) {
                            $resultMessage = $result['message'];
                            $resultType = "success";
                            // Redirect to prevent form resubmission
                            header("Location: school_dashboard.php?section=students&success=1&message=" . urlencode($resultMessage));
                            exit;
                        } else {
                            $resultMessage = $result['message'];
                            $resultType = "danger";
                        }
                    }
                } catch (Exception $e) {
                    $resultMessage = "Error updating student: " . $e->getMessage();
                    $resultType = "danger";
                }
                break;
                
            case 'delete_student':
                try {
                    if (isset($_POST['student_id'])) {
                        $result = $attendanceManager->deleteStudent($_POST['student_id']);
                        if ($result['success']) {
                            $resultMessage = $result['message'];
                            $resultType = "success";
                            // Redirect to prevent form resubmission
                            header("Location: school_dashboard.php?section=students&success=1&message=" . urlencode($resultMessage));
                            exit;
                        } else {
                            $resultMessage = $result['message'];
                            $resultType = "danger";
                        }
                    }
                } catch (Exception $e) {
                    $resultMessage = "Error deleting student: " . $e->getMessage();
                    $resultType = "danger";
                }
                break;

            case 'add_teacher':
                try {
                    $teacherData = [
                        'first_name' => $_POST['first_name'],
                        'last_name' => $_POST['last_name'],
                        'gender' => $_POST['gender'],
                        'date_of_birth' => $_POST['date_of_birth'],
                        'employee_id' => $_POST['employee_id'],
                        'subject' => $_POST['subject'] ?? null,
                        'department' => $_POST['department'] ?? null,
                        'phone' => $_POST['phone'] ?? null,
                        'email' => $_POST['email'] ?? null,
                        'hire_date' => $_POST['hire_date'] ?? null,
                        'address' => $_POST['address'] ?? null
                    ];
                    
                    $result = $attendanceManager->addTeacher($teacherData);
                    if ($result['success']) {
                        $resultMessage = $result['message'];
                        $resultType = "success";
                        // Redirect to prevent form resubmission
                        header("Location: school_dashboard.php?section=teachers&success=1&message=" . urlencode($resultMessage));
                        exit;
                    } else {
                        $resultMessage = $result['message'];
                        $resultType = "danger";
                    }
                } catch (Exception $e) {
                    $resultMessage = "Error adding teacher: " . $e->getMessage();
                    $resultType = "danger";
                }
                break;
                
            case 'update_teacher':
                try {
                    if (isset($_POST['teacher_id'])) {
                        $teacherId = $_POST['teacher_id'];
                        $teacherData = [
                            'first_name' => $_POST['first_name'],
                            'last_name' => $_POST['last_name'],
                            'gender' => $_POST['gender'],
                            'date_of_birth' => $_POST['date_of_birth'],
                            'employee_id' => $_POST['employee_id'],
                            'subject' => $_POST['subject'] ?? null,
                            'department' => $_POST['department'] ?? null,
                            'phone' => $_POST['phone'] ?? null,
                            'email' => $_POST['email'] ?? null,
                            'hire_date' => $_POST['hire_date'] ?? null,
                            'address' => $_POST['address'] ?? null
                        ];
                        
                        $result = $attendanceManager->updateTeacher($teacherId, $teacherData);
                        if ($result['success']) {
                            $resultMessage = $result['message'];
                            $resultType = "success";
                            // Redirect to prevent form resubmission
                            header("Location: school_dashboard.php?section=teachers&success=1&message=" . urlencode($resultMessage));
                            exit;
                        } else {
                            $resultMessage = $result['message'];
                            $resultType = "danger";
                        }
                    }
                } catch (Exception $e) {
                    $resultMessage = "Error updating teacher: " . $e->getMessage();
                    $resultType = "danger";
                }
                break;
                
            case 'delete_teacher':
                try {
                    if (isset($_POST['teacher_id'])) {
                        $result = $attendanceManager->deleteTeacher($_POST['teacher_id']);
                        if ($result['success']) {
                            $resultMessage = $result['message'];
                            $resultType = "success";
                            // Redirect to prevent form resubmission
                            header("Location: school_dashboard.php?section=teachers&success=1&message=" . urlencode($resultMessage));
                            exit;
                        } else {
                            $resultMessage = $result['message'];
                            $resultType = "danger";
                        }
                    }
                } catch (Exception $e) {
                    $resultMessage = "Error deleting teacher: " . $e->getMessage();
                    $resultType = "danger";
                }
                break;

            case 'add_staff':
                try {
                    $staffData = [
                        'first_name' => $_POST['first_name'],
                        'last_name' => $_POST['last_name'],
                        'gender' => $_POST['gender'],
                        'date_of_birth' => $_POST['date_of_birth'],
                        'employee_id' => $_POST['employee_id'],
                        'position' => $_POST['position'],
                        'department' => $_POST['department'] ?? null,
                        'phone' => $_POST['phone'] ?? null,
                        'email' => $_POST['email'] ?? null,
                        'hire_date' => $_POST['hire_date'] ?? null,
                        'address' => $_POST['address'] ?? null
                    ];
                    
                    $result = $attendanceManager->addStaffMember($staffData);
                    if ($result['success']) {
                        $resultMessage = $result['message'];
                        $resultType = "success";
                        // Redirect to prevent form resubmission
                        header("Location: school_dashboard.php?section=staff&success=1&message=" . urlencode($resultMessage));
                        exit;
                    } else {
                        $resultMessage = $result['message'];
                        $resultType = "danger";
                    }
                } catch (Exception $e) {
                    $resultMessage = "Error adding staff member: " . $e->getMessage();
                    $resultType = "danger";
                }
                break;
                
            case 'update_staff':
                try {
                    if (isset($_POST['staff_id'])) {
                        $staffId = $_POST['staff_id'];
                        $staffData = [
                            'first_name' => $_POST['first_name'],
                            'last_name' => $_POST['last_name'],
                            'gender' => $_POST['gender'],
                            'date_of_birth' => $_POST['date_of_birth'],
                            'employee_id' => $_POST['employee_id'],
                            'position' => $_POST['position'],
                            'department' => $_POST['department'] ?? null,
                            'phone' => $_POST['phone'] ?? null,
                            'email' => $_POST['email'] ?? null,
                            'hire_date' => $_POST['hire_date'] ?? null,
                            'address' => $_POST['address'] ?? null
                        ];
                        
                        $result = $attendanceManager->updateStaffMember($staffId, $staffData);
                        if ($result['success']) {
                            $resultMessage = $result['message'];
                            $resultType = "success";
                            // Redirect to prevent form resubmission
                            header("Location: school_dashboard.php?section=staff&success=1&message=" . urlencode($resultMessage));
                            exit;
                        } else {
                            $resultMessage = $result['message'];
                            $resultType = "danger";
                        }
                    }
                } catch (Exception $e) {
                    $resultMessage = "Error updating staff member: " . $e->getMessage();
                    $resultType = "danger";
                }
                break;
                
            case 'delete_staff':
                try {
                    if (isset($_POST['staff_id'])) {
                        $result = $attendanceManager->deleteStaffMember($_POST['staff_id']);
                        if ($result['success']) {
                            $resultMessage = $result['message'];
                            $resultType = "success";
                            // Redirect to prevent form resubmission
                            header("Location: school_dashboard.php?section=staff&success=1&message=" . urlencode($resultMessage));
                            exit;
                        } else {
                            $resultMessage = $result['message'];
                            $resultType = "danger";
                        }
                    }
                } catch (Exception $e) {
                    $resultMessage = "Error deleting staff member: " . $e->getMessage();
                    $resultType = "danger";
                }
                break;
        }
    }
}

// Get attendance summary
$attendanceSummary = $attendanceManager->getAttendanceSummary();

// Get recent activities
try {
    // Check if students table exists
    $checkTable = $db->query("
        SELECT 1 
        FROM information_schema.tables 
        WHERE table_schema = DATABASE() 
        AND table_name = 'students'
    ");
    $studentsExist = ($checkTable && $checkTable->fetchColumn());
    
    // Check if teachers table exists
    $checkTable = $db->query("
        SELECT 1 
        FROM information_schema.tables 
        WHERE table_schema = DATABASE() 
        AND table_name = 'teachers'
    ");
    $teachersExist = ($checkTable && $checkTable->fetchColumn());
    
    if ($studentsExist && $teachersExist) {
        // Both tables exist, check their structure
        $studentIdExists = false;
        $teacherIdExists = false;
        
        $checkColumn = $db->query("
            SELECT 1 
            FROM information_schema.columns 
            WHERE table_schema = DATABASE() 
            AND table_name = 'students' 
            AND column_name = 'id'
        ");
        $studentIdExists = ($checkColumn && $checkColumn->fetchColumn());
        
        $checkColumn = $db->query("
            SELECT 1 
            FROM information_schema.columns 
            WHERE table_schema = DATABASE() 
            AND table_name = 'teachers' 
            AND column_name = 'id'
        ");
        $teacherIdExists = ($checkColumn && $checkColumn->fetchColumn());
        
        if ($studentIdExists && $teacherIdExists) {
            // Use id column
            $stmt = $db->prepare("
                SELECT * FROM (
                    SELECT 'student' as type, id as id, first_name, last_name, created_at
                    FROM students
                    UNION ALL
                    SELECT 'teacher' as type, id as id, first_name, last_name, created_at
                    FROM teachers
                ) as activities
                ORDER BY created_at DESC
                LIMIT 10
            ");
            $stmt->execute();
            $recentActivities = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            // Check for student_id and teacher_id columns
            $checkColumn = $db->query("
                SELECT 1 
                FROM information_schema.columns 
                WHERE table_schema = DATABASE() 
                AND table_name = 'students' 
                AND column_name = 'student_id'
            ");
            $studentIdExists = ($checkColumn && $checkColumn->fetchColumn());
            
            $checkColumn = $db->query("
                SELECT 1 
                FROM information_schema.columns 
                WHERE table_schema = DATABASE() 
                AND table_name = 'teachers' 
                AND column_name = 'teacher_id'
            ");
            $teacherIdExists = ($checkColumn && $checkColumn->fetchColumn());
            
            if ($studentIdExists && $teacherIdExists) {
                // Use student_id and teacher_id columns
                $stmt = $db->prepare("
                    SELECT * FROM (
                        SELECT 'student' as type, student_id as id, first_name, last_name, created_at
                        FROM students
                        UNION ALL
                        SELECT 'teacher' as type, teacher_id as id, first_name, last_name, created_at
                        FROM teachers
                    ) as activities
                    ORDER BY created_at DESC
                    LIMIT 10
                ");
                $stmt->execute();
                $recentActivities = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                // Use mock data
                throw new Exception("Required columns not found");
            }
        }
    } else {
        // One or both tables don't exist, use mock data
        throw new Exception("Required tables not found");
    }
} catch (Exception $e) {
    // Provide mock data
    $recentActivities = [
        [
            'type' => 'student',
            'id' => 1,
            'first_name' => 'John',
            'last_name' => 'Smith',
            'created_at' => date('Y-m-d H:i:s', strtotime('-2 days'))
        ],
        [
            'type' => 'teacher',
            'id' => 1,
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'created_at' => date('Y-m-d H:i:s', strtotime('-3 days'))
        ],
        [
            'type' => 'student',
            'id' => 2,
            'first_name' => 'Michael',
            'last_name' => 'Johnson',
            'created_at' => date('Y-m-d H:i:s', strtotime('-5 days'))
        ]
    ];
}

// Include header file
$includesPath = __DIR__ . '/includes/';
$pageTitle = "School Management Dashboard";
require_once('includes/admin_header.php');

// Custom CSS for school dashboard
?>
<style>
    .stat-card {
        background: linear-gradient(45deg, #2193b0, #6dd5ed);
        color: white;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 20px;
        transition: transform 0.3s, box-shadow 0.3s;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
    }
    
    .dashboard-card {
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        margin-bottom: 20px;
        overflow: hidden;
        transition: transform 0.3s, box-shadow 0.3s;
    }
    
    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
    }
    
    .list-group-item {
        transition: background-color 0.3s;
    }
    
    .list-group-item:hover {
        background-color: #f8f9fa;
    }
        
    /* Rwanda Report Card Styles */
    .rwanda-report {
        font-family: 'Arial', sans-serif;
        padding: 20px;
        background-color: white;
        border: 2px solid #000;
        max-width: 100%;
        margin: 0 auto;
    }
    
    .rwanda-report h4, .rwanda-report h5, .rwanda-report h6 {
        font-weight: bold;
    }
    
    .rwanda-report table {
        border-collapse: collapse;
        width: 100%;
    }
    
    .rwanda-report table th, .rwanda-report table td {
        border: 1px solid #000;
        padding: 5px;
        font-size: 0.9rem;
    }
    
    .rwanda-report table th {
        background-color: #f2f2f2;
        text-align: center;
        vertical-align: middle;
    }
    
    /* Print Styles */
    @media print {
        body * {
            visibility: hidden;
        }
        
        .rwanda-report, .rwanda-report * {
            visibility: visible;
        }
        
        .rwanda-report {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            border: none;
        }
        
        button.btn-primary {
            display: none;
        }
    }
</style>

<div class="container-fluid mt-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="admin_dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">School Management</li>
            <?php if ($section != 'dashboard'): ?>
            <li class="breadcrumb-item active" aria-current="page"><?php echo ucfirst($section); ?></li>
            <?php endif; ?>
        </ol>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid py-4">
        <!-- Dashboard Tabs -->
        <div class="dashboard-tabs mb-4">
            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a class="nav-link <?php echo $section == 'dashboard' ? 'active' : ''; ?>" href="school_dashboard.php?section=dashboard">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $section == 'attendance' ? 'active' : ''; ?>" href="school_dashboard.php?section=attendance">
                        <i class="fas fa-calendar-check me-2"></i>Attendance
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $section == 'cards' ? 'active' : ''; ?>" href="school_dashboard.php?section=cards">
                        <i class="fas fa-id-card me-2"></i>ID Cards
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $section == 'sms' ? 'active' : ''; ?>" href="school_dashboard.php?section=sms">
                        <i class="fas fa-sms me-2"></i>SMS
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $section == 'students' ? 'active' : ''; ?>" href="school_dashboard.php?section=students">
                        <i class="fas fa-user-graduate me-2"></i>Students
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $section == 'teachers' ? 'active' : ''; ?>" href="school_dashboard.php?section=teachers">
                        <i class="fas fa-chalkboard-teacher me-2"></i>Teachers
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $section == 'staff' ? 'active' : ''; ?>" href="school_dashboard.php?section=staff">
                        <i class="fas fa-user-cog me-2"></i>Staff
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $section == 'reports' ? 'active' : ''; ?>" href="school_dashboard.php?section=reports">
                        <i class="fas fa-file-alt me-2"></i>Reports
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $section == 'timetable' ? 'active' : ''; ?>" href="school_dashboard.php?section=timetable">
                        <i class="fas fa-clock me-2"></i>Timetable
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $section == 'exams' ? 'active' : ''; ?>" href="school_dashboard.php?section=exams">
                        <i class="fas fa-tasks me-2"></i>Exams
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $section == 'finance' ? 'active' : ''; ?>" href="school_dashboard.php?section=finance">
                        <i class="fas fa-money-bill-wave me-2"></i>Finance
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $section == 'resources' ? 'active' : ''; ?>" href="school_dashboard.php?section=resources">
                        <i class="fas fa-boxes me-2"></i>Resources
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $section == 'communication' ? 'active' : ''; ?>" href="school_dashboard.php?section=communication">
                        <i class="fas fa-comments me-2"></i>Communication
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- Alert Message Display -->
        <?php if (!empty($resultMessage)): ?>
            <div class="alert alert-<?php echo $resultType; ?> alert-dismissible fade show" role="alert">
                <i class="fas fa-info-circle me-2"></i><?php echo $resultMessage; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Quick Stats -->
        <div class="row">
            <div class="col-md-3">
                <div class="stat-card">
                    <h5>Total Students</h5>
                    <h2><?php echo array_sum(array_column($attendanceSummary['student_summary'], 'total_students')); ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <h5>Total Teachers</h5>
                    <h2><?php echo $attendanceSummary['teacher_summary']['total_teachers']; ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <h5>Today's Attendance</h5>
                    <h2><?php echo $attendanceSummary['student_summary'][0]['present_count'] ?? 0; ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <h5>Active Classes</h5>
                    <h2><?php echo count($attendanceSummary['student_summary']); ?></h2>
                </div>
            </div>
        </div>

        <?php if ($section == 'dashboard' || $section == ''): ?>
        <div class="row mt-4">
            <!-- Database Setup Button (Only visible to admin) -->
            <div class="col-md-12 mb-3">
                <div class="alert alert-info">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Missing tables detected!</strong> If you're seeing errors, you may need to set up the database tables for the school management system.
                        </div>
                        <a href="includes/setup_database.php" class="btn btn-primary">
                            <i class="fas fa-database me-2"></i>Setup Database Tables
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Attendance Overview -->
            <div class="col-md-6">
                <div class="dashboard-card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Attendance Overview</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="attendanceChart" width="400" height="300"></canvas>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="col-md-6">
                <div class="dashboard-card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Activities</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <?php foreach ($recentActivities as $activity): ?>
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?php echo ucfirst($activity['type']); ?> Added</h6>
                                        <small><?php echo date('M d, Y', strtotime($activity['created_at'])); ?></small>
                                    </div>
                                    <p class="mb-1"><?php echo $activity['first_name'] . ' ' . $activity['last_name']; ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($section == 'attendance'): ?>
        <div class="row mt-4">
            <!-- Attendance Management -->
            <div class="col-md-6">
                <div class="dashboard-card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Mark Attendance</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="mt-3">
                            <input type="hidden" name="action" value="mark_attendance">
                            <div class="mb-3">
                                <label class="form-label">Select Type</label>
                                <select class="form-select" id="attendanceType" name="type" required>
                                    <option value="student">Student</option>
                                    <option value="teacher">Teacher</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" id="id-label">Student ID</label>
                                <input type="text" class="form-control" id="attendanceId" name="student_id" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status" required>
                                    <option value="Present">Present</option>
                                    <option value="Absent">Absent</option>
                                    <option value="Late">Late</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Date</label>
                                <input type="date" class="form-control" name="date" value="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Remarks</label>
                                <textarea class="form-control" name="remarks" rows="2"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Mark Attendance</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Attendance Summary -->
            <div class="col-md-6">
                <div class="dashboard-card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Attendance Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Class</th>
                                        <th>Present</th>
                                        <th>Absent</th>
                                        <th>Late</th>
                                        <th>Percentage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($attendanceSummary['student_summary'] as $grade => $summary): ?>
                                    <tr>
                                        <td>Grade <?php echo $grade; ?></td>
                                        <td><?php echo $summary['present_count']; ?></td>
                                        <td><?php echo $summary['absent_count']; ?></td>
                                        <td><?php echo $summary['late_count']; ?></td>
                                        <td>
                                            <?php 
                                                $present = $summary['present_count'];
                                                $total = $summary['total_students'];
                                                $percentage = ($total > 0) ? round(($present / $total) * 100) : 0;
                                                echo $percentage . '%';
                                            ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($section == 'cards'): ?>
        <div class="row mt-4">
            <!-- Student Card Generation -->
            <div class="col-md-12">
                <div class="dashboard-card">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0"><i class="fas fa-id-card me-2"></i>Generate Student Card</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="mt-3">
                            <input type="hidden" name="action" value="generate_card">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Student ID</label>
                                        <input type="text" class="form-control" name="student_id" required>
                                    </div>
                                    <button type="submit" class="btn btn-warning">Generate Card</button>
                                </div>
                                <div class="col-md-6">
                                    <div class="card-preview">
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Enter a student ID to preview and generate their ID card.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($section == 'sms'): ?>
        <div class="row mt-4">
            <!-- SMS Management -->
            <div class="col-md-12">
                <div class="dashboard-card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-sms me-2"></i>Send SMS</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="mt-3">
                            <input type="hidden" name="action" value="send_sms">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Recipient Type</label>
                                        <select class="form-select" name="recipient_type" required>
                                            <option value="Student">Student</option>
                                            <option value="Teacher">Teacher</option>
                                            <option value="Parent">Parent</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Recipient ID</label>
                                        <input type="text" class="form-control" name="recipient_id" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Phone Number</label>
                                        <input type="text" class="form-control" name="phone_number" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Message</label>
                                        <textarea class="form-control" name="message" rows="5" required></textarea>
                                        <div class="form-text">
                                            <span id="character-count">0</span>/160 characters
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-success">Send SMS</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($section == 'students'): ?>
        <!-- Student Management Section -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="dashboard-card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-user-graduate me-2"></i>Manage Students</h5>
                        
                        <!-- Add Student Button -->
                        <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                            <i class="fas fa-plus me-1"></i>Add New Student
                        </button>
                    </div>
                    
                    <div class="card-body">
                        <!-- Display success message from URL parameter if exists -->
                        <?php if (isset($_GET['success']) && $_GET['success'] == '1' && isset($_GET['message'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars(urldecode($_GET['message'])); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Students Table -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Admission #</th>
                                        <th>Grade</th>
                                        <th>Gender</th>
                                        <th>Parent Contact</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    // Get all students using the AttendanceManager
                                    $result = $attendanceManager->getAllStudents();
                                    $students = [];
                                    
                                    if (isset($result['success']) && $result['success'] && isset($result['data'])) {
                                        $students = $result['data'];
                                    }
                                    
                                    if (count($students) > 0):
                                        foreach ($students as $student): 
                                    ?>
                                    <tr>
                                        <td><?php echo $student['id'] ?? $student['student_id'] ?? 'N/A'; ?></td>
                                        <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($student['admission_number'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($student['grade_level'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($student['gender'] ?? 'N/A'); ?></td>
                                        <td>
                                            <?php if (!empty($student['parent_phone'])): ?>
                                                <a href="tel:<?php echo htmlspecialchars($student['parent_phone']); ?>"><?php echo htmlspecialchars($student['parent_phone']); ?></a>
                                            <?php else: ?>
                                                N/A
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <!-- View button -->
                                            <button type="button" class="btn btn-info btn-sm view-student" 
                                                    data-id="<?php echo $student['id'] ?? $student['student_id']; ?>"
                                                    data-bs-toggle="modal" data-bs-target="#viewStudentModal">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            
                                            <!-- Edit button -->
                                            <button type="button" class="btn btn-warning btn-sm edit-student" 
                                                    data-id="<?php echo $student['id'] ?? $student['student_id']; ?>"
                                                    data-bs-toggle="modal" data-bs-target="#editStudentModal">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            
                                            <!-- Delete button -->
                                            <button type="button" class="btn btn-danger btn-sm delete-student" 
                                                    data-id="<?php echo $student['id'] ?? $student['student_id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>"
                                                    data-bs-toggle="modal" data-bs-target="#deleteStudentModal">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php 
                                        endforeach;
                                    else:
                                    ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No students found. Add a new student to get started.</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Add Student Modal -->
        <div class="modal fade" id="addStudentModal" tabindex="-1" aria-labelledby="addStudentModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="addStudentModalLabel"><i class="fas fa-user-plus me-2"></i>Add New Student</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addStudentForm" method="POST">
                            <input type="hidden" name="action" value="add_student">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">First Name</label>
                                    <input type="text" class="form-control" name="first_name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" class="form-control" name="last_name" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Gender</label>
                                    <select class="form-select" name="gender" required>
                                        <option value="">Select Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Date of Birth</label>
                                    <input type="date" class="form-control" name="date_of_birth">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Admission Number</label>
                                    <input type="text" class="form-control" name="admission_number" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Grade Level</label>
                                    <input type="number" class="form-control" name="grade_level" min="1" max="12">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Class ID</label>
                                    <input type="number" class="form-control" name="class_id" min="1">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Parent Name</label>
                                    <input type="text" class="form-control" name="parent_name">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Parent Phone</label>
                                    <input type="tel" class="form-control" name="parent_phone">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Parent Email</label>
                                    <input type="email" class="form-control" name="parent_email">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Address</label>
                                <textarea class="form-control" name="address" rows="3"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" form="addStudentForm" class="btn btn-primary">Add Student</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- View Student Modal -->
        <div class="modal fade" id="viewStudentModal" tabindex="-1" aria-labelledby="viewStudentModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title" id="viewStudentModalLabel"><i class="fas fa-user me-2"></i>Student Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="studentDetails">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Name:</strong> <span id="view-name"></span></p>
                                    <p><strong>Admission Number:</strong> <span id="view-admission"></span></p>
                                    <p><strong>Gender:</strong> <span id="view-gender"></span></p>
                                    <p><strong>Date of Birth:</strong> <span id="view-dob"></span></p>
                                    <p><strong>Grade Level:</strong> <span id="view-grade"></span></p>
                                    <p><strong>Class ID:</strong> <span id="view-class"></span></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Parent Name:</strong> <span id="view-parent-name"></span></p>
                                    <p><strong>Parent Phone:</strong> <span id="view-parent-phone"></span></p>
                                    <p><strong>Parent Email:</strong> <span id="view-parent-email"></span></p>
                                    <p><strong>Address:</strong> <span id="view-address"></span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Edit Student Modal -->
        <div class="modal fade" id="editStudentModal" tabindex="-1" aria-labelledby="editStudentModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title" id="editStudentModalLabel"><i class="fas fa-edit me-2"></i>Edit Student</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editStudentForm" method="POST">
                            <input type="hidden" name="action" value="update_student">
                            <input type="hidden" name="student_id" id="edit-student-id">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">First Name</label>
                                    <input type="text" class="form-control" name="first_name" id="edit-first-name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" class="form-control" name="last_name" id="edit-last-name" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Gender</label>
                                    <select class="form-select" name="gender" id="edit-gender" required>
                                        <option value="">Select Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Date of Birth</label>
                                    <input type="date" class="form-control" name="date_of_birth" id="edit-dob">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Admission Number</label>
                                    <input type="text" class="form-control" name="admission_number" id="edit-admission" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Grade Level</label>
                                    <input type="number" class="form-control" name="grade_level" id="edit-grade" min="1" max="12">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Class ID</label>
                                    <input type="number" class="form-control" name="class_id" id="edit-class" min="1">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Parent Name</label>
                                    <input type="text" class="form-control" name="parent_name" id="edit-parent-name">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Parent Phone</label>
                                    <input type="tel" class="form-control" name="parent_phone" id="edit-parent-phone">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Parent Email</label>
                                    <input type="email" class="form-control" name="parent_email" id="edit-parent-email">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Address</label>
                                <textarea class="form-control" name="address" id="edit-address" rows="3"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" form="editStudentForm" class="btn btn-warning">Update Student</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Delete Student Modal -->
        <div class="modal fade" id="deleteStudentModal" tabindex="-1" aria-labelledby="deleteStudentModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="deleteStudentModalLabel"><i class="fas fa-trash me-2"></i>Delete Student</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete student <strong id="delete-student-name"></strong>?</p>
                        <p class="text-danger"><i class="fas fa-exclamation-triangle me-1"></i> This action cannot be undone.</p>
                        
                        <form id="deleteStudentForm" method="POST">
                            <input type="hidden" name="action" value="delete_student">
                            <input type="hidden" name="student_id" id="delete-student-id">
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" form="deleteStudentForm" class="btn btn-danger">Delete Student</button>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($section == 'teachers'): ?>
        <!-- Teacher Management Section -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="dashboard-card">
                    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-chalkboard-teacher me-2"></i>Manage Teachers</h5>
                        
                        <!-- Add Teacher Button -->
                        <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addTeacherModal">
                            <i class="fas fa-plus me-1"></i>Add New Teacher
                        </button>
                    </div>
                    
                    <div class="card-body">
                        <!-- Display success message from URL parameter if exists -->
                        <?php if (isset($_GET['success']) && $_GET['success'] == '1' && isset($_GET['message'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars(urldecode($_GET['message'])); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Teachers Table -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Employee ID</th>
                                        <th>Subject</th>
                                        <th>Department</th>
                                        <th>Contact</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    // Get all teachers using the AttendanceManager
                                    $result = $attendanceManager->getAllTeachers();
                                    $teachers = [];
                                    
                                    if (isset($result['success']) && $result['success'] && isset($result['data'])) {
                                        $teachers = $result['data'];
                                    }
                                    
                                    if (count($teachers) > 0):
                                        foreach ($teachers as $teacher): 
                                    ?>
                                    <tr>
                                        <td><?php echo $teacher['id'] ?? $teacher['teacher_id'] ?? 'N/A'; ?></td>
                                        <td><?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($teacher['employee_id'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($teacher['subject'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($teacher['department'] ?? 'N/A'); ?></td>
                                        <td>
                                            <?php if (!empty($teacher['phone'])): ?>
                                                <a href="tel:<?php echo htmlspecialchars($teacher['phone']); ?>"><?php echo htmlspecialchars($teacher['phone']); ?></a>
                                            <?php else: ?>
                                                N/A
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <!-- View button -->
                                            <button type="button" class="btn btn-info btn-sm view-teacher" 
                                                    data-id="<?php echo $teacher['id'] ?? $teacher['teacher_id']; ?>"
                                                    data-bs-toggle="modal" data-bs-target="#viewTeacherModal">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            
                                            <!-- Edit button -->
                                            <button type="button" class="btn btn-warning btn-sm edit-teacher" 
                                                    data-id="<?php echo $teacher['id'] ?? $teacher['teacher_id']; ?>"
                                                    data-bs-toggle="modal" data-bs-target="#editTeacherModal">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            
                                            <!-- Delete button -->
                                            <button type="button" class="btn btn-danger btn-sm delete-teacher" 
                                                    data-id="<?php echo $teacher['id'] ?? $teacher['teacher_id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?>"
                                                    data-bs-toggle="modal" data-bs-target="#deleteTeacherModal">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php 
                                        endforeach;
                                    else:
                                    ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No teachers found. Add a new teacher to get started.</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Add Teacher Modal -->
        <div class="modal fade" id="addTeacherModal" tabindex="-1" aria-labelledby="addTeacherModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title" id="addTeacherModalLabel"><i class="fas fa-user-plus me-2"></i>Add New Teacher</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addTeacherForm" method="POST">
                            <input type="hidden" name="action" value="add_teacher">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">First Name</label>
                                    <input type="text" class="form-control" name="first_name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" class="form-control" name="last_name" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Gender</label>
                                    <select class="form-select" name="gender" required>
                                        <option value="">Select Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Date of Birth</label>
                                    <input type="date" class="form-control" name="date_of_birth">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Employee ID</label>
                                    <input type="text" class="form-control" name="employee_id" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Hire Date</label>
                                    <input type="date" class="form-control" name="hire_date">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Subject</label>
                                    <input type="text" class="form-control" name="subject">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Department</label>
                                    <input type="text" class="form-control" name="department">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Phone</label>
                                    <input type="tel" class="form-control" name="phone">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Address</label>
                                <textarea class="form-control" name="address" rows="3"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" form="addTeacherForm" class="btn btn-info">Add Teacher</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- View Teacher Modal -->
        <div class="modal fade" id="viewTeacherModal" tabindex="-1" aria-labelledby="viewTeacherModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title" id="viewTeacherModalLabel"><i class="fas fa-user me-2"></i>Teacher Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="teacherDetails">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Name:</strong> <span id="view-teacher-name"></span></p>
                                    <p><strong>Employee ID:</strong> <span id="view-employee-id"></span></p>
                                    <p><strong>Gender:</strong> <span id="view-teacher-gender"></span></p>
                                    <p><strong>Date of Birth:</strong> <span id="view-teacher-dob"></span></p>
                                    <p><strong>Hire Date:</strong> <span id="view-hire-date"></span></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Subject:</strong> <span id="view-subject"></span></p>
                                    <p><strong>Department:</strong> <span id="view-department"></span></p>
                                    <p><strong>Phone:</strong> <span id="view-phone"></span></p>
                                    <p><strong>Email:</strong> <span id="view-email"></span></p>
                                    <p><strong>Address:</strong> <span id="view-teacher-address"></span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Edit Teacher Modal -->
        <div class="modal fade" id="editTeacherModal" tabindex="-1" aria-labelledby="editTeacherModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title" id="editTeacherModalLabel"><i class="fas fa-edit me-2"></i>Edit Teacher</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editTeacherForm" method="POST">
                            <input type="hidden" name="action" value="update_teacher">
                            <input type="hidden" name="teacher_id" id="edit-teacher-id">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">First Name</label>
                                    <input type="text" class="form-control" name="first_name" id="edit-teacher-first-name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" class="form-control" name="last_name" id="edit-teacher-last-name" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Gender</label>
                                    <select class="form-select" name="gender" id="edit-teacher-gender" required>
                                        <option value="">Select Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Date of Birth</label>
                                    <input type="date" class="form-control" name="date_of_birth" id="edit-teacher-dob">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Employee ID</label>
                                    <input type="text" class="form-control" name="employee_id" id="edit-employee-id" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Hire Date</label>
                                    <input type="date" class="form-control" name="hire_date" id="edit-hire-date">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Subject</label>
                                    <input type="text" class="form-control" name="subject" id="edit-subject">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Department</label>
                                    <input type="text" class="form-control" name="department" id="edit-department">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Phone</label>
                                    <input type="tel" class="form-control" name="phone" id="edit-phone">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" id="edit-email">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Address</label>
                                <textarea class="form-control" name="address" id="edit-teacher-address" rows="3"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" form="editTeacherForm" class="btn btn-warning">Update Teacher</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Delete Teacher Modal -->
        <div class="modal fade" id="deleteTeacherModal" tabindex="-1" aria-labelledby="deleteTeacherModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="deleteTeacherModalLabel"><i class="fas fa-trash me-2"></i>Delete Teacher</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete teacher <strong id="delete-teacher-name"></strong>?</p>
                        <p class="text-danger"><i class="fas fa-exclamation-triangle me-1"></i> This action cannot be undone.</p>
                        
                        <form id="deleteTeacherForm" method="POST">
                            <input type="hidden" name="action" value="delete_teacher">
                            <input type="hidden" name="teacher_id" id="delete-teacher-id">
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" form="deleteTeacherForm" class="btn btn-danger">Delete Teacher</button>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($section == 'staff'): ?>
        <!-- Staff Management Section -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="dashboard-card">
                    <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-user-cog me-2"></i>Manage Staff</h5>
                        
                        <!-- Add Staff Button -->
                        <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addStaffModal">
                            <i class="fas fa-plus me-1"></i>Add New Staff Member
                        </button>
                    </div>
                    
                    <div class="card-body">
                        <!-- Display success message from URL parameter if exists -->
                        <?php if (isset($_GET['success']) && $_GET['success'] == '1' && isset($_GET['message'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars(urldecode($_GET['message'])); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Staff Table -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Employee ID</th>
                                        <th>Position</th>
                                        <th>Department</th>
                                        <th>Contact</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    // Get all staff members using the AttendanceManager
                                    $result = $attendanceManager->getAllStaffMembers();
                                    $staffMembers = [];
                                    
                                    if (isset($result['success']) && $result['success'] && isset($result['data'])) {
                                        $staffMembers = $result['data'];
                                    }
                                    
                                    if (count($staffMembers) > 0):
                                        foreach ($staffMembers as $staff): 
                                    ?>
                                    <tr>
                                        <td><?php echo $staff['id'] ?? $staff['staff_id'] ?? 'N/A'; ?></td>
                                        <td><?php echo htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($staff['employee_id'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($staff['position'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($staff['department'] ?? 'N/A'); ?></td>
                                        <td>
                                            <?php if (!empty($staff['phone'])): ?>
                                                <a href="tel:<?php echo htmlspecialchars($staff['phone']); ?>"><?php echo htmlspecialchars($staff['phone']); ?></a>
                                            <?php else: ?>
                                                N/A
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <!-- View button -->
                                            <button type="button" class="btn btn-info btn-sm view-staff" 
                                                    data-id="<?php echo $staff['id'] ?? $staff['staff_id']; ?>"
                                                    data-bs-toggle="modal" data-bs-target="#viewStaffModal">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            
                                            <!-- Edit button -->
                                            <button type="button" class="btn btn-warning btn-sm edit-staff" 
                                                    data-id="<?php echo $staff['id'] ?? $staff['staff_id']; ?>"
                                                    data-bs-toggle="modal" data-bs-target="#editStaffModal">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            
                                            <!-- Delete button -->
                                            <button type="button" class="btn btn-danger btn-sm delete-staff" 
                                                    data-id="<?php echo $staff['id'] ?? $staff['staff_id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']); ?>"
                                                    data-bs-toggle="modal" data-bs-target="#deleteStaffModal">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php 
                                        endforeach;
                                    else:
                                    ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No staff members found. Add a new staff member to get started.</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Add Staff Modal -->
        <div class="modal fade" id="addStaffModal" tabindex="-1" aria-labelledby="addStaffModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-secondary text-white">
                        <h5 class="modal-title" id="addStaffModalLabel"><i class="fas fa-user-plus me-2"></i>Add New Staff Member</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addStaffForm" method="POST">
                            <input type="hidden" name="action" value="add_staff">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">First Name</label>
                                    <input type="text" class="form-control" name="first_name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" class="form-control" name="last_name" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Gender</label>
                                    <select class="form-select" name="gender" required>
                                        <option value="">Select Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Date of Birth</label>
                                    <input type="date" class="form-control" name="date_of_birth">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Employee ID</label>
                                    <input type="text" class="form-control" name="employee_id" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Hire Date</label>
                                    <input type="date" class="form-control" name="hire_date">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Position</label>
                                    <input type="text" class="form-control" name="position" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Department</label>
                                    <input type="text" class="form-control" name="department">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Phone</label>
                                    <input type="tel" class="form-control" name="phone">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Address</label>
                                <textarea class="form-control" name="address" rows="3"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" form="addStaffForm" class="btn btn-secondary">Add Staff Member</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- View Staff Modal -->
        <div class="modal fade" id="viewStaffModal" tabindex="-1" aria-labelledby="viewStaffModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title" id="viewStaffModalLabel"><i class="fas fa-user me-2"></i>Staff Member Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="staffDetails">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Name:</strong> <span id="view-staff-name"></span></p>
                                    <p><strong>Employee ID:</strong> <span id="view-staff-employee-id"></span></p>
                                    <p><strong>Gender:</strong> <span id="view-staff-gender"></span></p>
                                    <p><strong>Date of Birth:</strong> <span id="view-staff-dob"></span></p>
                                    <p><strong>Hire Date:</strong> <span id="view-staff-hire-date"></span></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Position:</strong> <span id="view-position"></span></p>
                                    <p><strong>Department:</strong> <span id="view-staff-department"></span></p>
                                    <p><strong>Phone:</strong> <span id="view-staff-phone"></span></p>
                                    <p><strong>Email:</strong> <span id="view-staff-email"></span></p>
                                    <p><strong>Address:</strong> <span id="view-staff-address"></span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Edit Staff Modal -->
        <div class="modal fade" id="editStaffModal" tabindex="-1" aria-labelledby="editStaffModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title" id="editStaffModalLabel"><i class="fas fa-edit me-2"></i>Edit Staff Member</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editStaffForm" method="POST">
                            <input type="hidden" name="action" value="update_staff">
                            <input type="hidden" name="staff_id" id="edit-staff-id">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">First Name</label>
                                    <input type="text" class="form-control" name="first_name" id="edit-staff-first-name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" class="form-control" name="last_name" id="edit-staff-last-name" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Gender</label>
                                    <select class="form-select" name="gender" id="edit-staff-gender" required>
                                        <option value="">Select Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Date of Birth</label>
                                    <input type="date" class="form-control" name="date_of_birth" id="edit-staff-dob">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Employee ID</label>
                                    <input type="text" class="form-control" name="employee_id" id="edit-staff-employee-id" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Hire Date</label>
                                    <input type="date" class="form-control" name="hire_date" id="edit-staff-hire-date">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Position</label>
                                    <input type="text" class="form-control" name="position" id="edit-position" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Department</label>
                                    <input type="text" class="form-control" name="department" id="edit-staff-department">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Phone</label>
                                    <input type="tel" class="form-control" name="phone" id="edit-staff-phone">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" id="edit-staff-email">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Address</label>
                                <textarea class="form-control" name="address" id="edit-staff-address" rows="3"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" form="editStaffForm" class="btn btn-warning">Update Staff Member</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Delete Staff Modal -->
        <div class="modal fade" id="deleteStaffModal" tabindex="-1" aria-labelledby="deleteStaffModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="deleteStaffModalLabel"><i class="fas fa-trash me-2"></i>Delete Staff Member</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete staff member <strong id="delete-staff-name"></strong>?</p>
                        <p class="text-danger"><i class="fas fa-exclamation-triangle me-1"></i> This action cannot be undone.</p>
                        
                        <form id="deleteStaffForm" method="POST">
                            <input type="hidden" name="action" value="delete_staff">
                            <input type="hidden" name="staff_id" id="delete-staff-id">
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" form="deleteStaffForm" class="btn btn-danger">Delete Staff Member</button>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($section == 'reports'): ?>
        <!-- Reports Section -->
        <div class="row mt-4">
            <!-- Tab navigation -->
            <div class="col-md-12 mb-4">
                <ul class="nav nav-tabs" id="reportTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?php echo (!isset($_GET['tab']) || $_GET['tab'] != 'rwanda') ? 'active' : ''; ?>" 
                                id="standard-tab" data-bs-toggle="tab" data-bs-target="#standard" 
                                type="button" role="tab">Standard Reports</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'rwanda') ? 'active' : ''; ?>" 
                                id="rwanda-tab" data-bs-toggle="tab" data-bs-target="#rwanda" 
                                type="button" role="tab">Rwanda Style Reports</button>
                    </li>
                </ul>
            </div>
            
            <div class="tab-content" id="reportTabsContent">
                <!-- Standard Reports Tab -->
                <div class="tab-pane fade <?php echo (!isset($_GET['tab']) || $_GET['tab'] != 'rwanda') ? 'show active' : ''; ?>" 
                     id="standard" role="tabpanel">
                    <!-- Report Generation Form -->
                    <div class="col-md-4">
                        <div class="dashboard-card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Generate Report</h5>
                            </div>
                            <div class="card-body">
                                <!-- Display success message from URL parameter if exists -->
                                <?php if (isset($_GET['success']) && $_GET['success'] == '1' && isset($_GET['message']) && !isset($_GET['tab'])): ?>
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars(urldecode($_GET['message'])); ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                <?php endif; ?>
                                
                                <form method="POST">
                                    <input type="hidden" name="action" value="generate_report">
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Student ID</label>
                                        <input type="text" class="form-control" name="student_id" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Report Type</label>
                                        <select class="form-select" name="report_type" required>
                                            <option value="">Select Report Type</option>
                                            <option value="transcript">Academic Transcript</option>
                                            <option value="attendance">Attendance Report</option>
                                            <option value="comprehensive">Comprehensive Report</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Start Date</label>
                                        <input type="date" class="form-control" name="start_date">
                                        <div class="form-text">Optional: Defaults to current term start</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">End Date</label>
                                        <input type="date" class="form-control" name="end_date">
                                        <div class="form-text">Optional: Defaults to current date</div>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">Generate Report</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Report Display Area -->
                    <div class="col-md-8">
                        <div class="dashboard-card">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0"><i class="fas fa-file-contract me-2"></i>Student Report</h5>
                            </div>
                            <div class="card-body">
                                <?php if (isset($_SESSION['report_data']) && isset($_SESSION['student_info']) && (!isset($_GET['tab']) || $_GET['tab'] != 'rwanda')): ?>
                                    <!-- Report Header -->
                                    <div class="report-header mb-4 text-center">
                                        <h4 class="school-name">School Name</h4>
                                        <h5 class="report-title">
                                            <?php 
                                            $reportTitle = 'Student Report';
                                            if ($_SESSION['report_type'] == 'transcript') {
                                                $reportTitle = 'Academic Transcript';
                                            } elseif ($_SESSION['report_type'] == 'attendance') {
                                                $reportTitle = 'Attendance Report';
                                            } elseif ($_SESSION['report_type'] == 'comprehensive') {
                                                $reportTitle = 'Comprehensive Report';
                                            }
                                            echo $reportTitle;
                                            ?>
                                        </h5>
                                    </div>
                                    
                                    <!-- Student Information -->
                                    <div class="student-info mb-4">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p><strong>Name:</strong> <?php echo htmlspecialchars($_SESSION['student_info']['first_name'] . ' ' . $_SESSION['student_info']['last_name']); ?></p>
                                                <p><strong>ID:</strong> <?php echo htmlspecialchars($_SESSION['student_info']['id'] ?? $_SESSION['student_info']['student_id'] ?? 'N/A'); ?></p>
                                                <p><strong>Admission Number:</strong> <?php echo htmlspecialchars($_SESSION['student_info']['admission_number'] ?? 'N/A'); ?></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Grade/Class:</strong> <?php echo htmlspecialchars($_SESSION['student_info']['grade_level'] ?? 'N/A'); ?></p>
                                                <p><strong>Academic Year:</strong> <?php echo date('Y'); ?></p>
                                                <p><strong>Report Date:</strong> <?php echo date('F d, Y'); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Report Content -->
                                    <div class="report-content">
                                        <?php if ($_SESSION['report_type'] == 'transcript'): ?>
                                            <!-- Academic Transcript -->
                                            <h5 class="section-title mb-3">Academic Performance</h5>
                                            
                                            <?php if (!empty($_SESSION['report_data']['courses'])): ?>
                                                <table class="table table-bordered table-striped">
                                                    <thead class="table-primary">
                                                        <tr>
                                                            <th>Course</th>
                                                            <th>Grade</th>
                                                            <th>Credits</th>
                                                            <th>Remarks</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($_SESSION['report_data']['courses'] as $course): ?>
                                                            <tr>
                                                                <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                                                                <td><?php echo htmlspecialchars($course['grade']); ?></td>
                                                                <td><?php echo htmlspecialchars($course['credits']); ?></td>
                                                                <td><?php echo htmlspecialchars($course['remarks']); ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                    <tfoot class="table-secondary">
                                                        <tr>
                                                            <th colspan="3">GPA</th>
                                                            <th><?php echo htmlspecialchars($_SESSION['report_data']['gpa'] ?? 'N/A'); ?></th>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            <?php else: ?>
                                                <div class="alert alert-info">No course data available for this student.</div>
                                            <?php endif; ?>
                                            
                                        <?php elseif ($_SESSION['report_type'] == 'attendance'): ?>
                                            <!-- Attendance Report -->
                                            <h5 class="section-title mb-3">Attendance Summary</h5>
                                            
                                            <?php if (!empty($_SESSION['report_data']['attendance'])): ?>
                                                <div class="row mb-4">
                                                    <div class="col-md-4 text-center">
                                                        <div class="card bg-success text-white">
                                                            <div class="card-body">
                                                                <h3><?php echo $_SESSION['report_data']['summary']['present_percentage']; ?>%</h3>
                                                                <p>Present</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4 text-center">
                                                        <div class="card bg-danger text-white">
                                                            <div class="card-body">
                                                                <h3><?php echo $_SESSION['report_data']['summary']['absent_percentage']; ?>%</h3>
                                                                <p>Absent</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4 text-center">
                                                        <div class="card bg-warning text-white">
                                                            <div class="card-body">
                                                                <h3><?php echo $_SESSION['report_data']['summary']['late_percentage']; ?>%</h3>
                                                                <p>Late</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <table class="table table-bordered table-striped">
                                                    <thead class="table-primary">
                                                        <tr>
                                                            <th>Date</th>
                                                            <th>Status</th>
                                                            <th>Remarks</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($_SESSION['report_data']['attendance'] as $record): ?>
                                                            <tr>
                                                                <td><?php echo htmlspecialchars($record['date']); ?></td>
                                                                <td>
                                                                    <?php 
                                                                    $statusClass = 'text-success';
                                                                    if ($record['status'] == 'Absent') {
                                                                        $statusClass = 'text-danger';
                                                                    } elseif ($record['status'] == 'Late') {
                                                                        $statusClass = 'text-warning';
                                                                    }
                                                                    ?>
                                                                    <span class="<?php echo $statusClass; ?>"><?php echo htmlspecialchars($record['status']); ?></span>
                                                                </td>
                                                                <td><?php echo htmlspecialchars($record['remarks'] ?? ''); ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            <?php else: ?>
                                                <div class="alert alert-info">No attendance records available for this student.</div>
                                            <?php endif; ?>
                                            
                                        <?php elseif ($_SESSION['report_type'] == 'comprehensive'): ?>
                                            <!-- Comprehensive Report with both academic and attendance data -->
                                            <ul class="nav nav-tabs mb-3" id="reportTabs" role="tablist">
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link active" id="academic-tab" data-bs-toggle="tab" data-bs-target="#academic" type="button" role="tab">Academic Performance</button>
                                                </li>
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link" id="attendance-tab" data-bs-toggle="tab" data-bs-target="#attendance" type="button" role="tab">Attendance</button>
                                                </li>
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link" id="remarks-tab" data-bs-toggle="tab" data-bs-target="#remarks" type="button" role="tab">Remarks</button>
                                                </li>
                                            </ul>
                                            
                                            <div class="tab-content" id="reportTabContent">
                                                <!-- Academic Tab -->
                                                <div class="tab-pane fade show active" id="academic" role="tabpanel">
                                                    <?php if (!empty($_SESSION['report_data']['courses'])): ?>
                                                        <table class="table table-bordered table-striped">
                                                            <thead class="table-primary">
                                                                <tr>
                                                                    <th>Course</th>
                                                                    <th>Grade</th>
                                                                    <th>Credits</th>
                                                                    <th>Remarks</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($_SESSION['report_data']['courses'] as $course): ?>
                                                                    <tr>
                                                                        <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                                                                        <td><?php echo htmlspecialchars($course['grade']); ?></td>
                                                                        <td><?php echo htmlspecialchars($course['credits']); ?></td>
                                                                        <td><?php echo htmlspecialchars($course['remarks']); ?></td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                            <tfoot class="table-secondary">
                                                                <tr>
                                                                    <th colspan="3">GPA</th>
                                                                    <th><?php echo htmlspecialchars($_SESSION['report_data']['gpa'] ?? 'N/A'); ?></th>
                                                                </tr>
                                                            </tfoot>
                                                        </table>
                                                    <?php else: ?>
                                                        <div class="alert alert-info">No course data available for this student.</div>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <!-- Attendance Tab -->
                                                <div class="tab-pane fade" id="attendance" role="tabpanel">
                                                    <?php if (!empty($_SESSION['report_data']['attendance'])): ?>
                                                        <div class="row mb-4">
                                                            <div class="col-md-4 text-center">
                                                                <div class="card bg-success text-white">
                                                                    <div class="card-body">
                                                                        <h3><?php echo $_SESSION['report_data']['summary']['present_percentage']; ?>%</h3>
                                                                        <p>Present</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4 text-center">
                                                                <div class="card bg-danger text-white">
                                                                    <div class="card-body">
                                                                        <h3><?php echo $_SESSION['report_data']['summary']['absent_percentage']; ?>%</h3>
                                                                        <p>Absent</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4 text-center">
                                                                <div class="card bg-warning text-white">
                                                                    <div class="card-body">
                                                                        <h3><?php echo $_SESSION['report_data']['summary']['late_percentage']; ?>%</h3>
                                                                        <p>Late</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <table class="table table-bordered table-striped">
                                                            <thead class="table-primary">
                                                                <tr>
                                                                    <th>Date</th>
                                                                    <th>Status</th>
                                                                    <th>Remarks</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($_SESSION['report_data']['attendance'] as $record): ?>
                                                                    <tr>
                                                                        <td><?php echo htmlspecialchars($record['date']); ?></td>
                                                                        <td>
                                                                            <?php 
                                                                            $statusClass = 'text-success';
                                                                            if ($record['status'] == 'Absent') {
                                                                                $statusClass = 'text-danger';
                                                                            } elseif ($record['status'] == 'Late') {
                                                                                $statusClass = 'text-warning';
                                                                            }
                                                                            ?>
                                                                            <span class="<?php echo $statusClass; ?>"><?php echo htmlspecialchars($record['status']); ?></span>
                                                                        </td>
                                                                        <td><?php echo htmlspecialchars($record['remarks'] ?? ''); ?></td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    <?php else: ?>
                                                        <div class="alert alert-info">No attendance records available for this student.</div>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <!-- Remarks Tab -->
                                                <div class="tab-pane fade" id="remarks" role="tabpanel">
                                                    <div class="card">
                                                        <div class="card-body">
                                                            <h6 class="card-title">Teacher's Remarks</h6>
                                                            <p><?php echo htmlspecialchars($_SESSION['report_data']['remarks']['teacher_remarks'] ?? 'No teacher remarks available.'); ?></p>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="card mt-3">
                                                        <div class="card-body">
                                                            <h6 class="card-title">Principal's Remarks</h6>
                                                            <p><?php echo htmlspecialchars($_SESSION['report_data']['remarks']['principal_remarks'] ?? 'No principal remarks available.'); ?></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Report Footer -->
                                    <div class="report-footer mt-4">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p><strong>Generated on:</strong> <?php echo date('F d, Y h:i A'); ?></p>
                                            </div>
                                            <div class="col-md-6 text-end">
                                                <button type="button" class="btn btn-primary" onclick="window.print()">
                                                    <i class="fas fa-print me-2"></i>Print Report
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Select a student and report type to generate a report.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Rwanda Style Reports Tab -->
                <div class="tab-pane fade <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'rwanda') ? 'show active' : ''; ?>" 
                     id="rwanda" role="tabpanel">
                    <div class="row">
                        <!-- Rwanda Report Generation Form -->
                        <div class="col-md-4">
                            <div class="dashboard-card">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Generate Rwanda Report</h5>
                                </div>
                                <div class="card-body">
                                    <!-- Display success message from URL parameter if exists -->
                                    <?php if (isset($_GET['success']) && $_GET['success'] == '1' && isset($_GET['message']) && isset($_GET['tab']) && $_GET['tab'] == 'rwanda'): ?>
                                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                                            <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars(urldecode($_GET['message'])); ?>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <form method="POST">
                                        <input type="hidden" name="action" value="generate_rwanda_report">
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Student ID</label>
                                            <input type="text" class="form-control" name="student_id" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Education Level</label>
                                            <select class="form-select" name="education_level" required>
                                                <option value="">Select Education Level</option>
                                                <option value="advanced">Advanced Level</option>
                                                <option value="ordinary">Ordinary Level</option>
                                                <option value="primary">Primary Level</option>
                                                <option value="preprimary">Pre-Primary Level</option>
                                            </select>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Term</label>
                                            <select class="form-select" name="term">
                                                <option value="1">Term 1</option>
                                                <option value="2">Term 2</option>
                                                <option value="3">Term 3</option>
                                                <option value="annual">Annual Report</option>
                                            </select>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Academic Year</label>
                                            <input type="text" class="form-control" name="academic_year" 
                                                placeholder="e.g., 2023-2024" 
                                                value="<?php echo date('Y') . '-' . (date('Y') + 1); ?>">
                                        </div>
                                        
                                        <button type="submit" class="btn btn-success">Generate Rwanda Report</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Rwanda Report Display Area -->
                        <div class="col-md-8">
                            <div class="dashboard-card">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0"><i class="fas fa-file-contract me-2"></i>Rwanda Style Report Card</h5>
                                </div>
                                <div class="card-body">
                                    <?php if (isset($_SESSION['rwanda_report']) && isset($_GET['tab']) && $_GET['tab'] == 'rwanda'): ?>
                                        <!-- Rwanda Report Card -->
                                        <div class="rwanda-report" id="rwandaReport">
                                            <!-- Report Header -->
                                            <div class="text-center border border-dark p-3 mb-3">
                                                <h5 class="text-uppercase mb-0">REPUBLIC OF RWANDA</h5>
                                                <h6 class="text-uppercase mb-0">MINISTRY OF EDUCATION</h6>
                                                <p class="mb-0">SCHOOL MANAGEMENT</p>
                                                <hr>
                                                <h4 class="mb-0 mt-4">PROGRESSIVE REPORT: 
                                                    <?php 
                                                    $levelText = 'ORDINARY LEVEL';
                                                    if ($_SESSION['rwanda_report']['level'] === 'advanced') {
                                                        $levelText = 'ADVANCED LEVEL';
                                                    } elseif ($_SESSION['rwanda_report']['level'] === 'primary') {
                                                        $levelText = 'PRIMARY LEVEL';
                                                    } elseif ($_SESSION['rwanda_report']['level'] === 'preprimary') {
                                                        $levelText = 'PRE-PRIMARY LEVEL';
                                                    }
                                                    echo $levelText;
                                                    ?>
                                                </h4>
                                            </div>
                                            
                                            <!-- Student Info -->
                                            <div class="border border-dark p-3 mb-3">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <p><strong>Student's name:</strong> 
                                                            <?php echo htmlspecialchars($_SESSION['rwanda_report']['student']['first_name'] . ' ' . $_SESSION['rwanda_report']['student']['last_name']); ?>
                                                        </p>
                                                        <p><strong>Student's number:</strong> 
                                                            <?php echo htmlspecialchars($_SESSION['rwanda_report']['student']['id'] ?? $_SESSION['rwanda_report']['student']['student_id'] ?? 'N/A'); ?>
                                                        </p>
                                                        <p><strong>Class:</strong> 
                                                            <?php echo htmlspecialchars($_SESSION['rwanda_report']['class']); ?>
                                                        </p>
                                                        <?php if (!empty($_SESSION['rwanda_report']['combination'])): ?>
                                                        <p><strong>Combination:</strong> 
                                                            <?php echo htmlspecialchars($_SESSION['rwanda_report']['combination']); ?>
                                                        </p>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="col-6 text-end">
                                                        <p><strong>School Year:</strong> 
                                                            <?php echo htmlspecialchars($_SESSION['rwanda_report']['academic_year']); ?>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Marks Table -->
                                            <div class="table-responsive">
                                                <table class="table table-bordered">
                                                    <thead class="bg-light">
                                                        <tr>
                                                            <th rowspan="2">Subjects</th>
                                                            <th rowspan="2">MAX POINTS</th>
                                                            
                                                            <!-- Terms Headers -->
                                                            <th colspan="3">1<sup>st</sup> TERM</th>
                                                            <th colspan="3">2<sup>nd</sup> TERM</th>
                                                            <th colspan="3">3<sup>rd</sup> TERM</th>
                                                            
                                                            <!-- Annual Headers -->
                                                            <th colspan="2">ANNUAL POINTS</th>
                                                            <th rowspan="2">%</th>
                                                        </tr>
                                                        <tr>
                                                            <!-- Term 1 Subheaders -->
                                                            <th>TEST</th>
                                                            <th>EX</th>
                                                            <th>TOT</th>
                                                            
                                                            <!-- Term 2 Subheaders -->
                                                            <th>TEST</th>
                                                            <th>EX</th>
                                                            <th>TOT</th>
                                                            
                                                            <!-- Term 3 Subheaders -->
                                                            <th>TEST</th>
                                                            <th>EX</th>
                                                            <th>TOT</th>
                                                            
                                                            <!-- Annual Subheaders -->
                                                            <th>MAX</th>
                                                            <th>O.P.</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($_SESSION['rwanda_report']['subjects'] as $subject => $details): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($subject); ?></td>
                                                            <td class="text-center"><?php echo $details['max']; ?></td>
                                                            
                                                            <!-- Term 1 -->
                                                            <td class="text-center"><?php echo $_SESSION['rwanda_report']['terms']['1']['subjects'][$subject]['test']; ?></td>
                                                            <td class="text-center"><?php echo $_SESSION['rwanda_report']['terms']['1']['subjects'][$subject]['exam']; ?></td>
                                                            <td class="text-center"><?php echo $_SESSION['rwanda_report']['terms']['1']['subjects'][$subject]['total']; ?></td>
                                                            
                                                            <!-- Term 2 -->
                                                            <td class="text-center"><?php echo $_SESSION['rwanda_report']['terms']['2']['subjects'][$subject]['test']; ?></td>
                                                            <td class="text-center"><?php echo $_SESSION['rwanda_report']['terms']['2']['subjects'][$subject]['exam']; ?></td>
                                                            <td class="text-center"><?php echo $_SESSION['rwanda_report']['terms']['2']['subjects'][$subject]['total']; ?></td>
                                                            
                                                            <!-- Term 3 -->
                                                            <td class="text-center"><?php echo $_SESSION['rwanda_report']['terms']['3']['subjects'][$subject]['test']; ?></td>
                                                            <td class="text-center"><?php echo $_SESSION['rwanda_report']['terms']['3']['subjects'][$subject]['exam']; ?></td>
                                                            <td class="text-center"><?php echo $_SESSION['rwanda_report']['terms']['3']['subjects'][$subject]['total']; ?></td>
                                                            
                                                            <!-- Annual -->
                                                            <td class="text-center"><?php echo $_SESSION['rwanda_report']['annual']['subjects'][$subject]['max']; ?></td>
                                                            <td class="text-center"><?php echo $_SESSION['rwanda_report']['annual']['subjects'][$subject]['total']; ?></td>
                                                            <td class="text-center"><?php echo $_SESSION['rwanda_report']['annual']['subjects'][$subject]['percentage']; ?></td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                        
                                                        <!-- Totals Row -->
                                                        <tr class="table-secondary">
                                                            <td><strong>Total</strong></td>
                                                            <td class="text-center"><?php echo $_SESSION['rwanda_report']['terms']['1']['total_max']; ?></td>
                                                            
                                                            <!-- Term 1 Total -->
                                                            <td colspan="2"></td>
                                                            <td class="text-center"><?php echo $_SESSION['rwanda_report']['terms']['1']['total_obtained']; ?></td>
                                                            
                                                            <!-- Term 2 Total -->
                                                            <td colspan="2"></td>
                                                            <td class="text-center"><?php echo $_SESSION['rwanda_report']['terms']['2']['total_obtained']; ?></td>
                                                            
                                                            <!-- Term 3 Total -->
                                                            <td colspan="2"></td>
                                                            <td class="text-center"><?php echo $_SESSION['rwanda_report']['terms']['3']['total_obtained']; ?></td>
                                                            
                                                            <!-- Annual Total -->
                                                            <td class="text-center"><?php echo $_SESSION['rwanda_report']['annual']['total_max']; ?></td>
                                                            <td class="text-center"><?php echo $_SESSION['rwanda_report']['annual']['total_obtained']; ?></td>
                                                            <td class="text-center"><?php echo $_SESSION['rwanda_report']['annual']['percentage']; ?>%</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            
                                            <!-- Position Information -->
                                            <div class="row mb-3">
                                                <div class="col-12">
                                                    <table class="table table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th colspan="2">Term 1</th>
                                                                <th colspan="2">Term 2</th>
                                                                <th colspan="2">Term 3</th>
                                                                <th colspan="2">Annual</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td>Position:</td>
                                                                <td><?php echo $_SESSION['rwanda_report']['terms']['1']['position']; ?> / <?php echo $_SESSION['rwanda_report']['terms']['1']['total_students']; ?></td>
                                                                <td>Position:</td>
                                                                <td><?php echo $_SESSION['rwanda_report']['terms']['2']['position']; ?> / <?php echo $_SESSION['rwanda_report']['terms']['2']['total_students']; ?></td>
                                                                <td>Position:</td>
                                                                <td><?php echo $_SESSION['rwanda_report']['terms']['3']['position']; ?> / <?php echo $_SESSION['rwanda_report']['terms']['3']['total_students']; ?></td>
                                                                <td>Position:</td>
                                                                <td><?php echo $_SESSION['rwanda_report']['annual']['position']; ?> / <?php echo $_SESSION['rwanda_report']['annual']['total_students']; ?></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            
                                            <!-- Signatures Section -->
                                            <div class="row mb-3">
                                                <div class="col-6">
                                                    <p><strong>Teacher's signature:</strong> ___________________</p>
                                                </div>
                                                <div class="col-6">
                                                    <p><strong>Parent's signature:</strong> ___________________</p>
                                                </div>
                                            </div>
                                            
                                            <!-- Verdict of the Jury -->
                                            <div class="border border-dark p-3 mb-3">
                                                <h6>VERDICT OF THE JURY</h6>
                                                <table class="table table-bordered">
                                                    <tr>
                                                        <td width="20%">1<sup>st</sup> Sitting</td>
                                                        <td>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" <?php echo ($_SESSION['rwanda_report']['verdict'] === 'Promoted' || $_SESSION['rwanda_report']['verdict'] === 'Promoted with distinction') ? 'checked' : ''; ?>>
                                                                <label class="form-check-label">Promoted</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" <?php echo ($_SESSION['rwanda_report']['verdict'] === 'Advised to repeat') ? 'checked' : ''; ?>>
                                                                <label class="form-check-label">Advised to repeat</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox">
                                                                <label class="form-check-label">Discontinued</label>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>Remarks</td>
                                                        <td>
                                                            <p><?php echo htmlspecialchars($_SESSION['rwanda_report']['remarks']['teacher']); ?></p>
                                                            <p><strong>Headmaster:</strong></p>
                                                            <p><?php echo htmlspecialchars($_SESSION['rwanda_report']['remarks']['headmaster']); ?></p>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                            
                                            <!-- Print Button -->
                                            <div class="text-center mb-3">
                                                <button type="button" class="btn btn-primary" onclick="window.print()">
                                                    <i class="fas fa-print me-2"></i>Print Report Card
                                                </button>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Generate a Rwanda-style report by selecting a student and education level.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($section == 'timetable'): ?>
        <!-- Timetable Management Section -->
        <div class="row mt-4">
            <!-- Tab navigation for Timetable functions -->
            <div class="col-md-12 mb-4">
                <ul class="nav nav-pills mb-3" id="timetableTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="class-schedule-tab" data-bs-toggle="pill" 
                                data-bs-target="#class-schedule" type="button" role="tab" 
                                aria-controls="class-schedule" aria-selected="true">
                            <i class="fas fa-calendar-alt me-2"></i>Class Schedule
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="teacher-allocation-tab" data-bs-toggle="pill" 
                                data-bs-target="#teacher-allocation" type="button" role="tab" 
                                aria-controls="teacher-allocation" aria-selected="false">
                            <i class="fas fa-user-check me-2"></i>Teacher Allocation
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="room-assignment-tab" data-bs-toggle="pill" 
                                data-bs-target="#room-assignment" type="button" role="tab" 
                                aria-controls="room-assignment" aria-selected="false">
                            <i class="fas fa-door-open me-2"></i>Room Assignment
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="conflict-detector-tab" data-bs-toggle="pill" 
                                data-bs-target="#conflict-detector" type="button" role="tab" 
                                aria-controls="conflict-detector" aria-selected="false">
                            <i class="fas fa-exclamation-triangle me-2"></i>Conflict Detector
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content p-0" id="timetableTabContent">
                    <!-- Class Schedule Tab -->
                    <div class="tab-pane fade show active" id="class-schedule" role="tabpanel" aria-labelledby="class-schedule-tab">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="dashboard-card">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Add Class Schedule</h5>
                                    </div>
                                    <div class="card-body">
                                        <form id="addScheduleForm" method="POST">
                                            <input type="hidden" name="action" value="add_schedule">
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Grade/Class</label>
                                                <select class="form-select" name="class_id" required>
                                                    <option value="">Select Class</option>
                                                    <option value="1">Grade 1</option>
                                                    <option value="2">Grade 2</option>
                                                    <option value="3">Grade 3</option>
                                                    <!-- Add more classes dynamically -->
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Subject</label>
                                                <select class="form-select" name="subject_id" required>
                                                    <option value="">Select Subject</option>
                                                    <option value="1">Mathematics</option>
                                                    <option value="2">English</option>
                                                    <option value="3">Science</option>
                                                    <!-- Add more subjects dynamically -->
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Teacher</label>
                                                <select class="form-select" name="teacher_id" required>
                                                    <option value="">Select Teacher</option>
                                                    <!-- Populate from teachers table -->
                                                    <?php 
                                                    $teacherResult = $attendanceManager->getAllTeachers();
                                                    if (isset($teacherResult['success']) && $teacherResult['success'] && !empty($teacherResult['data'])) {
                                                        foreach ($teacherResult['data'] as $teacher) {
                                                            echo '<option value="' . $teacher['id'] . '">' . 
                                                                htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']) . 
                                                                '</option>';
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Day of Week</label>
                                                <select class="form-select" name="day_of_week" required>
                                                    <option value="Monday">Monday</option>
                                                    <option value="Tuesday">Tuesday</option>
                                                    <option value="Wednesday">Wednesday</option>
                                                    <option value="Thursday">Thursday</option>
                                                    <option value="Friday">Friday</option>
                                                    <option value="Saturday">Saturday</option>
                                                    <option value="Sunday">Sunday</option>
                                                </select>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Start Time</label>
                                                    <input type="time" class="form-control" name="start_time" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">End Time</label>
                                                    <input type="time" class="form-control" name="end_time" required>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Room</label>
                                                <select class="form-select" name="room_id" required>
                                                    <option value="">Select Room</option>
                                                    <option value="1">Room 101</option>
                                                    <option value="2">Room 102</option>
                                                    <option value="3">Room 103</option>
                                                    <option value="4">Science Lab</option>
                                                    <option value="5">Computer Lab</option>
                                                    <!-- Add more rooms dynamically -->
                                                </select>
                                            </div>
                                            
                                            <button type="submit" class="btn btn-primary">Add to Schedule</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-8">
                                <div class="dashboard-card">
                                    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0"><i class="fas fa-calendar-week me-2"></i>Weekly Schedule</h5>
                                        
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-light" id="printScheduleBtn">
                                                <i class="fas fa-print me-1"></i>Print
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light ms-2" id="exportScheduleBtn">
                                                <i class="fas fa-file-export me-1"></i>Export
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label">Select Class</label>
                                            <select class="form-select" id="viewClassSchedule">
                                                <option value="">All Classes</option>
                                                <option value="1">Grade 1</option>
                                                <option value="2">Grade 2</option>
                                                <option value="3">Grade 3</option>
                                                <!-- Add more classes dynamically -->
                                            </select>
                                        </div>
                                        
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th style="width: 100px;">Time</th>
                                                        <th>Monday</th>
                                                        <th>Tuesday</th>
                                                        <th>Wednesday</th>
                                                        <th>Thursday</th>
                                                        <th>Friday</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Sample schedule data - will be replaced by dynamic data -->
                                                    <tr>
                                                        <td>8:00 - 8:45</td>
                                                        <td class="bg-light-success">Math<br><small>Room 101</small></td>
                                                        <td class="bg-light-primary">English<br><small>Room 102</small></td>
                                                        <td class="bg-light-warning">Science<br><small>Lab</small></td>
                                                        <td class="bg-light-info">Social Studies<br><small>Room 101</small></td>
                                                        <td class="bg-light-danger">P.E.<br><small>Field</small></td>
                                                    </tr>
                                                    <tr>
                                                        <td>8:50 - 9:35</td>
                                                        <td class="bg-light-primary">English<br><small>Room 102</small></td>
                                                        <td class="bg-light-success">Math<br><small>Room 101</small></td>
                                                        <td class="bg-light-info">Social Studies<br><small>Room 101</small></td>
                                                        <td class="bg-light-warning">Science<br><small>Lab</small></td>
                                                        <td class="bg-light-success">Math<br><small>Room 101</small></td>
                                                    </tr>
                                                    <tr>
                                                        <td>9:40 - 10:25</td>
                                                        <td class="bg-light-warning">Science<br><small>Lab</small></td>
                                                        <td class="bg-light-info">Social Studies<br><small>Room 101</small></td>
                                                        <td class="bg-light-primary">English<br><small>Room 102</small></td>
                                                        <td class="bg-light-success">Math<br><small>Room 101</small></td>
                                                        <td class="bg-light-primary">English<br><small>Room 102</small></td>
                                                    </tr>
                                                    <!-- Add more time slots -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Teacher Allocation Tab -->
                    <div class="tab-pane fade" id="teacher-allocation" role="tabpanel" aria-labelledby="teacher-allocation-tab">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="dashboard-card">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Assign Teacher</h5>
                                    </div>
                                    <div class="card-body">
                                        <form id="assignTeacherForm" method="POST">
                                            <input type="hidden" name="action" value="assign_teacher">
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Teacher</label>
                                                <select class="form-select" name="teacher_id" required>
                                                    <option value="">Select Teacher</option>
                                                    <!-- Populate from teachers table -->
                                                    <?php 
                                                    if (isset($teacherResult['success']) && $teacherResult['success'] && !empty($teacherResult['data'])) {
                                                        foreach ($teacherResult['data'] as $teacher) {
                                                            echo '<option value="' . $teacher['id'] . '">' . 
                                                                htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']) . 
                                                                '</option>';
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Subject</label>
                                                <select class="form-select" name="subject_id" required>
                                                    <option value="">Select Subject</option>
                                                    <option value="1">Mathematics</option>
                                                    <option value="2">English</option>
                                                    <option value="3">Science</option>
                                                    <!-- Add more subjects dynamically -->
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Classes</label>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="1" id="class1" name="classes[]">
                                                    <label class="form-check-label" for="class1">
                                                        Grade 1
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="2" id="class2" name="classes[]">
                                                    <label class="form-check-label" for="class2">
                                                        Grade 2
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="3" id="class3" name="classes[]">
                                                    <label class="form-check-label" for="class3">
                                                        Grade 3
                                                    </label>
                                                </div>
                                                <!-- Add more classes dynamically -->
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Hours per Week</label>
                                                <input type="number" class="form-control" name="hours_per_week" min="1" max="40" required>
                                            </div>
                                            
                                            <button type="submit" class="btn btn-primary">Assign Teacher</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-8">
                                <div class="dashboard-card">
                                    <div class="card-header bg-info text-white">
                                        <h5 class="mb-0"><i class="fas fa-user-clock me-2"></i>Teacher Workload</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label">Select Teacher</label>
                                            <select class="form-select" id="viewTeacherWorkload">
                                                <option value="">All Teachers</option>
                                                <!-- Populate from teachers table -->
                                                <?php 
                                                if (isset($teacherResult['success']) && $teacherResult['success'] && !empty($teacherResult['data'])) {
                                                    foreach ($teacherResult['data'] as $teacher) {
                                                        echo '<option value="' . $teacher['id'] . '">' . 
                                                            htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']) . 
                                                            '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Teacher</th>
                                                        <th>Subject</th>
                                                        <th>Classes</th>
                                                        <th>Hours/Week</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Sample teacher workload data - will be replaced by dynamic data -->
                                                    <tr>
                                                        <td>John Smith</td>
                                                        <td>Mathematics</td>
                                                        <td>Grade 1, Grade 2</td>
                                                        <td>12</td>
                                                        <td>
                                                            <button type="button" class="btn btn-warning btn-sm">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-danger btn-sm">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>Jane Doe</td>
                                                        <td>English</td>
                                                        <td>Grade 1, Grade 3</td>
                                                        <td>10</td>
                                                        <td>
                                                            <button type="button" class="btn btn-warning btn-sm">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-danger btn-sm">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <!-- Add more teacher allocations -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Room Assignment Tab -->
                    <div class="tab-pane fade" id="room-assignment" role="tabpanel" aria-labelledby="room-assignment-tab">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="dashboard-card">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0"><i class="fas fa-door-closed me-2"></i>Manage Rooms</h5>
                                    </div>
                                    <div class="card-body">
                                        <form id="manageRoomForm" method="POST">
                                            <input type="hidden" name="action" value="manage_room">
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Room Number/Name</label>
                                                <input type="text" class="form-control" name="room_name" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Room Type</label>
                                                <select class="form-select" name="room_type" required>
                                                    <option value="Classroom">Classroom</option>
                                                    <option value="Laboratory">Laboratory</option>
                                                    <option value="Library">Library</option>
                                                    <option value="Computer Lab">Computer Lab</option>
                                                    <option value="Multipurpose">Multipurpose Hall</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Capacity</label>
                                                <input type="number" class="form-control" name="capacity" min="1" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Available Facilities</label>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="1" id="projector" name="facilities[]">
                                                    <label class="form-check-label" for="projector">
                                                        Projector
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="2" id="smartboard" name="facilities[]">
                                                    <label class="form-check-label" for="smartboard">
                                                        Smart Board
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="3" id="ac" name="facilities[]">
                                                    <label class="form-check-label" for="ac">
                                                        Air Conditioning
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="4" id="computers" name="facilities[]">
                                                    <label class="form-check-label" for="computers">
                                                        Computers
                                                    </label>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Room Status</label>
                                                <select class="form-select" name="room_status" required>
                                                    <option value="Available">Available</option>
                                                    <option value="Under Maintenance">Under Maintenance</option>
                                                    <option value="Reserved">Reserved</option>
                                                </select>
                                            </div>
                                            
                                            <button type="submit" class="btn btn-primary">Save Room</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-8">
                                <div class="dashboard-card">
                                    <div class="card-header bg-info text-white">
                                        <h5 class="mb-0"><i class="fas fa-th me-2"></i>Room Availability</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label">Select Day</label>
                                            <select class="form-select" id="viewRoomAvailability">
                                                <option value="Monday">Monday</option>
                                                <option value="Tuesday">Tuesday</option>
                                                <option value="Wednesday">Wednesday</option>
                                                <option value="Thursday">Thursday</option>
                                                <option value="Friday">Friday</option>
                                            </select>
                                        </div>
                                        
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Room</th>
                                                        <th>8:00 - 9:00</th>
                                                        <th>9:00 - 10:00</th>
                                                        <th>10:00 - 11:00</th>
                                                        <th>11:00 - 12:00</th>
                                                        <th>12:00 - 1:00</th>
                                                        <th>1:00 - 2:00</th>
                                                        <th>2:00 - 3:00</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Sample room availability data - will be replaced by dynamic data -->
                                                    <tr>
                                                        <td>Room 101</td>
                                                        <td class="bg-light-danger">Grade 1 Math</td>
                                                        <td class="bg-light-danger">Grade 1 Math</td>
                                                        <td class="bg-light-success">Available</td>
                                                        <td class="bg-light-danger">Grade 2 English</td>
                                                        <td class="bg-light-warning">Lunch Break</td>
                                                        <td class="bg-light-danger">Grade 3 Science</td>
                                                        <td class="bg-light-success">Available</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Room 102</td>
                                                        <td class="bg-light-success">Available</td>
                                                        <td class="bg-light-danger">Grade 2 Math</td>
                                                        <td class="bg-light-danger">Grade 2 Math</td>
                                                        <td class="bg-light-success">Available</td>
                                                        <td class="bg-light-warning">Lunch Break</td>
                                                        <td class="bg-light-danger">Grade 1 English</td>
                                                        <td class="bg-light-danger">Grade 1 English</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Science Lab</td>
                                                        <td class="bg-light-danger">Grade 3 Science</td>
                                                        <td class="bg-light-danger">Grade 3 Science</td>
                                                        <td class="bg-light-success">Available</td>
                                                        <td class="bg-light-success">Available</td>
                                                        <td class="bg-light-warning">Lunch Break</td>
                                                        <td class="bg-light-danger">Grade 2 Science</td>
                                                        <td class="bg-light-danger">Grade 2 Science</td>
                                                    </tr>
                                                    <!-- Add more rooms -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Conflict Detection Tab -->
                    <div class="tab-pane fade" id="conflict-detector" role="tabpanel" aria-labelledby="conflict-detector-tab">
                        <div class="dashboard-card">
                            <div class="card-header bg-danger text-white">
                                <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Schedule Conflicts</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <button type="button" class="btn btn-danger" id="detectConflictsBtn">
                                        <i class="fas fa-search me-2"></i>Detect Conflicts
                                    </button>
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Conflict Type</th>
                                                <th>Details</th>
                                                <th>Affected Classes</th>
                                                <th>Day & Time</th>
                                                <th>Suggested Resolution</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Sample conflict data - will be replaced by dynamic data -->
                                            <tr>
                                                <td><span class="badge bg-danger">Teacher</span></td>
                                                <td>Jane Doe is assigned to two classes at the same time</td>
                                                <td>Grade 1 English, Grade 3 English</td>
                                                <td>Monday, 8:00 - 8:45</td>
                                                <td>Reschedule Grade 3 English to Monday, 9:40 - 10:25</td>
                                                <td>
                                                    <button type="button" class="btn btn-primary btn-sm">
                                                        <i class="fas fa-check"></i> Apply Fix
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><span class="badge bg-warning">Room</span></td>
                                                <td>Room 101 is double-booked</td>
                                                <td>Grade 1 Math, Grade 2 Science</td>
                                                <td>Tuesday, 10:00 - 10:45</td>
                                                <td>Move Grade 2 Science to Science Lab</td>
                                                <td>
                                                    <button type="button" class="btn btn-primary btn-sm">
                                                        <i class="fas fa-check"></i> Apply Fix
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><span class="badge bg-info">Class</span></td>
                                                <td>Grade 3 has no break time</td>
                                                <td>Grade 3</td>
                                                <td>Wednesday</td>
                                                <td>Add 20-minute break after period 3</td>
                                                <td>
                                                    <button type="button" class="btn btn-primary btn-sm">
                                                        <i class="fas fa-check"></i> Apply Fix
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($section == 'exams'): ?>
        <!-- Exam Management Section -->
        <div class="row mt-4">
            <!-- Tab navigation for Exam functions -->
            <div class="col-md-12 mb-4">
                <ul class="nav nav-pills mb-3" id="examTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="exam-schedule-tab" data-bs-toggle="pill" 
                                data-bs-target="#exam-schedule" type="button" role="tab" 
                                aria-controls="exam-schedule" aria-selected="true">
                            <i class="fas fa-calendar-alt me-2"></i>Exam Schedule
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="question-bank-tab" data-bs-toggle="pill" 
                                data-bs-target="#question-bank" type="button" role="tab" 
                                aria-controls="question-bank" aria-selected="false">
                            <i class="fas fa-question-circle me-2"></i>Question Bank
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="online-assessment-tab" data-bs-toggle="pill" 
                                data-bs-target="#online-assessment" type="button" role="tab" 
                                aria-controls="online-assessment" aria-selected="false">
                            <i class="fas fa-laptop me-2"></i>Online Assessment
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="result-processing-tab" data-bs-toggle="pill" 
                                data-bs-target="#result-processing" type="button" role="tab" 
                                aria-controls="result-processing" aria-selected="false">
                            <i class="fas fa-chart-bar me-2"></i>Result Processing
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content p-0" id="examTabContent">
                    <!-- Exam Schedule Tab -->
                    <div class="tab-pane fade show active" id="exam-schedule" role="tabpanel" aria-labelledby="exam-schedule-tab">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="dashboard-card">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Schedule New Exam</h5>
                                    </div>
                                    <div class="card-body">
                                        <form id="scheduleExamForm" method="POST">
                                            <input type="hidden" name="action" value="schedule_exam">
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Exam Title</label>
                                                <input type="text" class="form-control" name="exam_title" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Exam Type</label>
                                                <select class="form-select" name="exam_type" required>
                                                    <option value="">Select Type</option>
                                                    <option value="Mid-Term">Mid-Term</option>
                                                    <option value="Final">Final</option>
                                                    <option value="Quiz">Quiz</option>
                                                    <option value="Assignment">Assignment</option>
                                                    <option value="Project">Project</option>
                                                </select>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Date</label>
                                                    <input type="date" class="form-control" name="exam_date" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Duration (mins)</label>
                                                    <input type="number" class="form-control" name="duration" min="15" required>
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Start Time</label>
                                                    <input type="time" class="form-control" name="start_time" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">End Time</label>
                                                    <input type="time" class="form-control" name="end_time" required>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Subject</label>
                                                <select class="form-select" name="subject_id" required>
                                                    <option value="">Select Subject</option>
                                                    <option value="1">Mathematics</option>
                                                    <option value="2">English</option>
                                                    <option value="3">Science</option>
                                                    <option value="4">Social Studies</option>
                                                    <option value="5">Physical Education</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Class/Grade</label>
                                                <select class="form-select" name="class_id" required>
                                                    <option value="">Select Class</option>
                                                    <option value="1">Grade 1</option>
                                                    <option value="2">Grade 2</option>
                                                    <option value="3">Grade 3</option>
                                                    <option value="4">Grade 4</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Room</label>
                                                <select class="form-select" name="room_id" required>
                                                    <option value="">Select Room</option>
                                                    <option value="1">Room 101</option>
                                                    <option value="2">Room 102</option>
                                                    <option value="3">Room 103</option>
                                                    <option value="4">Hall</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Instructions</label>
                                                <textarea class="form-control" name="instructions" rows="3"></textarea>
                                            </div>
                                            
                                            <button type="submit" class="btn btn-primary">Schedule Exam</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-8">
                                <div class="dashboard-card">
                                    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Upcoming Exams</h5>
                                        
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-light" id="printExamScheduleBtn">
                                                <i class="fas fa-print me-1"></i>Print
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light ms-2" id="exportExamScheduleBtn">
                                                <i class="fas fa-file-export me-1"></i>Export
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label">Filter by Class</label>
                                            <select class="form-select" id="filterExamsByClass">
                                                <option value="">All Classes</option>
                                                <option value="1">Grade 1</option>
                                                <option value="2">Grade 2</option>
                                                <option value="3">Grade 3</option>
                                                <option value="4">Grade 4</option>
                                            </select>
                                        </div>
                                        
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Exam Title</th>
                                                        <th>Subject</th>
                                                        <th>Class</th>
                                                        <th>Date</th>
                                                        <th>Time</th>
                                                        <th>Room</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Sample exam data - will be replaced by dynamic data -->
                                                    <tr>
                                                        <td>Mid-Term Mathematics</td>
                                                        <td>Mathematics</td>
                                                        <td>Grade 3</td>
                                                        <td>2023-11-15</td>
                                                        <td>09:00 - 10:30</td>
                                                        <td>Room 101</td>
                                                        <td>
                                                            <button type="button" class="btn btn-primary btn-sm">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-warning btn-sm">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-danger btn-sm">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>Final English</td>
                                                        <td>English</td>
                                                        <td>Grade 2</td>
                                                        <td>2023-12-10</td>
                                                        <td>10:45 - 12:15</td>
                                                        <td>Room 102</td>
                                                        <td>
                                                            <button type="button" class="btn btn-primary btn-sm">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-warning btn-sm">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-danger btn-sm">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>Science Quiz</td>
                                                        <td>Science</td>
                                                        <td>Grade 4</td>
                                                        <td>2023-11-12</td>
                                                        <td>08:30 - 09:15</td>
                                                        <td>Room 103</td>
                                                        <td>
                                                            <button type="button" class="btn btn-primary btn-sm">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-warning btn-sm">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-danger btn-sm">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Result Processing Tab -->
                    <div class="tab-pane fade" id="result-processing" role="tabpanel" aria-labelledby="result-processing-tab">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="dashboard-card">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Enter Marks</h5>
                                    </div>
                                    <div class="card-body">
                                        <form id="enterMarksForm" method="POST">
                                            <input type="hidden" name="action" value="enter_marks">
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Select Exam</label>
                                                <select class="form-select" name="exam_id" required>
                                                    <option value="">Select Exam</option>
                                                    <option value="1">Mid-Term Mathematics - Grade 3</option>
                                                    <option value="2">Final English - Grade 2</option>
                                                    <option value="3">Science Quiz - Grade 4</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Class/Grade</label>
                                                <select class="form-select" name="class_id" required>
                                                    <option value="">Select Class</option>
                                                    <option value="1">Grade 1</option>
                                                    <option value="2">Grade 2</option>
                                                    <option value="3">Grade 3</option>
                                                    <option value="4">Grade 4</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Subject</label>
                                                <select class="form-select" name="subject_id" required>
                                                    <option value="">Select Subject</option>
                                                    <option value="1">Mathematics</option>
                                                    <option value="2">English</option>
                                                    <option value="3">Science</option>
                                                    <option value="4">Social Studies</option>
                                                </select>
                                            </div>
                                            
                                            <button type="submit" class="btn btn-primary">Proceed to Enter Marks</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-8">
                                <div class="dashboard-card">
                                    <div class="card-header bg-success text-white">
                                        <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Exam Results Analysis</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3 row">
                                            <div class="col-md-4">
                                                <label class="form-label">Exam</label>
                                                <select class="form-select" id="analysisExam">
                                                    <option value="">Select Exam</option>
                                                    <option value="1">Mid-Term Mathematics - Grade 3</option>
                                                    <option value="2">Final English - Grade 2</option>
                                                    <option value="3">Science Quiz - Grade 4</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Class</label>
                                                <select class="form-select" id="analysisClass">
                                                    <option value="">Select Class</option>
                                                    <option value="1">Grade 1</option>
                                                    <option value="2">Grade 2</option>
                                                    <option value="3">Grade 3</option>
                                                    <option value="4">Grade 4</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Subject</label>
                                                <select class="form-select" id="analysisSubject">
                                                    <option value="">Select Subject</option>
                                                    <option value="1">Mathematics</option>
                                                    <option value="2">English</option>
                                                    <option value="3">Science</option>
                                                    <option value="4">Social Studies</option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <button type="button" class="btn btn-success" id="generateAnalysisBtn">
                                                <i class="fas fa-chart-bar me-1"></i> Generate Analysis
                                            </button>
                                            <button type="button" class="btn btn-primary ms-2" id="printAnalysisBtn">
                                                <i class="fas fa-print me-1"></i> Print Report
                                            </button>
                                            <button type="button" class="btn btn-info ms-2" id="exportAnalysisBtn">
                                                <i class="fas fa-file-export me-1"></i> Export Excel
                                            </button>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="card mb-3">
                                                    <div class="card-header bg-light">
                                                        <h6 class="mb-0">Performance Summary</h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <canvas id="resultChart" width="100%" height="200"></canvas>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="card mb-3">
                                                    <div class="card-header bg-light">
                                                        <h6 class="mb-0">Grade Distribution</h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <canvas id="gradeChart" width="100%" height="200"></canvas>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Student</th>
                                                        <th>Grade</th>
                                                        <th>Marks</th>
                                                        <th>Percentage</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Sample results data - will be replaced by dynamic data -->
                                                    <tr>
                                                        <td>John Smith</td>
                                                        <td>A</td>
                                                        <td>85/100</td>
                                                        <td>85%</td>
                                                        <td><span class="badge bg-success">Passed</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Jane Doe</td>
                                                        <td>B+</td>
                                                        <td>78/100</td>
                                                        <td>78%</td>
                                                        <td><span class="badge bg-success">Passed</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Michael Brown</td>
                                                        <td>C</td>
                                                        <td>65/100</td>
                                                        <td>65%</td>
                                                        <td><span class="badge bg-success">Passed</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Lisa Green</td>
                                                        <td>D</td>
                                                        <td>52/100</td>
                                                        <td>52%</td>
                                                        <td><span class="badge bg-warning">Barely Passed</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td>James Wilson</td>
                                                        <td>F</td>
                                                        <td>35/100</td>
                                                        <td>35%</td>
                                                        <td><span class="badge bg-danger">Failed</span></td>
                                                    </tr>
                                                </tbody>
                                                <tfoot class="table-secondary">
                                                    <tr>
                                                        <th colspan="2">Class Average</th>
                                                        <th>63/100</th>
                                                        <th>63%</th>
                                                        <th></th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($section == 'finance'): ?>
        <!-- Financial Management Section -->
        <div class="row mt-4">
            <!-- Tab navigation for finance functions -->
            <div class="col-md-12 mb-4">
                <ul class="nav nav-pills mb-3" id="financeTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="fee-collection-tab" data-bs-toggle="pill" 
                                data-bs-target="#fee-collection" type="button" role="tab" 
                                aria-controls="fee-collection" aria-selected="true">
                            <i class="fas fa-money-check-alt me-2"></i>Fee Collection
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="payment-tracking-tab" data-bs-toggle="pill" 
                                data-bs-target="#payment-tracking" type="button" role="tab" 
                                aria-controls="payment-tracking" aria-selected="false">
                            <i class="fas fa-receipt me-2"></i>Payment Tracking
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="expense-management-tab" data-bs-toggle="pill" 
                                data-bs-target="#expense-management" type="button" role="tab" 
                                aria-controls="expense-management" aria-selected="false">
                            <i class="fas fa-file-invoice-dollar me-2"></i>Expense Management
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="salary-processing-tab" data-bs-toggle="pill" 
                                data-bs-target="#salary-processing" type="button" role="tab" 
                                aria-controls="salary-processing" aria-selected="false">
                            <i class="fas fa-money-bill-wave me-2"></i>Salary Processing
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content p-0" id="financeTabContent">
                    <!-- Fee Collection Tab -->
                    <div class="tab-pane fade show active" id="fee-collection" role="tabpanel" aria-labelledby="fee-collection-tab">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="dashboard-card">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Collect Fee</h5>
                                    </div>
                                    <div class="card-body">
                                        <form id="collectFeeForm" method="POST">
                                            <input type="hidden" name="action" value="collect_fee">
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Student</label>
                                                <select class="form-select" name="student_id" required>
                                                    <option value="">Select Student</option>
                                                    <!-- Populate from students table -->
                                                    <?php 
                                                    $studentResult = $attendanceManager->getAllStudents();
                                                    if (isset($studentResult['success']) && $studentResult['success'] && !empty($studentResult['data'])) {
                                                        foreach ($studentResult['data'] as $student) {
                                                            echo '<option value="' . $student['id'] . '">' . 
                                                                htmlspecialchars($student['first_name'] . ' ' . $student['last_name'] . ' (Grade ' . $student['grade_level'] . ')') . 
                                                                '</option>';
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Fee Type</label>
                                                <select class="form-select" name="fee_type" required>
                                                    <option value="">Select Fee Type</option>
                                                    <option value="Tuition">Tuition Fee</option>
                                                    <option value="Transportation">Transportation Fee</option>
                                                    <option value="Library">Library Fee</option>
                                                    <option value="Laboratory">Laboratory Fee</option>
                                                    <option value="Sports">Sports Fee</option>
                                                    <option value="Examination">Examination Fee</option>
                                                    <option value="Miscellaneous">Miscellaneous Fee</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Term/Period</label>
                                                <select class="form-select" name="term" required>
                                                    <option value="">Select Term</option>
                                                    <option value="Term 1">Term 1</option>
                                                    <option value="Term 2">Term 2</option>
                                                    <option value="Term 3">Term 3</option>
                                                    <option value="Annual">Annual</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Amount</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">$</span>
                                                    <input type="number" class="form-control" name="amount" step="0.01" min="0" required>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Payment Method</label>
                                                <select class="form-select" name="payment_method" required>
                                                    <option value="">Select Payment Method</option>
                                                    <option value="Cash">Cash</option>
                                                    <option value="Check">Check</option>
                                                    <option value="Bank Transfer">Bank Transfer</option>
                                                    <option value="Credit Card">Credit Card</option>
                                                    <option value="Mobile Money">Mobile Money</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Date</label>
                                                <input type="date" class="form-control" name="payment_date" value="<?php echo date('Y-m-d'); ?>" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Reference/Receipt No.</label>
                                                <input type="text" class="form-control" name="reference_no">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Remarks</label>
                                                <textarea class="form-control" name="remarks" rows="2"></textarea>
                                            </div>
                                            
                                            <button type="submit" class="btn btn-primary">Record Payment</button>
                                            <button type="button" class="btn btn-success ms-2">
                                                <i class="fas fa-print me-1"></i> Print Receipt
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-8">
                                <div class="dashboard-card">
                                    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0"><i class="fas fa-list-alt me-2"></i>Recent Payments</h5>
                                        
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-light" id="exportPaymentsBtn">
                                                <i class="fas fa-file-export me-1"></i>Export
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Receipt No.</th>
                                                        <th>Student</th>
                                                        <th>Fee Type</th>
                                                        <th>Term</th>
                                                        <th>Amount</th>
                                                        <th>Date</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Sample payments data - will be replaced by dynamic data -->
                                                    <tr>
                                                        <td>REC-001</td>
                                                        <td>John Smith</td>
                                                        <td>Tuition Fee</td>
                                                        <td>Term 1</td>
                                                        <td>$500.00</td>
                                                        <td>2023-09-05</td>
                                                        <td><span class="badge bg-success">Paid</span></td>
                                                        <td>
                                                            <button type="button" class="btn btn-primary btn-sm">
                                                                <i class="fas fa-print"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-info btn-sm">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>REC-002</td>
                                                        <td>Jane Doe</td>
                                                        <td>Transportation Fee</td>
                                                        <td>Term 1</td>
                                                        <td>$150.00</td>
                                                        <td>2023-09-06</td>
                                                        <td><span class="badge bg-success">Paid</span></td>
                                                        <td>
                                                            <button type="button" class="btn btn-primary btn-sm">
                                                                <i class="fas fa-print"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-info btn-sm">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>REC-003</td>
                                                        <td>Michael Brown</td>
                                                        <td>Tuition Fee</td>
                                                        <td>Term 1</td>
                                                        <td>$500.00</td>
                                                        <td>2023-09-07</td>
                                                        <td><span class="badge bg-warning">Partial</span></td>
                                                        <td>
                                                            <button type="button" class="btn btn-primary btn-sm">
                                                                <i class="fas fa-print"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-info btn-sm">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Expense Management Tab -->
                    <div class="tab-pane fade" id="expense-management" role="tabpanel" aria-labelledby="expense-management-tab">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="dashboard-card">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Add Expense</h5>
                                    </div>
                                    <div class="card-body">
                                        <form id="addExpenseForm" method="POST">
                                            <input type="hidden" name="action" value="add_expense">
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Expense Title</label>
                                                <input type="text" class="form-control" name="expense_title" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Category</label>
                                                <select class="form-select" name="category" required>
                                                    <option value="">Select Category</option>
                                                    <option value="Utilities">Utilities</option>
                                                    <option value="Supplies">Supplies</option>
                                                    <option value="Maintenance">Maintenance</option>
                                                    <option value="Equipment">Equipment</option>
                                                    <option value="Salary">Salary</option>
                                                    <option value="Transportation">Transportation</option>
                                                    <option value="Miscellaneous">Miscellaneous</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Amount</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">$</span>
                                                    <input type="number" class="form-control" name="amount" step="0.01" min="0" required>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Date</label>
                                                <input type="date" class="form-control" name="expense_date" value="<?php echo date('Y-m-d'); ?>" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Payment Method</label>
                                                <select class="form-select" name="payment_method" required>
                                                    <option value="">Select Payment Method</option>
                                                    <option value="Cash">Cash</option>
                                                    <option value="Check">Check</option>
                                                    <option value="Bank Transfer">Bank Transfer</option>
                                                    <option value="Credit Card">Credit Card</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Reference No.</label>
                                                <input type="text" class="form-control" name="reference_no">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Description</label>
                                                <textarea class="form-control" name="description" rows="3"></textarea>
                                            </div>
                                            
                                            <button type="submit" class="btn btn-primary">Save Expense</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-8">
                                <div class="dashboard-card">
                                    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Expense Overview</h5>
                                        
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-light" id="printExpenseBtn">
                                                <i class="fas fa-print me-1"></i>Print
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light ms-2" id="exportExpenseBtn">
                                                <i class="fas fa-file-export me-1"></i>Export
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <canvas id="expenseChart" width="100%" height="250"></canvas>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <h6 class="card-title">Expense Summary</h6>
                                                        <div class="table-responsive">
                                                            <table class="table table-sm">
                                                                <tbody>
                                                                    <tr>
                                                                        <td>Total Expenses</td>
                                                                        <td class="text-end">$12,500.00</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Utilities</td>
                                                                        <td class="text-end">$2,300.00</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Supplies</td>
                                                                        <td class="text-end">$1,750.00</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Maintenance</td>
                                                                        <td class="text-end">$800.00</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Equipment</td>
                                                                        <td class="text-end">$3,200.00</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Salary</td>
                                                                        <td class="text-end">$4,000.00</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Transportation</td>
                                                                        <td class="text-end">$450.00</td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Title</th>
                                                        <th>Category</th>
                                                        <th>Amount</th>
                                                        <th>Date</th>
                                                        <th>Payment Method</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Sample expense data - will be replaced by dynamic data -->
                                                    <tr>
                                                        <td>EXP001</td>
                                                        <td>Electricity Bill</td>
                                                        <td>Utilities</td>
                                                        <td>$850.00</td>
                                                        <td>2023-09-05</td>
                                                        <td>Bank Transfer</td>
                                                        <td>
                                                            <button type="button" class="btn btn-info btn-sm">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-warning btn-sm">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-danger btn-sm">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>EXP002</td>
                                                        <td>Office Supplies</td>
                                                        <td>Supplies</td>
                                                        <td>$320.00</td>
                                                        <td>2023-09-10</td>
                                                        <td>Credit Card</td>
                                                        <td>
                                                            <button type="button" class="btn btn-info btn-sm">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-warning btn-sm">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-danger btn-sm">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>EXP003</td>
                                                        <td>Computer Repair</td>
                                                        <td>Maintenance</td>
                                                        <td>$450.00</td>
                                                        <td>2023-09-15</td>
                                                        <td>Cash</td>
                                                        <td>
                                                            <button type="button" class="btn btn-info btn-sm">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-warning btn-sm">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-danger btn-sm">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($section == 'resources'): ?>
        <!-- Resource Management Section -->
        <div class="row mt-4">
            <!-- Tab navigation for resource functions -->
            <div class="col-md-12 mb-4">
                <ul class="nav nav-pills mb-3" id="resourceTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="inventory-tab" data-bs-toggle="pill" 
                                data-bs-target="#inventory" type="button" role="tab" 
                                aria-controls="inventory" aria-selected="true">
                            <i class="fas fa-boxes me-2"></i>Inventory
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="library-tab" data-bs-toggle="pill" 
                                data-bs-target="#library" type="button" role="tab" 
                                aria-controls="library" aria-selected="false">
                            <i class="fas fa-book me-2"></i>Library
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="maintenance-tab" data-bs-toggle="pill" 
                                data-bs-target="#maintenance" type="button" role="tab" 
                                aria-controls="maintenance" aria-selected="false">
                            <i class="fas fa-tools me-2"></i>Maintenance
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="allocation-tab" data-bs-toggle="pill" 
                                data-bs-target="#allocation" type="button" role="tab" 
                                aria-controls="allocation" aria-selected="false">
                            <i class="fas fa-hand-holding me-2"></i>Resource Allocation
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content p-0" id="resourceTabContent">
                    <!-- Inventory Tab -->
                    <div class="tab-pane fade show active" id="inventory" role="tabpanel" aria-labelledby="inventory-tab">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="dashboard-card">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Add Inventory Item</h5>
                                    </div>
                                    <div class="card-body">
                                        <form id="addInventoryForm" method="POST">
                                            <input type="hidden" name="action" value="add_inventory">
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Item Name</label>
                                                <input type="text" class="form-control" name="item_name" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Category</label>
                                                <select class="form-select" name="category" required>
                                                    <option value="">Select Category</option>
                                                    <option value="Furniture">Furniture</option>
                                                    <option value="Electronics">Electronics</option>
                                                    <option value="Stationery">Stationery</option>
                                                    <option value="Sports Equipment">Sports Equipment</option>
                                                    <option value="Laboratory Equipment">Laboratory Equipment</option>
                                                    <option value="Teaching Aids">Teaching Aids</option>
                                                    <option value="Office Supplies">Office Supplies</option>
                                                </select>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Quantity</label>
                                                    <input type="number" class="form-control" name="quantity" min="1" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Unit</label>
                                                    <select class="form-select" name="unit" required>
                                                        <option value="Piece">Piece</option>
                                                        <option value="Set">Set</option>
                                                        <option value="Pack">Pack</option>
                                                        <option value="Box">Box</option>
                                                        <option value="Ream">Ream</option>
                                                        <option value="Dozen">Dozen</option>
                                                    </select>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Unit Price</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">$</span>
                                                    <input type="number" class="form-control" name="unit_price" step="0.01" min="0" required>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Supplier</label>
                                                <input type="text" class="form-control" name="supplier">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Location</label>
                                                <input type="text" class="form-control" name="location" placeholder="e.g., Store Room, Lab, etc.">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Purchase Date</label>
                                                <input type="date" class="form-control" name="purchase_date" value="<?php echo date('Y-m-d'); ?>">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Description</label>
                                                <textarea class="form-control" name="description" rows="2"></textarea>
                                            </div>
                                            
                                            <button type="submit" class="btn btn-primary">Add Item</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-8">
                                <div class="dashboard-card">
                                    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Inventory Items</h5>
                                        
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-light" id="printInventoryBtn">
                                                <i class="fas fa-print me-1"></i>Print
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light ms-2" id="exportInventoryBtn">
                                                <i class="fas fa-file-export me-1"></i>Export
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3 row">
                                            <div class="col-md-4">
                                                <input type="text" class="form-control" id="searchInventory" placeholder="Search items...">
                                            </div>
                                            <div class="col-md-3">
                                                <select class="form-select" id="filterCategory">
                                                    <option value="">All Categories</option>
                                                    <option value="Furniture">Furniture</option>
                                                    <option value="Electronics">Electronics</option>
                                                    <option value="Stationery">Stationery</option>
                                                    <option value="Sports Equipment">Sports Equipment</option>
                                                    <option value="Laboratory Equipment">Laboratory Equipment</option>
                                                    <option value="Teaching Aids">Teaching Aids</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <select class="form-select" id="sortInventory">
                                                    <option value="name">Sort by Name</option>
                                                    <option value="date">Sort by Date</option>
                                                    <option value="quantity">Sort by Quantity</option>
                                                    <option value="value">Sort by Value</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" class="btn btn-primary w-100" id="filterBtn">
                                                    <i class="fas fa-filter me-1"></i>Filter
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Item Name</th>
                                                        <th>Category</th>
                                                        <th>Quantity</th>
                                                        <th>Unit Price</th>
                                                        <th>Total Value</th>
                                                        <th>Location</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Sample inventory data - will be replaced by dynamic data -->
                                                    <tr>
                                                        <td>Student Desk</td>
                                                        <td>Furniture</td>
                                                        <td>50</td>
                                                        <td>$75.00</td>
                                                        <td>$3,750.00</td>
                                                        <td>Store Room</td>
                                                        <td><span class="badge bg-success">In Stock</span></td>
                                                        <td>
                                                            <button type="button" class="btn btn-info btn-sm">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-warning btn-sm">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-danger btn-sm">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>Projector</td>
                                                        <td>Electronics</td>
                                                        <td>5</td>
                                                        <td>$450.00</td>
                                                        <td>$2,250.00</td>
                                                        <td>IT Office</td>
                                                        <td><span class="badge bg-success">In Stock</span></td>
                                                        <td>
                                                            <button type="button" class="btn btn-info btn-sm">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-warning btn-sm">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-danger btn-sm">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>Whiteboard Markers</td>
                                                        <td>Stationery</td>
                                                        <td>8</td>
                                                        <td>$12.50</td>
                                                        <td>$100.00</td>
                                                        <td>Admin Office</td>
                                                        <td><span class="badge bg-warning">Low Stock</span></td>
                                                        <td>
                                                            <button type="button" class="btn btn-info btn-sm">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-warning btn-sm">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-danger btn-sm">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Library Tab -->
                    <div class="tab-pane fade" id="library" role="tabpanel" aria-labelledby="library-tab">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="dashboard-card">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0"><i class="fas fa-book-medical me-2"></i>Add Book</h5>
                                    </div>
                                    <div class="card-body">
                                        <form id="addBookForm" method="POST">
                                            <input type="hidden" name="action" value="add_book">
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Title</label>
                                                <input type="text" class="form-control" name="title" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Author</label>
                                                <input type="text" class="form-control" name="author" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">ISBN</label>
                                                <input type="text" class="form-control" name="isbn">
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Category</label>
                                                    <select class="form-select" name="category" required>
                                                        <option value="">Select Category</option>
                                                        <option value="Textbook">Textbook</option>
                                                        <option value="Reference">Reference</option>
                                                        <option value="Fiction">Fiction</option>
                                                        <option value="Non-Fiction">Non-Fiction</option>
                                                        <option value="Biography">Biography</option>
                                                        <option value="Science">Science</option>
                                                        <option value="Mathematics">Mathematics</option>
                                                        <option value="History">History</option>
                                                        <option value="Literature">Literature</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Shelf Number</label>
                                                    <input type="text" class="form-control" name="shelf_number">
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Publication Year</label>
                                                    <input type="number" class="form-control" name="publication_year" min="1900" max="<?php echo date('Y'); ?>">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Quantity</label>
                                                    <input type="number" class="form-control" name="quantity" min="1" value="1" required>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Publisher</label>
                                                <input type="text" class="form-control" name="publisher">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Description</label>
                                                <textarea class="form-control" name="description" rows="3"></textarea>
                                            </div>
                                            
                                            <button type="submit" class="btn btn-primary">Add Book</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-8">
                                <div class="dashboard-card">
                                    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0"><i class="fas fa-book-reader me-2"></i>Book Catalog</h5>
                                        
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-light" id="issueBooksBtn">
                                                <i class="fas fa-share me-1"></i>Issue Books
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light ms-2" id="returnBooksBtn">
                                                <i class="fas fa-undo me-1"></i>Return Books
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3 row">
                                            <div class="col-md-8">
                                                <input type="text" class="form-control" id="searchBooks" placeholder="Search by title, author, or ISBN...">
                                            </div>
                                            <div class="col-md-4">
                                                <select class="form-select" id="filterBookCategory">
                                                    <option value="">All Categories</option>
                                                    <option value="Textbook">Textbook</option>
                                                    <option value="Reference">Reference</option>
                                                    <option value="Fiction">Fiction</option>
                                                    <option value="Non-Fiction">Non-Fiction</option>
                                                    <option value="Biography">Biography</option>
                                                    <option value="Science">Science</option>
                                                    <option value="Mathematics">Mathematics</option>
                                                    <option value="History">History</option>
                                                    <option value="Literature">Literature</option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Title</th>
                                                        <th>Author</th>
                                                        <th>Category</th>
                                                        <th>Available</th>
                                                        <th>Total</th>
                                                        <th>Shelf</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Sample book data - will be replaced by dynamic data -->
                                                    <tr>
                                                        <td>Advanced Mathematics</td>
                                                        <td>John Smith</td>
                                                        <td>Textbook</td>
                                                        <td>12</td>
                                                        <td>15</td>
                                                        <td>A-12</td>
                                                        <td>
                                                            <button type="button" class="btn btn-info btn-sm">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-warning btn-sm">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-primary btn-sm">
                                                                <i class="fas fa-share"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>Introduction to Physics</td>
                                                        <td>Robert Johnson</td>
                                                        <td>Science</td>
                                                        <td>8</td>
                                                        <td>10</td>
                                                        <td>B-05</td>
                                                        <td>
                                                            <button type="button" class="btn btn-info btn-sm">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-warning btn-sm">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-primary btn-sm">
                                                                <i class="fas fa-share"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>To Kill a Mockingbird</td>
                                                        <td>Harper Lee</td>
                                                        <td>Fiction</td>
                                                        <td>0</td>
                                                        <td>5</td>
                                                        <td>C-10</td>
                                                        <td>
                                                            <button type="button" class="btn btn-info btn-sm">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-warning btn-sm">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-secondary btn-sm" disabled>
                                                                <i class="fas fa-share"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($section == 'communication'): ?>
        <!-- Communication Platform Section -->
        <div class="row mt-4">
            <!-- Tab navigation for communication functions -->
            <div class="col-md-12 mb-4">
                <ul class="nav nav-pills mb-3" id="communicationTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="announcement-tab" data-bs-toggle="pill" 
                                data-bs-target="#announcement" type="button" role="tab" 
                                aria-controls="announcement" aria-selected="true">
                            <i class="fas fa-bullhorn me-2"></i>Announcements
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="messaging-tab" data-bs-toggle="pill" 
                                data-bs-target="#messaging" type="button" role="tab" 
                                aria-controls="messaging" aria-selected="false">
                            <i class="fas fa-comments me-2"></i>Messaging
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="document-sharing-tab" data-bs-toggle="pill" 
                                data-bs-target="#document-sharing" type="button" role="tab" 
                                aria-controls="document-sharing" aria-selected="false">
                            <i class="fas fa-file-alt me-2"></i>Document Sharing
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="parent-portal-tab" data-bs-toggle="pill" 
                                data-bs-target="#parent-portal" type="button" role="tab" 
                                aria-controls="parent-portal" aria-selected="false">
                            <i class="fas fa-users me-2"></i>Parent Portal
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content p-0" id="communicationTabContent">
                    <!-- Announcements Tab -->
                    <div class="tab-pane fade show active" id="announcement" role="tabpanel" aria-labelledby="announcement-tab">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="dashboard-card">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Create Announcement</h5>
                                    </div>
                                    <div class="card-body">
                                        <form id="createAnnouncementForm" method="POST">
                                            <input type="hidden" name="action" value="create_announcement">
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Title</label>
                                                <input type="text" class="form-control" name="title" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Content</label>
                                                <textarea class="form-control" name="content" rows="6" required></textarea>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Target Audience</label>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="students" id="audience-students" name="audience[]" checked>
                                                    <label class="form-check-label" for="audience-students">
                                                        Students
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="teachers" id="audience-teachers" name="audience[]" checked>
                                                    <label class="form-check-label" for="audience-teachers">
                                                        Teachers
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="parents" id="audience-parents" name="audience[]" checked>
                                                    <label class="form-check-label" for="audience-parents">
                                                        Parents
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="staff" id="audience-staff" name="audience[]">
                                                    <label class="form-check-label" for="audience-staff">
                                                        Staff
                                                    </label>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Publish Date</label>
                                                <input type="date" class="form-control" name="publish_date" value="<?php echo date('Y-m-d'); ?>" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Expiry Date</label>
                                                <input type="date" class="form-control" name="expiry_date">
                                                <div class="form-text">Leave blank if announcement doesn't expire</div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="1" id="send-notification" name="send_notification" checked>
                                                    <label class="form-check-label" for="send-notification">
                                                        Send notification via SMS/Email
                                                    </label>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="1" id="important" name="important">
                                                    <label class="form-check-label" for="important">
                                                        Mark as important
                                                    </label>
                                                </div>
                                            </div>
                                            
                                            <button type="submit" class="btn btn-primary">Publish Announcement</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-8">
                                <div class="dashboard-card">
                                    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0"><i class="fas fa-bullhorn me-2"></i>Recent Announcements</h5>
                                        
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-light" id="archiveAnnouncementsBtn">
                                                <i class="fas fa-archive me-1"></i>Archive
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <!-- Announcement 1 -->
                                        <div class="card mb-3">
                                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-star text-warning me-2"></i>Annual Sports Day Schedule
                                                </h6>
                                                <span class="badge bg-primary">2023-12-05</span>
                                            </div>
                                            <div class="card-body">
                                                <p>The Annual Sports Day will be held on December 15, 2023. All students are required to participate in at least one event. Please refer to the attached schedule for more details.</p>
                                                <p><strong>Target:</strong> Students, Teachers, Parents</p>
                                                <div class="d-flex justify-content-end">
                                                    <button type="button" class="btn btn-warning btn-sm me-2">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-sm">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Announcement 2 -->
                                        <div class="card mb-3">
                                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0">Parent-Teacher Meeting</h6>
                                                <span class="badge bg-primary">2023-11-28</span>
                                            </div>
                                            <div class="card-body">
                                                <p>The Parent-Teacher Meeting for this semester will be held on December 10, 2023, from 9:00 AM to 3:00 PM. Parents are requested to book their slots in advance through the parent portal.</p>
                                                <p><strong>Target:</strong> Parents, Teachers</p>
                                                <div class="d-flex justify-content-end">
                                                    <button type="button" class="btn btn-warning btn-sm me-2">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-sm">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Announcement 3 -->
                                        <div class="card mb-3">
                                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0">Holiday Notice</h6>
                                                <span class="badge bg-primary">2023-11-20</span>
                                            </div>
                                            <div class="card-body">
                                                <p>The school will remain closed from December 22, 2023, to January 3, 2024, for the winter break. Classes will resume on January 4, 2024.</p>
                                                <p><strong>Target:</strong> Students, Teachers, Parents, Staff</p>
                                                <div class="d-flex justify-content-end">
                                                    <button type="button" class="btn btn-warning btn-sm me-2">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-sm">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Messaging Tab -->
                    <div class="tab-pane fade" id="messaging" role="tabpanel" aria-labelledby="messaging-tab">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="dashboard-card">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0"><i class="fas fa-paper-plane me-2"></i>Send Message</h5>
                                    </div>
                                    <div class="card-body">
                                        <form id="sendMessageForm" method="POST">
                                            <input type="hidden" name="action" value="send_message">
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Recipient Type</label>
                                                <select class="form-select" name="recipient_type" id="recipient-type" required>
                                                    <option value="">Select Recipient Type</option>
                                                    <option value="individual">Individual</option>
                                                    <option value="class">Class/Grade</option>
                                                    <option value="staff">Staff</option>
                                                    <option value="all">All Users</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3" id="individual-recipient-container" style="display: none;">
                                                <label class="form-label">Recipient</label>
                                                <select class="form-select" name="individual_recipient">
                                                    <option value="">Select Recipient</option>
                                                    <optgroup label="Teachers">
                                                        <option value="teacher-1">John Smith (Mathematics)</option>
                                                        <option value="teacher-2">Jane Doe (English)</option>
                                                        <option value="teacher-3">Michael Brown (Science)</option>
                                                    </optgroup>
                                                    <optgroup label="Students">
                                                        <option value="student-1">Alex Johnson (Grade 3)</option>
                                                        <option value="student-2">Emily Wilson (Grade 2)</option>
                                                        <option value="student-3">Ryan Thompson (Grade 4)</option>
                                                    </optgroup>
                                                    <optgroup label="Parents">
                                                        <option value="parent-1">Mr. & Mrs. Johnson (Alex's Parents)</option>
                                                        <option value="parent-2">Mr. & Mrs. Wilson (Emily's Parents)</option>
                                                        <option value="parent-3">Mr. & Mrs. Thompson (Ryan's Parents)</option>
                                                    </optgroup>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3" id="class-recipient-container" style="display: none;">
                                                <label class="form-label">Class/Grade</label>
                                                <select class="form-select" name="class_recipient">
                                                    <option value="">Select Class/Grade</option>
                                                    <option value="grade-1">Grade 1</option>
                                                    <option value="grade-2">Grade 2</option>
                                                    <option value="grade-3">Grade 3</option>
                                                    <option value="grade-4">Grade 4</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Subject</label>
                                                <input type="text" class="form-control" name="subject" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Message</label>
                                                <textarea class="form-control" name="message" rows="5" required></textarea>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once('includes/admin_footer.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Initialize charts if on dashboard
if ('<?php echo $section; ?>' === 'dashboard' || '<?php echo $section; ?>' === '') {
    // Attendance chart
    const ctx = document.getElementById('attendanceChart').getContext('2d');
    const attendanceChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6'],
            datasets: [
                {
                    label: 'Present',
                    data: [
                        <?php 
                            foreach ($attendanceSummary['student_summary'] as $grade => $summary) {
                                echo $summary['present_count'] . ', ';
                            }
                        ?>
                    ],
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Absent',
                    data: [
                        <?php 
                            foreach ($attendanceSummary['student_summary'] as $grade => $summary) {
                                echo $summary['absent_count'] . ', ';
                            }
                        ?>
                    ],
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Initialize exams charts if on exams section
if ('<?php echo $section; ?>' === 'exams') {
    // Only initialize if charts exist in the DOM
    const resultChartElement = document.getElementById('resultChart');
    const gradeChartElement = document.getElementById('gradeChart');
    
    if (resultChartElement) {
        const resultChart = new Chart(resultChartElement, {
            type: 'bar',
            data: {
                labels: ['Excellent', 'Good', 'Average', 'Needs Improvement', 'Failed'],
                datasets: [{
                    label: 'Number of Students',
                    data: [15, 25, 12, 8, 5],
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.6)',
                        'rgba(54, 162, 235, 0.6)',
                        'rgba(255, 206, 86, 0.6)',
                        'rgba(255, 159, 64, 0.6)',
                        'rgba(255, 99, 132, 0.6)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
    
    if (gradeChartElement) {
        const gradeChart = new Chart(gradeChartElement, {
            type: 'pie',
            data: {
                labels: ['A', 'B', 'C', 'D', 'F'],
                datasets: [{
                    data: [30, 25, 20, 15, 10],
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.6)',
                        'rgba(54, 162, 235, 0.6)',
                        'rgba(255, 206, 86, 0.6)',
                        'rgba(255, 159, 64, 0.6)',
                        'rgba(255, 99, 132, 0.6)'
                    ],
                    borderWidth: 1
                }]
            }
        });
    }
}

// Initialize finance charts if on finance section
if ('<?php echo $section; ?>' === 'finance') {
    const expenseChartElement = document.getElementById('expenseChart');
    
    if (expenseChartElement) {
        const expenseChart = new Chart(expenseChartElement, {
            type: 'doughnut',
            data: {
                labels: ['Utilities', 'Supplies', 'Maintenance', 'Equipment', 'Salary', 'Transportation'],
                datasets: [{
                    data: [2300, 1750, 800, 3200, 4000, 450],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.6)',
                        'rgba(54, 162, 235, 0.6)',
                        'rgba(255, 206, 86, 0.6)',
                        'rgba(75, 192, 192, 0.6)',
                        'rgba(153, 102, 255, 0.6)',
                        'rgba(255, 159, 64, 0.6)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right',
                    }
                }
            }
        });
    }
}

// Handle attendance type change
if ('<?php echo $section; ?>' === 'attendance') {
    const attendanceTypeSelect = document.getElementById('attendanceType');
    const idLabel = document.getElementById('id-label');
    const idInput = document.getElementById('attendanceId');

    if (attendanceTypeSelect && idLabel && idInput) {
        attendanceTypeSelect.addEventListener('change', function() {
            const selectedType = this.value;
            
            if (selectedType === 'student') {
                idLabel.textContent = 'Student ID';
                idInput.name = 'student_id';
            } else if (selectedType === 'teacher') {
                idLabel.textContent = 'Teacher ID';
                idInput.name = 'teacher_id';
            }
        });
    }
}

// Handle timetable options if on timetable section
if ('<?php echo $section; ?>' === 'timetable') {
    const printScheduleBtn = document.getElementById('printScheduleBtn');
    if (printScheduleBtn) {
        printScheduleBtn.addEventListener('click', function() {
            window.print();
        });
    }
    
    const detectConflictsBtn = document.getElementById('detectConflictsBtn');
    if (detectConflictsBtn) {
        detectConflictsBtn.addEventListener('click', function() {
            alert('Scanning for scheduling conflicts...');
            // In a real implementation, this would call a backend API to detect conflicts
            setTimeout(() => {
                alert('Scan complete. 3 conflicts detected.');
            }, 1500);
        });
    }
}

// Handle communication recipient type change if on communication section
if ('<?php echo $section; ?>' === 'communication') {
    const recipientTypeSelect = document.getElementById('recipient-type');
    const individualRecipientContainer = document.getElementById('individual-recipient-container');
    const classRecipientContainer = document.getElementById('class-recipient-container');
    
    if (recipientTypeSelect && individualRecipientContainer && classRecipientContainer) {
        recipientTypeSelect.addEventListener('change', function() {
            const selectedType = this.value;
            
            individualRecipientContainer.style.display = (selectedType === 'individual') ? 'block' : 'none';
            classRecipientContainer.style.display = (selectedType === 'class') ? 'block' : 'none';
        });
    }
    
    // Handle document access level change
    const accessLevelSelect = document.querySelector('select[name="access_level"]');
    const classAccessContainer = document.getElementById('class-access-container');
    
    if (accessLevelSelect && classAccessContainer) {
        accessLevelSelect.addEventListener('change', function() {
            classAccessContainer.style.display = (this.value === 'Class') ? 'block' : 'none';
        });
    }
}

// SMS character counter
document.addEventListener('DOMContentLoaded', function() {
    const messageTextarea = document.querySelector('textarea[name="message"]');
    const characterCount = document.getElementById('character-count');
    
    if (messageTextarea && characterCount) {
        messageTextarea.addEventListener('input', function() {
            characterCount.textContent = this.value.length;
        });
    }
});

// Add JavaScript to handle student management
document.addEventListener('DOMContentLoaded', function() {
    // View Student
    const viewButtons = document.querySelectorAll('.view-student');
    if (viewButtons) {
        viewButtons.forEach(button => {
            button.addEventListener('click', function() {
                const studentId = this.getAttribute('data-id');
                
                // Make an AJAX request to get student data
                fetch('api/get_student.php?id=' + studentId)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const student = data.data;
                            document.getElementById('view-name').textContent = student.first_name + ' ' + student.last_name;
                            document.getElementById('view-admission').textContent = student.admission_number || 'N/A';
                            document.getElementById('view-gender').textContent = student.gender || 'N/A';
                            document.getElementById('view-dob').textContent = student.date_of_birth || 'N/A';
                            document.getElementById('view-grade').textContent = student.grade_level || 'N/A';
                            document.getElementById('view-class').textContent = student.class_id || 'N/A';
                        } else {
                            alert('Error fetching student details: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while fetching student data');
                    });
            });
        });
    }
    
    // Edit Student
    const editButtons = document.querySelectorAll('.edit-student');
    if (editButtons) {
        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const studentId = this.getAttribute('data-id');
                
                // Make an AJAX request to get student data
                fetch('api/get_student.php?id=' + studentId)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const student = data.data;
                            document.getElementById('edit-student-id').value = student.id || student.student_id;
                            document.getElementById('edit-first-name').value = student.first_name || '';
                            document.getElementById('edit-last-name').value = student.last_name || '';
                            document.getElementById('edit-gender').value = student.gender || '';
                            document.getElementById('edit-dob').value = student.date_of_birth || '';
                            document.getElementById('edit-admission').value = student.admission_number || '';
                            document.getElementById('edit-grade').value = student.grade_level || '';
                            document.getElementById('edit-class').value = student.class_id || '';
                        } else {
                            alert('Error fetching student details: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while fetching student data');
                    });
            });
        });
    }
    
    // Delete Student
    const deleteButtons = document.querySelectorAll('.delete-student');
    if (deleteButtons) {
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const studentId = this.getAttribute('data-id');
                const studentName = this.getAttribute('data-name');
                
                document.getElementById('delete-student-id').value = studentId;
                document.getElementById('delete-student-name').textContent = studentName;
            });
        });
    }
});

// Teacher Management
document.addEventListener('DOMContentLoaded', function() {
    // View Teacher
    const viewTeacherButtons = document.querySelectorAll('.view-teacher');
    if (viewTeacherButtons) {
        viewTeacherButtons.forEach(button => {
            button.addEventListener('click', function() {
                const teacherId = this.getAttribute('data-id');
                
                // Make an AJAX request to get teacher data
                fetch('api/get_teacher.php?id=' + teacherId)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const teacher = data.data;
                            document.getElementById('view-teacher-name').textContent = teacher.first_name + ' ' + teacher.last_name;
                            document.getElementById('view-employee-id').textContent = teacher.employee_id || 'N/A';
                            document.getElementById('view-teacher-gender').textContent = teacher.gender || 'N/A';
                            document.getElementById('view-teacher-dob').textContent = teacher.date_of_birth || 'N/A';
                            document.getElementById('view-hire-date').textContent = teacher.hire_date || 'N/A';
                            document.getElementById('view-subject').textContent = teacher.subject || 'N/A';
                            document.getElementById('view-department').textContent = teacher.department || 'N/A';
                            document.getElementById('view-phone').textContent = teacher.phone || 'N/A';
                            document.getElementById('view-email').textContent = teacher.email || 'N/A';
                            document.getElementById('view-teacher-address').textContent = teacher.address || 'N/A';
                        } else {
                            alert('Error fetching teacher details: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while fetching teacher data');
                    });
            });
        });
    }
    
    // Edit Teacher
    const editTeacherButtons = document.querySelectorAll('.edit-teacher');
    if (editTeacherButtons) {
        editTeacherButtons.forEach(button => {
            button.addEventListener('click', function() {
                const teacherId = this.getAttribute('data-id');
                
                // Make an AJAX request to get teacher data
                fetch('api/get_teacher.php?id=' + teacherId)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const teacher = data.data;
                            document.getElementById('edit-teacher-id').value = teacher.id || teacher.teacher_id;
                            document.getElementById('edit-teacher-first-name').value = teacher.first_name || '';
                            document.getElementById('edit-teacher-last-name').value = teacher.last_name || '';
                            document.getElementById('edit-teacher-gender').value = teacher.gender || '';
                            document.getElementById('edit-teacher-dob').value = teacher.date_of_birth || '';
                            document.getElementById('edit-employee-id').value = teacher.employee_id || '';
                            document.getElementById('edit-hire-date').value = teacher.hire_date || '';
                            document.getElementById('edit-subject').value = teacher.subject || '';
                            document.getElementById('edit-department').value = teacher.department || '';
                            document.getElementById('edit-phone').value = teacher.phone || '';
                            document.getElementById('edit-email').value = teacher.email || '';
                            document.getElementById('edit-teacher-address').value = teacher.address || '';
                        } else {
                            alert('Error fetching teacher details: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while fetching teacher data');
                    });
            });
        });
    }
    
    // Delete Teacher
    const deleteTeacherButtons = document.querySelectorAll('.delete-teacher');
    if (deleteTeacherButtons) {
        deleteTeacherButtons.forEach(button => {
            button.addEventListener('click', function() {
                const teacherId = this.getAttribute('data-id');
                const teacherName = this.getAttribute('data-name');
                
                document.getElementById('delete-teacher-id').value = teacherId;
                document.getElementById('delete-teacher-name').textContent = teacherName;
            });
        });
    }
});

// Staff Management
document.addEventListener('DOMContentLoaded', function() {
    // View Staff
    const viewStaffButtons = document.querySelectorAll('.view-staff');
    if (viewStaffButtons) {
        viewStaffButtons.forEach(button => {
            button.addEventListener('click', function() {
                const staffId = this.getAttribute('data-id');
                
                // Make an AJAX request to get staff data
                fetch('api/get_staff.php?id=' + staffId)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const staff = data.data;
                            document.getElementById('view-staff-name').textContent = staff.first_name + ' ' + staff.last_name;
                            document.getElementById('view-staff-employee-id').textContent = staff.employee_id || 'N/A';
                            document.getElementById('view-staff-gender').textContent = staff.gender || 'N/A';
                            document.getElementById('view-staff-dob').textContent = staff.date_of_birth || 'N/A';
                            document.getElementById('view-staff-hire-date').textContent = staff.hire_date || 'N/A';
                            document.getElementById('view-position').textContent = staff.position || 'N/A';
                            document.getElementById('view-staff-department').textContent = staff.department || 'N/A';
                            document.getElementById('view-staff-phone').textContent = staff.phone || 'N/A';
                            document.getElementById('view-staff-email').textContent = staff.email || 'N/A';
                            document.getElementById('view-staff-address').textContent = staff.address || 'N/A';
                        } else {
                            alert('Error fetching staff details: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while fetching staff data');
                    });
            });
        });
    }
    
    // Edit Staff
    const editStaffButtons = document.querySelectorAll('.edit-staff');
    if (editStaffButtons) {
        editStaffButtons.forEach(button => {
            button.addEventListener('click', function() {
                const staffId = this.getAttribute('data-id');
                
                // Make an AJAX request to get staff data
                fetch('api/get_staff.php?id=' + staffId)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const staff = data.data;
                            document.getElementById('edit-staff-id').value = staff.id || staff.staff_id;
                            document.getElementById('edit-staff-first-name').value = staff.first_name || '';
                            document.getElementById('edit-staff-last-name').value = staff.last_name || '';
                            document.getElementById('edit-staff-gender').value = staff.gender || '';
                            document.getElementById('edit-staff-dob').value = staff.date_of_birth || '';
                            document.getElementById('edit-staff-employee-id').value = staff.employee_id || '';
                            document.getElementById('edit-staff-hire-date').value = staff.hire_date || '';
                            document.getElementById('edit-position').value = staff.position || '';
                            document.getElementById('edit-staff-department').value = staff.department || '';
                            document.getElementById('edit-staff-phone').value = staff.phone || '';
                            document.getElementById('edit-staff-email').value = staff.email || '';
                            document.getElementById('edit-staff-address').value = staff.address || '';
                        } else {
                            alert('Error fetching staff details: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while fetching staff data');
                    });
            });
        });
    }
    
    // Delete Staff
    const deleteStaffButtons = document.querySelectorAll('.delete-staff');
    if (deleteStaffButtons) {
        deleteStaffButtons.forEach(button => {
            button.addEventListener('click', function() {
                const staffId = this.getAttribute('data-id');
                const staffName = this.getAttribute('data-name');
                
                document.getElementById('delete-staff-id').value = staffId;
                document.getElementById('delete-staff-name').textContent = staffName;
            });
        });
    }
});
</script> < ? p h p   e n d i f ;   ? >  
 