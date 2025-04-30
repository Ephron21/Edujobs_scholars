<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Include database connection
require_once "config/db_connect.php";

// Define variables and initialize with empty values
$success_msg = $error_msg = "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize input data
    $student_name = trim($_POST["student_name"]);
    $registration_number = trim($_POST["registration_number"]);
    $academic_year = trim($_POST["academic_year"]);
    $class_name = trim($_POST["class_name"]);
    $conduct_score = intval($_POST["conduct_score"]);
    
    // Prepare an insert statement
    $sql = "INSERT INTO student_reports (
        student_name, registration_number, academic_year, class_name, conduct_score,
        kinyarwanda_score, kiswahili_score, literature_score, entrepreneurship_score, gsc_score,
        english_score, french_score, ict_score, physical_education_score, religion_score,
        total_score, percentage, position, class_teacher_remarks
    ) VALUES (
        ?, ?, ?, ?, ?,
        ?, ?, ?, ?, ?,
        ?, ?, ?, ?, ?,
        ?, ?, ?, ?
    )";
    
    if ($stmt = $mysqli->prepare($sql)) {
        // Calculate total score and percentage
        $total_score = array_sum([
            $_POST["kinyarwanda_score"],
            $_POST["kiswahili_score"],
            $_POST["literature_score"],
            $_POST["entrepreneurship_score"],
            $_POST["gsc_score"],
            $_POST["english_score"],
            $_POST["french_score"],
            $_POST["ict_score"],
            $_POST["physical_education_score"],
            $_POST["religion_score"]
        ]);
        
        $percentage = ($total_score / 1000) * 100; // Assuming total possible score is 1000
        
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("ssssiddddddddddddss",
            $student_name,
            $registration_number,
            $academic_year,
            $class_name,
            $conduct_score,
            $_POST["kinyarwanda_score"],
            $_POST["kiswahili_score"],
            $_POST["literature_score"],
            $_POST["entrepreneurship_score"],
            $_POST["gsc_score"],
            $_POST["english_score"],
            $_POST["french_score"],
            $_POST["ict_score"],
            $_POST["physical_education_score"],
            $_POST["religion_score"],
            $total_score,
            $percentage,
            $_POST["position"],
            $_POST["class_teacher_remarks"]
        );
        
        // Attempt to execute the prepared statement
        if ($stmt->execute()) {
            $success_msg = "Student report created successfully!";
        } else {
            $error_msg = "Something went wrong. Please try again later.";
        }
        
        // Close statement
        $stmt->close();
    }
}

// Include header
$pageTitle = "Manage Student Reports";
require_once "includes/admin_header.php";
?>

<div class="container-fluid mt-4">
    <?php 
    // Show success message if report was deleted
    if(isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Success!</strong> The student report has been deleted successfully.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h5 class="card-title">Create Student Report</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($success_msg)): ?>
                        <div class="alert alert-success"><?php echo $success_msg; ?></div>
                    <?php endif; ?>
                    <?php if (!empty($error_msg)): ?>
                        <div class="alert alert-danger"><?php echo $error_msg; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="needs-validation" novalidate>
                        <!-- Student Information -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="student_name">Student Name</label>
                                    <input type="text" class="form-control" id="student_name" name="student_name" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="registration_number">Registration Number</label>
                                    <input type="text" class="form-control" id="registration_number" name="registration_number" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="academic_year">Academic Year</label>
                                    <input type="text" class="form-control" id="academic_year" name="academic_year" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="class_name">Class</label>
                                    <input type="text" class="form-control" id="class_name" name="class_name" required>
                                </div>
                            </div>
                        </div>

                        <!-- Core Subjects -->
                        <h5 class="mb-3">Core Subjects</h5>
                        <div class="row mb-4">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="kinyarwanda_score">Kinyarwanda</label>
                                    <input type="number" step="0.01" class="form-control" id="kinyarwanda_score" name="kinyarwanda_score" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="kiswahili_score">Kiswahili</label>
                                    <input type="number" step="0.01" class="form-control" id="kiswahili_score" name="kiswahili_score" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="literature_score">Literature</label>
                                    <input type="number" step="0.01" class="form-control" id="literature_score" name="literature_score" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="entrepreneurship_score">Entrepreneurship</label>
                                    <input type="number" step="0.01" class="form-control" id="entrepreneurship_score" name="entrepreneurship_score" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="gsc_score">GSC</label>
                                    <input type="number" step="0.01" class="form-control" id="gsc_score" name="gsc_score" required>
                                </div>
                            </div>
                        </div>

                        <!-- Non-Core Subjects -->
                        <h5 class="mb-3">Non-Core Subjects</h5>
                        <div class="row mb-4">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="english_score">English</label>
                                    <input type="number" step="0.01" class="form-control" id="english_score" name="english_score" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="french_score">French</label>
                                    <input type="number" step="0.01" class="form-control" id="french_score" name="french_score" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="ict_score">ICT</label>
                                    <input type="number" step="0.01" class="form-control" id="ict_score" name="ict_score" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="physical_education_score">Physical Education</label>
                                    <input type="number" step="0.01" class="form-control" id="physical_education_score" name="physical_education_score" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="religion_score">Religion</label>
                                    <input type="number" step="0.01" class="form-control" id="religion_score" name="religion_score" required>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="conduct_score">Conduct Score</label>
                                    <input type="number" class="form-control" id="conduct_score" name="conduct_score" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="position">Position</label>
                                    <input type="text" class="form-control" id="position" name="position" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="class_teacher_remarks">Class Teacher's Remarks</label>
                                    <textarea class="form-control" id="class_teacher_remarks" name="class_teacher_remarks" rows="3" required></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">Generate Report</button>
                                <button type="reset" class="btn btn-secondary">Reset Form</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- List of Generated Reports -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h5 class="card-title">Generated Reports</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Registration Number</th>
                                    <th>Class</th>
                                    <th>Academic Year</th>
                                    <th>Total Score</th>
                                    <th>Percentage</th>
                                    <th>Position</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Fetch all reports
                                $sql = "SELECT * FROM student_reports ORDER BY created_at DESC";
                                if ($result = $mysqli->query($sql)) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row['student_name']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['registration_number']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['class_name']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['academic_year']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['total_score']) . "</td>";
                                        echo "<td>" . htmlspecialchars(number_format($row['percentage'], 2)) . "%</td>";
                                        echo "<td>" . htmlspecialchars($row['position']) . "</td>";
                                        echo "<td>";
                                        echo "<a href='view_report.php?id=" . $row['id'] . "' class='btn btn-info btn-sm'>";
                                        echo "<i class='fas fa-eye'></i> View</a>";
                                        echo "<a href='edit_report.php?id=" . $row['id'] . "' class='btn btn-primary btn-sm'>";
                                        echo "<i class='fas fa-edit'></i> Edit</a>";
                                        echo "<a href='delete_report.php?id=" . $row['id'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure?\")'>";
                                        echo "<i class='fas fa-trash'></i> Delete</a>";
                                        echo "</td>";
                                        echo "</tr>";
                                    }
                                    $result->free();
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Form validation
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();

// Auto-calculate total and percentage
document.querySelectorAll('input[type="number"]').forEach(input => {
    input.addEventListener('change', calculateTotal);
});

function calculateTotal() {
    const scores = [
        'kinyarwanda_score', 'kiswahili_score', 'literature_score',
        'entrepreneurship_score', 'gsc_score', 'english_score',
        'french_score', 'ict_score', 'physical_education_score',
        'religion_score'
    ].map(id => parseFloat(document.getElementById(id).value) || 0);
    
    const total = scores.reduce((a, b) => a + b, 0);
    const percentage = (total / 1000) * 100;
    
    // Display total and percentage (you might want to add these fields to your form)
    console.log(`Total: ${total}, Percentage: ${percentage.toFixed(2)}%`);
}
</script>

<?php require_once "includes/admin_footer.php"; ?> 