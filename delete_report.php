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

// Define variables
$report_id = null;
$report = null;
$error = "";

// Check existence of id parameter before processing further
if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){
    $report_id = trim($_GET["id"]);
    
    // First fetch the report to confirm it exists
    $sql = "SELECT student_name, registration_number FROM student_reports WHERE id = ?";
    if($stmt = $mysqli->prepare($sql)){
        $stmt->bind_param("i", $report_id);
        if($stmt->execute()){
            $result = $stmt->get_result();
            if($result->num_rows == 1){
                $report = $result->fetch_assoc();
            } else {
                $error = "No report found with ID " . $report_id;
            }
        } else {
            $error = "Error fetching report details.";
        }
        $stmt->close();
    }
    
    // Process deletion if confirmation is received
    if(isset($_GET["confirm"]) && $_GET["confirm"] === "yes" && $report){
        // Prepare a delete statement
        $sql = "DELETE FROM student_reports WHERE id = ?";
        if($stmt = $mysqli->prepare($sql)){
            $stmt->bind_param("i", $report_id);
            if($stmt->execute()){
                // Records deleted successfully. Redirect to landing page
                header("location: manage_reports.php?deleted=1");
                exit();
            } else {
                $error = "Error deleting the report. Please try again later.";
            }
            $stmt->close();
        }
    }
} else {
    // URL doesn't contain valid id parameter
    header("location: error.php");
    exit();
}

// Include header
$pageTitle = "Delete Report";
require_once "includes/admin_header.php";
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0">Delete Report</h4>
                </div>
                <div class="card-body">
                    <?php if($error): ?>
                        <div class="alert alert-danger">
                            <?php echo $error; ?>
                            <p class="mt-3">
                                <a href="manage_reports.php" class="btn btn-secondary">Back to Reports</a>
                            </p>
                        </div>
                    <?php elseif($report): ?>
                        <div class="alert alert-warning">
                            <h5>Confirm Deletion</h5>
                            <p>Are you sure you want to delete the report for:</p>
                            <ul>
                                <li><strong>Student Name:</strong> <?php echo htmlspecialchars($report['student_name']); ?></li>
                                <li><strong>Registration Number:</strong> <?php echo htmlspecialchars($report['registration_number']); ?></li>
                            </ul>
                            <p class="text-danger"><strong>Warning:</strong> This action cannot be undone!</p>
                            <div class="mt-4">
                                <a href="delete_report.php?id=<?php echo $report_id; ?>&confirm=yes" 
                                   class="btn btn-danger">Yes, Delete Report</a>
                                <a href="manage_reports.php" class="btn btn-secondary">No, Cancel</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once "includes/admin_footer.php"; ?> 