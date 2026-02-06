<?php
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

// Get admin information (mock data - replace with actual database query)
$adminInfo = [
    'name' => isset($_SESSION["username"]) ? $_SESSION["username"] : 'Admin User',
    'email' => isset($_SESSION["email"]) ? $_SESSION["email"] : 'admin@example.com',
    'avatar' => 'assets/img/admin-avatar.jpg', // Default path - replace with actual path
    'lastLogin' => date('Y-m-d H:i:s', isset($_SESSION["last_login"]) ? $_SESSION["last_login"] : time()),
    'role' => 'System Administrator'
];

// Mock statistics (replace with actual database queries)
$totalUsers = 245;
$newUsersToday = 12;
$pendingApplicants = 38;
$systemAlerts = 5;

// Include header file
$includesPath = __DIR__ . '/includes/';
$pageTitle = "Admin Dashboard";
require_once($includesPath . 'admin_header.php');
?>

<div class="container-fluid mt-4">
    <!-- Welcome Banner with Typing Effect -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card dashboard-card">
                <div class="card-body">
                    <h2 class="welcome-text">Welcome back, <span class="typed-text" id="adminName" data-name="<?php echo htmlspecialchars($adminInfo['name']); ?>"></span></h2>
                    <p class="text-muted">Today is <?php echo date('l, F j, Y'); ?> | Last login: <?php echo $adminInfo['lastLogin']; ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Left sidebar with admin profile -->
        <div class="col-md-3">
            <div class="admin-profile mb-4">
                <div class="text-center mb-3">
                    <img src="<?php echo $adminInfo['avatar']; ?>" alt="Admin Profile" class="profile-img mb-3" onerror="this.src='https://via.placeholder.com/100x100'">
                    <h4><?php echo $adminInfo['name']; ?></h4>
                    <p><?php echo $adminInfo['role']; ?></p>
                </div>
                <div class="d-grid gap-2">
                    <a href="profile.php" class="btn btn-light btn-sm"><i class="fas fa-user-cog"></i> Edit Profile</a>
                    <a href="logout.php" class="btn btn-outline-light btn-sm"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
            
            <!-- Quick Navigation Menu -->
            <div class="card dashboard-card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-compass"></i> Quick Navigation</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="admin_dashboard.php" class="list-group-item list-group-item-action active">
                            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                        </a>
                        <a href="manage_users.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-users me-2"></i> User Management
                        </a>
                        <a href="manage_students.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-user-graduate me-2"></i> Student Management
                        </a>
                        <a href="school_dashboard.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-school me-2"></i> School Dashboard
                        </a>
                        <a href="manage_reports.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-file-alt me-2"></i> School Reports
                        </a>
                        <a href="view_applicants.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-user-graduate me-2"></i> Applicants
                        </a>
                        <a href="reports.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-chart-bar me-2"></i> Reports
                        </a>
                        <a href="settings.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-cogs me-2"></i> System Settings
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Task Management Widget -->
            <div class="card dashboard-card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-tasks"></i> Task Management</h5>
                </div>
                <div class="card-body">
                    <form id="taskForm" class="mb-3">
                        <div class="input-group">
                            <input type="text" id="newTask" class="form-control" placeholder="Add new task...">
                            <button class="btn btn-info" type="submit">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </form>
                    <ul id="taskList" class="task-list">
                        <!-- Tasks will be added here dynamically -->
                    </ul>
                </div>
            </div>
            
            <!-- System Alerts -->
            <div class="card dashboard-card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-bell"></i> System Alerts</h5>
                </div>
                <div class="card-body">
                    <div class="system-alert">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong><?php echo $systemAlerts; ?> alerts</strong> require your attention
                        </div>
                    </div>
                    <div class="d-grid">
                        <a href="alerts.php" class="btn btn-outline-danger">View All Alerts</a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Content Area -->
        <div class="col-md-9">
            <!-- Statistics Row -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card stats-card primary dashboard-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted">Total Users</h6>
                                    <div class="stat-number"><?php echo $totalUsers; ?></div>
                                </div>
                                <div class="dashboard-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                            <div class="progress mt-3" style="height: 5px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: 75%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card success dashboard-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted">New Today</h6>
                                    <div class="stat-number"><?php echo $newUsersToday; ?></div>
                                </div>
                                <div class="dashboard-icon">
                                    <i class="fas fa-user-plus"></i>
                                </div>
                            </div>
                            <div class="progress mt-3" style="height: 5px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: 40%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card warning dashboard-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted">Pending</h6>
                                    <div class="stat-number"><?php echo $pendingApplicants; ?></div>
                                </div>
                                <div class="dashboard-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                            <div class="progress mt-3" style="height: 5px;">
                                <div class="progress-bar bg-warning" role="progressbar" style="width: 60%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card danger dashboard-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted">Alerts</h6>
                                    <div class="stat-number"><?php echo $systemAlerts; ?></div>
                                </div>
                                <div class="dashboard-icon">
                                    <i class="fas fa-bell"></i>
                                </div>
                            </div>
                            <div class="progress mt-3" style="height: 5px;">
                                <div class="progress-bar bg-danger" role="progressbar" style="width: 25%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Charts Row -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="card dashboard-card">
                        <div class="card-header">
                            <h5 class="page-title">User Activity</h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="activityChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card dashboard-card">
                        <div class="card-header">
                            <h5 class="page-title">User Types</h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="userTypesChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Feature Cards Row -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="feature-card bg-gradient-primary" data-link="manage_users.php">
                        <div class="feature-card-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h4 class="feature-card-title">User Management</h4>
                        <p class="feature-card-text">Manage system users and their permissions</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="feature-card bg-gradient-success" data-link="manage_students.php">
                        <div class="feature-card-icon">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <h4 class="feature-card-title">Student Management</h4>
                        <p class="feature-card-text">Manage student records and information</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="feature-card bg-gradient-info" data-link="manage_reports.php">
                        <div class="feature-card-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <h4 class="feature-card-title">School Reports</h4>
                        <p class="feature-card-text">Generate and manage student academic reports</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="feature-card bg-gradient-warning" data-link="file_upload.php">
                        <div class="feature-card-icon">
                            <i class="fas fa-file-upload"></i>
                        </div>
                        <h4 class="feature-card-title">File Management</h4>
                        <p class="feature-card-text">Upload and manage system files</p>
                    </div>
                </div>
            </div>
            
            <!-- School Dashboard Row -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card dashboard-card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-school me-2"></i>School Management Dashboard</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="feature-card bg-gradient-danger" data-link="school_dashboard.php">
                                        <div class="feature-card-icon">
                                            <i class="fas fa-school"></i>
                                        </div>
                                        <h4 class="feature-card-title">School Dashboard</h4>
                                        <p class="feature-card-text">Manage all school activities</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="feature-card bg-gradient-info" data-link="school_dashboard.php?section=attendance">
                                        <div class="feature-card-icon">
                                            <i class="fas fa-clipboard-check"></i>
                                        </div>
                                        <h4 class="feature-card-title">Attendance</h4>
                                        <p class="feature-card-text">Mark and track student & teacher attendance</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="feature-card bg-gradient-warning" data-link="school_dashboard.php?section=cards">
                                        <div class="feature-card-icon">
                                            <i class="fas fa-id-card"></i>
                                        </div>
                                        <h4 class="feature-card-title">ID Cards</h4>
                                        <p class="feature-card-text">Generate student identification cards</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="feature-card bg-gradient-success" data-link="school_dashboard.php?section=sms">
                                        <div class="feature-card-icon">
                                            <i class="fas fa-sms"></i>
                                        </div>
                                        <h4 class="feature-card-title">SMS Services</h4>
                                        <p class="feature-card-text">Send messages to students and parents</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Main Cards Row -->
            <div class="row">
                <!-- Student Management Card -->
                <div class="col-md-6">
                    <div class="card dashboard-card menu-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-success text-white p-3 rounded me-3">
                                    <i class="fas fa-user-graduate fa-2x"></i>
                                </div>
                                <div>
                                    <h4 class="card-title">Student Management</h4>
                                    <p class="card-text text-muted">Manage student records and information</p>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-3">
                                <a href="manage_students.php" class="btn btn-success">
                                    <i class="fas fa-eye me-1"></i> View Students
                                </a>
                                <a href="add_student.php" class="btn btn-outline-success">
                                    <i class="fas fa-plus me-1"></i> Add Student
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Applicant Management Card -->
                <div class="col-md-6">
                    <div class="card dashboard-card menu-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-primary text-white p-3 rounded me-3">
                                    <i class="fas fa-user-graduate fa-2x"></i>
                                </div>
                                <div>
                                    <h4 class="card-title">Applicant Management</h4>
                                    <p class="card-text text-muted">Manage applicant records and their documents</p>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-3">
                                <a href="view_applicants.php" class="btn btn-primary">
                                    <i class="fas fa-eye me-1"></i> View Applicants
                                </a>
                                <a href="export_applicants.php" class="btn btn-outline-primary">
                                    <i class="fas fa-file-export me-1"></i> Export Data
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- File Upload Section -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card dashboard-card">
                        <div class="card-header">
                            <h5 class="page-title">File Management</h5>
                        </div>
                        <div class="card-body">
                            <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#fileUploadModal">
                                <i class="fas fa-upload me-2"></i>Upload New File
                            </button>
                            
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>File Name</th>
                                            <th>Type</th>
                                            <th>Size</th>
                                            <th>Uploaded</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="filesList">
                                        <!-- Files will be loaded dynamically via AJAX -->
                                        <tr>
                                            <td colspan="5" class="text-center">Loading files...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Calendar and Recent Activity Row -->
            <div class="row mt-4">
                <div class="col-md-5">
                    <div class="card dashboard-card">
                        <div class="card-header">
                            <h5 class="page-title">Calendar</h5>
                        </div>
                        <div class="card-body">
                            <div id="adminCalendar"></div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-7">
                    <div class="card dashboard-card">
                        <div class="card-header">
                            <h5 class="page-title">Recent Activity</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="activity-item p-3">
                                <span class="activity-dot dot-success"></span>
                                <strong>John Doe</strong> created a new account
                                <small class="text-muted float-end">2 mins ago</small>
                            </div>
                            <div class="activity-item p-3">
                                <span class="activity-dot dot-info"></span>
                                <strong>Sarah Smith</strong> updated applicant status
                                <small class="text-muted float-end">45 mins ago</small>
                            </div>
                            <div class="activity-item p-3">
                                <span class="activity-dot dot-warning"></span>
                                <strong>System</strong> backup completed
                                <small class="text-muted float-end">1 hour ago</small>
                            </div>
                            <div class="activity-item p-3">
                                <span class="activity-dot dot-danger"></span>
                                <strong>Admin</strong> deleted user account
                                <small class="text-muted float-end">2 hours ago</small>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="activity_log.php" class="btn btn-sm btn-outline-primary w-100">View All Activity</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- File Upload Modal -->
<div class="modal fade" id="fileUploadModal" tabindex="-1" aria-labelledby="fileUploadModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fileUploadModalLabel">Upload New File</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="fileUploadForm" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="fileUpload" class="form-label">Choose File</label>
                        <input class="form-control" type="file" id="fileUpload" name="file" required>
                        <div class="form-text">Max file size: 50MB. Allowed types: images, PDFs, Word docs, videos</div>
                    </div>
                    <div class="mb-3">
                        <label for="fileTitle" class="form-label">File Title</label>
                        <input type="text" class="form-control" id="fileTitle" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="fileDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="fileDescription" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="isPublic" name="is_public">
                        <label class="form-check-label" for="isPublic">Make file publicly visible</label>
                    </div>
                    <div id="uploadFeedback" class="mt-3"></div>
                    <button type="submit" class="btn btn-primary">Upload File</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Dark Mode Toggle -->
<div class="dark-mode-toggle" id="darkModeToggle">
    <i class="fas fa-moon"></i>
</div>

<script>
document.getElementById('taskForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const newTask = document.getElementById('newTask').value;
    if (newTask) {
        fetch('add_task.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ task: newTask })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const taskList = document.getElementById('taskList');
                const newTaskItem = document.createElement('li');
                newTaskItem.textContent = newTask;
                taskList.appendChild(newTaskItem);
                document.getElementById('newTask').value = '';
            } else {
                alert('Failed to add task');
            }
        })
        .catch(error => console.error('Error:', error));
    }
});

document.getElementById('profileForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch('update_profile.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Profile updated successfully');
        } else {
            alert('Failed to update profile');
        }
    })
    .catch(error => console.error('Error:', error));
});

// Make all feature cards clickable
document.addEventListener('DOMContentLoaded', function() {
    const featureCards = document.querySelectorAll('.feature-card');
    featureCards.forEach(card => {
        card.addEventListener('click', function() {
            const link = this.getAttribute('data-link');
            if (link) {
                window.location.href = link;
            }
        });
        
        // Add cursor pointer to show it's clickable
        card.style.cursor = 'pointer';
    });
});
</script>

<?php require_once($includesPath . 'admin_footer.php'); ?>