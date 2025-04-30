<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Check if the user is an admin
if(!isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] !== true) {
    // Not an admin, redirect to access denied page
    header("location: access_denied.php");
    exit;
}

// Include config file
require_once "config/db_connect.php";

// Initialize variables
$report_id = $_GET['id'] ?? null;
$report = null;
$error = '';

// Fetch report data if ID is provided
if ($report_id) {
    $sql = "SELECT * FROM student_reports WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $report_id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows == 1) {
                $report = $result->fetch_assoc();
            } else {
                $error = "Report not found.";
            }
        } else {
            $error = "Error fetching report data.";
        }
        $stmt->close();
    }
} else {
    $error = "No report ID provided.";
}

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize input
    $student_name = trim($_POST["student_name"]);
    $registration_number = trim($_POST["registration_number"]);
    $academic_year = trim($_POST["academic_year"]);
    $class_name = trim($_POST["class_name"]);
    $conduct_score = trim($_POST["conduct_score"]);
    
    // Core subjects
    $kinyarwanda_score = trim($_POST["kinyarwanda_score"]);
    $kiswahili_score = trim($_POST["kiswahili_score"]);
    $literature_score = trim($_POST["literature_score"]);
    $entrepreneurship_score = trim($_POST["entrepreneurship_score"]);
    $gsc_score = trim($_POST["gsc_score"]);
    
    // Non-core subjects
    $english_score = trim($_POST["english_score"]);
    $french_score = trim($_POST["french_score"]);
    $ict_score = trim($_POST["ict_score"]);
    $physical_education_score = trim($_POST["physical_education_score"]);
    $religion_score = trim($_POST["religion_score"]);
    
    // Additional fields
    $class_teacher_remarks = trim($_POST["class_teacher_remarks"]);
    $parent_signature = isset($_POST["parent_signature"]) ? 1 : 0;
    
    // Calculate total score and percentage
    $total_score = $kinyarwanda_score + $kiswahili_score + $literature_score + 
                  $entrepreneurship_score + $gsc_score + $english_score + 
                  $french_score + $ict_score + $physical_education_score + 
                  $religion_score;
    $percentage = ($total_score / 1000) * 100;
    
    // Update the report in the database
    $sql = "UPDATE student_reports SET 
            student_name = ?, 
            registration_number = ?, 
            academic_year = ?, 
            class_name = ?, 
            conduct_score = ?,
            kinyarwanda_score = ?,
            kiswahili_score = ?,
            literature_score = ?,
            entrepreneurship_score = ?,
            gsc_score = ?,
            english_score = ?,
            french_score = ?,
            ict_score = ?,
            physical_education_score = ?,
            religion_score = ?,
            total_score = ?,
            percentage = ?,
            class_teacher_remarks = ?,
            parent_signature = ?
            WHERE id = ?";
    
    if ($stmt = $mysqli->prepare($sql)) {
        // Convert numeric values to float
        $conduct_score = (float)$conduct_score;
        $kinyarwanda_score = (float)$kinyarwanda_score;
        $kiswahili_score = (float)$kiswahili_score;
        $literature_score = (float)$literature_score;
        $entrepreneurship_score = (float)$entrepreneurship_score;
        $gsc_score = (float)$gsc_score;
        $english_score = (float)$english_score;
        $french_score = (float)$french_score;
        $ict_score = (float)$ict_score;
        $physical_education_score = (float)$physical_education_score;
        $religion_score = (float)$religion_score;
        $total_score = (float)$total_score;
        $percentage = (float)$percentage;
        $parent_signature = (int)$parent_signature;
        $report_id = (int)$report_id;
        
        // Count the number of parameters: 20 total
        // 4 strings (ssss) + 1 integer (i) + 13 doubles (ddddddddddddd) + 1 string (s) + 1 integer (i)
        $stmt->bind_param("ssssidddddddddddddsi", 
            $student_name, $registration_number, $academic_year, $class_name, 
            $conduct_score, $kinyarwanda_score, $kiswahili_score, 
            $literature_score, $entrepreneurship_score, $gsc_score, 
            $english_score, $french_score, $ict_score, 
            $physical_education_score, $religion_score, $total_score, 
            $percentage, $class_teacher_remarks, $parent_signature, $report_id);
        
        if ($stmt->execute()) {
            // Report updated successfully
            header("location: manage_reports.php?success=1");
            exit();
        } else {
            $error = "Error updating report. Please try again.";
        }
        $stmt->close();
    }
}

// Include header file
$includesPath = __DIR__ . '/includes/';
$pageTitle = "Edit Student Report";
require_once($includesPath . 'admin_header.php');
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Edit Student Report</h4>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($report): ?>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $report_id); ?>" method="post">
                        <div class="row">
                            <!-- Student Information -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Student Name</label>
                                <input type="text" name="student_name" class="form-control" value="<?php echo $report['student_name']; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Registration Number</label>
                                <input type="text" name="registration_number" class="form-control" value="<?php echo $report['registration_number']; ?>" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Academic Year</label>
                                <input type="text" name="academic_year" class="form-control" value="<?php echo $report['academic_year']; ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Class Name</label>
                                <input type="text" name="class_name" class="form-control" value="<?php echo $report['class_name']; ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Conduct Score</label>
                                <input type="number" name="conduct_score" class="form-control" value="<?php echo $report['conduct_score']; ?>" min="0" max="100" required>
                            </div>
                        </div>
                        
                        <hr>
                        <h5 class="mb-3">Core Subjects</h5>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Kinyarwanda</label>
                                <input type="number" name="kinyarwanda_score" class="form-control" value="<?php echo $report['kinyarwanda_score']; ?>" min="0" max="100" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Kiswahili</label>
                                <input type="number" name="kiswahili_score" class="form-control" value="<?php echo $report['kiswahili_score']; ?>" min="0" max="100" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Literature</label>
                                <input type="number" name="literature_score" class="form-control" value="<?php echo $report['literature_score']; ?>" min="0" max="100" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Entrepreneurship</label>
                                <input type="number" name="entrepreneurship_score" class="form-control" value="<?php echo $report['entrepreneurship_score']; ?>" min="0" max="100" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">GSC</label>
                                <input type="number" name="gsc_score" class="form-control" value="<?php echo $report['gsc_score']; ?>" min="0" max="100" required>
                            </div>
                        </div>
                        
                        <hr>
                        <h5 class="mb-3">Non-Core Subjects</h5>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">English</label>
                                <input type="number" name="english_score" class="form-control" value="<?php echo $report['english_score']; ?>" min="0" max="100" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">French</label>
                                <input type="number" name="french_score" class="form-control" value="<?php echo $report['french_score']; ?>" min="0" max="100" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">ICT</label>
                                <input type="number" name="ict_score" class="form-control" value="<?php echo $report['ict_score']; ?>" min="0" max="100" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Physical Education</label>
                                <input type="number" name="physical_education_score" class="form-control" value="<?php echo $report['physical_education_score']; ?>" min="0" max="100" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Religion</label>
                                <input type="number" name="religion_score" class="form-control" value="<?php echo $report['religion_score']; ?>" min="0" max="100" required>
                            </div>
                        </div>
                        
                        <hr>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Class Teacher Remarks</label>
                                <textarea name="class_teacher_remarks" class="form-control" rows="3"><?php echo $report['class_teacher_remarks']; ?></textarea>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="parent_signature" class="form-check-input" id="parent_signature" <?php echo $report['parent_signature'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="parent_signature">Parent has signed the report</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">Update Report</button>
                                <a href="manage_reports.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </div>
                    </form>
                    <?php else: ?>
                        <div class="alert alert-warning">No report data found.</div>
                        <a href="manage_reports.php" class="btn btn-secondary">Back to Reports</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once($includesPath . 'admin_footer.php'); ?> 