<?php
// Initialize the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Include database connection
require_once "config/db_connect.php";

// Check if ID parameter is set
if (!isset($_GET["id"])) {
    header("location: manage_reports.php");
    exit;
}

// Fetch report data
$sql = "SELECT * FROM student_reports WHERE id = ?";
if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("i", $_GET["id"]);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows == 1) {
            $report = $result->fetch_assoc();
        } else {
            header("location: manage_reports.php");
            exit;
        }
    }
    $stmt->close();
}

// Include header
$pageTitle = "View Student Report";
require_once "includes/admin_header.php";
?>

<div class="container mt-4">
    <div class="card">
        <div class="card-body">
            <!-- Report Header -->
            <div class="text-center mb-4">
                <div class="row">
                    <div class="col-md-3">
                        <img src="assets/img/rwanda_coat_of_arms.png" alt="Rwanda Coat of Arms" class="img-fluid" style="max-height: 100px;">
                    </div>
                    <div class="col-md-6">
                        <h4 class="mb-0">REPUBLIC OF RWANDA</h4>
                        <h5>MINISTRY OF EDUCATION</h5>
                        <h4>GROUPE SCOLAIRE SAINT BONAVENTURE</h4>
                        <h4>BUHOKORO</h4>
                        <h3 class="mt-3">STUDENT ANNUAL REPORT</h3>
                    </div>
                    <div class="col-md-3">
                        <img src="assets/img/school_logo.png" alt="School Logo" class="img-fluid" style="max-height: 100px;">
                    </div>
                </div>
            </div>

            <!-- Student Information -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <p><strong>Student Names:</strong> <?php echo htmlspecialchars($report['student_name']); ?></p>
                    <p><strong>Registration Number:</strong> <?php echo htmlspecialchars($report['registration_number']); ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Academic Year:</strong> <?php echo htmlspecialchars($report['academic_year']); ?></p>
                    <p><strong>Class:</strong> <?php echo htmlspecialchars($report['class_name']); ?></p>
                </div>
            </div>

            <!-- Grades Table -->
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th rowspan="2">Subject</th>
                            <th colspan="2">Maxima</th>
                            <th colspan="2">1st Term</th>
                            <th colspan="2">2nd Term</th>
                            <th colspan="2">3rd Term</th>
                            <th colspan="3">Annual Total</th>
                            <th>2nd</th>
                        </tr>
                        <tr>
                            <th>TS</th>
                            <th>EX</th>
                            <th>TS</th>
                            <th>EX</th>
                            <th>TS</th>
                            <th>EX</th>
                            <th>TS</th>
                            <th>EX</th>
                            <th>TOT</th>
                            <th>%</th>
                            <th>GR</th>
                            <th>Sitting</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Conduct -->
                        <tr>
                            <td>Conduct</td>
                            <td colspan="11"><?php echo htmlspecialchars($report['conduct_score']); ?></td>
                            <td>/120</td>
                            <td><?php echo number_format(($report['conduct_score'] / 120) * 100, 1); ?>%</td>
                        </tr>

                        <!-- Core Subjects -->
                        <tr class="table-secondary">
                            <td colspan="14"><strong>Core Subjects</strong></td>
                        </tr>
                        <?php
                        $core_subjects = [
                            'Kinyarwanda' => $report['kinyarwanda_score'],
                            'Kiswahili' => $report['kiswahili_score'],
                            'Literature' => $report['literature_score'],
                            'Entrepreneurship' => $report['entrepreneurship_score'],
                            'GSC' => $report['gsc_score']
                        ];

                        foreach ($core_subjects as $subject => $score) {
                            echo "<tr>";
                            echo "<td>$subject</td>";
                            echo "<td>70</td><td>70</td>"; // Maxima
                            echo "<td colspan='2'>$score</td>"; // 1st Term
                            echo "<td colspan='2'>-</td>"; // 2nd Term
                            echo "<td colspan='2'>-</td>"; // 3rd Term
                            echo "<td>$score</td>"; // Total
                            echo "<td>" . number_format(($score / 140) * 100, 1) . "%</td>"; // Percentage
                            echo "<td>" . getGrade($score) . "</td>"; // Grade
                            echo "<td>-</td>"; // 2nd Sitting
                            echo "</tr>";
                        }
                        ?>

                        <!-- Non-Core Subjects -->
                        <tr class="table-secondary">
                            <td colspan="14"><strong>Non-Core Subjects</strong></td>
                        </tr>
                        <?php
                        $non_core_subjects = [
                            'English' => $report['english_score'],
                            'French' => $report['french_score'],
                            'ICT' => $report['ict_score'],
                            'Physical Education' => $report['physical_education_score'],
                            'Religion' => $report['religion_score']
                        ];

                        foreach ($non_core_subjects as $subject => $score) {
                            echo "<tr>";
                            echo "<td>$subject</td>";
                            echo "<td>30</td><td>30</td>"; // Maxima
                            echo "<td colspan='2'>$score</td>"; // 1st Term
                            echo "<td colspan='2'>-</td>"; // 2nd Term
                            echo "<td colspan='2'>-</td>"; // 3rd Term
                            echo "<td>$score</td>"; // Total
                            echo "<td>" . number_format(($score / 60) * 100, 1) . "%</td>"; // Percentage
                            echo "<td>" . getGrade($score) . "</td>"; // Grade
                            echo "<td>-</td>"; // 2nd Sitting
                            echo "</tr>";
                        }
                        ?>

                        <!-- Total Row -->
                        <tr class="table-primary">
                            <td>Total</td>
                            <td colspan="8"></td>
                            <td><?php echo htmlspecialchars($report['total_score']); ?></td>
                            <td><?php echo number_format($report['percentage'], 1); ?>%</td>
                            <td colspan="2"><?php echo htmlspecialchars($report['position']); ?> out of 51</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Teacher's Remarks -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <h5>Class Teacher's Remarks:</h5>
                    <p><?php echo nl2br(htmlspecialchars($report['class_teacher_remarks'])); ?></p>
                </div>
            </div>

            <!-- Signatures -->
            <div class="row mt-4">
                <div class="col-md-4">
                    <p><strong>Class Teacher's Signature:</strong> _________________</p>
                </div>
                <div class="col-md-4">
                    <p><strong>Parent's Signature:</strong> _________________</p>
                </div>
                <div class="col-md-4">
                    <p><strong>Head Teacher's Signature:</strong> _________________</p>
                </div>
            </div>

            <!-- Grading Scale -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <h5>Grading Scale:</h5>
                    <table class="table table-sm table-bordered w-auto">
                        <tr>
                            <th>Grade</th>
                            <th>A</th>
                            <th>B</th>
                            <th>C</th>
                            <th>D</th>
                            <th>E</th>
                            <th>S</th>
                            <th>F</th>
                        </tr>
                        <tr>
                            <td>Points (Core Subjects)</td>
                            <td>14</td>
                            <td>13</td>
                            <td>12</td>
                            <td>11</td>
                            <td>10</td>
                            <td>09</td>
                            <td>00</td>
                        </tr>
                        <tr>
                            <td>Points (Non-Core Subjects)</td>
                            <td colspan="7">% : 90 - 100 | Grade : 5 | Points : 1</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <a href="print_report.php?id=<?php echo $_GET['id']; ?>" class="btn btn-primary">
                        <i class="fas fa-print"></i> Print Report
                    </a>
                    <a href="edit_report.php?id=<?php echo $_GET['id']; ?>" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit Report
                    </a>
                    <a href="manage_reports.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Reports
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Helper function to calculate grade
function getGrade($score) {
    if ($score >= 90) return 'A';
    if ($score >= 80) return 'B';
    if ($score >= 70) return 'C';
    if ($score >= 60) return 'D';
    if ($score >= 50) return 'E';
    if ($score >= 40) return 'S';
    return 'F';
}

require_once "includes/admin_footer.php";
?> 