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

// Include database configuration
require_once "config/db_connect.php";

// Get export format from request
$format = isset($_GET['format']) ? $_GET['format'] : '';

// Fetch all applicants data
$sql = "SELECT 
            student_name,
            registration_number,
            academic_year,
            class_name,
            kinyarwanda_score,
            kiswahili_score,
            literature_score,
            entrepreneurship_score,
            gsc_score,
            english_score,
            french_score,
            ict_score,
            physical_education_score,
            religion_score,
            total_score,
            percentage,
            position,
            class_teacher_remarks,
            created_at
        FROM student_reports 
        ORDER BY created_at DESC";

$result = $mysqli->query($sql);

if ($result) {
    $applicants = [];
    while ($row = $result->fetch_assoc()) {
        $applicants[] = $row;
    }

    if ($format === 'csv') {
        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="student_reports_' . date('Y-m-d') . '.csv"');
        
        // Create CSV file
        $output = fopen('php://output', 'w');
        
        // Add UTF-8 BOM for proper Excel display
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Add headers
        if (!empty($applicants)) {
            // Customize header labels
            $headers = [
                'Student Name',
                'Registration Number',
                'Academic Year',
                'Class',
                'Kinyarwanda',
                'Kiswahili',
                'Literature',
                'Entrepreneurship',
                'GSC',
                'English',
                'French',
                'ICT',
                'Physical Education',
                'Religion',
                'Total Score',
                'Percentage',
                'Position',
                'Teacher Remarks',
                'Report Date'
            ];
            fputcsv($output, $headers);
            
            // Add data
            foreach ($applicants as $row) {
                fputcsv($output, $row);
            }
        }
        
        fclose($output);
        exit();
    } else {
        // Show export options page
        $pageTitle = "Export Student Reports";
        require_once "includes/admin_header.php";
        ?>
        
        <div class="container mt-4">
            <div class="row">
                <div class="col-md-8 offset-md-2">
                    <div class="card dashboard-card">
                        <div class="card-header">
                            <h4 class="mb-0"><i class="fas fa-file-export"></i> Export Student Reports</h4>
                        </div>
                        <div class="card-body">
                            <p>Choose a format to export student reports data:</p>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100">
                                        <div class="card-body text-center">
                                            <i class="fas fa-file-csv fa-3x mb-3 text-primary"></i>
                                            <h5 class="card-title">CSV Format</h5>
                                            <p class="card-text">Export data in CSV format, compatible with Excel and other spreadsheet software.</p>
                                            <a href="export_applicants.php?format=csv" class="btn btn-primary">
                                                <i class="fas fa-download"></i> Export as CSV
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100">
                                        <div class="card-body text-center">
                                            <i class="fas fa-file-excel fa-3x mb-3 text-secondary"></i>
                                            <h5 class="card-title">Excel Format</h5>
                                            <p class="card-text">Excel export coming soon! Please use CSV format for now.</p>
                                            <button class="btn btn-secondary" disabled>
                                                <i class="fas fa-clock"></i> Coming Soon
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <a href="manage_reports.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to Reports
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <?php
        require_once "includes/admin_footer.php";
    }
} else {
    // Handle database error
    $_SESSION['error'] = "Error fetching reports data: " . $mysqli->error;
    header("location: manage_reports.php");
    exit();
}
?> 