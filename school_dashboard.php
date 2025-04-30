<?php
require_once 'config/config.php';
require_once 'includes/SMSService.php';
require_once 'includes/StudentCardGenerator.php';
require_once 'includes/AttendanceManager.php';

// Check authentication
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Initialize services
$smsService = new SMSService($db);
$cardGenerator = new StudentCardGenerator($db);
$attendanceManager = new AttendanceManager($db);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'mark_attendance':
                if (isset($_POST['student_id'])) {
                    $result = $attendanceManager->markStudentAttendance(
                        $_POST['student_id'],
                        $_POST['status'],
                        $_POST['date'] ?? null,
                        $_POST['remarks'] ?? ''
                    );
                } elseif (isset($_POST['teacher_id'])) {
                    $result = $attendanceManager->markTeacherAttendance(
                        $_POST['teacher_id'],
                        $_POST['status'],
                        $_POST['date'] ?? null,
                        $_POST['remarks'] ?? ''
                    );
                }
                break;

            case 'generate_card':
                if (isset($_POST['student_id'])) {
                    $result = $cardGenerator->generateCard($_POST['student_id']);
                }
                break;

            case 'send_sms':
                if (isset($_POST['recipient_type']) && isset($_POST['recipient_id'])) {
                    $result = $smsService->sendSMS(
                        $_POST['phone_number'],
                        $_POST['message'],
                        $_POST['recipient_type'],
                        $_POST['recipient_id']
                    );
                }
                break;
        }
    }
}

// Get attendance summary
$attendanceSummary = $attendanceManager->getAttendanceSummary();

// Get recent activities
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Management Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <style>
        .dashboard-card {
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            padding: 20px;
        }
        .stat-card {
            background: linear-gradient(45deg, #2193b0, #6dd5ed);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Quick Stats -->
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

        <div class="row mt-4">
            <!-- Attendance Management -->
            <div class="col-md-6">
                <div class="dashboard-card">
                    <h4>Mark Attendance</h4>
                    <form method="POST" class="mt-3">
                        <input type="hidden" name="action" value="mark_attendance">
                        <div class="mb-3">
                            <label class="form-label">Select Type</label>
                            <select class="form-select" name="type" required>
                                <option value="student">Student</option>
                                <option value="teacher">Teacher</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">ID</label>
                            <input type="text" class="form-control" name="id" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" required>
                                <option value="Present">Present</option>
                                <option value="Absent">Absent</option>
                                <option value="Late">Late</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Mark Attendance</button>
                    </form>
                </div>
            </div>

            <!-- Student Card Generation -->
            <div class="col-md-6">
                <div class="dashboard-card">
                    <h4>Generate Student Card</h4>
                    <form method="POST" class="mt-3">
                        <input type="hidden" name="action" value="generate_card">
                        <div class="mb-3">
                            <label class="form-label">Student ID</label>
                            <input type="text" class="form-control" name="student_id" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Generate Card</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <!-- SMS Management -->
            <div class="col-md-6">
                <div class="dashboard-card">
                    <h4>Send SMS</h4>
                    <form method="POST" class="mt-3">
                        <input type="hidden" name="action" value="send_sms">
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
                            <label class="form-label">Message</label>
                            <textarea class="form-control" name="message" rows="3" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Send SMS</button>
                    </form>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="col-md-6">
                <div class="dashboard-card">
                    <h4>Recent Activities</h4>
                    <div class="list-group mt-3">
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

    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 